<?php
class Produto {
    private $conn;
    private $table_name = "produtos";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Ler todos os produtos (Substitui o array $produtos do adm.php)
    public function lerTodos() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Criar produto
    public function criar($nome, $categoria, $qtd, $preco) {
        $query = "INSERT INTO " . $this->table_name . " SET nome=:nome, categoria=:cat, quantidade=:qtd, preco=:preco";
        $stmt = $this->conn->prepare($query);
        
        // Bind dos valores
        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":cat", $categoria);
        $stmt->bindParam(":qtd", $qtd);
        $stmt->bindParam(":preco", $preco);

        return $stmt->execute();
    }
}
?>