<?php
require_once __DIR__ . '/Aluno.php';
require_once __DIR__ . '/connection.php';

class AlunoDAO {
    private $conn;

    public function __construct() {
        $this->conn = Connection::getInstance();

        // Cria a tabela igual você fez no Bebidas
        $this->conn->exec("
            CREATE TABLE IF NOT EXISTS alunos (
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
            )
        ");
    }

    public function criarAluno(Aluno $aluno) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO alunos (nome, data_nascimento, email, telefone, cpf, genero, senha, objetivo, plano)
                VALUES (:nome, :data_nascimento, :email, :telefone, :cpf, :genero, :senha, :objetivo, :plano)
            ");
            
            // Criptografar senha por segurança
            $senhaHash = password_hash($aluno->getSenha(), PASSWORD_DEFAULT);

            $stmt->execute([
                ':nome' => $aluno->getNome(),
                ':data_nascimento' => $aluno->getDataNascimento(),
                ':email' => $aluno->getEmail(),
                ':telefone' => $aluno->getTelefone(),
                ':cpf' => $aluno->getCpf(),
                ':genero' => $aluno->getGenero(),
                ':senha' => $senhaHash, 
                ':objetivo' => $aluno->getObjetivo(),
                ':plano' => $aluno->getPlano()
            ]);
            return true;
        } catch (PDOException $e) {
            // Se der erro (ex: email duplicado), retorna o erro
            throw $e;
        }
    }

    public function buscarPorEmail($email) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM alunos WHERE email = :email");
            $stmt->bindValue(':email', $email);
            $stmt->execute();
            
            // Retorna os dados do aluno como um array (ID, Nome, Senha Hash, etc)
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
}