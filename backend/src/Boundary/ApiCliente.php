<?php

namespace Boundary;

use Control\CtrlCliente;
use Exception;

class ApiCliente {
    
    public function processarRequisicao() {
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET, PUT");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

        $metodo = $_SERVER['REQUEST_METHOD'];

        try {
            $controladora = new CtrlCliente();

            if ($metodo === 'POST') {
                $dadosJson = file_get_contents("php://input");
                $dados = json_decode($dadosJson, true);
                
                $resultado = $controladora->manterCliente($dados);
                
                http_response_code(201);
                echo json_encode($resultado);
                
            } elseif ($metodo === 'GET') {
                $filtros = [
                    'nome' => $_GET['nome'] ?? null,
                    'cpf' => $_GET['cpf'] ?? null
                ];
                
                $resultado = $controladora->buscarTodos($filtros);
                
                http_response_code(200);
                echo json_encode($resultado);
                
            } elseif ($metodo === 'PUT') {
                $dadosJson = file_get_contents("php://input");
                $dados = json_decode($dadosJson, true);
                
                if (isset($dados['ativo'])) {
                    $resultado = $controladora->alterarStatusCliente($dados);
                } else {
                    $resultado = $controladora->atualizarCliente($dados);
                }
                
                http_response_code(200);
                echo json_encode($resultado);
                
            } else {
                http_response_code(405);
                echo json_encode([
                    'sucesso' => false,
                    'mensagem' => 'Método HTTP não permitido. Utilize POST, GET ou PUT.'
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