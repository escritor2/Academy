<?php
require_once __DIR__ . '/Fornecedor.php';
require_once __DIR__ . '/Connect.php';

class FornecedorDAO {
    private $pdo;

    public function __construct() {
        $this->pdo = Connect::connect();
        $this->criarTabelaSeNaoExistir();
    }

    private function criarTabelaSeNaoExistir() {
        $sql = "CREATE TABLE IF NOT EXISTS fornecedores (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL UNIQUE,
            contato VARCHAR(100) NOT NULL,
            produto VARCHAR(100) NOT NULL,
            telefone VARCHAR(20) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->pdo->exec($sql);
    }

    public function criarFornecedor(Fornecedor $fornecedor) {
        $sql = "INSERT INTO fornecedores (nome, contato, produto, telefone) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        
        try {
            $stmt->execute([
                $fornecedor->getNome(),
                $fornecedor->getContato(),
                $fornecedor->getProduto(),
                $fornecedor->getTelefone()
            ]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function lerFornecedor() {
        $sql = "SELECT * FROM fornecedores";
        $stmt = $this->pdo->query($sql);
        $fornecedores = [];
        
        while ($row = $stmt->fetch()) {
            $fornecedores[$row['nome']] = new Fornecedor(
                $row['nome'],
                $row['contato'],
                $row['produto'],
                $row['telefone']
            );
        }
        return $fornecedores;
    }

    public function atualizarFornecedor($nome, $contato, $produto, $telefone) {
        $sql = "UPDATE fornecedores SET contato = ?, produto = ?, telefone = ? WHERE nome = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([$contato, $produto, $telefone, $nome]);
    }

    public function excluirFornecedor($nome) {
        $sql = "DELETE FROM fornecedores WHERE nome = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([$nome]);
    }
}
?>