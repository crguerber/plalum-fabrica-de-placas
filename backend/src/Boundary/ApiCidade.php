<?php
namespace Boundary;
use Entity\Cidade;
use Exception;

class ApiCidade {
    public function processarRequisicao() {
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Origin: *");
        
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['uf_id'])) {
                echo json_encode(['sucesso' => true, 'dados' => Cidade::listarPorUf($_GET['uf_id'])]);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
    }
}