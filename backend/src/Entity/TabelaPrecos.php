<?php

namespace Entity;

use PDO;
use PDOException;
use Exception;

class TabelaPrecos {
    private $idTabelaPrecos;
    private $dataVigencia;
    private $precoMaterial;
    private $precoLetra;
    private $ativo;

    public function __construct($dataVigencia = null, $precoMaterial = null, $precoLetra = null, $ativo = 1) {
        $this->dataVigencia = $dataVigencia;
        $this->precoMaterial = $precoMaterial;
        $this->precoLetra = $precoLetra;
        $this->ativo = $ativo;
    }

    public function getIdTabelaPrecos() { return $this->idTabelaPrecos; }
    public function getDataVigencia() { return $this->dataVigencia; }
    public function getPrecoMaterial() { return $this->precoMaterial; }
    public function getPrecoLetra() { return $this->precoLetra; }
    public function getAtivo() { return $this->ativo; }
    public function setIdTabelaPrecos($id) { $this->idTabelaPrecos = $id; }
    public function setAtivo($ativo) { $this->ativo = $ativo; }

    public function inserir() {
        try {
            $conexao = Conexao::getConexao();
            $sql = "INSERT INTO TabelaPrecos (dataVigencia, preco_material, preco_letra) VALUES (?, ?, ?)";
            $stmt = $conexao->prepare($sql);
            
            $stmt->execute([
                $this->dataVigencia,
                $this->precoMaterial,
                $this->precoLetra
            ]);
            
            $this->idTabelaPrecos = $conexao->lastInsertId();
            return true;
        } catch (PDOException $e) {
            die("Erro ao inserir a tabela de preços: " . $e->getMessage());
        }
    }

    /*Método para listar*/
    public static function listar($filtros = []) {
        try {
            $conexao = Conexao::getConexao();
            $sql = "SELECT * FROM tabelaprecos";
            $parametros = [];

            if (!empty($filtros['dataVigencia'])) {
                $sql .= " WHERE dataVigencia = ?";
                $parametros[] = $filtros['dataVigencia'];
            }

            $sql .= " ORDER BY dataVigencia DESC";

            $stmt = $conexao->prepare($sql);
            $stmt->execute($parametros);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \Exception("Erro ao consultar o banco de dados: " . $e->getMessage());
        }
    }

    /*Método para editar*/
    public function atualizar() {
        try {
            $conexao = Conexao::getConexao();
            $sql = "UPDATE TabelaPrecos SET dataVigencia = ?, preco_material = ?, preco_letra = ? WHERE idTabelaPrecos = ?";
            $stmt = $conexao->prepare($sql);
            return $stmt->execute([
                $this->dataVigencia,
                $this->precoMaterial,
                $this->precoLetra,
                $this->idTabelaPrecos
            ]);
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar a tabela de preços: " . $e->getMessage());
        }
    }

    /*Método para altarar situação*/
    public static function alterarStatus($idTabelaPrecos, $ativo) {
        try {
            $conexao = Conexao::getConexao();
            $sql = "UPDATE TabelaPrecos SET ativo = ? WHERE idTabelaPrecos = ?";
            $stmt = $conexao->prepare($sql);
            return $stmt->execute([$ativo, $idTabelaPrecos]);
        } catch (PDOException $e) {
            throw new Exception("Erro ao alterar o status: " . $e->getMessage());
        }
    }
}