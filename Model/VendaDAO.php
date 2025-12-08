<?php
require_once __DIR__ . '/connection.php';

class VendaDAO {
    private $conn;

    public function __construct() {
        $this->conn = Connection::getInstance();
        $this->inicializarTabela();
    }

    private function inicializarTabela() {
        $sql = "CREATE TABLE IF NOT EXISTS vendas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            aluno_id INT NOT NULL,
            produto_id INT NOT NULL,
            quantidade INT NOT NULL,
            valor_unitario DECIMAL(10,2) NOT NULL,
            valor_total DECIMAL(10,2) NOT NULL,
            forma_pagamento VARCHAR(50) NOT NULL,
            status VARCHAR(20) DEFAULT 'Concluída',
            data_venda TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
            FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
        )";
        $this->conn->exec($sql);
    }

    public function registrarVenda($aluno_id, $produto_id, $quantidade, $valor_unitario, $forma_pagamento) {
        $valor_total = $valor_unitario * $quantidade;
        
        $stmt = $this->conn->prepare("
            INSERT INTO vendas (aluno_id, produto_id, quantidade, valor_unitario, valor_total, forma_pagamento) 
            VALUES (:aluno_id, :produto_id, :quantidade, :valor_unitario, :valor_total, :forma_pagamento)
        ");
        
        return $stmt->execute([
            ':aluno_id' => $aluno_id,
            ':produto_id' => $produto_id,
            ':quantidade' => $quantidade,
            ':valor_unitario' => $valor_unitario,
            ':valor_total' => $valor_total,
            ':forma_pagamento' => $forma_pagamento
        ]);
    }

    public function buscarRecentes($limite = 10) {
        $stmt = $this->conn->prepare("
            SELECT v.*, a.nome as nome_cliente, p.nome as nome_produto 
            FROM vendas v
            JOIN alunos a ON v.aluno_id = a.id
            JOIN produtos p ON v.produto_id = p.id
            ORDER BY v.data_venda DESC 
            LIMIT :limite
        ");
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalVendas() {
        return $this->conn->query("SELECT COUNT(*) FROM vendas")->fetchColumn();
    }

   public function getFaturamentoTotal() {
    $result = $this->conn->query("SELECT COALESCE(SUM(valor_total), 0) FROM vendas")->fetchColumn();
    return floatval($result);
}

    public function getVendasPorAluno($aluno_id) {
        $stmt = $this->conn->prepare("
            SELECT v.*, p.nome as produto_nome 
            FROM vendas v
            JOIN produtos p ON v.produto_id = p.id
            WHERE v.aluno_id = :aluno_id
            ORDER BY v.data_venda DESC
        ");
        $stmt->execute([':aluno_id' => $aluno_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}