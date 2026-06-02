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
            
            // Regra de Negócio: Cálculo do prazo de entrega
            $dataPedido = $dados['dataPedido'];
            $quantidadePlacas = count($dados['itens']);
            $dataEntregaPrevista = $this->calcularDataEntrega($conexao, $dataPedido, $quantidadePlacas, $dados['idPedido']);
            
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
            
            // 3. Atualiza a "capa" do Pedido com o Valor Total exato e o Valor do Sinal (50%)
            $valorSinalCalculado = $valorTotalPedido * 0.50;
            
            $stmtUpdate = $conexao->prepare("UPDATE Pedido SET valor_total = ?, valor_sinal = ? WHERE idPedido = ?");
            $stmtUpdate->execute([$valorTotalPedido, $valorSinalCalculado, $idPedido]);
            
            // CONFIRMA A TRANSAÇÃO: Se o código chegou até aqui sem erros, grava tudo definitivamente
            $conexao->commit();
            
            return [
                'sucesso' => true,
                'mensagem' => 'Pedido registado com sucesso.',
                'idPedido' => $idPedido,
                'valorTotal' => $valorTotalPedido,
                'valorSinal' => $valorSinalCalculado,
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
            // 1. Busca a lista de pedidos (a "capa")
            $pedidos = Pedido::listar($filtros);
            
            // 2. Para cada pedido encontrado, busca os seus respetivos itens
            foreach ($pedidos as $chave => $pedido) {
                // Utiliza a entidade ItemPedido que acabámos de atualizar
                $itens = ItemPedido::buscarPorPedido($pedido['idPedido']);
                
                // Anexa os itens ao array do pedido atual
                $pedidos[$chave]['itens'] = $itens;
            }
            
            return [
                'sucesso' => true,
                'dados' => $pedidos
            ];
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro ao listar os pedidos: " . $e->getMessage());
        }
    }

    /*Método para controlar a alteração da situação do pedido*/
    public function alterarSituacaoPedido($dados) {
        if (empty($dados['idPedido']) || empty($dados['situacao'])) {
            throw new Exception("O identificador do pedido e a nova situação são obrigatórios.");
        }
        
        $situacoesPermitidas = ['A', 'C', 'E', 'F', 'P'];
        if (!in_array($dados['situacao'], $situacoesPermitidas)) {
            throw new Exception("A situação deve ser obrigatoriamente 'A' (Aberto), 'C' (Cancelado), 'E' (Entregue) ou 'F' (Finalizado) ou 'P' (Produção).");
        }
        
        if (Pedido::alterarSituacao($dados['idPedido'], $dados['situacao'])) {
            return [
                'sucesso' => true,
                'mensagem' => 'Situação do pedido modificada com sucesso.'
            ];
        } else {
            throw new Exception("Ocorreu um erro ao alterar a situação na base de dados.");
        }
    }

    //Método de controle de atualização de item pedido:
    public function atualizarPedido($dados) {
        if (empty($dados['idPedido']) || empty($dados['idCliente']) || empty($dados['itens']) || !is_array($dados['itens'])) {
            throw new Exception("O identificador do pedido, o cliente e a lista de placas são obrigatórios para a edição.");
        }
        
        $conexao = Conexao::getConexao();
        
        try {
            $conexao->beginTransaction();
            
            $dataPedido = date('Y-m-d');
            $quantidadePlacas = count($dados['itens']);
            $dataEntregaPrevista = $this->calcularDataEntrega($conexao, $dataPedido, $quantidadePlacas);
            
            $pedido = new Pedido($dados['idCliente'], $dataPedido, $dataEntregaPrevista, 0, 0, 'A');
            $pedido->atualizar($conexao, $dados['idPedido']);
            
            ItemPedido::excluirPorPedido($conexao, $dados['idPedido']);
            
            foreach ($dados['itens'] as $item) {
                $stmtPreco = $conexao->prepare("SELECT preco_material, preco_letra FROM TabelaPrecos WHERE idTabelaPrecos = ?");
                $stmtPreco->execute([$item['idTabelaPrecos']]);
                $precos = $stmtPreco->fetch();
                
                if (!$precos) {
                    throw new Exception("Tabela de preços inválida para um dos itens.");
                }
                
                $area = $item['altura'] * $item['largura'];
                $custoMaterial = $area * $precos['preco_material'];
                $fraseSemEspacos = str_replace(' ', '', $item['frase']);
                $custoLetras = strlen($fraseSemEspacos) * $precos['preco_letra'];
                $valorCalculado = $custoMaterial + $custoLetras;
                $valorTotalPedido += $valorCalculado;
                
                $itemPedido = new ItemPedido(
                    $dados['idPedido'], $item['idTabelaPrecos'], $item['idCorPlaca'], $item['idCorLetra'], 
                    $item['altura'], $item['largura'], $item['frase'], $valorCalculado
                );
                $itemPedido->inserir($conexao);
            }
            
            $valorSinalCalculado = $valorTotalPedido * 0.50;
            $stmtUpdate = $conexao->prepare("UPDATE Pedido SET valor_total = ?, valor_sinal = ?, data_entrega_prevista = ? WHERE idPedido = ?");
            $stmtUpdate->execute([$valorTotalPedido, $valorSinalCalculado, $dataEntregaPrevista, $dados['idPedido']]);
            
            $conexao->commit();
            
            return [
                'sucesso' => true,
                'mensagem' => 'Pedido atualizado com sucesso.',
                'idPedido' => $dados['idPedido'],
                'valorTotal' => $valorTotalPedido,
                'valorSinal' => $valorSinalCalculado
            ];
            
        } catch (Exception $e) {
            if ($conexao->inTransaction()) {
                $conexao->rollBack();
            }
            throw new Exception("Erro ao processar a atualização do pedido: " . $e->getMessage());
        }
    }

    private function calcularDataEntrega($conexao, $dataBase, $quantidadePlacas, $idPedidoAtual = null) {
        $dataAvaliada = date('Y-m-d', strtotime($dataBase . ' +1 day'));
        $placasRestantes = $quantidadePlacas;

        while ($placasRestantes > 0) {
            $placasJaAgendadas = Pedido::contarPlacasPorData($conexao, $dataAvaliada, $idPedidoAtual);
            $capacidadeLivre = 15 - $placasJaAgendadas;

            if ($capacidadeLivre > 0) {
                if ($placasRestantes <= $capacidadeLivre) {
                    $placasRestantes = 0; 
                } else {
                    $placasRestantes -= $capacidadeLivre;
                }
            }
            
            if ($placasRestantes > 0) {
                $dataAvaliada = date('Y-m-d', strtotime($dataAvaliada . ' +1 day'));
            }
        }
        return $dataAvaliada;
    }
        
}