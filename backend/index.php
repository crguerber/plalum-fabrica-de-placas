<?php

//Cabeçalhos de controle
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);

    exit(0);
} 


// Habilita o carregamento automático de classes do Composer
require_once __DIR__ . '/vendor/autoload.php';

use Boundary\ApiCliente;
use Boundary\ApiTabelaPrecos;
use Boundary\ApiCor;
use Boundary\ApiPedido;

// Obtém o caminho da URL acessada
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Sistema de roteamento simples
if (strpos($uri, '/api/clientes') !== false) {
    $apiCliente = new ApiCliente();
    $apiCliente->processarRequisicao();

} elseif (strpos($uri, '/api/tabela-precos') !== false) {
    
    $apiTabela = new ApiTabelaPrecos();
    $apiTabela->processarRequisicao();  
    
} elseif (strpos($uri, '/api/cores') !== false) {
    
    $apiCor = new ApiCor();
    $apiCor->processarRequisicao();

} elseif (strpos($uri, '/api/pedidos') !== false) {
    $apiPedido = new ApiPedido();
    $apiPedido->processarRequisicao();

} else {
    // Rota não encontrada
    http_response_code(404);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Endpoint não encontrado no servidor.'
    ]);
}