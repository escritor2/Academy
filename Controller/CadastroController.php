<?php
session_start();

// Caminhos absolutos para evitar erros
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../Model/Usuario.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($nome) || empty($email) || empty($senha)) {
        // Se faltar dados, volta para o cadastro com erro
        header("Location: ../view/areacliente.php?cadastro=erro&msg=Preencha tudo");
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();
    $usuario = new Usuario($db);

    // 1. Tenta Criar o Usuário
    if ($usuario->criarUsuario($nome, $email, $senha, 'aluno')) {
        
        // 2. SUCESSO! Agora faz o login automático
        $dados = $usuario->login($email, $senha);

        if ($dados) {
            $_SESSION['user_id'] = $dados['id'];
            $_SESSION['user_nome'] = $dados['nome'];
            $_SESSION['user_perfil'] = 'aluno';

            // 3. REDIRECIONA PARA O TEMPLATE DO ALUNO
            header("Location: ../view/paginacliente.php");
            exit;
        }
    } else {
        // Se der erro (ex: email repetido)
        header("Location: ../view/areacliente.php?cadastro=erro&msg=Email ja existe");
        exit;
    }
}
?>