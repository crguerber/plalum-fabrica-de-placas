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

     /*Método para listar cores*/
    public static function listar($filtros = []) {
        try {
            $conexao = Conexao::getConexao();
            $sql = "SELECT * FROM Cor";
            $parametros = [];

            if (!empty($filtros['nome'])) {
                $termo = "%" . $filtros['nome'] . "%";
                $sql .= " WHERE nome LIKE ? OR tipo LIKE ?";
                $parametros[] = $termo;
                $parametros[] = $termo;
            }

            $stmt = $conexao->prepare($sql);
            $stmt->execute($parametros);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \Exception("Erro ao consultar o banco de dados: " . $e->getMessage());
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