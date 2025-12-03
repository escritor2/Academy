<?php
require_once '../config/Database.php';
require_once '../Model/Usuario.php';
require_once '../Model/Produto.php';

class AdminController {
    
    public function index() {
        $database = new Database();
        $db = $database->getConnection();

        // Buscar dados reais do banco
        $userModel = new Usuario($db);
        $produtoModel = new Produto($db);

        // FetchAll retorna arrays similares aos que você tinha hardcoded
        $alunos = $userModel->lerPorPerfil('aluno')->fetchAll(PDO::FETCH_ASSOC);
        $professores = $userModel->lerPorPerfil('professor')->fetchAll(PDO::FETCH_ASSOC);
        $produtos = $produtoModel->lerTodos()->fetchAll(PDO::FETCH_ASSOC);

        // Carrega a View e passa as variáveis para ela
        // Na view, você usará $alunos, $professores, etc. normalmente
        include '../views/admin/dashboard.php';
    }
}
?>