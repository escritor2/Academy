<?php
require_once __DIR__ . '/Usuario.php';
require_once __DIR__ . '/Connect.php';

class CadastroDAO {
    private $pdo;

    public function __construct() {
        $this->pdo = Connect::connect();
        $this->criarTabelaSeNaoExistir();
    }

    private function criarTabelaSeNaoExistir() {
        $sql = "CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            senha VARCHAR(255) NOT NULL,
            tipo ENUM('aluno', 'admin', 'funcionario') DEFAULT 'aluno',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->pdo->exec($sql);
    }

    public function criarUsuario(Usuario $usuario) {
        $sql = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        
        try {
            $stmt->execute([
                $usuario->getNome(),
                $usuario->getEmail(),
                $usuario->getSenha(),
                $usuario->getTipo()
            ]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function autenticar($email, $senha) {
        $sql = "SELECT * FROM usuarios WHERE email = ? AND senha = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email, $senha]);
        
        $row = $stmt->fetch();
        if ($row) {
            return new Usuario($row['nome'], $row['email'], $row['senha'], $row['tipo']);
        }
        return false;
    }

    public function buscarPorEmail($email) {
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        
        $row = $stmt->fetch();
        if ($row) {
            return new Usuario($row['nome'], $row['email'], $row['senha'], $row['tipo']);
        }
        return null;
    }

    public function listarUsuarios() {
        $sql = "SELECT * FROM usuarios";
        $stmt = $this->pdo->query($sql);
        $usuarios = [];
        
        while ($row = $stmt->fetch()) {
            $usuarios[$row['email']] = new Usuario(
                $row['nome'],
                $row['email'],
                $row['senha'],
                $row['tipo']
            );
        }
        return $usuarios;
    }
}
?>