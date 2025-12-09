<?php
require_once __DIR__ . '/connection.php';

class ProfessorDAO {
    private $conn;

    public function __construct() {
        $this->conn = Connection::getInstance();
        $this->inicializarTabela();
    }

    private function inicializarTabela() {
        $sql = "CREATE TABLE IF NOT EXISTS professores (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            telefone VARCHAR(20),
            cpf VARCHAR(14) NOT NULL UNIQUE,
            data_nascimento DATE NOT NULL,
            cref VARCHAR(20) NOT NULL UNIQUE,
            especialidade VARCHAR(50) NOT NULL,
            senha VARCHAR(255) NOT NULL,
            status VARCHAR(20) DEFAULT 'Ativo',
            foto_perfil VARCHAR(255) DEFAULT NULL,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->conn->exec($sql);
    }

    public function cadastrar($nome, $email, $telefone, $cpf, $data_nascimento, $cref, $especialidade, $senha) {
        // Validação de senha forte
        $validacaoSenha = $this->validarSenhaForte($senha);
        if ($validacaoSenha !== true) {
            throw new Exception($validacaoSenha);
        }
        
        // Limpar nome
        $nomeLimpo = trim(preg_replace('/\s+/', ' ', $nome));
        
        // Hash da senha
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        
        $stmt = $this->conn->prepare("
            INSERT INTO professores 
            (nome, email, telefone, cpf, data_nascimento, cref, especialidade, senha) 
            VALUES (:nome, :email, :telefone, :cpf, :data_nascimento, :cref, :especialidade, :senha)
        ");
        
        return $stmt->execute([
            ':nome' => $nomeLimpo,
            ':email' => $email,
            ':telefone' => $telefone,
            ':cpf' => $cpf,
            ':data_nascimento' => $data_nascimento,
            ':cref' => $cref,
            ':especialidade' => $especialidade,
            ':senha' => $senhaHash
        ]);
    }

    public function listar() {
        $stmt = $this->conn->query("SELECT * FROM professores ORDER BY nome ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id) {
        $stmt = $this->conn->prepare("SELECT * FROM professores WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarPorEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM professores WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarPorCref($cref) {
        $stmt = $this->conn->prepare("SELECT * FROM professores WHERE cref = :cref");
        $stmt->execute([':cref' => $cref]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar($id, $nome, $email, $telefone, $cpf, $data_nascimento, $cref, $especialidade, $senha = null) {
        // Limpar nome
        $nomeLimpo = trim(preg_replace('/\s+/', ' ', $nome));
        
        $sql = "UPDATE professores SET 
                nome = :nome, 
                email = :email, 
                telefone = :telefone, 
                cpf = :cpf, 
                data_nascimento = :data_nascimento, 
                cref = :cref, 
                especialidade = :especialidade 
                ";
        
        $params = [
            ':nome' => $nomeLimpo,
            ':email' => $email,
            ':telefone' => $telefone,
            ':cpf' => $cpf,
            ':data_nascimento' => $data_nascimento,
            ':cref' => $cref,
            ':especialidade' => $especialidade,
            ':id' => $id
        ];
        
        if (!empty($senha)) {
            // Validação de senha forte
            $validacaoSenha = $this->validarSenhaForte($senha);
            if ($validacaoSenha !== true) {
                throw new Exception($validacaoSenha);
            }
            
            $sql .= ", senha = :senha";
            $params[':senha'] = password_hash($senha, PASSWORD_DEFAULT);
        }
        
        $sql .= " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function excluir($id) {
        $stmt = $this->conn->prepare("DELETE FROM professores WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function atualizarStatus($id, $status) {
        $stmt = $this->conn->prepare("UPDATE professores SET status = :status WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public function contarAtivos() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM professores WHERE status = 'Ativo'");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function contarEspecialidades() {
        $stmt = $this->conn->query("SELECT COUNT(DISTINCT especialidade) FROM professores");
        return $stmt->fetchColumn();
    }

    public function validarLogin($email, $senha) {
        $professor = $this->buscarPorEmail($email);
        if ($professor && password_verify($senha, $professor['senha'])) {
            return $professor;
        }
        return false;
    }

    private function validarSenhaForte($senha) {
        if (strlen($senha) < 8) {
            return "A senha deve ter pelo menos 8 caracteres.";
        }
        
        if (!preg_match('/[A-Z]/', $senha)) {
            return "A senha deve conter pelo menos uma letra maiúscula.";
        }
        
        if (!preg_match('/[a-z]/', $senha)) {
            return "A senha deve conter pelo menos uma letra minúscula.";
        }
        
        if (!preg_match('/[0-9]/', $senha)) {
            return "A senha deve conter pelo menos um número.";
        }
        
        if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $senha)) {
            return "A senha deve conter pelo menos um caractere especial (!@#$%^&* etc).";
        }
        
        return true;
    }

    public function atualizarFotoPerfil($id, $foto_perfil) {
        $stmt = $this->conn->prepare("UPDATE professores SET foto_perfil = :foto WHERE id = :id");
        return $stmt->execute([':foto' => $foto_perfil, ':id' => $id]);
    }

    public function getEstatisticas() {
        $total = $this->conn->query("SELECT COUNT(*) FROM professores")->fetchColumn();
        $ativos = $this->conn->query("SELECT COUNT(*) FROM professores WHERE status = 'Ativo'")->fetchColumn();
        $especialidades = $this->conn->query("SELECT COUNT(DISTINCT especialidade) FROM professores")->fetchColumn();
        
        return [
            'total' => $total,
            'ativos' => $ativos,
            'especialidades' => $especialidades
        ];
    }
}
?>