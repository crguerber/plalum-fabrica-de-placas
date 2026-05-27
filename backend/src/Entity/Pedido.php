<?php

namespace Entity;

use PDO;
use PDOException;
use Exception;

class Pedido {
    private $idPedido;
    private $idCliente;
    private $dataPedido;
    private $dataEntregaPrevista;
    private $valorTotal;
    private $valorSinal;
    private $situacao;

    public function __construct($idCliente = null, $dataPedido = null, $dataEntregaPrevista = null, $valorTotal = null, $valorSinal = null, $situacao = 'A') {
        $this->idCliente = $idCliente;
        $this->dataPedido = $dataPedido;
        $this->dataEntregaPrevista = $dataEntregaPrevista;
        $this->valorTotal = $valorTotal;
        $this->valorSinal = $valorSinal;
        $this->situacao = $situacao;
    }

    public function getIdPedido() { return $this->idPedido; }
    public function setIdPedido($id) { $this->idPedido = $id; }

    public function inserir($conexao) {
        $sql = "INSERT INTO Pedido (idCliente, dataPedido, data_entrega_prevista, valor_total, valor_sinal, situacao) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conexao->prepare($sql);
        $stmt->execute([
            $this->idCliente,
            $this->dataPedido,
            $this->dataEntregaPrevista,
            $this->valorTotal,
            $this->valorSinal,
            $this->situacao
        ]);
        $this->idPedido = $conexao->lastInsertId();
        return $this->idPedido;
    }

    /*Método para listar*/
    public static function listar($filtros = []) {
        try {
            $conexao = Conexao::getConexao();
            $sql = "SELECT p.idPedido, c.nome AS nomeCliente, c.cpf, p.dataPedido, p.valor_total, p.situacao 
                    FROM Pedido p 
                    INNER JOIN Cliente c ON p.idCliente = c.idCliente 
                    WHERE 1=1";
            $parametros = [];

            if (!empty($filtros['idCliente'])) {
                $sql .= " AND p.idCliente = ?";
                $parametros[] = $filtros['idCliente'];
            }

            if (!empty($filtros['idPedido'])) {
                $sql .= " AND p.idPedido = ?";
                $parametros[] = $filtros['idPedido'];
            }

            if (!empty($filtros['situacao'])) {
                $sql .= " AND p.situacao = ?";
                $parametros[] = $filtros['situacao'];
            }

            if (!empty($filtros['dataInicial'])) {
                $sql .= " AND p.dataPedido >= ?";
                $parametros[] = $filtros['dataInicial'];
            }

            if (!empty($filtros['dataFinal'])) {
                $sql .= " AND p.dataPedido <= ?";
                $parametros[] = $filtros['dataFinal'];
            }

            if (!empty($filtros['cpf'])) {
                $sql .= " AND c.cpf = ?";
                $parametros[] = $filtros['cpf'];
            }

            if (!empty($filtros['nome'])) {
                $sql .= " AND c.nome LIKE ?";
                $parametros[] = "%" . $filtros['nome'] . "%";
            }

            $sql .= " ORDER BY p.dataPedido DESC";
            $stmt = $conexao->prepare($sql);
            $stmt->execute($parametros);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar os pedidos: " . $e->getMessage());
        }
    }
    
}