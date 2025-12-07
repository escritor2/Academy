<?php
require_once __DIR__ . '/connection.php';

class ProdutoDAO {
    private $conn;

    public function __construct() {
        $this->conn = Connection::getInstance();
        $this->inicializarTabela();
    }

    private function inicializarTabela() {
        // Tabela com ESTOQUE
        $sql = "CREATE TABLE IF NOT EXISTS produtos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL UNIQUE,
            preco DECIMAL(10,2) NOT NULL,
            estoque INT DEFAULT 0,  -- NOVO CAMPO
            categoria VARCHAR(50) NOT NULL,
            descricao VARCHAR(255),
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->conn->exec($sql);

        // Migration: Adiciona a coluna se a tabela já existir sem ela
        try { $this->conn->exec("ALTER TABLE produtos ADD COLUMN estoque INT DEFAULT 0"); } catch (Exception $e) {}
    }

    public function cadastrar($nome, $preco, $estoque, $categoria, $descricao) {
        // Verifica duplicidade
        $check = $this->conn->prepare("SELECT id FROM produtos WHERE nome = :n");
        $check->execute([':n' => $nome]);
        if ($check->rowCount() > 0) return false;

        $stmt = $this->conn->prepare("INSERT INTO produtos (nome, preco, estoque, categoria, descricao) VALUES (:n, :p, :e, :c, :d)");
        return $stmt->execute([':n' => $nome, ':p' => $preco, ':e' => $estoque, ':c' => $categoria, ':d' => $descricao]);
    }

    public function listar() {
        $stmt = $this->conn->query("SELECT * FROM produtos ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Excluir um
    public function excluir($id) {
        $stmt = $this->conn->prepare("DELETE FROM produtos WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // Excluir Vários (Bulk Delete)
    public function excluirLista($ids) {
        if (empty($ids)) return false;
        // Cria uma string de interrogações: ?,?,?
        $inQuery = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->conn->prepare("DELETE FROM produtos WHERE id IN ($inQuery)");
        return $stmt->execute($ids);
    }

    // Calcular totais para o Dashboard da Loja
    public function getTotais() {
        $qtd = $this->conn->query("SELECT COUNT(*) FROM produtos")->fetchColumn();
        $valor = $this->conn->query("SELECT SUM(preco * estoque) FROM produtos")->fetchColumn();
        return ['qtd' => $qtd, 'valor' => $valor ?: 0];
    }
}