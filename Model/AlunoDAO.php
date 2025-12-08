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
        // Verifica se tem pelo menos 8 caracteres
        if (strlen($senha) < 8) {
            return "A senha deve ter pelo menos 8 caracteres.";
        }
        
        // Verifica se tem pelo menos uma letra maiúscula
        if (!preg_match('/[A-Z]/', $senha)) {
            return "A senha deve conter pelo menos uma letra maiúscula.";
        }
        
        // Verifica se tem pelo menos uma letra minúscula
        if (!preg_match('/[a-z]/', $senha)) {
            return "A senha deve conter pelo menos uma letra minúscula.";
        }
        
        // Verifica se tem pelo menos um número
        if (!preg_match('/[0-9]/', $senha)) {
            return "A senha deve conter pelo menos um número.";
        }
        
        // Verifica se tem pelo menos um caractere especial
        if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $senha)) {
            return "A senha deve conter pelo menos um caractere especial (!@#$%^&* etc).";
        }
        
        return true; // Senha válida
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
            foto_perfil VARCHAR(255) DEFAULT NULL,
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
        try { $this->conn->exec("ALTER TABLE alunos ADD COLUMN foto_perfil VARCHAR(255) DEFAULT NULL"); } catch (Exception $e) {}
        
        // Adiciona colunas de hora se a tabela frequência já existia na versão antiga
        try { $this->conn->exec("ALTER TABLE frequencia ADD COLUMN hora_entrada DATETIME DEFAULT NULL"); } catch (Exception $e) {}
        try { $this->conn->exec("ALTER TABLE frequencia ADD COLUMN hora_saida DATETIME DEFAULT NULL"); } catch (Exception $e) {}
    }

    // --- CADASTRO ---
    public function criarAluno(Aluno $aluno, $foto_perfil = null) {
        // Validação da senha antes de criar
        $validacaoSenha = $this->validarSenhaForte($aluno->getSenha());
        if ($validacaoSenha !== true) {
            throw new Exception($validacaoSenha);
        }
        
        // Limpar nome antes de salvar
        $nomeLimpo = trim(preg_replace('/\s+/', ' ', $aluno->getNome()));
        
        $senhaHash = password_hash($aluno->getSenha(), PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO alunos (nome, data_nascimento, email, telefone, cpf, genero, senha, objetivo, plano, status";
        $sqlValues = "VALUES (:nome, :data_nascimento, :email, :telefone, :cpf, :genero, :senha, :objetivo, :plano, 'Ativo'";
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
        
        if ($foto_perfil) {
            $sql .= ", foto_perfil";
            $sqlValues .= ", :foto_perfil";
            $params[':foto_perfil'] = $foto_perfil;
        }
        
        $sql .= ") " . $sqlValues . ")";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
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
        // Validação da nova senha
        $validacaoSenha = $this->validarSenhaForte($novaSenha);
        if ($validacaoSenha !== true) {
            throw new Exception($validacaoSenha);
        }
        
        $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("UPDATE alunos SET senha = :senha WHERE id = :id");
        $stmt->execute([':senha' => $hash, ':id' => $id]);
    }

    // --- ADMINISTRAÇÃO ---
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
        $stmt->execute([':status' => $novoStatus, ':id' => $id]);
    }

    public function atualizarDadosAdmin($id, $nome, $email, $telefone, $plano, $objetivo, $novaSenha = null, $foto_perfil = null) {
        // Limpar nome antes de atualizar
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
            // Validação da nova senha
            $validacaoSenha = $this->validarSenhaForte($novaSenha);
            if ($validacaoSenha !== true) {
                throw new Exception($validacaoSenha);
            }
            
            $sql .= ", senha = :senha"; 
            $params[':senha'] = password_hash($novaSenha, PASSWORD_DEFAULT); 
        }
        
        if (!empty($foto_perfil)) {
            $sql .= ", foto_perfil = :foto_perfil";
            $params[':foto_perfil'] = $foto_perfil;
        }
        
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
        $result = $stmt->fetchColumn();
        
        // Formatar o tempo para exibição mais amigável
        if ($result) {
            list($horas, $minutos, $segundos) = explode(':', $result);
            return "$horas:$minutos:$segundos";
        }
        
        return '00:00:00';
    }

    // 5. Obter hora de entrada hoje
    public function getHoraEntradaHoje($alunoId) {
        $hoje = date('Y-m-d');
        $stmt = $this->conn->prepare("SELECT hora_entrada FROM frequencia WHERE aluno_id = :id AND data_treino = :data");
        $stmt->execute([':id' => $alunoId, ':data' => $hoje]);
        return $stmt->fetchColumn();
    }

    // 6. Histórico para o Calendário
    public function getFrequenciaMes($alunoId, $mes, $ano) {
        $stmt = $this->conn->prepare("SELECT DAY(data_treino) as dia FROM frequencia WHERE aluno_id = :id AND MONTH(data_treino) = :mes AND YEAR(data_treino) = :ano");
        $stmt->execute([':id' => $alunoId, ':mes' => $mes, ':ano' => $ano]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // 7. Editar Perfil (Aba Perfil) - ATUALIZADA COM FOTO
    public function atualizarPerfilAluno($id, $nome, $email, $telefone, $senha = null, $foto_perfil = null) {
        // Limpar nome - remover espaços extras e normalizar
        $nomeLimpo = trim(preg_replace('/\s+/', ' ', $nome));
        
        // Validar que o nome não está vazio após limpeza
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
            // Validação da nova senha
            $validacaoSenha = $this->validarSenhaForte($senha);
            if ($validacaoSenha !== true) {
                throw new Exception($validacaoSenha);
            }
            
            $sql .= ", senha = :senha"; 
            $params[':senha'] = password_hash($senha, PASSWORD_DEFAULT); 
        }
        
        if (!empty($foto_perfil)) {
            $sql .= ", foto_perfil = :foto_perfil";
            $params[':foto_perfil'] = $foto_perfil;
        }
        
        $sql .= " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }
    
    // 8. Atualizar apenas a foto de perfil
    public function atualizarFotoPerfil($id, $foto_perfil) {
        $stmt = $this->conn->prepare("UPDATE alunos SET foto_perfil = :foto WHERE id = :id");
        return $stmt->execute([':foto' => $foto_perfil, ':id' => $id]);
    }
    
    // 9. Método auxiliar para formatar tempo
    public function formatarTempo($tempo) {
        if (!$tempo) return '00:00:00';
        
        // Se já está no formato HH:MM:SS
        if (strpos($tempo, ':') !== false) {
            $partes = explode(':', $tempo);
            if (count($partes) === 3) {
                return sprintf('%02d:%02d:%02d', $partes[0], $partes[1], $partes[2]);
            }
        }
        
        // Se é em segundos
        if (is_numeric($tempo)) {
            $horas = floor($tempo / 3600);
            $minutos = floor(($tempo % 3600) / 60);
            $segundos = $tempo % 60;
            return sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos);
        }
        
        return '00:00:00';
    }
    
    // 10. Obter estatísticas do mês
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
    
    // 11. Limpar nome (método público para uso externo)
    public function limparNome($nome) {
        return trim(preg_replace('/\s+/', ' ', $nome));
    }
    
    // 12. Processar upload de foto
    public function processarUploadFoto($arquivo, $alunoId) {
        // Configurações
        $diretorio = __DIR__ . '/../uploads/perfis/';
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $tamanhoMaximo = 5 * 1024 * 1024; // 5MB
        
        // Criar diretório se não existir
        if (!file_exists($diretorio)) {
            mkdir($diretorio, 0777, true);
        }
        
        // Verificar erros
        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Erro no upload: " . $arquivo['error']);
        }
        
        // Verificar tamanho
        if ($arquivo['size'] > $tamanhoMaximo) {
            throw new Exception("Arquivo muito grande. Máximo: 5MB");
        }
        
        // Verificar extensão
        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extensao, $extensoesPermitidas)) {
            throw new Exception("Extensão não permitida. Use: " . implode(', ', $extensoesPermitidas));
        }
        
        // Verificar se é realmente uma imagem
        $infoImagem = getimagesize($arquivo['tmp_name']);
        if (!$infoImagem) {
            throw new Exception("Arquivo não é uma imagem válida");
        }
        
        // Gerar nome único para o arquivo
        $nomeArquivo = 'aluno_' . $alunoId . '_' . time() . '_' . uniqid() . '.' . $extensao;
        $caminhoCompleto = $diretorio . $nomeArquivo;
        
        // Mover arquivo
        if (!move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
            throw new Exception("Falha ao salvar a imagem");
        }
        
        // Redimensionar imagem para tamanho padrão (200x200)
        $this->redimensionarImagem($caminhoCompleto, 200, 200);
        
        // Retornar caminho relativo para salvar no banco
        return 'uploads/perfis/' . $nomeArquivo;
    }
    
    // 13. Redimensionar imagem
    private function redimensionarImagem($caminho, $largura, $altura) {
        $info = getimagesize($caminho);
        if (!$info) return false;
        
        list($larguraOrig, $alturaOrig, $tipo) = $info;
        
        // Criar imagem a partir do tipo
        switch ($tipo) {
            case IMAGETYPE_JPEG:
                $origem = imagecreatefromjpeg($caminho);
                break;
            case IMAGETYPE_PNG:
                $origem = imagecreatefrompng($caminho);
                break;
            case IMAGETYPE_GIF:
                $origem = imagecreatefromgif($caminho);
                break;
            case IMAGETYPE_WEBP:
                $origem = imagecreatefromwebp($caminho);
                break;
            default:
                return false;
        }
        
        // Calcular proporção
        $proporcaoOrig = $larguraOrig / $alturaOrig;
        $proporcaoDest = $largura / $altura;
        
        if ($proporcaoOrig > $proporcaoDest) {
            $novaAltura = $altura;
            $novaLargura = $altura * $proporcaoOrig;
        } else {
            $novaLargura = $largura;
            $novaAltura = $largura / $proporcaoOrig;
        }
        
        // Criar nova imagem
        $destino = imagecreatetruecolor($largura, $altura);
        
        // Fundo transparente para PNG/GIF
        if ($tipo == IMAGETYPE_PNG || $tipo == IMAGETYPE_GIF) {
            imagealphablending($destino, false);
            imagesavealpha($destino, true);
            $transparente = imagecolorallocatealpha($destino, 0, 0, 0, 127);
            imagefill($destino, 0, 0, $transparente);
        } else {
            // Fundo branco para JPEG
            $branco = imagecolorallocate($destino, 255, 255, 255);
            imagefill($destino, 0, 0, $branco);
        }
        
        // Calcular posição para centralizar
        $x = ($largura - $novaLargura) / 2;
        $y = ($altura - $novaAltura) / 2;
        
        // Redimensionar com alta qualidade
        imagecopyresampled($destino, $origem, $x, $y, 0, 0, 
                          $novaLargura, $novaAltura, $larguraOrig, $alturaOrig);
        
        // Salvar imagem
        switch ($tipo) {
            case IMAGETYPE_JPEG:
                imagejpeg($destino, $caminho, 90);
                break;
            case IMAGETYPE_PNG:
                imagepng($destino, $caminho, 9);
                break;
            case IMAGETYPE_GIF:
                imagegif($destino, $caminho);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($destino, $caminho, 90);
                break;
        }
        
        // Liberar memória
        imagedestroy($origem);
        imagedestroy($destino);
        
        return true;
    }
    
    // 14. Deletar foto antiga
    public function deletarFotoAntiga($caminhoFoto) {
        if ($caminhoFoto && file_exists(__DIR__ . '/../' . $caminhoFoto)) {
            unlink(__DIR__ . '/../' . $caminhoFoto);
        }
    }
}
