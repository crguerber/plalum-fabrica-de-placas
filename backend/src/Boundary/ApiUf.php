<?php
namespace Boundary;
use Entity\Uf;
use Exception;

class ApiUf {
    public function processarRequisicao() {
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Origin: *");
        
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                echo json_encode(['sucesso' => true, 'dados' => Uf::listar()]);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
    }
}