<?php 

    public function criarUsuario($nome, $email, $senha, $perfil = 'aluno') {
        // NOTE: Em produção, a senha deve ser criptografada com password_hash()
        $query = "INSERT INTO " . $this->table . " SET nome=:nome, email=:email, senha=:senha, perfil=:perfil";

        $stmt = $this->conn->prepare($query);

        // Limpeza de dados para segurança
        $nome = htmlspecialchars(strip_tags($nome));
        $email = htmlspecialchars(strip_tags($email));
        
        // Bind dos valores
        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":senha", $senha); // USE password_hash() AQUI EM PRODUÇÃO
        $stmt->bindParam(":perfil", $perfil);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>