<?php
class Connection {
    private static $instance = null;

    public static function getInstance() {
        if (!self::$instance) {
            try {
                // Configurações do Banco
                $host = 'localhost';
                $dbname = 'techfit_ofc';
                $user = 'root';
                $pass = 'senaisp'; 

                // 1. Conecta ao MySQL sem especificar o banco (para poder criar se não existir)
                $pdo = new PDO("mysql:host=$host;charset=utf8", $user, $pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // 2. Cria o banco se não existir
                $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE $dbname");

                // 3. Cria a tabela 'alunos' se não existir
                $sqlTabela = "CREATE TABLE IF NOT EXISTS alunos (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nome VARCHAR(100) NOT NULL,
                    data_nascimento DATE NOT NULL,
                    email VARCHAR(100) NOT NULL UNIQUE,
                    telefone VARCHAR(20),
                    cpf VARCHAR(14) NOT NULL UNIQUE,
                    genero VARCHAR(20),
                    senha VARCHAR(255) NOT NULL,
                    objetivo VARCHAR(50),
                    plano VARCHAR(20),
                    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                $pdo->exec($sqlTabela);

                self::$instance = $pdo;

            } catch (PDOException $e) {
                die("Erro de conexão: " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}
?>