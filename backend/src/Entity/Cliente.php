<?php

namespace Entity;

use PDO;
use PDOException;

class Cliente {
    private $idCliente;
    private $nome;
    private $cpf;
    private $telefone;
    private $email;

    public function __construct($nome = null, $cpf = null, $telefone = null, $email = null) {
        $this->nome = $nome;
        $this->cpf = $cpf;
        $this->telefone = $telefone;
        $this->email = $email;
    }

    // Métodos de Acesso (Getters)
    public function getIdCliente() { return $this->idCliente; }
    public function getNome() { return $this->nome; }
    public function getCpf() { return $this->cpf; }
    public function getTelefone() { return $this->telefone; }
    public function getEmail() { return $this->email; }

    // Métodos de Modificação (Setters)
    public function setIdCliente($id) { $this->idCliente = $id; }
    public function setNome($nome) { $this->nome = $nome; }
    public function setCpf($cpf) { $this->cpf = $cpf; }
    public function setTelefone($telefone) { $this->telefone = $telefone; }
    public function setEmail($email) { $this->email = $email; }

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
}