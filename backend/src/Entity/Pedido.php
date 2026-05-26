<?php

namespace Entity;

use PDO;

class Pedido {
    private $idPedido;
    private $idCliente;
    private $dataPedido;
    private $dataEntregaPrevista;
    private $valorTotal;
    private $valorSinal;
    private $situacao;

    public function __construct($idCliente = null, $dataPedido = null, $dataEntregaPrevista = null, $valorTotal = null, $valorSinal = null, $situacao = 'A') {
        $this->idCliente = $idCliente;
        $this->dataPedido = $dataPedido;
        $this->dataEntregaPrevista = $dataEntregaPrevista;
        $this->valorTotal = $valorTotal;
        $this->valorSinal = $valorSinal;
        $this->situacao = $situacao;
    }

    public function getIdPedido() { return $this->idPedido; }
    public function setIdPedido($id) { $this->idPedido = $id; }

    public function inserir($conexao) {
        $sql = "INSERT INTO Pedido (idCliente, dataPedido, data_entrega_prevista, valor_total, valor_sinal, situacao) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conexao->prepare($sql);
        $stmt->execute([
            $this->idCliente,
            $this->dataPedido,
            $this->dataEntregaPrevista,
            $this->valorTotal,
            $this->valorSinal,
            $this->situacao
        ]);
        $this->idPedido = $conexao->lastInsertId();
        return $this->idPedido;
    }
}