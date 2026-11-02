<?php
namespace Entity;
use PDO;
use PDOException;
use Exception;

class Cliente {
    private $idCliente;
    private $nome;
    private $cpf;
    private $telefone;
    private $email;
    private $ativo;
    private $dataCadastro;
    private $observacao;
    private $cidade_id;
    private $cep;
    private $logradouro;
    private $numero;
    private $complemento;
    private $bairro;
    private $tipo_cliente;

    public function __construct($dados = []) {
        $this->nome = $dados['nome'] ?? null;
        $this->cpf = $dados['cpf'] ?? null;
        $this->telefone = $dados['telefone'] ?? null;
        $this->email = $dados['email'] ?? null;
        $this->ativo = $dados['ativo'] ?? 1;
        $this->dataCadastro = $dados['dataCadastro'] ?? date('Y-m-d');
        $this->observacao = $dados['observacao'] ?? null;
        $this->cidade_id = $dados['cidade_id'] ?? null;
        $this->cep = $dados['cep'] ?? null;
        $this->logradouro = $dados['logradouro'] ?? null;
        $this->numero = $dados['numero'] ?? null;
        $this->complemento = $dados['complemento'] ?? null;
        $this->bairro = $dados['bairro'] ?? null;
        $this->tipo_cliente = $dados['tipo_cliente'] ?? null;
    }

    public function getIdCliente() { return $this->idCliente; }
    public function setIdCliente($id) { $this->idCliente = $id; }

    public function inserir() {
        try {
            $conexao = Conexao::getConexao();
            $sql = "INSERT INTO cliente (nome, cpf, telefone, email, ativo, dataCadastro, observacao, cidade_id, cep, logradouro, numero, complemento, bairro, tipo_cliente) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            // Força a data do sistema no momento da gravação
            $this->dataCadastro = date('Y-m-d'); 
            
            $stmt = $conexao->prepare($sql);
            $stmt->execute([
                $this->nome, $this->cpf, $this->telefone, $this->email, $this->ativo, 
                $this->dataCadastro, $this->observacao, $this->cidade_id, $this->cep, 
                $this->logradouro, $this->numero, $this->complemento, $this->bairro, $this->tipo_cliente
            ]);
            
            $this->idCliente = $conexao->lastInsertId();
            return true;
        } catch (PDOException $e) {
            throw new Exception("Erro ao inserir cliente: " . $e->getMessage());
        }
    }

    public static function listar($filtros = []) {
        try {
            $conexao = Conexao::getConexao();
            // Adicionado cid.uf_id para o frontend conseguir selecionar o estado correto na edição
            $sql = "SELECT c.*, cid.nome as nome_cidade, cid.uf_id, u.sigla as uf_sigla 
                    FROM cliente c 
                    LEFT JOIN cidade cid ON c.cidade_id = cid.id_cidade 
                    LEFT JOIN uf u ON cid.uf_id = u.uf_id";
            $parametros = [];

            if (!empty($filtros['nome'])) {
                $termo = "%" . $filtros['nome'] . "%";
                $sql .= " WHERE c.nome LIKE ? OR c.cpf LIKE ?";
                $parametros[] = $termo;
                $parametros[] = $termo;
            }

            $stmt = $conexao->prepare($sql);
            $stmt->execute($parametros);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao consultar o banco de dados: " . $e->getMessage());
        }
    }

    public function atualizar() {
        try {
            $conexao = Conexao::getConexao();
            $sql = "UPDATE cliente SET 
                    nome = ?, cpf = ?, telefone = ?, email = ?, observacao = ?, 
                    cidade_id = ?, cep = ?, logradouro = ?, numero = ?, 
                    complemento = ?, bairro = ?, tipo_cliente = ? 
                    WHERE idCliente = ?";
            
            $stmt = $conexao->prepare($sql);
            return $stmt->execute([
                $this->nome, $this->cpf, $this->telefone, $this->email, $this->observacao,
                $this->cidade_id, $this->cep, $this->logradouro, $this->numero,
                $this->complemento, $this->bairro, $this->tipo_cliente, $this->idCliente
            ]);
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar o cliente: " . $e->getMessage());
        }
    }

    public static function alterarStatus($idCliente, $ativo) {
        try {
            $conexao = Conexao::getConexao();
            $sql = "UPDATE cliente SET ativo = ? WHERE idCliente = ?";
            $stmt = $conexao->prepare($sql);
            return $stmt->execute([$ativo, $idCliente]);
        } catch (PDOException $e) {
            throw new Exception("Erro ao alterar o status: " . $e->getMessage());
        }
    }
}