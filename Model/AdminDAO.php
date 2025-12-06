<?php
require_once __DIR__ . '/connection.php';

class AdminDAO {
    private $conn;

    public function __construct() {
        $this->conn = Connection::getInstance();
        $this->inicializarAdmin();
    }

    private function inicializarAdmin() {
        // Criando a tabela com MAIS segurança (PIN e Palavra-chave)
        $this->conn->exec("
            CREATE TABLE IF NOT EXISTS admins (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(100) NOT NULL UNIQUE,
                senha VARCHAR(255) NOT NULL,
                nome VARCHAR(100),
                cpf_secreto VARCHAR(20) NOT NULL,
                pin_secreto VARCHAR(6) NOT NULL,
                palavra_chave VARCHAR(50) NOT NULL
            )
        ");

        // Cria o Gerente Padrão se não existir
        $stmt = $this->conn->query("SELECT COUNT(*) FROM admins");
        if ($stmt->fetchColumn() == 0) {
            $senhaSecreta = password_hash('admin123', PASSWORD_DEFAULT);
            
            $insere = $this->conn->prepare("
                INSERT INTO admins (email, senha, nome, cpf_secreto, pin_secreto, palavra_chave) 
                VALUES (:email, :senha, :nome, :cpf, :pin, :palavra)
            ");
            
            $insere->execute([
                ':email'   => 'gerente@techfit.com',
                ':senha'   => $senhaSecreta,
                ':nome'    => 'Gerente Supremo',
                // --- DADOS SECRETOS PADRÃO ---
                ':cpf'     => '000.000.000-00',
                ':pin'     => '123456',         // O PIN de 6 dígitos
                ':palavra' => 'TECHFIT_MASTER'  // A Palavra-Chave
            ]);
        }
    }

    public function buscarPorEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM admins WHERE email = :email");
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // NOVA FUNÇÃO: Verifica as 3 chaves de segurança
    public function verificarCredenciaisCompletas($idAdmin, $cpf, $pin, $palavra) {
        $stmt = $this->conn->prepare("
            SELECT * FROM admins 
            WHERE id = :id 
            AND cpf_secreto = :cpf 
            AND pin_secreto = :pin 
            AND palavra_chave = :palavra
        ");
        
        $stmt->execute([
            ':id'      => $idAdmin,
            ':cpf'     => $cpf,
            ':pin'     => $pin,
            ':palavra' => $palavra
        ]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}