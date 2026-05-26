<?php

namespace Control;

use Entity\TabelaPrecos;
use Exception;
use DateTime;

class CtrlTabelaPrecos {
    
    /**
     * Método responsável por contorlar o registo de uma nova tabela de preços.
     */
    public function manterTabelaPrecos($dados) {
        
        // Validação da presença dos campos obrigatórios
        if (empty($dados['dataVigencia']) || !isset($dados['precoMaterial']) || !isset($dados['precoLetra'])) {
            throw new Exception("A data de vigência e os preços do material e da letra são de preenchimento obrigatório.");
        }
        
        // Regra de Negócio: Os preços não podem ser negativos ou nulos
        if (!is_numeric($dados['precoMaterial']) || $dados['precoMaterial'] <= 0 || 
            !is_numeric($dados['precoLetra']) || $dados['precoLetra'] <= 0) {
            throw new Exception("Os preços do material e da letra devem ser valores numéricos positivos maiores que zero.");
        }
        
        // Regra de Negócio: Validação do formato exato da data para o MySQL (AAAA-MM-DD)
        $data = DateTime::createFromFormat('Y-m-d', $dados['dataVigencia']);
        if (!$data || $data->format('Y-m-d') !== $dados['dataVigencia']) {
            throw new Exception("A data de vigência deve ser submetida no formato válido AAAA-MM-DD.");
        }
        
        // Instanciação da Entidade com os dados validados
        $tabela = new TabelaPrecos(
            $dados['dataVigencia'],
            $dados['precoMaterial'],
            $dados['precoLetra']
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
}