<?php

namespace Boundary;

use Control\CtrlCor;
use Exception;

class ApiCor {
    
    public function processarRequisicao() {
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $dadosJson = file_get_contents("php://input");
                $dados = json_decode($dadosJson, true);
                
                $controladora = new CtrlCor();
                $resultado = $controladora->manterCor($dados);
                
                http_response_code(201);
                echo json_encode($resultado);
                
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode([
                    'sucesso' => false,
                    'mensagem' => $e->getMessage()
                ]);
            }
        } else {
            http_response_code(405);
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Método HTTP não permitido. Utilize o método POST.'
            ]);
        }
    }
}