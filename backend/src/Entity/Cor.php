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

    /*Método para listar*/
    public static function listar($filtros = []) {
        try {
            $conexao = Conexao::getConexao();
            $sql = "SELECT idCor, nome, tipo FROM Cor WHERE 1=1";
            $parametros = [];

            if (!empty($filtros['nome'])) {
                $sql .= " AND nome LIKE ?";
                $parametros[] = "%" . $filtros['nome'] . "%";
            }

            if (!empty($filtros['tipo'])) {
                $sql .= " AND tipo = ?";
                $parametros[] = $filtros['tipo'];
            }

            $sql .= " ORDER BY nome ASC";
            $stmt = $conexao->prepare($sql);
            $stmt->execute($parametros);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar as cores: " . $e->getMessage());
        }
    }
}