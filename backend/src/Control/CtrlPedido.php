<?php

namespace Control;

use Entity\Pedido;
use Entity\ItemPedido;
use Entity\Conexao;
use Exception;

class CtrlPedido {
    
    public function manterPedido($dados) {
        // Validação primária para garantir que o cliente e os itens foram enviados
        if (empty($dados['idCliente']) || empty($dados['itens']) || !is_array($dados['itens'])) {
            throw new Exception("O identificador do cliente e a lista de itens (placas) são obrigatórios.");
        }

        // Obtém a ligação única e partilhada
        $conexao = Conexao::getConexao();
        
        try {
            // INÍCIO DA TRANSAÇÃO: A partir daqui, o MySQL "congela" a gravação 
            $conexao->beginTransaction();
            
            // Regra de Negócio: O prazo de entrega padrão é de 7 dias a partir de hoje
            $dataPedido = date('Y-m-d');
            $dataEntregaPrevista = date('Y-m-d', strtotime('+7 days'));
            
            $valorTotalPedido = 0;
            $valorSinal = isset($dados['valorSinal']) ? (float)$dados['valorSinal'] : 0;
            
            // 1. Cria o objeto Pedido temporário (o valor total começa a zero e será atualizado no fim)
            $pedido = new Pedido(
                $dados['idCliente'],
                $dataPedido,
                $dataEntregaPrevista,
                0, 
                $valorSinal,
                'A' // 'A' de Aberto
            );
            
            // Insere a "capa" do pedido e recupera o ID gerado
            $idPedido = $pedido->inserir($conexao);
            
            // 2. Processamento matemático de cada placa (Item)
            foreach ($dados['itens'] as $item) {
                // Busca os preços na base de dados para garantir que o utilizador não forja valores
                $stmtPreco = $conexao->prepare("SELECT preco_material, preco_letra FROM TabelaPrecos WHERE idTabelaPrecos = ?");
                $stmtPreco->execute([$item['idTabelaPrecos']]);
                $precos = $stmtPreco->fetch();
                
                if (!$precos) {
                    throw new Exception("Tabela de preços inválida para um dos itens.");
                }
                
                // Lógica Matemática: Cálculo da Área (em metros quadrados)
                $area = $item['altura'] * $item['largura'];
                $custoMaterial = $area * $precos['preco_material'];
                
                // Lógica Matemática: Contagem de caracteres (ignorando os espaços em branco)
                $fraseSemEspacos = str_replace(' ', '', $item['frase']);
                $quantidadeLetras = strlen($fraseSemEspacos);
                $custoLetras = $quantidadeLetras * $precos['preco_letra'];
                
                // Custo final desta placa específica
                $valorCalculado = $custoMaterial + $custoLetras;
                
                // Adiciona ao total da encomenda
                $valorTotalPedido += $valorCalculado;
                
                // Instancia a Entidade do Item e grava-a na base de dados, associando ao ID do Pedido
                $itemPedido = new ItemPedido(
                    $idPedido,
                    $item['idTabelaPrecos'],
                    $item['idCorPlaca'],
                    $item['idCorLetra'],
                    $item['altura'],
                    $item['largura'],
                    $item['frase'],
                    $valorCalculado
                );
                
                $itemPedido->inserir($conexao);
            }
            
            // 3. Atualiza a "capa" do Pedido com o Valor Total exato após somar todas as placas
            $stmtUpdate = $conexao->prepare("UPDATE Pedido SET valor_total = ? WHERE idPedido = ?");
            $stmtUpdate->execute([$valorTotalPedido, $idPedido]);
            
            // CONFIRMA A TRANSAÇÃO: Se o código chegou até aqui sem erros, grava tudo definitivamente
            $conexao->commit();
            
            return [
                'sucesso' => true,
                'mensagem' => 'Pedido registado com sucesso.',
                'idPedido' => $idPedido,
                'valorTotal' => $valorTotalPedido,
                'dataEntrega' => $dataEntregaPrevista
            ];
            
        } catch (Exception $e) {
            // ROLLBACK: Se alguma placa der erro, desfaz a gravação do pedido e das outras placas
            if ($conexao->inTransaction()) {
                $conexao->rollBack();
            }
            throw new Exception("Erro ao processar o pedido: " . $e->getMessage());
        }
    }

    /*Método para controlar a consulta*/
    public function buscarTodos($filtros = []) {
        try {
            $pedidos = Pedido::listar($filtros);
            return [
                'sucesso' => true,
                'dados' => $pedidos
            ];
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro ao listar os pedidos.");
        }
    }
    
}