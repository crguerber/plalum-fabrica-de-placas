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
    //private $ativo;

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

    //Método de inserção de novos
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

    //Método para listar
    public static function listar($filtros = []) {
            try {
                $conexao = Conexao::getConexao();
                $sql = "SELECT p.*, c.nome as nomeCliente, c.cpf as cpfCliente 
                        FROM Pedido p 
                        INNER JOIN Cliente c ON p.idCliente = c.idCliente";
                $parametros = [];

                if (!empty($filtros['termo'])) {
                    $termo = "%" . $filtros['termo'] . "%";
                    $sql .= " WHERE p.idPedido LIKE ? OR c.nome LIKE ? OR c.cpf LIKE ?";
                    $parametros[] = $termo;
 //                   $parametros[] = $termo;
                    $parametros[] = $termo;
                    $parametros[] = $termo;
                }

                $sql .= " ORDER BY p.dataPedido DESC";

                $stmt = $conexao->prepare($sql);
                $stmt->execute($parametros);
                return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                throw new \Exception("Erro ao consultar o banco de dados: " . $e->getMessage());
            }
        }

    //Método para alterar a situação A=Aberta, C=Cancelada, E=Entregue, Finalizado
    public static function alterarSituacao($idPedido, $situacao) {
        try {
            $conexao = Conexao::getConexao();
            $sql = "UPDATE Pedido SET situacao = ? WHERE idPedido = ?";
            $stmt = $conexao->prepare($sql);
            return $stmt->execute([$situacao, $idPedido]);
        } catch (PDOException $e) {
            throw new Exception("Erro ao alterar a situação do pedido: " . $e->getMessage());
        }
    }

    public function atualizar($conexao, $idPedido) {
        $sql = "UPDATE Pedido SET idCliente = ?, dataPedido = ?, data_entrega_prevista = ?, valor_total = ?, valor_sinal = ? WHERE idPedido = ?";
        $stmt = $conexao->prepare($sql);
        return $stmt->execute([
            $this->idCliente,
            $this->dataPedido,
            $this->dataEntregaPrevista,
            $this->valorTotal,
            $this->valorSinal,
            $idPedido
        ]);
    }

    public static function contarPlacasPorData($conexao, $data, $idPedidoDesconsiderar = null) {
        $sql = "SELECT COUNT(*) as total FROM ItemPedido i 
                INNER JOIN Pedido p ON i.idPedido = p.idPedido 
                WHERE p.data_entrega_prevista = ? AND p.situacao != 'C'";
        $parametros = [$data];
        
        if ($idPedidoDesconsiderar) {
            $sql .= " AND p.idPedido != ?";
            $parametros[] = $idPedidoDesconsiderar;
        }
        
        $stmt = $conexao->prepare($sql);
        $stmt->execute($parametros);
        $resultado = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int) $resultado['total'];
    }
    
}