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
}