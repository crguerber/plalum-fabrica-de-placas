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

    public static function excluirPorPedido($conexao, $idPedido) {
        $sql = "DELETE FROM ItemPedido WHERE idPedido = ?";
        $stmt = $conexao->prepare($sql);
        return $stmt->execute([$idPedido]);
    }

    public static function buscarPorPedido($idPedido) {
        try {
            $conexao = Conexao::getConexao();
            $sql = "SELECT i.*, cp.nome as nomeCorPlaca, cl.nome as nomeCorLetra 
                    FROM ItemPedido i 
                    LEFT JOIN cor cp ON i.idCorPlaca = cp.idCor 
                    LEFT JOIN cor cl ON i.idCorLetra = cl.idCor 
                    WHERE i.idPedido = ?";
            
            $stmt = $conexao->prepare($sql);
            $stmt->execute([$idPedido]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \Exception("Erro ao consultar os itens do pedido: " . $e->getMessage());
        }
    }    
}