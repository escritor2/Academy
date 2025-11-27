<?php
require_once __DIR__ . '/Venda.php';
require_once __DIR__ . '/Connect.php';

class VendasDAO {
    private $pdo;

    public function __construct() {
        $this->pdo = Connect::connect();
        $this->criarTabelaSeNaoExistir();
    }

    private function criarTabelaSeNaoExistir() {
        $sql = "CREATE TABLE IF NOT EXISTS vendas (
            id VARCHAR(20) PRIMARY KEY,
            produto VARCHAR(100) NOT NULL,
            cliente VARCHAR(100) NOT NULL,
            quantidade INT NOT NULL,
            valorTotal DECIMAL(10,2) NOT NULL,
            data TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->pdo->exec($sql);
    }

    public function criarVenda(Venda $venda) {
        $id = uniqid();
        $sql = "INSERT INTO vendas (id, produto, cliente, quantidade, valorTotal, data) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        
        try {
            $stmt->execute([
                $id,
                $venda->getProduto(),
                $venda->getCliente(),
                $venda->getQuantidade(),
                $venda->getValorTotal(),
                $venda->getData()
            ]);
            return $id;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function lerVendas() {
        $sql = "SELECT * FROM vendas ORDER BY data DESC";
        $stmt = $this->pdo->query($sql);
        $vendas = [];
        
        while ($row = $stmt->fetch()) {
            $venda = new Venda(
                $row['produto'],
                $row['cliente'],
                $row['quantidade'],
                $row['valorTotal'],
                $row['data']
            );
            $venda->setId($row['id']);
            $vendas[$row['id']] = $venda;
        }
        return $vendas;
    }

    public function buscarPorId($id) {
        $sql = "SELECT * FROM vendas WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        
        $row = $stmt->fetch();
        if ($row) {
            $venda = new Venda(
                $row['produto'],
                $row['cliente'],
                $row['quantidade'],
                $row['valorTotal'],
                $row['data']
            );
            $venda->setId($row['id']);
            return $venda;
        }
        return null;
    }

    public function excluirVenda($id) {
        $sql = "DELETE FROM vendas WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([$id]);
    }
}
?>