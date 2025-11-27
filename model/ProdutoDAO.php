<?php
require_once __DIR__ . '/Produto.php';
require_once __DIR__ . '/Connect.php';

class ProdutoDAO {
    private $pdo;

    public function __construct() {
        $this->pdo = Connect::connect();
        $this->criarTabelaSeNaoExistir();
    }

    private function criarTabelaSeNaoExistir() {
        $sql = "CREATE TABLE IF NOT EXISTS produtos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL UNIQUE,
            categoria VARCHAR(50) NOT NULL,
            preco DECIMAL(10,2) NOT NULL,
            estoque INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->pdo->exec($sql);
    }

    public function criarProduto(Produto $produto) {
        $sql = "INSERT INTO produtos (nome, categoria, preco, estoque) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        
        try {
            $stmt->execute([
                $produto->getNome(),
                $produto->getCategoria(),
                $produto->getPreco(),
                $produto->getEstoque()
            ]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function lerProdutos() {
        $sql = "SELECT * FROM produtos";
        $stmt = $this->pdo->query($sql);
        $produtos = [];
        
        while ($row = $stmt->fetch()) {
            $produtos[$row['nome']] = new Produto(
                $row['nome'],
                $row['categoria'],
                $row['preco'],
                $row['estoque']
            );
        }
        return $produtos;
    }

    public function atualizarProduto($nome, $categoria, $preco, $estoque) {
        $sql = "UPDATE produtos SET categoria = ?, preco = ?, estoque = ? WHERE nome = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([$categoria, $preco, $estoque, $nome]);
    }

    public function excluirProduto($nome) {
        $sql = "DELETE FROM produtos WHERE nome = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([$nome]);
    }
}
?>