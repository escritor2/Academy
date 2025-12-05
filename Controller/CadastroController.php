<?php
require_once '../config/Database.php';
require_once '../Model/Usuario.php'; // Pasta Model com M maiúsculo

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($nome) || empty($email) || empty($senha)) {
        header("Location: ../areacliente.php?cadastro=erro&msg=Preencha todos os campos");
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();
    $usuario = new Usuario($db);

    // O método criarUsuario já faz o hash da senha, pode mandar a senha pura
    if ($usuario->criarUsuario($nome, $email, $senha, 'aluno')) {
        header("Location: ../areacliente.php?cadastro=sucesso");
        exit;
    } else {
        header("Location: ../areacliente.php?cadastro=erro&msg=Email ja cadastrado");
        exit;
    }
}
?>