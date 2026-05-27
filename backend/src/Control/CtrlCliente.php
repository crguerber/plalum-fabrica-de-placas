<?php

namespace Control;

use Entity\Cliente;
use Exception;

class CtrlCliente {
    
    /**
     * Método responsável controlar o registo de um novo cliente.
     */
    public function manterCliente($dados) {
        
        // Validação primária das Regras de Negócio
        if (empty($dados['nome']) || empty($dados['cpf'])) {
            throw new Exception("Os campos Nome e CPF são de preenchimento obrigatório.");
        }
        
        // Instanciação da Entidade com os dados recebidos
        $cliente = new Cliente(
            $dados['nome'],
            $dados['cpf'],
            $dados['telefone'] ?? null,
            $dados['email'] ?? null
        );
        
        // Chamada do método de persistência na Entidade
        if ($cliente->inserir()) {
            return [
                'sucesso' => true,
                'mensagem' => 'Cliente registado com sucesso.',
                'idCliente' => $cliente->getIdCliente()
            ];
        } else {
            throw new Exception("Ocorreu um erro ao registar o cliente na base de dados.");
        }
    }

    /*Método para controlar a consulta de clientes*/
    public function buscarTodos($filtros = []) {
        try {
            $clientes = Cliente::listar($filtros);
            return [
                'sucesso' => true,
                'dados' => $clientes
            ];
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro ao listar os clientes.");
        }
    }

    /*Método para controlar a atualização*/
    public function atualizarCliente($dados) {
        if (empty($dados['idCliente']) || empty($dados['nome']) || empty($dados['cpf'])) {
            throw new Exception("O identificador do cliente, nome e CPF são de preenchimento obrigatório para a atualização.");
        }
        
        $cliente = new Cliente(
            $dados['nome'],
            $dados['cpf'],
            $dados['telefone'] ?? null,
            $dados['email'] ?? null
        );
        $cliente->setIdCliente($dados['idCliente']);
        
        if ($cliente->atualizar()) {
            return [
                'sucesso' => true,
                'mensagem' => 'Dados do cliente atualizados com sucesso.'
            ];
        } else {
            throw new Exception("Ocorreu um erro ao atualizar o registro no banco de dados.");
        }
    }

    /*Método para controlar a altearação de situação*/
    public function alterarStatusCliente($dados) {
        if (empty($dados['idCliente']) || !isset($dados['ativo'])) {
            throw new Exception("O identificador do cliente e o novo status são obrigatórios.");
        }
        
        if (Cliente::alterarStatus($dados['idCliente'], $dados['ativo'])) {
            return [
                'sucesso' => true,
                'mensagem' => 'Status do cliente modificado com sucesso.'
            ];
        } else {
            throw new Exception("Ocorreu um erro ao alterar o status no banco de dados.");
        }
    }

}