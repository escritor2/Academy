<?php
class Database {
    // Configurações do Banco
    private $host = "localhost";
    private $db_name = "techfit_ofc";
    private $username = "root";
    private $password = "senaisp"; 
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            // 1. Tenta conectar direto no banco
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");

        } catch(PDOException $e) {
            // 2. Erro 1049 = Banco não existe. Então vamos criar TUDO.
            if ($e->getCode() == 1049) {
                return $this->criarTudoDoZero();
            } else {
                echo "Erro de conexão: " . $e->getMessage();
                exit;
            }
        }

        return $this->conn;
    }

    // --- A MÁGICA ACONTECE AQUI ---
    private function criarTudoDoZero() {
        try {
            // Conecta sem banco
            $pdo = new PDO("mysql:host=" . $this->host, $this->username, $this->password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 1. Cria o Banco
            $pdo->exec("CREATE DATABASE IF NOT EXISTS " . $this->db_name);
            $pdo->exec("USE " . $this->db_name);

            // 2. Tabela USUARIOS
            $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                senha VARCHAR(255) NOT NULL,
                perfil ENUM('admin', 'aluno', 'professor', 'recepcao') NOT NULL,
                criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            // 3. Tabela ALUNOS
            $pdo->exec("CREATE TABLE IF NOT EXISTS alunos (
                usuario_id INT PRIMARY KEY,
                plano ENUM('Básico', 'Premium') DEFAULT 'Básico',
                status ENUM('Ativo', 'Inativo', 'Pendente') DEFAULT 'Pendente',
                peso DECIMAL(5,2),
                altura DECIMAL(3,2),
                meta_agua INT DEFAULT 3000,
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
            )");

            // 4. Tabela PRODUTOS
            $pdo->exec("CREATE TABLE IF NOT EXISTS produtos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                categoria VARCHAR(50),
                quantidade INT DEFAULT 0,
                preco DECIMAL(10, 2)
            )");

            // 5. Tabela EXERCICIOS
            $pdo->exec("CREATE TABLE IF NOT EXISTS exercicios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                equipamento VARCHAR(50),
                grupo_muscular VARCHAR(50)
            )");

            // 6. Tabela TREINOS
            $pdo->exec("CREATE TABLE IF NOT EXISTS treinos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                aluno_id INT NOT NULL,
                tipo CHAR(1) NOT NULL, 
                exercicio_id INT NOT NULL,
                series INT,
                repeticoes VARCHAR(20),
                carga INT,
                FOREIGN KEY (aluno_id) REFERENCES alunos(usuario_id) ON DELETE CASCADE,
                FOREIGN KEY (exercicio_id) REFERENCES exercicios(id)
            )");

            // 7. INSERIR DADOS PADRÃO (Admin e Exercícios)
            $this->popularBanco($pdo);

            // Define a conexão e retorna
            $this->conn = $pdo;
            return $this->conn;

        } catch (PDOException $e) {
            echo "<h1>Erro ao criar banco automaticamente:</h1>" . $e->getMessage();
            exit;
        }
    }

    private function popularBanco($pdo) {
        // Criar Admin se não existir
        $check = $pdo->query("SELECT id FROM usuarios WHERE email = 'admin@techfit.com'");
        if ($check->rowCount() == 0) {
            $senha = password_hash('admin123', PASSWORD_DEFAULT);
            $pdo->exec("INSERT INTO usuarios (nome, email, senha, perfil) VALUES ('Administrador', 'admin@techfit.com', '$senha', 'admin')");
        }

        // Criar Exercícios Básicos se não existir
        $checkEx = $pdo->query("SELECT id FROM exercicios LIMIT 1");
        if ($checkEx->rowCount() == 0) {
            $pdo->exec("INSERT INTO exercicios (nome, equipamento, grupo_muscular) VALUES 
                ('Supino Reto', 'Barra', 'Peito'),
                ('Agachamento Livre', 'Barra', 'Pernas'),
                ('Puxada Alta', 'Máquina', 'Costas'),
                ('Leg Press 45', 'Máquina', 'Pernas'),
                ('Rosca Direta', 'Halter', 'Bíceps')
            ");
        }
    }
}
?>