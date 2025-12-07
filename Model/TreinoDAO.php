<?php
require_once __DIR__ . '/connection.php';

class TreinoDAO {
    private $conn;

    public function __construct() {
        $this->conn = Connection::getInstance();
        $this->inicializarTabelas();
    }

    private function inicializarTabelas() {
        // Tabela de Treinos dos Alunos (Já existia)
        $this->conn->exec("CREATE TABLE IF NOT EXISTS treinos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            aluno_id INT NOT NULL,
            divisao CHAR(1) NOT NULL,
            exercicio VARCHAR(100) NOT NULL,
            series VARCHAR(50) DEFAULT '3x12',
            FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE
        )");

        // Tabela de MODELOS (Nomes dos treinos: Hipertrofia A, etc)
        $this->conn->exec("CREATE TABLE IF NOT EXISTS modelos_treino (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL UNIQUE
        )");

        // Tabela de ITENS DO MODELO (Os exercícios do modelo)
        $this->conn->exec("CREATE TABLE IF NOT EXISTS modelos_itens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            modelo_id INT NOT NULL,
            divisao CHAR(1) NOT NULL,
            exercicio VARCHAR(100) NOT NULL,
            series VARCHAR(50) DEFAULT '3x12',
            FOREIGN KEY (modelo_id) REFERENCES modelos_treino(id) ON DELETE CASCADE
        )");
    }

    // --- FUNÇÕES DO ALUNO (INDIVIDUAL) ---
    public function buscarPorAluno($alunoId) {
        $stmt = $this->conn->prepare("SELECT * FROM treinos WHERE aluno_id = :id ORDER BY divisao, id");
        $stmt->execute([':id' => $alunoId]);
        $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $treinos = ['A' => [], 'B' => [], 'C' => []];
        foreach ($todos as $t) { $treinos[$t['divisao']][] = $t; }
        return $treinos;
    }

    public function salvarTreino($alunoId, $dadosTreino) {
        try {
            $this->conn->beginTransaction();
            $this->conn->prepare("DELETE FROM treinos WHERE aluno_id = :id")->execute([':id' => $alunoId]);
            $ins = $this->conn->prepare("INSERT INTO treinos (aluno_id, divisao, exercicio, series) VALUES (:id, :div, :exe, :ser)");
            foreach ($dadosTreino as $divisao => $exercicios) {
                if (!empty($exercicios)) {
                    foreach ($exercicios as $ex) {
                        if (!empty($ex['nome'])) {
                            $ins->execute([':id' => $alunoId, ':div' => $divisao, ':exe' => $ex['nome'], ':ser' => $ex['series'] ?? '3x12']);
                        }
                    }
                }
            }
            $this->conn->commit();
            return true;
        } catch (Exception $e) { $this->conn->rollBack(); return false; }
    }

    // --- FUNÇÕES DA BIBLIOTECA (MODELOS) ---
    
    // 1. Listar todos os nomes de modelos
    public function listarModelos() {
        return $this->conn->query("SELECT * FROM modelos_treino ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. Buscar detalhes de um modelo específico
    public function buscarModeloPorId($id) {
        $stmt = $this->conn->prepare("SELECT * FROM modelos_itens WHERE modelo_id = :id ORDER BY divisao, id");
        $stmt->execute([':id' => $id]);
        $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $treinos = ['A' => [], 'B' => [], 'C' => []];
        foreach ($todos as $t) { $treinos[$t['divisao']][] = $t; }
        return $treinos;
    }

    // 3. Salvar um NOVO modelo (ou atualizar se deletar antes)
    public function salvarModelo($nomeModelo, $dadosTreino) {
        try {
            $this->conn->beginTransaction();
            
            // Cria o cabeçalho do modelo
            $stmt = $this->conn->prepare("INSERT INTO modelos_treino (nome) VALUES (:nome)");
            $stmt->execute([':nome' => $nomeModelo]);
            $modeloId = $this->conn->lastInsertId();

            // Insere os itens
            $ins = $this->conn->prepare("INSERT INTO modelos_itens (modelo_id, divisao, exercicio, series) VALUES (:id, :div, :exe, :ser)");
            foreach ($dadosTreino as $divisao => $exercicios) {
                if (!empty($exercicios)) {
                    foreach ($exercicios as $ex) {
                        if (!empty($ex['nome'])) {
                            $ins->execute([':id' => $modeloId, ':div' => $divisao, ':exe' => $ex['nome'], ':ser' => $ex['series'] ?? '3x12']);
                        }
                    }
                }
            }
            $this->conn->commit();
            return true;
        } catch (Exception $e) { $this->conn->rollBack(); return false; }
    }

    // 4. Excluir Modelo
    public function excluirModelo($id) {
        $stmt = $this->conn->prepare("DELETE FROM modelos_treino WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}