<?php
require_once __DIR__ . '/Aluno.php';
require_once __DIR__ . '/Connect.php';

class AlunoDAO {
    private $pdo;

    public function __construct() {
        $this->pdo = Connect::connect();
        $this->criarTabelaSeNaoExistir();
    }

    private function criarTabelaSeNaoExistir() {
        $sql = "CREATE TABLE IF NOT EXISTS alunos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL UNIQUE,
            idade INT NOT NULL,
            cpf VARCHAR(14) NOT NULL UNIQUE,
            matricula VARCHAR(20) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->pdo->exec($sql);
    }


    public function criarAluno(Aluno $aluno) {
        $sql = "INSERT INTO alunos (nome, idade, cpf, matricula) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        
        try {
            $stmt->execute([
                $aluno->getNome(),
                $aluno->getIdade(),
                $aluno->getCpf(),
                $aluno->getMatricula()
            ]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

  
    public function lerAluno() {
        $sql = "SELECT * FROM alunos";
        $stmt = $this->pdo->query($sql);
        $alunos = [];
        
        while ($row = $stmt->fetch()) {
            $alunos[$row['nome']] = new Aluno(
                $row['nome'],
                $row['idade'],
                $row['cpf'],
                $row['matricula']
            );
        }
        return $alunos;
    }


    public function atualizarAluno($nome, $idade, $cpf, $matricula) {
        $sql = "UPDATE alunos SET idade = ?, cpf = ?, matricula = ? WHERE nome = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([$idade, $cpf, $matricula, $nome]);
    }

    public function excluirAluno($nome) {
        $sql = "DELETE FROM alunos WHERE nome = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([$nome]);
    }


    public function buscarPorNome($nome) {
        $sql = "SELECT * FROM alunos WHERE nome = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nome]);
        
        $row = $stmt->fetch();
        if ($row) {
            return new Aluno($row['nome'], $row['idade'], $row['cpf'], $row['matricula']);
        }
        return null;
    }
}
?>