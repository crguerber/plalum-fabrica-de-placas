<?php

namespace Boundary;

use Control\CtrlTabelaPrecos;
use Exception;

class ApiTabelaPrecos {
    
    public function processarRequisicao() {
        // Configuração dos cabeçalhos para a API REST
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

        // Verifica se o verbo HTTP é POST (criação)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Lê o corpo da requisição em formato bruto
                $dadosJson = file_get_contents("php://input");
                
                // Converte o JSON para um array associativo do PHP
                $dados = json_decode($dadosJson, true);
                
                // Instancia a controladora e delega a responsabilidade
                $controladora = new CtrlTabelaPrecos();
                $resultado = $controladora->manterTabelaPrecos($dados);
                
                // Retorna 201 Created em caso de sucesso
                http_response_code(201);
                echo json_encode($resultado);
                
            } catch (Exception $e) {
                // Captura qualquer erro de validação (como preços negativos) e retorna 400 Bad Request
                http_response_code(400);
                echo json_encode([
                    'sucesso' => false,
                    'mensagem' => $e->getMessage()
                ]);
            }
        } else {
            // Bloqueia métodos não permitidos (GET, PUT, DELETE, etc.)
            http_response_code(405);
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Método HTTP não permitido. Utilize o método POST.'
            ]);
        }
    }
}