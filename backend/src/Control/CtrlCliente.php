<?php
namespace Control;
use Entity\Cliente;
use Exception;

class CtrlCliente {
    
    public function manterCliente($dados) {
        if (empty($dados['nome']) || empty($dados['cpf']) || empty($dados['cidade_id'])) {
            throw new Exception("Os campos Nome, CPF e Cidade são de preenchimento obrigatório.");
        }
        
        $cliente = new Cliente($dados);
        
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

    public function atualizarCliente($dados) {
        if (empty($dados['idCliente']) || empty($dados['nome']) || empty($dados['cpf']) || empty($dados['cidade_id'])) {
            throw new Exception("O identificador do cliente, nome, CPF e Cidade são obrigatórios para a atualização.");
        }
        
        $cliente = new Cliente($dados);
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