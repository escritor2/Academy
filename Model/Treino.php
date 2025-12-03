<?php
class Treino {
    private $conn;
    private $table = "treinos";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function lerPorAluno($aluno_id) {
        // Fazemos um JOIN para pegar o nome do exercício também
        $query = "SELECT t.tipo, t.series, t.repeticoes, t.carga, e.nome, e.equipamento 
                  FROM " . $this->table . " t
                  JOIN exercicios e ON t.exercicio_id = e.id
                  WHERE t.aluno_id = :aluno_id
                  ORDER BY t.tipo, e.nome";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":aluno_id", $aluno_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>