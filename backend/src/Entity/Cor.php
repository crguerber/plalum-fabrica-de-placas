<?php

namespace Entity;

use PDO;
use PDOException;

class Cor {
    private $idCor;
    private $nome;
    private $tipo;

    public function __construct($nome = null, $tipo = null) {
        $this->nome = $nome;
        $this->tipo = $tipo;
    }

    public function getIdCor() { return $this->idCor; }
    public function getNome() { return $this->nome; }
    public function getTipo() { return $this->tipo; }

    public function inserir() {
        try {
            $conexao = Conexao::getConexao();
            $sql = "INSERT INTO Cor (nome, tipo) VALUES (?, ?)";
            $stmt = $conexao->prepare($sql);
            
            $stmt->execute([
                $this->nome,
                $this->tipo
            ]);
            
            $this->idCor = $conexao->lastInsertId();
            return true;
        } catch (PDOException $e) {
            die("Erro ao inserir a cor: " . $e->getMessage());
        }
    }
}