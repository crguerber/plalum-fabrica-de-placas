<?php

namespace Entity;

use PDO;
use PDOException;


class Conexao {
    // Variável estática para guardar a instância única da conexão
    private static $instancia;

    /*
     Método estático que retorna a conexão com o banco de dados.
     Utiliza o padrão Singleton para evitar múltiplas conexões abertas.
     */
    public static function getConexao() {
        // Verifica se a conexão já existe; se não, cria uma nova.
        if (!isset(self::$instancia)) {
            
            // Credenciais do Banco de Dados
            $host = 'localhost';
            $dbname = 'plalum_db';
            $usuario = 'root';
            $senha = 'suasenhalocal'; 
            
            try {
                // Criação da conexão utilizando PDO
                self::$instancia = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $usuario, $senha);
                
                // Configura o PDO para lançar Exceções em caso de erros no SQL
                self::$instancia->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Configura para que as buscas retornem arrays associativos por padrão
                self::$instancia->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
            } catch (PDOException $e) {
                // Se der erro (ex: MySQL desligado ou senha errada), para o sistema e avisa
                die("Erro crítico de Conexão com o Banco de Dados: " . $e->getMessage());
            }
        }
        
        return self::$instancia;
    }
}