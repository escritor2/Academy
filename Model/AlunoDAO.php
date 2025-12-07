<?php
require_once __DIR__ . '/Aluno.php';
require_once __DIR__ . '/connection.php';

class AlunoDAO {
    private $conn;

    public function __construct() {
        $this->conn = Connection::getInstance();
        $this->inicializarTabela();
    }

    // --- CRIAÇÃO E ATUALIZAÇÃO AUTOMÁTICA DO BANCO ---
    private function inicializarTabela() {
        // 1. Tabela de Alunos
        $sqlAlunos = "CREATE TABLE IF NOT EXISTS alunos (
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
        $this->conn->exec($sqlAlunos);

        // 2. Tabela de Frequência (TURBINADA)
        $sqlFreq = "CREATE TABLE IF NOT EXISTS frequencia (
            id INT AUTO_INCREMENT PRIMARY KEY,
            aluno_id INT NOT NULL,
            data_treino DATE NOT NULL,
            hora_entrada DATETIME DEFAULT NULL,
            hora_saida DATETIME DEFAULT NULL,
            FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE
        )";
        $this->conn->exec($sqlFreq);

        // --- MIGRATIONS (Correções para bancos já existentes) ---
        try { $this->conn->exec("ALTER TABLE alunos ADD COLUMN status VARCHAR(20) DEFAULT 'Ativo'"); } catch (Exception $e) {}
        try { $this->conn->exec("ALTER TABLE alunos ADD COLUMN criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP"); } catch (Exception $e) {}
        
        // Adiciona colunas de hora se a tabela frequência já existia na versão antiga
        try { $this->conn->exec("ALTER TABLE frequencia ADD COLUMN hora_entrada DATETIME DEFAULT NULL"); } catch (Exception $e) {}
        try { $this->conn->exec("ALTER TABLE frequencia ADD COLUMN hora_saida DATETIME DEFAULT NULL"); } catch (Exception $e) {}
    }

    // --- CADASTRO ---
    public function criarAluno(Aluno $aluno) {
        $senhaHash = password_hash($aluno->getSenha(), PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO alunos (nome, data_nascimento, email, telefone, cpf, genero, senha, objetivo, plano, status) VALUES (:nome, :data_nascimento, :email, :telefone, :cpf, :genero, :senha, :objetivo, :plano, 'Ativo')");
        $stmt->execute([
            ':nome' => $aluno->getNome(), ':data_nascimento' => $aluno->getDataNascimento(), ':email' => $aluno->getEmail(),
            ':telefone' => $aluno->getTelefone(), ':cpf' => $aluno->getCpf(), ':genero' => $aluno->getGenero(),
            ':senha' => $senhaHash, ':objetivo' => $aluno->getObjetivo(), ':plano' => $aluno->getPlano()
        ]);
    }

    // --- LOGIN E RECUPERAÇÃO ---
    public function buscarPorEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM alunos WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id) {
        $stmt = $this->conn->prepare("SELECT * FROM alunos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

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

    // --- ADMINISTRAÇÃO ---
    public function contarTotal() { return $this->conn->query("SELECT COUNT(*) FROM alunos")->fetchColumn(); }
    public function contarPorStatus($status) { $stmt = $this->conn->prepare("SELECT COUNT(*) FROM alunos WHERE status = :status"); $stmt->execute([':status' => $status]); return $stmt->fetchColumn(); }
    
    public function buscarRecentes($limite = 10) {
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

    public function atualizarStatus($id, $novoStatus) {
        $stmt = $this->conn->prepare("UPDATE alunos SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $novoStatus, ':id' => $id]);
    }

    public function atualizarDadosAdmin($id, $nome, $email, $telefone, $plano, $objetivo, $novaSenha = null) {
        $sql = "UPDATE alunos SET nome = :nome, email = :email, telefone = :telefone, plano = :plano, objetivo = :objetivo";
        $params = [':nome' => $nome, ':email' => $email, ':telefone' => $telefone, ':plano' => $plano, ':objetivo' => $objetivo, ':id' => $id];
        if (!empty($novaSenha)) { $sql .= ", senha = :senha"; $params[':senha'] = password_hash($novaSenha, PASSWORD_DEFAULT); }
        $sql .= " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function excluirAluno($id) {
        $stmt = $this->conn->prepare("DELETE FROM alunos WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // --- ÁREA DO ALUNO (FREQUÊNCIA, HORÁRIOS E PERFIL) ---

    // 1. Verifica status (Entrou? Saiu? Não foi?)
    public function getStatusFrequenciaHoje($alunoId) {
        $hoje = date('Y-m-d');
        $stmt = $this->conn->prepare("SELECT * FROM frequencia WHERE aluno_id = :id AND data_treino = :data");
        $stmt->execute([':id' => $alunoId, ':data' => $hoje]);
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$registro) {
            return 'nao_entrou'; 
        } elseif ($registro['hora_entrada'] && !$registro['hora_saida']) {
            return 'treinando'; 
        } else {
            return 'finalizado';
        }
    }

    // 2. Registrar Entrada (Check-in)
    public function registrarEntrada($alunoId) {
        $hoje = date('Y-m-d');
        $agora = date('Y-m-d H:i:s');
        
        if ($this->getStatusFrequenciaHoje($alunoId) == 'nao_entrou') {
            $stmt = $this->conn->prepare("INSERT INTO frequencia (aluno_id, data_treino, hora_entrada) VALUES (:id, :data, :hora)");
            return $stmt->execute([':id' => $alunoId, ':data' => $hoje, ':hora' => $agora]);
        }
        return false;
    }

    // 3. Registrar Saída (Check-out)
    public function registrarSaida($alunoId) {
        $hoje = date('Y-m-d');
        $agora = date('Y-m-d H:i:s');
        
        $stmt = $this->conn->prepare("UPDATE frequencia SET hora_saida = :hora WHERE aluno_id = :id AND data_treino = :data AND hora_saida IS NULL");
        return $stmt->execute([':hora' => $agora, ':id' => $alunoId, ':data' => $hoje]);
    }

    // 4. Calcular tempo de treino
    public function getTempoTreinoHoje($alunoId) {
        $hoje = date('Y-m-d');
        $stmt = $this->conn->prepare("SELECT TIMEDIFF(hora_saida, hora_entrada) as tempo FROM frequencia WHERE aluno_id = :id AND data_treino = :data");
        $stmt->execute([':id' => $alunoId, ':data' => $hoje]);
        return $stmt->fetchColumn();
    }

    // 5. Histórico para o Calendário
    public function getFrequenciaMes($alunoId, $mes, $ano) {
        $stmt = $this->conn->prepare("SELECT DAY(data_treino) as dia FROM frequencia WHERE aluno_id = :id AND MONTH(data_treino) = :mes AND YEAR(data_treino) = :ano");
        $stmt->execute([':id' => $alunoId, ':mes' => $mes, ':ano' => $ano]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // 6. Editar Perfil (Aba Perfil)
    public function atualizarPerfilAluno($id, $nome, $email, $telefone, $senha = null): mixed {
        $sql = "UPDATE alunos SET nome = :nome, email = :email, telefone = :telefone";
        $params = [':nome' => $nome, ':email' => $email, ':telefone' => $telefone, ':id' => $id];
        if (!empty($senha)) { $sql .= ", senha = :senha"; $params[':senha'] = password_hash($senha, PASSWORD_DEFAULT); }
        $sql .= " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }
}