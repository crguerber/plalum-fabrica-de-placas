<?php

namespace Entity;

use PDO;
use PDOException;

class TabelaPrecos {
    private $idTabelaPrecos;
    private $dataVigencia;
    private $precoMaterial;
    private $precoLetra;

    public function __construct($dataVigencia = null, $precoMaterial = null, $precoLetra = null) {
        $this->dataVigencia = $dataVigencia;
        $this->precoMaterial = $precoMaterial;
        $this->precoLetra = $precoLetra;
    }

    public function getIdTabelaPrecos() { return $this->idTabelaPrecos; }
    public function getDataVigencia() { return $this->dataVigencia; }
    public function getPrecoMaterial() { return $this->precoMaterial; }
    public function getPrecoLetra() { return $this->precoLetra; }

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
}