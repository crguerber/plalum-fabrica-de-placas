<?php

// Habilita o carregamento automático de classes do Composer
require_once __DIR__ . '/vendor/autoload.php';

use Boundary\ApiCliente;
use Boundary\ApiTabelaPrecos;
use Boundary\ApiCor;

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

} else {
    // Rota não encontrada
    http_response_code(404);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Endpoint não encontrado no servidor.'
    ]);
}