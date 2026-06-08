<?php

namespace Control;

use Entity\TabelaPrecos;
use Exception;
use DateTime;

class CtrlTabelaPrecos {
    
    //Método responsável por contorlar o registo de uma nova tabela de preços.
    public function manterTabelaPrecos($dados) {
        
        // Validação da presença dos campos obrigatórios
        if (empty($dados['dataVigencia']) || !isset($dados['preco_material']) || !isset($dados['preco_letra'])) {
            throw new Exception("A data de vigência e os preços do material e da letra são de preenchimento obrigatório.");
        }
        
        // Os preços não podem ser negativos ou nulos
        if (!is_numeric($dados['preco_material']) || $dados['preco_material'] <= 0 || 
            !is_numeric($dados['preco_letra']) || $dados['preco_letra'] <= 0) {
            throw new Exception("Os preços do material e da letra devem ser valores numéricos positivos maiores que zero.");
        }
        
        // Validação do formato exato da data para o MySQL (AAAA-MM-DD)
        $data = DateTime::createFromFormat('Y-m-d', $dados['dataVigencia']);
        if (!$data || $data->format('Y-m-d') !== $dados['dataVigencia']) {
            throw new Exception("A data de vigência deve ser submetida no formato válido AAAA-MM-DD.");
        }
        
        // Instanciação da Entidade com os dados validados
        $tabela = new TabelaPrecos(
            $dados['dataVigencia'],
            $dados['preco_material'],
            $dados['preco_letra']
        );
        
        // Chamada do método de persistência
        if ($tabela->inserir()) {
            return [
                'sucesso' => true,
                'mensagem' => 'Tabela de preços registada com sucesso.',
                'idTabelaPrecos' => $tabela->getIdTabelaPrecos()
            ];
        } else {
            throw new Exception("Ocorreu um erro ao registar a tabela de preços na base de dados.");
        }
    }

    //Método para controlar a consulta
    public function buscarTodos($filtros = []) {
        try {
            $tabelas = TabelaPrecos::listar($filtros);
            return [
                'sucesso' => true,
                'dados' => $tabelas
            ];
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro ao listar as tabelas de preços.");
        }
    }

    //Método para controlar a edição
    public function atualizarTabelaPrecos($dados) {
        if (empty($dados['idTabelaPrecos']) || empty($dados['dataVigencia']) || !isset($dados['preco_material']) || !isset($dados['preco_letra'])) {
            throw new Exception("O identificador, a data de vigência e os preços são obrigatórios para a atualização.");
        }
        
        if (!is_numeric($dados['preco_material']) || $dados['preco_material'] <= 0 || 
            !is_numeric($dados['preco_letra']) || $dados['preco_letra'] <= 0) {
            throw new Exception("Os preços do material e da letra devem ser numéricos e positivos.");
        }
        
        $tabela = new TabelaPrecos(
            $dados['dataVigencia'],
            $dados['preco_material'],
            $dados['preco_letra']
        );
        $tabela->setIdTabelaPrecos($dados['idTabelaPrecos']);
        
        if ($tabela->atualizar()) {
            return [
                'sucesso' => true,
                'mensagem' => 'Tabela de preços atualizada com sucesso.'
            ];
        } else {
            throw new Exception("Ocorreu um erro ao atualizar o registo na base de dados.");
        }
    }

    //Método para controlar a alteração da situação
    public function alterarStatusTabelaPrecos($dados) {
        if (empty($dados['idTabelaPrecos']) || !isset($dados['ativo'])) {
            throw new Exception("O identificador e o novo status são obrigatórios.");
        }
        
        if (TabelaPrecos::alterarStatus($dados['idTabelaPrecos'], $dados['ativo'])) {
            return [
                'sucesso' => true,
                'mensagem' => 'Status da tabela de preços modificado com sucesso.'
            ];
        } else {
            throw new Exception("Ocorreu um erro ao alterar o status na base de dados.");
        }
    }
}