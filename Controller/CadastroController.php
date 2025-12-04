<?php
// controllers/CadastroController.php

// Ativar exibição de erros para facilitar testes
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Importações (Note o uso de 'models' em minúsculo)
require_once '../config/Database.php';
require_once '../Model/Usuario.php';

// Verifica se recebeu dados via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Coleta dados do formulário
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    // Validação Simples
    if (empty($nome) || empty($email) || empty($senha)) {
        header("Location: ../areacliente.php?cadastro=erro&msg=Preencha todos os campos");
        exit;
    }

    // Conexão com Banco
    $database = new Database();
    $db = $database->getConnection();
    
    // Instancia o Model
    $usuario = new Usuario($db);

    // Tenta Criar
    // Note que passamos a senha normal, o Model que vai criptografar
    if ($usuario->criarUsuario($nome, $email, $senha, 'aluno')) {
        // SUCESSO
        header("Location: ../areacliente.php?cadastro=sucesso");
        exit;
    } else {
        // ERRO (Geralmente Email duplicado)
        header("Location: ../areacliente.php?cadastro=erro&msg=Erro: Email ja cadastrado");
        exit;
    }

} else {
    // Se tentar acessar direto pela URL sem enviar formulário
    header("Location: ../areacliente.php");
    exit;
}
?>