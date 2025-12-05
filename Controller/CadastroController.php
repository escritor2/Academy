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
        // Cadastro realizado com sucesso, volta para tela de cadastro/login
        header("Location: ../view/areacliente.php?cadastro=sucesso");
        exit;
    } else {
        // Se der erro (ex: email repetido)
        header("Location: ../view/areacliente.php?cadastro=erro&msg=Email ja existe");
        exit;
    }
}
?>