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

    public function __construct($nome = null, $cpf = null, $telefone = null, $email = null, $ativo = 1) {
        $this->nome = $nome;
        $this->cpf = $cpf;
        $this->telefone = $telefone;
        $this->email = $email;
        $this->ativo = $ativo;
    }

    // Métodos de Acesso (Getters)
    public function getIdCliente() { return $this->idCliente; }
    
    public function getNome() { return $this->nome; }
    public function getCpf() { return $this->cpf; }
    public function getTelefone() { return $this->telefone; }
    public function getEmail() { return $this->email; }
    public function getAtivo() { return $this->ativo; }
    

    // Métodos de Modificação (Setters)
    public function setIdCliente($id) { $this->idCliente = $id; }
    public function setNome($nome) { $this->nome = $nome; }
    public function setCpf($cpf) { $this->cpf = $cpf; }
    public function setTelefone($telefone) { $this->telefone = $telefone; }
    public function setEmail($email) { $this->email = $email; }
    public function setAtivo($ativo) { $this->ativo = $ativo; }

    /**
     * Método para inserir o cliente na base de dados.
     */
    public function inserir() {
        try {
            $conexao = Conexao::getConexao();
            
            // A instrução SQL utiliza marcadores (?) para evitar Injeção SQL
            $sql = "INSERT INTO Cliente (nome, cpf, telefone, email) VALUES (?, ?, ?, ?)";
            
            $stmt = $conexao->prepare($sql);
            
            // Passamos os valores num array, substituindo os marcadores na ordem correta
            $stmt->execute([
                $this->nome,
                $this->cpf,
                $this->telefone,
                $this->email
            ]);
            
            // Recupera o ID gerado automaticamente pela base de dados
            $this->idCliente = $conexao->lastInsertId();
            
            return true;
        } catch (PDOException $e) {
            die("Erro ao inserir cliente: " . $e->getMessage());
        }
    }

    /*Método para listar clientes*/
    public static function listar($filtros = []) {
        try {
            $conexao = Conexao::getConexao();
            $sql = "SELECT * FROM Cliente";
            $parametros = [];

            if (!empty($filtros['nome'])) {
                $termo = "%" . $filtros['nome'] . "%";
                $sql .= " WHERE nome LIKE ? OR cpf LIKE ?";
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

    /*Método para alterar*/
    public function atualizar() {
        try {
            $conexao = Conexao::getConexao();
            
            // O campo 'ativo' foi removido da instrução SQL
            $sql = "UPDATE Cliente SET nome = ?, cpf = ?, telefone = ?, email = ? WHERE idCliente = ?";
            $stmt = $conexao->prepare($sql);
            
            // Os parâmetros agora acompanham a ordem rigorosa do SQL
            return $stmt->execute([
                $this->nome,
                $this->cpf,
                $this->telefone,
                $this->email,
                $this->idCliente
            ]);
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar o cliente: " . $e->getMessage());
        }
    }

    /*Método para mudar situação*/
    public static function alterarStatus($idCliente, $ativo) {
        try {
            $conexao = Conexao::getConexao();
            $sql = "UPDATE Cliente SET ativo = ? WHERE idCliente = ?";
            $stmt = $conexao->prepare($sql);
            return $stmt->execute([$ativo, $idCliente]);
        } catch (PDOException $e) {
            throw new Exception("Erro ao alterar o status: " . $e->getMessage());
        }
    }

}