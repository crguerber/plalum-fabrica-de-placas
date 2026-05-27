<?php

namespace Entity;

use PDO;
use PDOException;
use Exception;

class ItemPedido {
    private $idItemPedido;
    private $idPedido;
    private $idTabelaPrecos;
    private $idCorPlaca;
    private $idCorLetra;
    private $altura;
    private $largura;
    private $frase;
    private $valorCalculado;

    public function __construct($idPedido, $idTabelaPrecos, $idCorPlaca, $idCorLetra, $altura, $largura, $frase, $valorCalculado) {
        $this->idPedido = $idPedido;
        $this->idTabelaPrecos = $idTabelaPrecos;
        $this->idCorPlaca = $idCorPlaca;
        $this->idCorLetra = $idCorLetra;
        $this->altura = $altura;
        $this->largura = $largura;
        $this->frase = $frase;
        $this->valorCalculado = $valorCalculado;
    }

    public function inserir($conexao) {
        $sql = "INSERT INTO ItemPedido (idPedido, idTabelaPrecos, idCorPlaca, idCorLetra, altura, largura, frase, valor_calculado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conexao->prepare($sql);
        return $stmt->execute([
            $this->idPedido,
            $this->idTabelaPrecos,
            $this->idCorPlaca,
            $this->idCorLetra,
            $this->altura,
            $this->largura,
            $this->frase,
            $this->valorCalculado
        ]);
    }
}