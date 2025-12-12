<?php
require_once __DIR__ . '/Aluno.php';
require_once __DIR__ . '/connection.php';

class AlunoDAO {
    private $conn;

    public function __construct() {
        $this->conn = Connection::getInstance();
        $this->inicializarTabela();
    }

    // --- VALIDAÇÃO DE SENHA FORTE ---
    public function validarSenhaForte($senha) {
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

    // --- CRIAÇÃO E ATUALIZAÇÃO AUTOMÁTICA DO BANCO ---
    private function inicializarTabela() {
        // Tabela de Alunos - VERSÃO SIMPLIFICADA
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
            status2 VARCHAR(20) DEFAULT 'Ativo',
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        try {
            $this->conn->exec($sqlAlunos);
        } catch (Exception $e) {
            error_log("Erro ao criar tabela alunos: " . $e->getMessage());
        }

        // Tabela de Frequência
        $sqlFreq = "CREATE TABLE IF NOT EXISTS frequencia (
            id INT AUTO_INCREMENT PRIMARY KEY,
            aluno_id INT NOT NULL,
            data_treino DATE NOT NULL,
            hora_entrada DATETIME DEFAULT NULL,
            hora_saida DATETIME DEFAULT NULL,
            FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE
        )";
        
        try {
            $this->conn->exec($sqlFreq);
        } catch (Exception $e) {
            error_log("Erro ao criar tabela frequencia: " . $e->getMessage());
        }
    }

    // --- CADASTRO (VERSÃO SIMPLIFICADA E FUNCIONAL) ---
    public function criarAluno(Aluno $aluno) {
    try {
        // Validação da senha
        $validacaoSenha = $this->validarSenhaForte($aluno->getSenha());
        if ($validacaoSenha !== true) {
            throw new Exception($validacaoSenha);
        }
        
        // Limpar nome
        $nomeLimpo = trim(preg_replace('/\s+/', ' ', $aluno->getNome()));
        
        // Hash da senha
        $senhaHash = password_hash($aluno->getSenha(), PASSWORD_DEFAULT);
        
        // Query CORRIGIDA - removido o parâmetro foto_perfil que não existe
        $sql = "INSERT INTO alunos 
                (nome, data_nascimento, email, telefone, cpf, genero, senha, objetivo, plano, status) 
                VALUES 
                (:nome, :data_nascimento, :email, :telefone, :cpf, :genero, :senha, :objetivo, :plano, 'Ativo')";
        
        $params = [
            ':nome' => $nomeLimpo, 
            ':data_nascimento' => $aluno->getDataNascimento(), 
            ':email' => $aluno->getEmail(),
            ':telefone' => $aluno->getTelefone(), 
            ':cpf' => $aluno->getCpf(), 
            ':genero' => $aluno->getGenero(),
            ':senha' => $senhaHash, 
            ':objetivo' => $aluno->getObjetivo(), 
            ':plano' => $aluno->getPlano()
        ];
        
        $stmt = $this->conn->prepare($sql);
        
        // Executar e verificar
        if ($stmt->execute($params)) {
            return $this->conn->lastInsertId(); // Retorna o ID do aluno cadastrado
        } else {
            $error = $stmt->errorInfo();
            throw new Exception("Erro no banco de dados: " . $error[2]);
        }
        
    } catch (PDOException $e) {
        // Tratar erros específicos do PDO
        if ($e->getCode() == 23000) { // Violação de unicidade (email ou CPF duplicado)
            if (strpos($e->getMessage(), 'email') !== false) {
                throw new Exception("Este e-mail já está cadastrado.");
            } elseif (strpos($e->getMessage(), 'cpf') !== false) {
                throw new Exception("Este CPF já está cadastrado.");
            } else {
                throw new Exception("Dados duplicados. Verifique email e CPF.");
            }
        } else {
            throw new Exception("Erro ao cadastrar: " . $e->getMessage());
        }
    } catch (Exception $e) {
        throw $e; // Re-lançar exceções de validação
    }
}

    // --- LOGIN E RECUPERAÇÃO ---
    public function buscarPorEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM alunos WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $aluno = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($aluno && empty($aluno['foto_perfil'])) {
            $aluno['foto_perfil'] = 'user';
        }
        return $aluno;
    }

    public function buscarPorId($id) {
        $stmt = $this->conn->prepare("SELECT * FROM alunos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $aluno = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($aluno && empty($aluno['foto_perfil'])) {
            $aluno['foto_perfil'] = 'user';
        }
        return $aluno;
    }

    public function validarRecuperacao($email, $cpf, $data_nascimento) {
        $stmt = $this->conn->prepare("SELECT id, nome, senha FROM alunos WHERE email = :email AND cpf = :cpf AND data_nascimento = :data");
        $stmt->execute([':email' => $email, ':cpf' => $cpf, ':data' => $data_nascimento]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizarSenha($id, $novaSenha) {
        $validacaoSenha = $this->validarSenhaForte($novaSenha);
        if ($validacaoSenha !== true) {
            throw new Exception($validacaoSenha);
        }
        
        $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("UPDATE alunos SET senha = :senha WHERE id = :id");
        return $stmt->execute([':senha' => $hash, ':id' => $id]);
    }


    public function contarTotal() { 
        return $this->conn->query("SELECT COUNT(*) FROM alunos")->fetchColumn(); 
    }
    
    public function contarPorStatus($status) { 
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM alunos WHERE status = :status"); 
        $stmt->execute([':status' => $status]); 
        return $stmt->fetchColumn(); 
    }
    
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
        return $stmt->execute([':status' => $novoStatus, ':id' => $id]);
    }

    public function atualizarDadosAdmin($id, $nome, $email, $telefone, $plano, $objetivo, $novaSenha = null, $icone_perfil = null) {
        $nomeLimpo = trim(preg_replace('/\s+/', ' ', $nome));
        
        $sql = "UPDATE alunos SET nome = :nome, email = :email, telefone = :telefone, plano = :plano, objetivo = :objetivo";
        $params = [
            ':nome' => $nomeLimpo, 
            ':email' => $email, 
            ':telefone' => $telefone, 
            ':plano' => $plano, 
            ':objetivo' => $objetivo, 
            ':id' => $id
        ];
        
        if (!empty($novaSenha)) { 
            $validacaoSenha = $this->validarSenhaForte($novaSenha);
            if ($validacaoSenha !== true) {
                throw new Exception($validacaoSenha);
            }
            
            $sql .= ", senha = :senha"; 
            $params[':senha'] = password_hash($novaSenha, PASSWORD_DEFAULT); 
        }
        
        if (!empty($icone_perfil)) {
            $sql .= ", foto_perfil = :foto_perfil";
            $params[':foto_perfil'] = $icone_perfil;
        }
        
        $sql .= " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function excluirAluno($id) {
        $stmt = $this->conn->prepare("DELETE FROM alunos WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // --- ÁREA DO ALUNO ---
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

    public function registrarEntrada($alunoId) {
        $hoje = date('Y-m-d');
        $agora = date('Y-m-d H:i:s');
        
        if ($this->getStatusFrequenciaHoje($alunoId) == 'nao_entrou') {
            $stmt = $this->conn->prepare("INSERT INTO frequencia (aluno_id, data_treino, hora_entrada) VALUES (:id, :data, :hora)");
            return $stmt->execute([':id' => $alunoId, ':data' => $hoje, ':hora' => $agora]);
        }
        return false;
    }

    public function registrarSaida($alunoId) {
        $hoje = date('Y-m-d');
        $agora = date('Y-m-d H:i:s');
        
        $stmt = $this->conn->prepare("UPDATE frequencia SET hora_saida = :hora WHERE aluno_id = :id AND data_treino = :data AND hora_saida IS NULL");
        return $stmt->execute([':hora' => $agora, ':id' => $alunoId, ':data' => $hoje]);
    }

    public function getTempoTreinoHoje($alunoId) {
        $hoje = date('Y-m-d');
        $stmt = $this->conn->prepare("SELECT TIMEDIFF(hora_saida, hora_entrada) as tempo FROM frequencia WHERE aluno_id = :id AND data_treino = :data");
        $stmt->execute([':id' => $alunoId, ':data' => $hoje]);
        $result = $stmt->fetchColumn();
        
        if ($result) {
            list($horas, $minutos, $segundos) = explode(':', $result);
            return "$horas:$minutos:$segundos";
        }
        
        return '00:00:00';
    }

    public function getHoraEntradaHoje($alunoId) {
        $hoje = date('Y-m-d');
        $stmt = $this->conn->prepare("SELECT hora_entrada FROM frequencia WHERE aluno_id = :id AND data_treino = :data");
        $stmt->execute([':id' => $alunoId, ':data' => $hoje]);
        return $stmt->fetchColumn();
    }

    public function getFrequenciaMes($alunoId, $mes, $ano) {
        $stmt = $this->conn->prepare("SELECT DAY(data_treino) as dia FROM frequencia WHERE aluno_id = :id AND MONTH(data_treino) = :mes AND YEAR(data_treino) = :ano");
        $stmt->execute([':id' => $alunoId, ':mes' => $mes, ':ano' => $ano]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function atualizarPerfilAluno($id, $nome, $email, $telefone, $senha = null, $icone_perfil = null) {
        $nomeLimpo = trim(preg_replace('/\s+/', ' ', $nome));
        
        if (empty($nomeLimpo)) {
            return false;
        }
        
        $sql = "UPDATE alunos SET nome = :nome, email = :email, telefone = :telefone";
        $params = [
            ':nome' => $nomeLimpo, 
            ':email' => $email, 
            ':telefone' => $telefone, 
            ':id' => $id
        ];
        
        if (!empty($senha)) { 
            $validacaoSenha = $this->validarSenhaForte($senha);
            if ($validacaoSenha !== true) {
                throw new Exception($validacaoSenha);
            }
            
            $sql .= ", senha = :senha"; 
            $params[':senha'] = password_hash($senha, PASSWORD_DEFAULT); 
        }
        
        if (!empty($icone_perfil)) {
            $sql .= ", foto_perfil = :foto_perfil";
            $params[':foto_perfil'] = $icone_perfil;
        }
        
        $sql .= " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function atualizarFotoPerfil($id, $icone_perfil) {
        $stmt = $this->conn->prepare("UPDATE alunos SET foto_perfil = :foto WHERE id = :id");
        return $stmt->execute([':foto' => $icone_perfil, ':id' => $id]);
    }
    
    // Método para deletar foto antiga (removido o upload físico)
    public function deletarFotoAntiga($fotoCaminho) {
        return true;
    }
    
    // Método para processar upload de foto (removido)
    public function processarUploadFoto($arquivo, $alunoId) {
        return 'user';
    }
    
    public function getEstatisticasMes($alunoId, $mes = null, $ano = null) {
        if (!$mes) $mes = date('n');
        if (!$ano) $ano = date('Y');
        
        $stmt = $this->conn->prepare("
            SELECT 
                COUNT(*) as total_dias,
                SUM(TIMESTAMPDIFF(MINUTE, hora_entrada, hora_saida)) as total_minutos,
                AVG(TIMESTAMPDIFF(MINUTE, hora_entrada, hora_saida)) as media_minutos
            FROM frequencia 
            WHERE aluno_id = :id 
            AND MONTH(data_treino) = :mes 
            AND YEAR(data_treino) = :ano
            AND hora_saida IS NOT NULL
        ");
        
        $stmt->execute([':id' => $alunoId, ':mes' => $mes, ':ano' => $ano]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function limparNome($nome) {
        return trim(preg_replace('/\s+/', ' ', $nome));
    }
}