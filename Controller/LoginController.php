<?php
session_start();

// ATENÇÃO: Caminhos atualizados para suas pastas
require_once '../config/Database.php'; 
require_once '../Model/Usuario.php';   // Pasta Model com M maiúsculo

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        header("Location: ../index.php?login=erro&msg=Preencha todos os campos");
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();
    $usuario = new Usuario($db);

    $user_data = $usuario->login($email, $senha);

    if ($user_data) {
        $_SESSION['user_id'] = $user_data['id'];
        $_SESSION['user_nome'] = $user_data['nome'];
        $_SESSION['user_perfil'] = $user_data['perfil'];

        // Redirecionamento
        switch ($user_data['perfil']) {
            case 'admin': header("Location: ../adm.php"); break;
            case 'professor': header("Location: ../professor.php"); break;
            case 'recepcao': header("Location: ../recepcionista.php"); break;
            default: header("Location: ../paginacliente.php"); break;
        }
        exit;
    } else {
        header("Location: ../index.php?login=erro&msg=Email ou senha incorretos");
        exit;
    }
}
?>