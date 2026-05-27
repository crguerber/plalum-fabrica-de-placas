<?php

namespace Control;

use Entity\Cor;
use Exception;

class CtrlCor {
    
    public function manterCor($dados) {
        
        if (empty($dados['nome']) || empty($dados['tipo'])) {
            throw new Exception("O nome e o tipo da cor são de preenchimento obrigatório.");
        }
        
        $tiposPermitidos = ['Fundo', 'Letra', 'Ambos'];
        if (!in_array($dados['tipo'], $tiposPermitidos)) {
            throw new Exception("O tipo da cor deve ser obrigatoriamente 'Fundo', 'Letra' ou 'Ambos'.");
        }
        
        $cor = new Cor(
            $dados['nome'],
            $dados['tipo']
        );
        
        if ($cor->inserir()) {
            return [
                'sucesso' => true,
                'mensagem' => 'Cor registada com sucesso.',
                'idCor' => $cor->getIdCor()
            ];
        } else {
            throw new Exception("Ocorreu um erro ao registar a cor na base de dados.");
        }
    }

    /*Método para controlar a consulta*/
    public function buscarTodos($filtros = []) {
        try {
            $cores = Cor::listar($filtros);
            return [
                'sucesso' => true,
                'dados' => $cores
            ];
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro ao listar as cores.");
        }
    }

    /*Método para controlar a atualização*/
    public function atualizarCor($dados) {
        if (empty($dados['idCor']) || empty($dados['nome']) || empty($dados['tipo'])) {
            throw new Exception("O identificador, o nome e o tipo são obrigatórios para a atualização.");
        }
        
        $tiposPermitidos = ['Fundo', 'Letra', 'Ambos'];
        if (!in_array($dados['tipo'], $tiposPermitidos)) {
            throw new Exception("O tipo da cor deve ser obrigatoriamente 'Fundo', 'Letra' ou 'Ambos'.");
        }
        
        $cor = new Cor(
            $dados['nome'],
            $dados['tipo']
        );
        $cor->setIdCor($dados['idCor']);
        
        if ($cor->atualizar()) {
            return [
                'sucesso' => true,
                'mensagem' => 'Cor atualizada com sucesso.'
            ];
        } else {
            throw new Exception("Ocorreu um erro ao atualizar o registo na base de dados.");
        }
    }

    /*Método para controlar a alteração de situação*/
    public function alterarStatusCor($dados) {
        if (empty($dados['idCor']) || !isset($dados['ativo'])) {
            throw new Exception("O identificador e o novo status são obrigatórios.");
        }
        
        if (Cor::alterarStatus($dados['idCor'], $dados['ativo'])) {
            return [
                'sucesso' => true,
                'mensagem' => 'Status da cor modificado com sucesso.'
            ];
        } else {
            throw new Exception("Ocorreu um erro ao alterar o status na base de dados.");
        }
    }

}