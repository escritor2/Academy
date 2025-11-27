<?php
require_once __DIR__ . '/Funcionario.php';
require_once __DIR__ . '/Connect.php';

class FuncionarioDAO {
    private $pdo;

    public function __construct() {
        $this->pdo = Connect::connect();
        $this->criarTabelaSeNaoExistir();
    }

    private function criarTabelaSeNaoExistir() {
        $sql = "CREATE TABLE IF NOT EXISTS funcionarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL UNIQUE,
            cargo VARCHAR(50) NOT NULL,
            salario DECIMAL(10,2) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->pdo->exec($sql);
    }

    public function criarFuncionario(Funcionario $funcionario) {
        $sql = "INSERT INTO funcionarios (nome, cargo, salario, email) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        
        try {
            $stmt->execute([
                $funcionario->getNome(),
                $funcionario->getCargo(),
                $funcionario->getSalario(),
                $funcionario->getEmail()
            ]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function lerFuncionarios() {
        $sql = "SELECT * FROM funcionarios";
        $stmt = $this->pdo->query($sql);
        $funcionarios = [];
        
        while ($row = $stmt->fetch()) {
            $funcionarios[$row['nome']] = new Funcionario(
                $row['nome'],
                $row['cargo'],
                $row['salario'],
                $row['email']
            );
        }
        return $funcionarios;
    }

    public function atualizarFuncionario($nome, $cargo, $salario, $email) {
        $sql = "UPDATE funcionarios SET cargo = ?, salario = ?, email = ? WHERE nome = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([$cargo, $salario, $email, $nome]);
    }

    public function excluirFuncionario($nome) {
        $sql = "DELETE FROM funcionarios WHERE nome = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([$nome]);
    }
}
?>