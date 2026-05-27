<?php

namespace Boundary;

use Control\CtrlCor;
use Exception;

class ApiCor {
    
    public function processarRequisicao() {
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

        $metodo = $_SERVER['REQUEST_METHOD'];

        try {
            $controladora = new CtrlCor();

            if ($metodo === 'POST') {
                $dadosJson = file_get_contents("php://input");
                $dados = json_decode($dadosJson, true);
                
                $resultado = $controladora->manterCor($dados);
                
                http_response_code(201);
                echo json_encode($resultado);
                
            } elseif ($metodo === 'GET') {
                
                $filtros = [
                    'nome' => $_GET['nome'] ?? null,
                    'tipo' => $_GET['tipo'] ?? null
                ];
                
                $resultado = $controladora->buscarTodos($filtros);
                
                http_response_code(200);
                echo json_encode($resultado);
                
            } else {
                http_response_code(405);
                echo json_encode([
                    'sucesso' => false,
                    'mensagem' => 'Método HTTP não permitido. Utilize POST ou GET.'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ]);
        }
    }
}