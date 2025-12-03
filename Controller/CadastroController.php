<?php
// controllers/CadastroController.php

require_once '../config/Database.php';
require_once '../Model/Usuario.php';

// Verifica se o formulário foi enviado via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Pega os dados do formulário
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    // Validação básica (adicione validação mais robusta!)
    if (empty($nome) || empty($email) || empty($senha)) {
        header("Location: ../areacliente.php?cadastro=erro&msg=Preencha todos os campos.");
        exit;
    }

    // Conecta e Instancia
    $database = new Database();
    $db = $database->getConnection();
    $usuario = new Usuario($db);

    // Tenta criar o usuário
    if ($usuario->criarUsuario($nome, $email, $senha, 'aluno')) {
        // Sucesso: Redireciona para login
        header("Location: ../areacliente.php?cadastro=sucesso");
        exit;
    } else {
        // Erro: Redireciona de volta
        header("Location: ../areacliente.php?cadastro=erro&msg=Email já cadastrado ou erro no servidor.");
        exit;
    }
} else {
    // Se não for POST, redireciona para a página inicial
    header("Location: ../index.php");
    exit;
}

?>