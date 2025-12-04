<?php
class Usuario {
    private $conn;
    private $table = "usuarios";

    public $id;
    public $nome;
    public $email;
    public $senha;
    public $perfil;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para ler usuários (usado no Painel Admin)
public function lerPorPerfil($perfil) {
        $query = "SELECT id, nome, email FROM " . $this->table . " WHERE perfil = :perfil";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":perfil", $perfil);
        $stmt->execute();
        return $stmt;
    }

    // --- NOVO MÉTODO DE CADASTRO CORRIGIDO ---
    public function criarUsuario($nome, $email, $senha, $perfil = 'aluno') {
        $query = "INSERT INTO " . $this->table . " SET nome=:nome, email=:email, senha=:senha, perfil=:perfil";

        $stmt = $this->conn->prepare($query);

        // 1. Limpeza básica (Segurança)
        $nome = htmlspecialchars(strip_tags($nome));
        $email = htmlspecialchars(strip_tags($email));
        
        // 2. Criptografia da Senha (CRUCIAL!)
        // O password_hash cria um hash seguro automaticamente
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        // 3. Bind dos valores
        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":senha", $senha_hash); // Salvamos o hash, não a senha pura
        $stmt->bindParam(":perfil", $perfil);

        // 4. Executa
        if($stmt->execute()) {
            return true;
        }

        // Se der erro (ex: email duplicado), retorna false
        return false;
    }

    // Adicione isso dentro da class Usuario, logo após o método criarUsuario
    
    public function login($email, $senha) {
        // 1. Busca o usuário pelo email
        $query = "SELECT id, nome, senha, perfil FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        // 2. Se encontrar o email
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // 3. Verifica a senha criptografada (hash)
            if (password_verify($senha, $row['senha'])) {
                return $row; // Retorna os dados do usuário (sucesso)
            }
        }
        return false; // Login falhou
    }
}


?>