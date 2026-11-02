<?php
namespace Entity;
use PDO;
use Exception;

class Cidade {
    public static function listarPorUf($uf_id) {
        try {
            $conexao = Conexao::getConexao();
            $sql = "SELECT id_cidade, nome FROM cidade WHERE uf_id = ? ORDER BY nome ASC";
            $stmt = $conexao->prepare($sql);
            $stmt->execute([$uf_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Erro ao listar cidades: " . $e->getMessage());
        }
    }
}