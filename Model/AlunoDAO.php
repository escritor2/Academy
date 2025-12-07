<?php
require_once __DIR__ . '/Aluno.php';
require_once __DIR__ . '/connection.php';

class AlunoDAO {
    private $conn;

    public function __construct() {
        $this->conn = Connection::getInstance();
        $this->inicializarTabela();
    }

    private function inicializarTabela() {
        // Garante que a tabela tenha todos os campos
        $sql = "CREATE TABLE IF NOT EXISTS alunos (
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
            status VARCHAR(20) DEFAULT 'Ativo',
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->conn->exec($sql);
        
        // Correções de colunas antigas (garantia)
        try { $this->conn->exec("ALTER TABLE alunos ADD COLUMN status VARCHAR(20) DEFAULT 'Ativo'"); } catch (Exception $e) {}
        try { $this->conn->exec("ALTER TABLE alunos ADD COLUMN criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP"); } catch (Exception $e) {}
    }

    // ... (Métodos de criar e buscarLogin mantidos iguais) ...
    public function criarAluno(Aluno $aluno) {
        $senhaHash = password_hash($aluno->getSenha(), PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO alunos (nome, data_nascimento, email, telefone, cpf, genero, senha, objetivo, plano, status) VALUES (:nome, :data_nascimento, :email, :telefone, :cpf, :genero, :senha, :objetivo, :plano, 'Ativo')");
        $stmt->execute([
            ':nome' => $aluno->getNome(), ':data_nascimento' => $aluno->getDataNascimento(), ':email' => $aluno->getEmail(),
            ':telefone' => $aluno->getTelefone(), ':cpf' => $aluno->getCpf(), ':genero' => $aluno->getGenero(),
            ':senha' => $senhaHash, ':objetivo' => $aluno->getObjetivo(), ':plano' => $aluno->getPlano()
        ]);
    }

    public function buscarPorEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM alunos WHERE email = :email");
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // --- FUNÇÕES DE DASHBOARD E LISTAGEM ---
    public function contarTotal() {
        return $this->conn->query("SELECT COUNT(*) FROM alunos")->fetchColumn();
    }
    
    public function contarPorStatus($status) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM alunos WHERE status = :status");
        $stmt->execute([':status' => $status]);
        return $stmt->fetchColumn();
    }

    public function buscarRecentes($limite = 10) {
        // Ordenado por ID DESC (O mais novo primeiro)
        $stmt = $this->conn->prepare("SELECT * FROM alunos ORDER BY id DESC LIMIT :limite");
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function pesquisar($termo) {
        $termo = "%$termo%";
        $stmt = $this->conn->prepare("SELECT * FROM alunos WHERE nome LIKE :termo OR email LIKE :termo OR cpf LIKE :termo ORDER BY id DESC");
        $stmt->execute([':termo' => $termo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- FUNÇÕES DE ADMINISTRAÇÃO (EDITAR/STATUS/EXCLUIR) ---

    // 1. Atualizar Status
    public function atualizarStatus($id, $novoStatus) {
        $stmt = $this->conn->prepare("UPDATE alunos SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $novoStatus, ':id' => $id]);
    }

    // 2. Editar Dados (COM SENHA OPCIONAL)
    public function atualizarDadosAdmin($id, $nome, $email, $telefone, $plano, $objetivo, $novaSenha = null) {
        $sql = "UPDATE alunos SET nome = :nome, email = :email, telefone = :telefone, plano = :plano, objetivo = :objetivo";
        $params = [
            ':nome' => $nome, ':email' => $email, ':telefone' => $telefone,
            ':plano' => $plano, ':objetivo' => $objetivo, ':id' => $id
        ];

        // Se o admin digitou uma senha nova, atualiza ela também
        if (!empty($novaSenha)) {
            $sql .= ", senha = :senha";
            $params[':senha'] = password_hash($novaSenha, PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    // 3. Excluir Aluno
    public function excluirAluno($id) {
        $stmt = $this->conn->prepare("DELETE FROM alunos WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
    
    // Validar Recuperação (Login)
    public function validarRecuperacao($email, $cpf, $data_nascimento) {
        $stmt = $this->conn->prepare("SELECT id, nome, senha FROM alunos WHERE email = :email AND cpf = :cpf AND data_nascimento = :data");
        $stmt->execute([':email' => $email, ':cpf' => $cpf, ':data' => $data_nascimento]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function atualizarSenha($id, $novaSenha) {
        $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("UPDATE alunos SET senha = :senha WHERE id = :id");
        $stmt->execute([':senha' => $hash, ':id' => $id]);
    }
}