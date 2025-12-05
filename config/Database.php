<?php
class Database {
    private $host = "localhost";
    private $db_name = "techfit_ofc";
    private $username = "root";
    private $password = "senaisp"; // <--- A TUA SENHA AQUI
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            // Tenta conectar
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $e) {
            // Se o erro for "Unknown Database" (1049), tenta criar o banco
            if ($e->getCode() == 1049) {
                return $this->criarBanco();
            } else {
                echo "Erro na conexão: " . $e->getMessage();
                exit;
            }
        }
        return $this->conn;
    }

    private function criarBanco() {
        try {
            // Conecta sem banco para poder criar um
            $pdo = new PDO("mysql:host=" . $this->host, $this->username, $this->password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Cria o banco e as tabelas
            $pdo->exec("CREATE DATABASE IF NOT EXISTS " . $this->db_name);
            $pdo->exec("USE " . $this->db_name);
            
            // Tabela Usuarios
            $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                senha VARCHAR(255) NOT NULL,
                perfil ENUM('admin', 'aluno', 'professor', 'recepcao') NOT NULL,
                criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");


            // --- AQUI ESTÁ O ADMIN DIFÍCIL ---
            // Email: master.admin@techfit.sys
            // Senha:  Sistem@2025#TechFit
            $senhaDificil = password_hash('Sistem@2025#TechFit', PASSWORD_DEFAULT);
            $pdo->exec("INSERT IGNORE INTO usuarios (nome, email, senha, perfil) 
                        VALUES ('Master Admin', 'master.admin@techfit.sys', '$senhaDificil', 'admin')");

            $this->conn = $pdo;
            return $this->conn;
        } catch (PDOException $e) {
            echo "Erro ao criar banco: " . $e->getMessage();
            exit;
        }
    }
}
?>