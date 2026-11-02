<?php
namespace Entity;
use PDO;
use Exception;

class Uf {
    public static function listar() {
        try {
            $conexao = Conexao::getConexao();
            $sql = "SELECT uf_id, sigla, nome FROM uf ORDER BY nome ASC";
            $stmt = $conexao->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Erro ao listar estados: " . $e->getMessage());
        }
    }
}