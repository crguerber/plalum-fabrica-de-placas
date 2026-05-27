<?php

namespace Entity;

use PDO;
use PDOException;
use Exception;

class Cor {
    private $idCor;
    private $nome;
    private $tipo;
    private $ativo;

    public function __construct($nome = null, $tipo = null, $ativo = 1) {
        $this->nome = $nome;
        $this->tipo = $tipo;
        $this->ativo = $ativo;
    }

    public function getIdCor() { return $this->idCor; }
    public function setIdCor($id) { $this->idCor = $id; }
    public function getNome() { return $this->nome; }
    public function getTipo() { return $this->tipo; }
    public function getAtivo() { return $this->ativo; }
    public function setAtivo($ativo) { $this->ativo = $ativo; }

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

    /*Método para atualizar*/
    public function atualizar() {
        try {
            $conexao = Conexao::getConexao();
            $sql = "UPDATE Cor SET nome = ?, tipo = ? WHERE idCor = ?";
            $stmt = $conexao->prepare($sql);
            return $stmt->execute([
                $this->nome,
                $this->tipo,
                $this->idCor
            ]);
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar a cor: " . $e->getMessage());
        }
    }

    /*Método para alterar a situação*/
    public static function alterarStatus($idCor, $ativo) {
        try {
            $conexao = Conexao::getConexao();
            $sql = "UPDATE Cor SET ativo = ? WHERE idCor = ?";
            $stmt = $conexao->prepare($sql);
            return $stmt->execute([$ativo, $idCor]);
        } catch (PDOException $e) {
            throw new Exception("Erro ao alterar o status: " . $e->getMessage());
        }
    }

}