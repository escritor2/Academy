<?php
session_start();
require_once __DIR__ . '/../Model/AlunoDAO.php';
require_once __DIR__ . '/../Model/TreinoDAO.php';
require_once __DIR__ . '/../Model/ProdutoDAO.php';
require_once __DIR__ . '/../Model/VendaDAO.php';
require_once __DIR__ . '/../Controller/AlunoController.php';
// DAOs para equipe
require_once __DIR__ . '/../Model/ProfessorDAO.php';
require_once __DIR__ . '/../Model/RecepcionistaDAO.php';

// API AJAX
if (isset($_GET['acao_ajax'])) {
    header('Content-Type: application/json');
    if (!isset($_SESSION['admin_logado'])) { echo json_encode([]); exit; }
    $treinoDao = new TreinoDAO();
    if ($_GET['acao_ajax'] === 'buscar_treino') echo json_encode($treinoDao->buscarPorAluno($_GET['id']));
    if ($_GET['acao_ajax'] === 'buscar_modelo') echo json_encode($treinoDao->buscarModeloPorId($_GET['id']));
    exit;
}

// SEGURANÇA
if (!isset($_SESSION['admin_logado']) || $_SESSION['admin_logado'] !== true) { 
    header('Location: admin_login.php'); 
    exit; 
}
if (isset($_GET['sair'])) { 
    session_destroy(); 
    header('Location: admin_login.php'); 
    exit; 
}

$nomeAdmin = $_SESSION['admin_nome'] ?? 'Administrador';
$dao = new AlunoDAO();
$treinoDao = new TreinoDAO();
$produtoDao = new ProdutoDAO();
$vendaDao = new VendaDAO();
$professorDao = new ProfessorDAO();
$recepcionistaDao = new RecepcionistaDAO();
$msgAdm = ''; 
$tipoMsgAdm = '';

// Dados para as novas abas
$listaProfessores = $professorDao->listar();
$listaRecepcionistas = $recepcionistaDao->listar();
$totalProfessores = count($listaProfessores);
$totalRecepcionistas = count($listaRecepcionistas);

// --- PROCESSAMENTO POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    // ALUNOS
    if ($acao === 'cadastrar_aluno') {
        try {
            $c = new AlunoController();
            $c->cadastrar($_POST['nome'], $_POST['data_nascimento'], $_POST['email'], $_POST['telefone'], $_POST['cpf'], $_POST['genero'], $_POST['senha'], 'Indefinido', $_POST['plano']);
            header("Location: adm.php?tab=alunos&msg=cad_sucesso"); 
            exit;
        } catch (Exception $e) { 
            $msgAdm = "Erro: " . $e->getMessage(); 
            $tipoMsgAdm = 'erro'; 
        }
    }
    if ($acao === 'editar_aluno') {
        try {
            $senha = !empty($_POST['nova_senha_adm']) ? $_POST['nova_senha_adm'] : null;
            $dao->atualizarDadosAdmin($_POST['id'], $_POST['nome'], $_POST['email'], $_POST['telefone'], $_POST['plano'], $_POST['objetivo'], $senha);
            header("Location: adm.php?tab=alunos&msg=edit_sucesso"); 
            exit;
        } catch (Exception $e) { 
            $msgAdm = "Erro: " . $e->getMessage(); 
            $tipoMsgAdm = 'erro'; 
        }
    }
    if ($acao === 'alterar_status') {
        $dao->atualizarStatus($_POST['id_aluno'], $_POST['novo_status']);
        $origem = $_POST['origem'] ?? 'alunos';
        $busca = !empty($_POST['busca_atual']) ? "&busca=".$_POST['busca_atual'] : "";
        header("Location: adm.php?tab=$origem$busca"); 
        exit;
    }
    if ($acao === 'excluir_aluno') {
        $dao->excluirAluno($_POST['id_exclusao']);
        header("Location: adm.php?tab=alunos&msg=del_sucesso"); 
        exit;
    }
    if ($acao === 'excluir_alunos_massa') {
        if (!empty($_POST['ids_exclusao'])) {
            $ids = $_POST['ids_exclusao'];
            if (!is_array($ids)) {
                $ids = [$ids];
            }
            foreach ($ids as $id) {
                $dao->excluirAluno($id);
            }
            $msgAdm = count($ids) . " aluno(s) excluído(s) com sucesso!";
            $tipoMsgAdm = 'sucesso';
        }
        header("Location: adm.php?tab=alunos&msg=del_sucesso"); 
        exit;
    }

    // TREINOS
    if ($acao === 'salvar_treino') {
        if ($treinoDao->salvarTreino($_POST['aluno_id_treino'], $_POST['treino'] ?? [])) {
            header("Location: adm.php?tab=treinos&msg=treino_sucesso"); 
            exit;
        }
    }
    if ($acao === 'salvar_modelo') {
        if ($treinoDao->salvarModelo($_POST['nome_modelo'], $_POST['treino'] ?? [])) {
            header("Location: adm.php?tab=treinos&sub=padrao&msg=modelo_salvo"); 
            exit;
        }
    }
    if ($acao === 'excluir_modelo') {
        $treinoDao->excluirModelo($_POST['id_exclusao']);
        header("Location: adm.php?tab=treinos&sub=padrao&msg=modelo_del"); 
        exit;
    }

    // LOJA
    if ($acao === 'cadastrar_produto') {
        $precoLimpo = str_replace(['R$', ' ', '.', ','], ['', '', '', '.'], $_POST['preco']);
        if ($produtoDao->cadastrar($_POST['nome'], $precoLimpo, $_POST['estoque'], $_POST['categoria'], $_POST['descricao'])) {
            header("Location: adm.php?tab=loja&msg=prod_sucesso"); 
            exit;
        } else {
            $msgAdm = "Erro: Produto já existe!"; 
            $tipoMsgAdm = 'erro';
        }
    }
    if ($acao === 'editar_produto') {
        $precoLimpo = str_replace(['R$', ' ', '.', ','], ['', '', '', '.'], $_POST['preco']);
        $produtoDao->atualizar($_POST['id_produto'], $_POST['nome'], $precoLimpo, $_POST['estoque'], $_POST['categoria'], $_POST['descricao']);
        header("Location: adm.php?tab=loja&msg=prod_edit"); 
        exit;
    }
    if ($acao === 'gerar_kit_produtos') {
        $produtoDao->cadastrar("Whey Protein Gold", 149.90, 50, "Suplemento", "900g Baunilha");
        $produtoDao->cadastrar("Energético Power", 12.50, 100, "Bebida", "Lata 473ml");
        $produtoDao->cadastrar("Camiseta TechFit", 59.90, 20, "Roupa", "Dry-Fit Preta M");
        $produtoDao->cadastrar("Luva de Treino", 45.00, 15, "Equipamento", "Antiderrapante");
        header("Location: adm.php?tab=loja&msg=prod_kit"); 
        exit;
    }
    if ($acao === 'excluir_produto') {
        $produtoDao->excluir($_POST['id_exclusao']);
        header("Location: adm.php?tab=loja&msg=prod_del"); 
        exit;
    }
    if ($acao === 'excluir_massa_produtos') {
        if (!empty($_POST['ids_exclusao'])) {
            $produtoDao->excluirLista($_POST['ids_exclusao']);
        }
        header("Location: adm.php?tab=loja&msg=prod_del_massa"); 
        exit;
    }

    // PROFESSORES
    if ($acao === 'cadastrar_professor') {
        try {
            $professorDao->cadastrar($_POST['nome'], $_POST['email'], $_POST['telefone'], $_POST['cpf'], $_POST['data_nascimento'], $_POST['cref'], $_POST['especialidade'], $_POST['senha']);
            header("Location: adm.php?tab=professor&msg=prof_cad_sucesso"); 
            exit;
        } catch (Exception $e) { 
            $msgAdm = "Erro: " . $e->getMessage(); 
            $tipoMsgAdm = 'erro'; 
        }
    }

    if ($acao === 'editar_professor') {
        try {
            $senha = !empty($_POST['nova_senha']) ? $_POST['nova_senha'] : null;
            $professorDao->atualizar($_POST['id'], $_POST['nome'], $_POST['email'], $_POST['telefone'], $_POST['cpf'], $_POST['data_nascimento'], $_POST['cref'], $_POST['especialidade'], $senha);
            header("Location: adm.php?tab=professor&msg=prof_edit_sucesso"); 
            exit;
        } catch (Exception $e) { 
            $msgAdm = "Erro: " . $e->getMessage(); 
            $tipoMsgAdm = 'erro'; 
        }
    }

    if ($acao === 'excluir_professor') {
        $professorDao->excluir($_POST['id_exclusao']);
        header("Location: adm.php?tab=professor&msg=prof_del_sucesso"); 
        exit;
    }

    // RECEPCIONISTAS
    if ($acao === 'cadastrar_recepcionista') {
        try {
            $recepcionistaDao->cadastrar($_POST['nome'], $_POST['email'], $_POST['telefone'], $_POST['cpf'], $_POST['data_nascimento'], $_POST['senha']);
            header("Location: adm.php?tab=recepcionista&msg=rec_cad_sucesso"); 
            exit;
        } catch (Exception $e) { 
            $msgAdm = "Erro: " . $e->getMessage(); 
            $tipoMsgAdm = 'erro'; 
        }
    }

    if ($acao === 'editar_recepcionista') {
        try {
            $senha = !empty($_POST['nova_senha']) ? $_POST['nova_senha'] : null;
            $recepcionistaDao->atualizar($_POST['id'], $_POST['nome'], $_POST['email'], $_POST['telefone'], $_POST['cpf'], $_POST['data_nascimento'], $senha);
            header("Location: adm.php?tab=recepcionista&msg=rec_edit_sucesso"); 
            exit;
        } catch (Exception $e) { 
            $msgAdm = "Erro: " . $e->getMessage(); 
            $tipoMsgAdm = 'erro'; 
        }
    }

    if ($acao === 'excluir_recepcionista') {
        $recepcionistaDao->excluir($_POST['id_exclusao']);
        header("Location: adm.php?tab=recepcionista&msg=rec_del_sucesso"); 
        exit;
    }
}

// DADOS
$totalAlunos = $dao->contarTotal();
$totalAtivos = $dao->contarPorStatus('Ativo');
$totalInativos = $dao->contarPorStatus('Inativo');
$dashAlunos = $dao->buscarRecentes(5);
$termoBusca = $_GET['busca'] ?? '';
$listaAlunos = $termoBusca ? $dao->pesquisar($termoBusca) : $dao->buscarRecentes(50);
$listaSelectTreino = $dao->buscarRecentes(100);
$listaModelos = $treinoDao->listarModelos();

// DADOS LOJA
$termoBuscaProd = $_GET['busca_produto'] ?? '';
$categoriaFiltro = $_GET['categoria'] ?? '';
$listaProdutos = $produtoDao->listar();

if ($termoBuscaProd || $categoriaFiltro) {
    $listaProdutos = array_filter($listaProdutos, function($p) use ($termoBuscaProd, $categoriaFiltro) {
        $matchNome = !$termoBuscaProd || stripos($p['nome'], $termoBuscaProd) !== false || stripos($p['categoria'], $termoBuscaProd) !== false;
        $matchCategoria = !$categoriaFiltro || $p['categoria'] === $categoriaFiltro;
        return $matchNome && $matchCategoria;
    });
}
$statsLoja = $produtoDao->getTotais();

// DADOS FINANCEIRO - Atualizado para buscar valor correto
$vendasRecentes = $vendaDao->buscarRecentes(10);
$totalVendas = $vendaDao->getTotalVendas();
$faturamentoTotal = $vendaDao->getFaturamentoTotal();

// Mensagens
if (isset($_GET['msg'])) {
    $m = [
        'cad_sucesso'=>'Aluno cadastrado!', 
        'edit_sucesso'=>'Dados atualizados!', 
        'del_sucesso'=>'Item excluído!',
        'treino_sucesso'=>'Ficha de treino salva!', 
        'modelo_salvo'=>'Modelo salvo na biblioteca!', 
        'modelo_del'=>'Modelo excluído!',
        'prod_sucesso'=>'Produto salvo!', 
        'prod_del'=>'Produto excluído!', 
        'prod_kit'=>'Kit inicial gerado!', 
        'prod_del_massa'=>'Produtos selecionados excluídos!',
        'prod_edit'=>'Produto editado!',
        'prof_cad_sucesso' => 'Professor cadastrado!',
        'prof_edit_sucesso' => 'Professor atualizado!',
        'prof_del_sucesso' => 'Professor excluído!',
        'rec_cad_sucesso' => 'Recepcionista cadastrado!',
        'rec_edit_sucesso' => 'Recepcionista atualizado!',
        'rec_del_sucesso' => 'Recepcionista excluído!'
    ];
    if(isset($m[$_GET['msg']])) { 
        $msgAdm = $m[$_GET['msg']]; 
        $tipoMsgAdm = 'sucesso'; 
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" href="icons/halter.png">
    <title>Painel TechFit</title>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { 
            theme: { 
                extend: { 
                    colors: { 
                        tech: { 
                            900: '#0f172a', 
                            800: '#1e293b', 
                            700: '#334155', 
                            primary: '#f97316' 
                        } 
                    }, 
                    boxShadow: { 
                        'glow': '0 0 15px rgba(249, 115, 22, 0.3)' 
                    } 
                } 
            } 
        }
    </script>
    <style>
        /* REMOVER BARRAS DE ROLAGEM VISUAIS */
        ::-webkit-scrollbar {
            width: 0px;
            height: 0px;
            background: transparent;
        }
        
        /* Para Firefox */
        * {
            scrollbar-width: none;
        }
        
        /* Para Internet Explorer e Edge */
        * {
            -ms-overflow-style: none;
        }
        
        /* Garantir que elementos com overflow não mostrem barras */
        .no-scrollbar::-webkit-scrollbar {
            width: 0px !important;
            height: 0px !important;
        }
        
        .no-scrollbar {
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }
        
        * {
            box-sizing: border-box;
        }
        
        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        
        .no-scrollbar::-webkit-scrollbar { 
            display: none; 
        }
        
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        .fade-in { 
            animation: fadeIn 0.3s ease-out; 
        }
        
        @keyframes fadeIn { 
            from { 
                opacity: 0; 
                transform: translateY(10px); 
            } 
            to { 
                opacity: 1; 
                transform: translateY(0); 
            } 
        }
        
        input, select, textarea { 
            background-color: #0f172a !important; 
            color: white !important; 
            border-color: #334155 !important; 
        }
        
        input:focus, select:focus, textarea:focus { 
            border-color: #f97316 !important; 
            box-shadow: 0 0 0 1px #f97316 !important; 
            outline: none;
        }
        
        ::-webkit-calendar-picker-indicator { 
            filter: invert(1); 
            cursor: pointer; 
            opacity: 0.7; 
        }
        
        .check-item:checked { 
            background-color: #f97316; 
            border-color: #f97316; 
        }
        
        /* Layout fixo */
        .main-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .content-container {
            flex: 1;
            overflow: hidden;
            display: flex;
        }
        
        .page-content {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE e Edge */
        }
        
        .page-content::-webkit-scrollbar {
            display: none; /* Chrome, Safari e Opera */
        }
        
        /* Sidebar styles */
        .sidebar-collapsed .nav-text, 
        .sidebar-collapsed .logo-text, 
        .sidebar-collapsed .section-title { 
            display: none; 
        }
        
        .sidebar-collapsed .nav-item { 
            justify-content: center; 
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        
        .sidebar-collapsed .logo-container { 
            justify-content: center; 
            padding-left: 0.75rem;
        }
        
        .logo-text { 
            transition: opacity 0.3s; 
        }
        
        .sidebar-collapsed .logo-text { 
            opacity: 0; 
            width: 0; 
            overflow: hidden; 
        }
        
        /* Table styles */
        .table-container {
            overflow-x: auto;
        }
        
        .table-container table {
            min-width: 640px;
        }
        
        /* Form styles */
        .form-input {
            background-color: #0f172a;
            border: 1px solid #334155;
            border-radius: 0.5rem;
            padding: 0.75rem;
            color: white;
            width: 100%;
        }
        
        .form-input:focus {
            border-color: #f97316;
            outline: none;
            box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.2);
        }
        
        /* Button styles */
        .btn-primary {
            background-color: #f97316;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        
        .btn-primary:hover {
            background-color: #ea580c;
        }
        
        .btn-danger {
            background-color: #dc2626;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        
        .btn-danger:hover {
            background-color: #b91c1c;
        }
        
        /* Card styles */
        .card {
            background-color: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 1rem;
            padding: 1.5rem;
        }
        
        /* Responsive fixes */
        @media (max-width: 768px) {
            .page-content {
                padding: 1rem;
            }
            
            .grid-cols-1 {
                grid-template-columns: 1fr !important;
            }
            
            .flex-col-mobile {
                flex-direction: column;
            }
        }
        
        /* Corrigir visual dos checkboxes */
        input[type="checkbox"] {
            appearance: none;
            -webkit-appearance: none;
            width: 1.2rem;
            height: 1.2rem;
            border: 2px solid #4b5563;
            border-radius: 0.25rem;
            background-color: #0f172a;
            cursor: pointer;
            position: relative;
            transition: all 0.2s;
        }
        
        input[type="checkbox"]:checked {
            background-color: #f97316;
            border-color: #f97316;
        }
        
        input[type="checkbox"]:checked::after {
            content: '✓';
            position: absolute;
            color: white;
            font-size: 0.8rem;
            font-weight: bold;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        
        input[type="checkbox"]:indeterminate {
            background-color: #f97316;
            border-color: #f97316;
        }
        
        input[type="checkbox"]:indeterminate::after {
            content: '–';
            position: absolute;
            color: white;
            font-size: 1rem;
            font-weight: bold;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        
        /* Garantir que ícones sejam visíveis */
        [data-lucide] {
            display: inline-block !important;
            vertical-align: middle;
        }
        
        /* Estilo para tabelas */
        table {
            border-collapse: separate;
            border-spacing: 0;
        }
        
        th, td {
            padding: 0.75rem 1rem;
        }
        
        /* Modal overlay */
        .modal-overlay {
            z-index: 9999;
        }
        
        /* Estilos para força da senha */
        .password-strength-meter {
            height: 4px;
            width: 100%;
            margin-top: 4px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }
        
        .password-strength-0 { background-color: #dc2626; width: 20%; }
        .password-strength-1 { background-color: #ea580c; width: 40%; }
        .password-strength-2 { background-color: #f59e0b; width: 60%; }
        .password-strength-3 { background-color: #10b981; width: 80%; }
        .password-strength-4 { background-color: #22c55e; width: 100%; }
        
        .password-requirement {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }
        
        .requirement-met {
            color: #10b981;
        }
        
        .requirement-not-met {
            color: #6b7280;
        }
        
        .requirement-icon {
            width: 0.75rem;
            height: 0.75rem;
        }  
    </style>
</head>
<body class="bg-[#0b1120] text-gray-100 font-sans h-full overflow-hidden">
    <div class="main-container">
        <div class="content-container">
            <!-- Sidebar -->
            <aside id="sidebar" class="w-64 bg-[#111827] border-r border-white/5 flex-col justify-between hidden md:flex transition-all duration-300 flex-shrink-0 relative">
                <button onclick="toggleSidebar()" class="absolute -right-3 top-6 bg-tech-primary text-white p-1.5 rounded-full shadow-glow z-50 hover:bg-orange-600 transition-transform hover:scale-110">
                    <i id="toggleIcon" data-lucide="chevron-left" class="w-3 h-3"></i>
                </button>
                <div class="flex-1 overflow-hidden flex flex-col">
                    <div class="h-20 flex items-center px-6 border-b border-white/5 logo-container flex-shrink-0">
                        <div class="bg-gradient-to-br from-orange-500 to-red-600 p-2 rounded-lg shadow-lg">
                            <i data-lucide="dumbbell" class="w-6 h-6 text-white"></i>
                        </div>
                        <span class="text-xl font-bold ml-3 logo-text tracking-wide">TECH<span class="text-tech-primary">FIT</span></span>
                    </div>
                    
                    <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-1">
                        <p class="px-4 text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 section-title">Principal</p>
                        <button onclick="switchTab('dashboard')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl bg-tech-primary/10 text-tech-primary shadow-sm border border-tech-primary/20">
                            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                            <span class="nav-text">Dashboard</span>
                        </button>
                        <button onclick="switchTab('alunos')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all">
                            <i data-lucide="users" class="w-5 h-5"></i>
                            <span class="nav-text">Alunos</span>
                        </button>
                        <button onclick="switchTab('treinos')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all">
                            <i data-lucide="biceps-flexed" class="w-5 h-5"></i>
                            <span class="nav-text">Treinos</span>
                        </button>
                        
                        <p class="px-4 text-xs font-bold text-gray-500 uppercase tracking-wider mt-6 mb-2 section-title">Gestão</p>
                        <button onclick="switchTab('loja')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all">
                            <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                            <span class="nav-text">Loja</span>
                        </button>
                        <button onclick="switchTab('financeiro')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all">
                            <i data-lucide="dollar-sign" class="w-5 h-5"></i>
                            <span class="nav-text">Financeiro</span>
                        </button>
                        
                        <!-- Adicione esta seção na sidebar, após a seção "Gestão" -->
                        <p class="px-4 text-xs font-bold text-gray-500 uppercase tracking-wider mt-6 mb-2 section-title">Equipe</p>
                        <button onclick="switchTab('professor')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all">
                            <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                            <span class="nav-text">Professores</span>
                        </button>
                        <button onclick="switchTab('recepcionista')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all">
                            <i data-lucide="user-check" class="w-5 h-5"></i>
                            <span class="nav-text">Recepcionistas</span>
                        </button>
                        
                        <p class="px-4 text-xs font-bold text-gray-500 uppercase tracking-wider mt-6 mb-2 section-title">Suporte</p>
                        <button onclick="switchTab('recepcionista')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all">
                            <i data-lucide="headset" class="w-5 h-5"></i>
                            <span class="nav-text">Recepção</span>
                        </button>
                    </nav>
                </div>
                
                <div class="p-4 border-t border-white/5 flex-shrink-0">
                    <a href="?sair=true" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-red-400 hover:bg-red-500/10 rounded-xl transition-colors">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                        <span class="nav-text">Sair</span>
                    </a>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 flex flex-col h-full overflow-hidden relative bg-[#0b1120]">
                <!-- Header -->
                <header class="h-20 bg-[#111827]/80 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-6 md:px-8 z-10 flex-shrink-0">
                    <div>
                        <h2 id="pageTitle" class="text-xl md:text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white to-gray-400">Visão Geral</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Painel Administrativo</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-bold text-white"><?= htmlspecialchars($nomeAdmin) ?></p>
                            <span class="text-[10px] uppercase font-bold tracking-wider bg-tech-primary/20 text-tech-primary px-2 py-0.5 rounded-full">Gerente</span>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-700 to-gray-900 border border-white/10 flex items-center justify-center shadow-lg">
                            <i data-lucide="shield" class="w-5 h-5 text-tech-primary"></i>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <div class="page-content no-scrollbar">
                    <!-- Dashboard -->
                    <div id="tab-dashboard" class="tab-content fade-in">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                            <div class="card">
                                <p class="text-gray-400 text-xs font-bold uppercase mb-2">Total Alunos</p>
                                <h3 class="text-2xl md:text-3xl font-bold text-white"><?= $totalAlunos ?></h3>
                            </div>
                            <div class="card">
                                <p class="text-gray-400 text-xs font-bold uppercase mb-2">Ativos</p>
                                <h3 class="text-2xl md:text-3xl font-bold text-green-400"><?= $totalAtivos ?></h3>
                            </div>
                            <div class="card">
                                <p class="text-gray-400 text-xs font-bold uppercase mb-2">Inativos</p>
                                <h3 class="text-2xl md:text-3xl font-bold text-red-400"><?= $totalInativos ?></h3>
                            </div>
                            <div class="card">
                                <p class="text-gray-400 text-xs font-bold uppercase mb-2">Faturamento</p>
                                <h3 class="text-2xl md:text-3xl font-bold text-white">R$ <?= number_format($faturamentoTotal, 2, ',', '.') ?></h3>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 pb-4 border-b border-white/5">
                                <h3 class="font-bold text-lg text-white flex items-center gap-2">
                                    <i data-lucide="clock" class="w-5 h-5 text-tech-primary"></i> Últimas Matrículas
                                </h3>
                                <button onclick="switchTab('alunos')" class="text-sm font-bold text-tech-primary hover:text-white transition-colors">
                                    Ver Todos →
                                </button>
                            </div>
                            <div class="table-container">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-[#0f172a] uppercase text-xs font-bold text-gray-500">
                                        <tr>
                                            <th class="px-4 py-3">Aluno</th>
                                            <th class="px-4 py-3">Plano</th>
                                            <th class="px-4 py-3">Status</th>
                                            <th class="px-4 py-3 text-right">Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-white/5">
                                        <?php foreach($dashAlunos as $aluno): ?>
                                        <tr class="hover:bg-white/5 transition-colors">
                                            <td class="px-4 py-3 font-medium text-white"><?= htmlspecialchars($aluno['nome']) ?></td>
                                            <td class="px-4 py-3"><?= htmlspecialchars($aluno['plano']) ?></td>
                                            <td class="px-4 py-3">
                                                <span class="px-2.5 py-1 rounded-full text-xs font-bold <?= $aluno['status'] == 'Ativo' ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400' ?>">
                                                    <?= $aluno['status'] ?>
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="acao" value="alterar_status">
                                                    <input type="hidden" name="id_aluno" value="<?= $aluno['id'] ?>">
                                                    <input type="hidden" name="origem" value="dashboard">
                                                    <button type="submit" name="novo_status" value="<?= $aluno['status'] == 'Ativo' ? 'Inativo' : 'Ativo' ?>" class="p-2 rounded-lg hover:bg-white/10 transition-colors">
                                                        <i data-lucide="<?= $aluno['status'] == 'Ativo' ? 'lock' : 'unlock' ?>" class="w-4 h-4 <?= $aluno['status'] == 'Ativo' ? 'text-red-400' : 'text-green-400' ?>"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Alunos -->
                    <div id="tab-alunos" class="tab-content hidden fade-in">
                        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                            <div class="relative w-full md:w-96">
                                <form method="GET" class="flex">
                                    <input type="hidden" name="tab" value="alunos">
                                    <input type="text" name="busca" value="<?= htmlspecialchars($termoBusca) ?>" placeholder="Buscar aluno..." class="w-full bg-[#1e293b] border border-white/10 rounded-l-xl pl-12 pr-4 py-3 focus:border-tech-primary outline-none text-sm text-white">
                                    <button type="submit" class="bg-tech-primary hover:bg-orange-600 text-white px-4 rounded-r-xl">
                                        <i data-lucide="search" class="w-5 h-5"></i>
                                    </button>
                                </form>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="abrirModalExcluirAlunosMassa()" id="btnExcluirAlunosMassa" class="bg-red-900/20 border border-red-500/50 hover:bg-red-900/40 text-red-400 px-4 py-3 rounded-xl font-bold text-sm flex gap-2 shadow-glow disabled:opacity-50 disabled:cursor-not-allowed transition-all" disabled>
                                    <i data-lucide="trash-2" class="w-5 h-5"></i> Excluir
                                </button>
                                <button onclick="abrirModalAluno()" class="bg-tech-primary hover:bg-orange-600 text-white px-6 py-3 rounded-xl font-bold text-sm flex gap-2 shadow-glow transition-all">
                                    <i data-lucide="user-plus" class="w-5 h-5"></i> Novo Aluno
                                </button>
                            </div>
                        </div>
                        
                        <form id="formAlunosMassa" method="POST">
                            <input type="hidden" name="acao" value="excluir_alunos_massa">
                            <div class="card">
                                <div class="table-container">
                                    <table class="w-full text-left text-sm">
                                        <thead class="bg-[#0f172a] uppercase text-xs font-bold text-gray-500">
                                            <tr>
                                                <th class="px-4 py-3 w-12">
                                                    <input type="checkbox" id="selectAllAlunos" class="w-4 h-4 rounded border-gray-600 bg-[#0f172a] text-tech-primary cursor-pointer" onclick="selecionarTodosAlunos()">
                                                </th>
                                                <th class="px-4 py-3">Aluno</th>
                                                <th class="px-4 py-3">Plano</th>
                                                <th class="px-4 py-3">Status</th>
                                                <th class="px-4 py-3 text-right">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-white/5">
                                            <?php foreach($listaAlunos as $aluno): ?>
                                            <tr class="hover:bg-white/5 transition-colors">
                                                <td class="px-4 py-3">
                                                    <input type="checkbox" name="ids_exclusao[]" value="<?= $aluno['id'] ?>" class="w-4 h-4 rounded border-gray-600 bg-[#0f172a] text-tech-primary cursor-pointer check-aluno" onchange="atualizarBotaoExcluirAlunos()">
                                                </td>
                                                <td class="px-4 py-3 font-medium text-white"><?= htmlspecialchars($aluno['nome']) ?></td>
                                                <td class="px-4 py-3"><?= htmlspecialchars($aluno['plano']) ?></td>
                                                <td class="px-4 py-3">
                                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold <?= $aluno['status'] == 'Ativo' ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400' ?>">
                                                        <?= $aluno['status'] ?>
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <button type="button" onclick='abrirModalEditar(<?= json_encode($aluno) ?>)' class="p-2 hover:bg-blue-500/20 text-blue-400 rounded-lg transition-colors">
                                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                                        </button>
                                                        <form method="POST" class="inline">
                                                            <input type="hidden" name="acao" value="alterar_status">
                                                            <input type="hidden" name="id_aluno" value="<?= $aluno['id'] ?>">
                                                            <input type="hidden" name="origem" value="alunos">
                                                            <input type="hidden" name="novo_status" value="<?= $aluno['status'] == 'Ativo' ? 'Inativo' : 'Ativo' ?>">
                                                            <button type="submit" class="p-2 hover:bg-white/10 rounded-lg transition-colors">
                                                                <i data-lucide="<?= $aluno['status'] == 'Ativo' ? 'lock' : 'unlock' ?>" class="w-4 h-4 <?= $aluno['status'] == 'Ativo' ? 'text-red-400' : 'text-green-400' ?>"></i>
                                                            </button>
                                                        </form>
                                                        <button type="button" onclick="abrirModalExcluir(<?= $aluno['id'] ?>, 'aluno')" class="p-2 hover:bg-red-500/20 text-red-400 rounded-lg transition-colors">
                                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Treinos -->
                    <div id="tab-treinos" class="tab-content hidden fade-in">
                        <div class="flex gap-4 mb-6 border-b border-white/10 pb-1 overflow-x-auto">
                            <button onclick="switchSubTab('individual')" id="btn-individual" class="pb-3 text-sm font-bold text-tech-primary border-b-2 border-tech-primary transition-all whitespace-nowrap px-4">Aluno Específico</button>
                            <button onclick="switchSubTab('padrao')" id="btn-padrao" class="pb-3 text-sm font-bold text-gray-400 hover:text-white transition-all whitespace-nowrap px-4">Biblioteca</button>
                        </div>
                        
                        <div id="view-individual">
                            <form method="POST" id="formTreino">
                                <input type="hidden" name="acao" value="salvar_treino">
                                <div class="card mb-6">
                                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-4">
                                        <div class="w-full lg:w-1/2">
                                            <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Selecione o Aluno</label>
                                            <select name="aluno_id_treino" onchange="carregarTreino(this.value, 'aluno')" class="w-full bg-[#0f172a] border border-white/10 rounded-xl p-3 text-white cursor-pointer">
                                                <option value="">-- Escolha --</option>
                                                <?php foreach($listaSelectTreino as $a): ?>
                                                    <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nome']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="flex gap-3 w-full lg:w-auto">
                                            <button type="button" onclick="gerarTreinoAutomatico()" class="flex-1 lg:flex-none bg-purple-600 hover:bg-purple-700 text-white px-4 py-3 rounded-xl font-bold flex items-center justify-center gap-2 transition-all">
                                                <i data-lucide="wand-2" class="w-5 h-5"></i> Mágica
                                            </button>
                                            <button type="submit" class="flex-1 lg:flex-none bg-tech-primary hover:bg-orange-600 text-white px-6 py-3 rounded-xl font-bold flex items-center justify-center gap-2 transition-all">
                                                <i data-lucide="save" class="w-5 h-5"></i> Salvar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                    <?php foreach(['A','B','C'] as $div): ?>
                                    <div class="card">
                                        <div class="flex justify-between items-center mb-4 pb-3 border-b border-white/5">
                                            <h3 class="font-bold text-white">Treino <?= $div ?></h3>
                                            <button type="button" onclick="addExercicio('container-<?= $div ?>')" class="text-tech-primary hover:text-orange-400 text-sm font-medium transition-colors">
                                                + Adicionar
                                            </button>
                                        </div>
                                        <div id="container-<?= $div ?>" class="space-y-3 min-h-[200px]"></div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </form>
                        </div>
                        
                        <div id="view-padrao" class="hidden">
                            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                                <div class="lg:col-span-1">
                                    <div class="card h-fit">
                                        <h3 class="text-xs font-bold text-gray-400 uppercase mb-4">Modelos</h3>
                                        <ul class="space-y-2 max-h-[400px] overflow-y-auto pr-2">
                                            <?php foreach($listaModelos as $mod): ?>
                                                <li class="flex justify-between items-center bg-[#0f172a] p-3 rounded-lg hover:bg-white/5 transition-colors group">
                                                    <button type="button" onclick="carregarTreino(<?= $mod['id'] ?>, 'modelo')" class="text-sm text-gray-300 hover:text-white flex-1 text-left">
                                                        <?= htmlspecialchars($mod['nome']) ?>
                                                    </button>
                                                    <button type="button" onclick="abrirModalExcluir(<?= $mod['id'] ?>, 'modelo')" class="text-gray-600 hover:text-red-500 opacity-0 group-hover:opacity-100 p-1 transition-opacity">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div class="lg:col-span-3">
                                    <form method="POST" id="formModelo">
                                        <input type="hidden" name="acao" value="salvar_modelo">
                                        <div class="card mb-6">
                                            <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
                                                <div class="w-full md:w-2/3">
                                                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Nome do Modelo</label>
                                                    <input type="text" name="nome_modelo" placeholder="Ex: Hipertrofia A" class="w-full bg-[#0f172a] border border-white/10 rounded-xl p-3 text-white">
                                                </div>
                                                <div class="flex gap-3 w-full md:w-auto">
                                                    <button type="button" onclick="gerarModeloAutomatico()" class="flex-1 md:flex-none bg-purple-600 hover:bg-purple-700 text-white px-4 py-3 rounded-xl font-bold flex items-center justify-center gap-2 transition-all">
                                                        <i data-lucide="wand-2" class="w-5 h-5"></i> Mágica
                                                    </button>
                                                    <button type="submit" class="flex-1 md:flex-none bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-xl font-bold flex items-center justify-center gap-2 transition-all">
                                                        <i data-lucide="save" class="w-5 h-5"></i> Salvar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                            <?php foreach(['A','B','C'] as $div): ?>
                                            <div class="card border-dashed border-white/20">
                                                <div class="flex justify-between items-center mb-4 pb-3 border-b border-white/5">
                                                    <h3 class="font-bold text-gray-300">Treino <?= $div ?></h3>
                                                    <button type="button" onclick="addExercicio('modelo-<?= $div ?>')" class="text-blue-400 hover:text-blue-300 text-xs font-bold transition-colors">
                                                        + ITEM
                                                    </button>
                                                </div>
                                                <div id="modelo-<?= $div ?>" class="space-y-2 min-h-[150px]"></div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loja -->
                    <div id="tab-loja" class="tab-content hidden fade-in">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="card">
                                <p class="text-gray-400 text-xs font-bold uppercase mb-2">Produtos Cadastrados</p>
                                <h3 class="text-2xl font-bold text-white"><?= $statsLoja['qtd'] ?></h3>
                            </div>
                            <div class="card">
                                <p class="text-gray-400 text-xs font-bold uppercase mb-2">Valor em Estoque</p>
                                <h3 class="text-2xl font-bold text-green-400">R$ <?= number_format($statsLoja['valor'], 2, ',', '.') ?></h3>
                            </div>
                            <button onclick="abrirModalExcluirMassa()" id="btnExcluirMassa" class="card bg-red-900/20 border border-red-500/50 hover:bg-red-900/40 text-red-400 flex items-center justify-center gap-2 font-bold transition-all disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                <i data-lucide="trash-2" class="w-5 h-5"></i> Excluir Selecionados
                            </button>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div class="lg:col-span-1">
                                <div class="card h-fit">
                                    <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                        <i data-lucide="plus-circle" class="w-5 h-5 text-tech-primary"></i> Novo Produto
                                    </h3>
                                    <form method="POST" class="space-y-4">
                                        <input type="hidden" name="acao" value="cadastrar_produto">
                                        <div>
                                            <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Nome</label>
                                            <input type="text" name="nome" required class="w-full bg-[#0f172a] border border-white/10 rounded-lg p-3 text-white">
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Preço</label>
                                                <input type="text" name="preco" oninput="mascaraMoeda(this)" required placeholder="R$ 0,00" class="w-full bg-[#0f172a] border border-white/10 rounded-lg p-3 text-white">
                                            </div>
                                            <div>
                                                <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Estoque</label>
                                                <input type="number" name="estoque" required class="w-full bg-[#0f172a] border border-white/10 rounded-lg p-3 text-white">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Categoria</label>
                                            <select name="categoria" class="w-full bg-[#0f172a] border border-white/10 rounded-lg p-3 text-white">
                                                <option value="Suplemento">Suplemento</option>
                                                <option value="Roupa">Roupa</option>
                                                <option value="Acessório">Acessório</option>
                                                <option value="Bebida">Bebida</option>
                                                <option value="Equipamento">Equipamento</option>
                                                <option value="Alimento">Alimento</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Descrição</label>
                                            <input type="text" name="descricao" class="w-full bg-[#0f172a] border border-white/10 rounded-lg p-3 text-white">
                                        </div>
                                        <button type="submit" class="w-full bg-tech-primary hover:bg-orange-600 text-white font-bold py-3 rounded-lg transition-all">
                                            Cadastrar Produto
                                        </button>
                                    </form>
                                    <form method="POST" class="mt-4">
                                        <input type="hidden" name="acao" value="gerar_kit_produtos">
                                        <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white text-sm font-bold py-2.5 rounded-lg flex items-center justify-center gap-2 transition-all">
                                            <i data-lucide="wand-2" class="w-4 h-4"></i> Gerar Kit Inicial
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="lg:col-span-2">
                                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4">
                                    <h3 class="text-lg font-bold text-white">Catálogo de Produtos</h3>
                                    <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                                        <form method="GET" class="relative w-full sm:w-48">
                                            <input type="hidden" name="tab" value="loja">
                                            <input type="text" name="busca_produto" value="<?= htmlspecialchars($termoBuscaProd) ?>" placeholder="Buscar..." class="w-full bg-[#0f172a] border border-white/10 rounded-lg pl-10 pr-3 py-2 text-sm text-white">
                                            <i data-lucide="search" class="absolute left-3 top-2.5 w-4 h-4 text-gray-500"></i>
                                        </form>
                                        <form method="GET" class="w-full sm:w-auto">
                                            <input type="hidden" name="tab" value="loja">
                                            <input type="hidden" name="busca_produto" value="<?= htmlspecialchars($termoBuscaProd) ?>">
                                            <select name="categoria" onchange="this.form.submit()" class="w-full bg-[#0f172a] border border-white/10 rounded-lg px-3 py-2 text-sm text-white">
                                                <option value="">Todas Categorias</option>
                                                <option value="Suplemento" <?= $categoriaFiltro == 'Suplemento' ? 'selected' : '' ?>>Suplementos</option>
                                                <option value="Roupa" <?= $categoriaFiltro == 'Roupa' ? 'selected' : '' ?>>Roupas</option>
                                                <option value="Bebida" <?= $categoriaFiltro == 'Bebida' ? 'selected' : '' ?>>Bebidas</option>
                                                <option value="Equipamento" <?= $categoriaFiltro == 'Equipamento' ? 'selected' : '' ?>>Equipamentos</option>
                                                <option value="Alimento" <?= $categoriaFiltro == 'Alimento' ? 'selected' : '' ?>>Alimentos</option>
                                                <option value="Acessório" <?= $categoriaFiltro == 'Acessório' ? 'selected' : '' ?>>Acessórios</option>
                                            </select>
                                        </form>
                                    </div>
                                </div>
                                
                                <form id="formMassa" method="POST">
                                    <input type="hidden" name="acao" value="excluir_massa_produtos">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-[500px] overflow-y-auto pr-2">
                                        <?php foreach($listaProdutos as $prod): 
                                            $icone='shopping-bag'; $cor='text-gray-400'; $bg='bg-white/5';
                                            if($prod['categoria']=='Suplemento'){$icone='zap';$cor='text-purple-400';$bg='bg-purple-500/10';}
                                            if($prod['categoria']=='Roupa'){$icone='shirt';$cor='text-blue-400';$bg='bg-blue-500/10';}
                                            if($prod['categoria']=='Bebida'){$icone='glass-water';$cor='text-cyan-400';$bg='bg-cyan-500/10';}
                                        ?>
                                        <div class="card <?= $prod['estoque'] < 10 ? 'border-red-500/30' : '' ?>">
                                            <div class="flex items-center gap-3">
                                                <input type="checkbox" name="ids_exclusao[]" value="<?= $prod['id'] ?>" class="w-5 h-5 rounded border-gray-600 bg-[#0f172a] text-orange-500 check-item" onchange="atualizarBotaoExcluir()">
                                                <div class="w-10 h-10 rounded-lg flex items-center justify-center <?= $bg ?> flex-shrink-0">
                                                    <i data-lucide="<?= $icone ?>" class="w-5 h-5 <?= $cor ?>"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="font-bold text-white truncate"><?= htmlspecialchars($prod['nome']) ?></h4>
                                                    <div class="flex justify-between items-center mt-1">
                                                        <p class="text-xs <?= $prod['estoque'] < 10 ? 'text-red-500 font-bold' : 'text-gray-400' ?>">
                                                            Estoque: <?= $prod['estoque'] ?>
                                                            <?php if($prod['estoque'] < 10): ?>
                                                                <span class="text-[10px] bg-red-500/20 text-red-400 px-1 rounded ml-1">Baixo</span>
                                                            <?php endif; ?>
                                                        </p>
                                                        <p class="text-tech-primary font-bold text-sm">R$ <?= number_format($prod['preco'], 2, ',', '.') ?></p>
                                                    </div>
                                                </div>
                                                <div class="flex gap-1">
                                                    <button type="button" onclick='abrirModalEditarProduto(<?= json_encode($prod) ?>)' class="p-1.5 text-blue-400 hover:bg-blue-500/20 rounded transition-colors">
                                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                                    </button>
                                                    <button type="button" onclick="abrirModalExcluir(<?= $prod['id'] ?>, 'produto')" class="p-1.5 text-red-400 hover:bg-red-500/20 rounded transition-colors">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Financeiro -->
                    <div id="tab-financeiro" class="tab-content hidden fade-in">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="card">
                                <p class="text-gray-400 text-xs font-bold uppercase mb-2">Total Vendas</p>
                                <h3 class="text-2xl md:text-3xl font-bold text-white"><?= $totalVendas ?></h3>
                            </div>
                            <div class="card">
                                <p class="text-gray-400 text-xs font-bold uppercase mb-2">Faturamento Total</p>
                                <h3 class="text-2xl md:text-3xl font-bold text-green-400">R$ <?= number_format($faturamentoTotal, 2, ',', '.') ?></h3>
                            </div>
                            <div class="card">
                                <p class="text-gray-400 text-xs font-bold uppercase mb-2">Ticket Médio</p>
                                <h3 class="text-2xl md:text-3xl font-bold text-blue-400">R$ <?= $totalVendas > 0 ? number_format($faturamentoTotal / $totalVendas, 2, ',', '.') : '0,00' ?></h3>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="flex justify-between items-center mb-6 pb-4 border-b border-white/5">
                                <h3 class="font-bold text-lg text-white flex items-center gap-2">
                                    <i data-lucide="dollar-sign" class="w-5 h-5 text-tech-primary"></i> Histórico de Vendas
                                </h3>
                            </div>
                            <div class="table-container">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-[#0f172a] uppercase text-xs font-bold text-gray-500">
                                        <tr>
                                            <th class="px-4 py-3">Data</th>
                                            <th class="px-4 py-3">Cliente</th>
                                            <th class="px-4 py-3">Produto</th>
                                            <th class="px-4 py-3">Qtd</th>
                                            <th class="px-4 py-3">Valor Unitário</th>
                                            <th class="px-4 py-3">Valor Total</th>
                                            <th class="px-4 py-3">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-white/5">
                                        <?php foreach($vendasRecentes as $venda): ?>
                                        <tr class="hover:bg-white/5 transition-colors">
                                            <td class="px-4 py-3 font-medium text-white"><?= date('d/m/Y H:i', strtotime($venda['data_venda'])) ?></td>
                                            <td class="px-4 py-3"><?= htmlspecialchars($venda['nome_cliente']) ?></td>
                                            <td class="px-4 py-3"><?= htmlspecialchars($venda['nome_produto']) ?></td>
                                            <td class="px-4 py-3"><?= $venda['quantidade'] ?></td>
                                            <td class="px-4 py-3 text-gray-400">R$ <?= number_format($venda['valor_unitario'], 2, ',', '.') ?></td>
                                            <td class="px-4 py-3 text-green-400 font-medium">R$ <?= number_format($venda['valor_total'], 2, ',', '.') ?></td>
                                            <td class="px-4 py-3">
                                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-green-500/10 text-green-400">
                                                    Concluída
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Aba Professores -->
                    <div id="tab-professor" class="tab-content hidden fade-in">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-white">Gerenciar Professores</h2>
                            <div class="flex gap-3">
                                <button onclick="abrirModalProfessor()" class="bg-tech-primary hover:bg-orange-600 text-white px-6 py-3 rounded-xl font-bold flex items-center gap-2 shadow-glow transition-all">
                                    <i data-lucide="user-plus" class="w-5 h-5"></i> Novo Professor
                                </button>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <div class="card">
                                <p class="text-gray-400 text-xs font-bold uppercase mb-2">Total Professores</p>
                                <h3 class="text-2xl font-bold text-white"><?= $totalProfessores ?></h3>
                            </div>
                            <div class="card">
                                <p class="text-gray-400 text-xs font-bold uppercase mb-2">Ativos</p>
                                <h3 class="text-2xl font-bold text-green-400"><?= $professorDao->contarAtivos() ?></h3>
                            </div>
                            <div class="card">
                                <p class="text-gray-400 text-xs font-bold uppercase mb-2">Inativos</p>
                                <h3 class="text-2xl font-bold text-red-400"><?= $totalProfessores - $professorDao->contarAtivos() ?></h3>
                            </div>
                            <div class="card">
                                <p class="text-gray-400 text-xs font-bold uppercase mb-2">Especialidades</p>
                                <h3 class="text-2xl font-bold text-blue-400"><?= $professorDao->contarEspecialidades() ?></h3>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="table-container">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-[#0f172a] uppercase text-xs font-bold text-gray-500">
                                        <tr>
                                            <th class="px-4 py-3">ID</th>
                                            <th class="px-4 py-3">Nome</th>
                                            <th class="px-4 py-3">Email</th>
                                            <th class="px-4 py-3">CREF</th>
                                            <th class="px-4 py-3">Especialidade</th>
                                            <th class="px-4 py-3">Status</th>
                                            <th class="px-4 py-3 text-right">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-white/5">
                                        <?php foreach($listaProfessores as $prof): ?>
                                        <tr class="hover:bg-white/5 transition-colors">
                                            <td class="px-4 py-3 font-medium text-white">#<?= $prof['id'] ?></td>
                                            <td class="px-4 py-3 font-medium text-white"><?= htmlspecialchars($prof['nome']) ?></td>
                                            <td class="px-4 py-3"><?= htmlspecialchars($prof['email']) ?></td>
                                            <td class="px-4 py-3"><?= htmlspecialchars($prof['cref']) ?></td>
                                            <td class="px-4 py-3">
                                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-500/10 text-blue-400">
                                                    <?= htmlspecialchars($prof['especialidade']) ?>
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="px-2.5 py-1 rounded-full text-xs font-bold <?= $prof['status'] == 'Ativo' ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400' ?>">
                                                    <?= $prof['status'] ?>
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <button type="button" onclick='abrirModalEditarProfessor(<?= json_encode($prof) ?>)' class="p-2 hover:bg-blue-500/20 text-blue-400 rounded-lg transition-colors">
                                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                                    </button>
                                                    <form method="POST" class="inline">
                                                        <input type="hidden" name="acao" value="alterar_status_professor">
                                                        <input type="hidden" name="id" value="<?= $prof['id'] ?>">
                                                        <input type="hidden" name="novo_status" value="<?= $prof['status'] == 'Ativo' ? 'Inativo' : 'Ativo' ?>">
                                                        <button type="submit" class="p-2 hover:bg-white/10 rounded-lg transition-colors">
                                                            <i data-lucide="<?= $prof['status'] == 'Ativo' ? 'lock' : 'unlock' ?>" class="w-4 h-4 <?= $prof['status'] == 'Ativo' ? 'text-red-400' : 'text-green-400' ?>"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" onclick="abrirModalExcluir(<?= $prof['id'] ?>, 'professor')" class="p-2 hover:bg-red-500/20 text-red-400 rounded-lg transition-colors">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Aba Recepcionistas -->
                    <div id="tab-recepcionista" class="tab-content hidden fade-in">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-white">Gerenciar Recepcionistas</h2>
                            <div class="flex gap-3">
                                <button onclick="abrirModalRecepcionista()" class="bg-tech-primary hover:bg-orange-600 text-white px-6 py-3 rounded-xl font-bold flex items-center gap-2 shadow-glow transition-all">
                                    <i data-lucide="user-plus" class="w-5 h-5"></i> Novo Recepcionista
                                </button>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="card">
                                <p class="text-gray-400 text-xs font-bold uppercase mb-2">Total Recepcionistas</p>
                                <h3 class="text-2xl font-bold text-white"><?= $totalRecepcionistas ?></h3>
                            </div>
                            <div class="card">
                                <p class="text-gray-400 text-xs font-bold uppercase mb-2">Ativos</p>
                                <h3 class="text-2xl font-bold text-green-400"><?= $recepcionistaDao->contarAtivos() ?></h3>
                            </div>
                            <div class="card">
                                <p class="text-gray-400 text-xs font-bold uppercase mb-2">Inativos</p>
                                <h3 class="text-2xl font-bold text-red-400"><?= $totalRecepcionistas - $recepcionistaDao->contarAtivos() ?></h3>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="table-container">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-[#0f172a] uppercase text-xs font-bold text-gray-500">
                                        <tr>
                                            <th class="px-4 py-3">ID</th>
                                            <th class="px-4 py-3">Nome</th>
                                            <th class="px-4 py-3">Email</th>
                                            <th class="px-4 py-3">Telefone</th>
                                            <th class="px-4 py-3">Status</th>
                                            <th class="px-4 py-3 text-right">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-white/5">
                                        <?php foreach($listaRecepcionistas as $rec): ?>
                                        <tr class="hover:bg-white/5 transition-colors">
                                            <td class="px-4 py-3 font-medium text-white">#<?= $rec['id'] ?></td>
                                            <td class="px-4 py-3 font-medium text-white"><?= htmlspecialchars($rec['nome']) ?></td>
                                            <td class="px-4 py-3"><?= htmlspecialchars($rec['email']) ?></td>
                                            <td class="px-4 py-3"><?= htmlspecialchars($rec['telefone']) ?></td>
                                            <td class="px-4 py-3">
                                                <span class="px-2.5 py-1 rounded-full text-xs font-bold <?= $rec['status'] == 'Ativo' ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400' ?>">
                                                    <?= $rec['status'] ?>
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <button type="button" onclick='abrirModalEditarRecepcionista(<?= json_encode($rec) ?>)' class="p-2 hover:bg-blue-500/20 text-blue-400 rounded-lg transition-colors">
                                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                                    </button>
                                                    <form method="POST" class="inline">
                                                        <input type="hidden" name="acao" value="alterar_status_recepcionista">
                                                        <input type="hidden" name="id" value="<?= $rec['id'] ?>">
                                                        <input type="hidden" name="novo_status" value="<?= $rec['status'] == 'Ativo' ? 'Inativo' : 'Ativo' ?>">
                                                        <button type="submit" class="p-2 hover:bg-white/10 rounded-lg transition-colors">
                                                            <i data-lucide="<?= $rec['status'] == 'Ativo' ? 'lock' : 'unlock' ?>" class="w-4 h-4 <?= $rec['status'] == 'Ativo' ? 'text-red-400' : 'text-green-400' ?>"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" onclick="abrirModalExcluir(<?= $rec['id'] ?>, 'recepcionista')" class="p-2 hover:bg-red-500/20 text-red-400 rounded-lg transition-colors">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- MODAL EXCLUIR ALUNOS EM MASSA -->
    <div id="modalExcluirAlunosMassa" class="fixed inset-0 z-50 hidden bg-black/90 flex items-center justify-center p-4">
        <div class="bg-[#1e293b] w-full max-w-md rounded-2xl border border-red-500/30 shadow-2xl p-8 text-center">
            <div class="w-16 h-16 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-6 text-red-500">
                <i data-lucide="alert-triangle" class="w-8 h-8"></i>
            </div>
            <h2 class="text-xl font-bold text-white mb-2">Excluir Alunos Selecionados?</h2>
            <p id="contagemAlunosExclusao" class="text-gray-400 mb-6">Esta ação excluirá permanentemente todos os alunos selecionados.</p>
            <div class="flex gap-3">
                <button type="button" onclick="fecharModalExcluirAlunosMassa()" class="flex-1 py-3 rounded-xl border border-white/10 text-gray-300 font-bold hover:bg-white/5 transition-colors">
                    Cancelar
                </button>
                <button type="button" onclick="confirmarExcluirAlunosMassa()" class="flex-1 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold shadow-lg transition-colors">
                    Sim, Excluir
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL EXCLUIR -->
    <div id="modalExcluir" class="fixed inset-0 z-50 hidden bg-black/90 flex items-center justify-center p-4">
        <div class="bg-[#1e293b] w-full max-w-md rounded-2xl border border-red-500/30 shadow-2xl p-8 text-center">
            <div class="w-16 h-16 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-6 text-red-500">
                <i data-lucide="alert-triangle" class="w-8 h-8"></i>
            </div>
            <h2 id="titulo_exclusao" class="text-xl font-bold text-white mb-2">Excluir?</h2>
            <p class="text-gray-400 mb-6">Essa ação é irreversível.</p>
            <form method="POST" id="formExcluir" class="flex gap-3">
                <input type="hidden" name="acao" id="acao_exclusao" value="">
                <input type="hidden" name="id_exclusao" id="id_exclusao">
                <button type="button" onclick="fecharModalExcluir()" class="flex-1 py-3 rounded-xl border border-white/10 text-gray-300 font-bold hover:bg-white/5 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold shadow-lg transition-colors">
                    Sim, Excluir
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL EXCLUIR MASSA -->
    <div id="modalExcluirMassa" class="fixed inset-0 z-50 hidden bg-black/90 flex items-center justify-center p-4">
        <div class="bg-[#1e293b] w-full max-w-md rounded-2xl border border-red-500/30 shadow-2xl p-8 text-center">
            <h2 class="text-xl font-bold text-white mb-2">Excluir Selecionados?</h2>
            <p class="text-gray-400 mb-6">Você vai apagar vários itens de uma vez.</p>
            <div class="flex gap-3">
                <button type="button" onclick="fecharModalExcluirMassa()" class="flex-1 py-3 rounded-xl border border-white/10 text-gray-300 font-bold hover:bg-white/5 transition-colors">
                    Cancelar
                </button>
                <button type="button" onclick="confirmarExcluirMassa()" class="flex-1 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold shadow-lg transition-colors">
                    Sim, Excluir
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR PRODUTO -->
    <div id="modalEditarProduto" class="fixed inset-0 z-50 hidden bg-black/90 flex items-center justify-center p-4">
        <div class="bg-[#1e293b] w-full max-w-lg rounded-2xl border border-white/10 shadow-2xl p-8 relative">
            <button type="button" onclick="fecharModalEditarProduto()" class="absolute top-4 right-4 text-gray-400 hover:text-white p-2">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
            <h2 class="text-xl font-bold mb-6 text-white">Editar Produto</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="acao" value="editar_produto">
                <input type="hidden" name="id_produto" id="edit_prod_id">
                <div>
                    <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Nome</label>
                    <input type="text" name="nome" id="edit_prod_nome" class="w-full bg-[#0f172a] border border-white/10 rounded-lg p-3 text-white">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Preço</label>
                        <input type="text" name="preco" id="edit_prod_preco" oninput="mascaraMoeda(this)" class="w-full bg-[#0f172a] border border-white/10 rounded-lg p-3 text-white">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Estoque</label>
                        <input type="number" name="estoque" id="edit_prod_estoque" class="w-full bg-[#0f172a] border border-white/10 rounded-lg p-3 text-white">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Categoria</label>
                    <select name="categoria" id="edit_prod_categoria" class="w-full bg-[#0f172a] border border-white/10 rounded-lg p-3 text-white">
                        <option value="Suplemento">Suplemento</option>
                        <option value="Roupa">Roupa</option>
                        <option value="Acessório">Acessório</option>
                        <option value="Bebida">Bebida</option>
                        <option value="Equipamento">Equipamento</option>
                        <option value="Alimento">Alimento</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Descrição</label>
                    <input type="text" name="descricao" id="edit_prod_descricao" class="w-full bg-[#0f172a] border border-white/10 rounded-lg p-3 text-white">
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition-all">
                    Salvar Alterações
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL ALUNO -->
    <div id="modalAluno" class="fixed inset-0 z-50 hidden bg-black/90 flex items-center justify-center p-4">
        <div class="bg-[#1e293b] w-full max-w-2xl rounded-2xl border border-white/10 shadow-2xl p-8 relative">
            <button type="button" onclick="fecharModalAluno()" class="absolute top-4 right-4 text-gray-400 hover:text-white p-2">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
            <h2 class="text-xl font-bold mb-6 text-white">Novo Aluno</h2>
            <form method="POST" class="space-y-4" onsubmit="return validarFormularioAluno()">
                <input type="hidden" name="acao" value="cadastrar_aluno">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <input type="text" name="nome" placeholder="Nome Completo" class="w-full bg-[#0f172a] p-3 text-white rounded-lg" required>
                    </div>
                    <div>
                        <input type="text" name="cpf" id="cpfAluno" placeholder="CPF (000.000.000-00)" 
                               class="w-full bg-[#0f172a] p-3 text-white rounded-lg" 
                               maxlength="14" required
                               oninput="aplicarMascaraCPF(this)">
                        <div id="cpfFeedback" class="text-xs mt-1 text-gray-500"></div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <input type="email" name="email" placeholder="Email" class="w-full bg-[#0f172a] p-3 text-white rounded-lg" required>
                    </div>
                    <div>
                        <input type="text" name="telefone" id="telefoneAluno" placeholder="Telefone ((00) 00000-0000)" 
                               class="w-full bg-[#0f172a] p-3 text-white rounded-lg" 
                               maxlength="15" required
                               oninput="aplicarMascaraTelefone(this)">
                        <div id="telefoneFeedback" class="text-xs mt-1 text-gray-500"></div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <input type="date" name="data_nascimento" class="w-full bg-[#0f172a] p-3 text-white rounded-lg" required>
                    </div>
                    <div>
                        <select name="genero" class="w-full bg-[#0f172a] p-3 text-white rounded-lg" required>
                            <option value="masculino">Masculino</option>
                            <option value="feminino">Feminino</option>
                        </select>
                    </div>
                    <div>
                        <select name="plano" class="w-full bg-[#0f172a] p-3 text-white rounded-lg" required>
                            <option value="Start">Start</option>
                            <option value="Pro">Pro</option>
                            <option value="VIP">VIP</option>
                        </select>
                    </div>
                </div>
                <div>
                    <input type="text" name="senha" id="senhaAluno" placeholder="Senha (mínimo 8 caracteres)" 
                           class="w-full bg-[#0f172a] p-3 text-white rounded-lg" 
                           required oninput="validarSenha(this.value)">
                    <!-- Indicador de força da senha -->
                    <div class="password-strength-meter mt-2" id="passwordStrengthMeter"></div>
                    <!-- Requisitos da senha -->
                    <div class="mt-2 space-y-1" id="passwordRequirements">
                        <div class="password-requirement requirement-not-met" id="reqLength">
                            <i data-lucide="circle" class="requirement-icon"></i>
                            <span>Mínimo 8 caracteres</span>
                        </div>
                        <div class="password-requirement requirement-not-met" id="reqUppercase">
                            <i data-lucide="circle" class="requirement-icon"></i>
                            <span>Pelo menos 1 letra maiúscula</span>
                        </div>
                        <div class="password-requirement requirement-not-met" id="reqLowercase">
                            <i data-lucide="circle" class="requirement-icon"></i>
                            <span>Pelo menos 1 letra minúscula</span>
                        </div>
                        <div class="password-requirement requirement-not-met" id="reqNumber">
                            <i data-lucide="circle" class="requirement-icon"></i>
                            <span>Pelo menos 1 número</span>
                        </div>
                        <div class="password-requirement requirement-not-met" id="reqSpecial">
                            <i data-lucide="circle" class="requirement-icon"></i>
                            <span>Pelo menos 1 caractere especial (@#$%&!)</span>
                        </div>
                    </div>
                </div>
                <button type="submit" class="w-full bg-tech-primary hover:bg-orange-600 py-3 rounded-lg text-white font-bold transition-all">
                    Cadastrar Aluno
                </button>
            </form>
        </div>
    </div>
    
    <!-- MODAL EDITAR ALUNO (CORRIGIDO) -->
    <div id="modalEditar" class="fixed inset-0 z-50 hidden bg-black/90 flex items-center justify-center p-4">
        <div class="bg-[#1e293b] w-full max-w-2xl rounded-2xl border border-white/10 shadow-2xl p-8 relative">
            <button type="button" onclick="fecharModalEditar()" class="absolute top-4 right-4 text-gray-400 hover:text-white p-2">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
            <h2 class="text-xl font-bold mb-6 text-white">Editar Aluno</h2>
            <form method="POST" class="space-y-4" onsubmit="return validarFormularioEditarAluno()">
                <input type="hidden" name="acao" value="editar_aluno">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Nome Completo</label>
                        <input type="text" name="nome" id="edit_nome" class="w-full bg-[#0f172a] p-3 text-white rounded-lg" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">CPF (somente visualização)</label>
                        <input type="text" id="edit_cpf" class="w-full bg-[#0f172a] p-3 text-gray-400 rounded-lg" disabled>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Email</label>
                        <input type="email" name="email" id="edit_email" class="w-full bg-[#0f172a] p-3 text-white rounded-lg" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Telefone</label>
                        <input type="text" name="telefone" id="edit_telefone" 
                               class="w-full bg-[#0f172a] p-3 text-white rounded-lg" 
                               maxlength="15" required
                               oninput="aplicarMascaraTelefone(this)"
                               placeholder="(00) 00000-0000">
                        <div id="editTelefoneFeedback" class="text-xs mt-1 text-gray-500"></div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Plano</label>
                        <select name="plano" id="edit_plano" class="w-full bg-[#0f172a] p-3 text-white rounded-lg" required>
                            <option value="Start">Start</option>
                            <option value="Pro">Pro</option>
                            <option value="VIP">VIP</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Objetivo</label>
                        <input type="text" name="objetivo" id="edit_objetivo" placeholder="Ex: Emagrecimento, Hipertrofia" class="w-full bg-[#0f172a] p-3 text-white rounded-lg">
                    </div>
                </div>
                
                <div>
                    <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Nova Senha (opcional)</label>
                    <input type="text" name="nova_senha_adm" id="edit_senha" 
                           placeholder="Deixe em branco para manter a senha atual" 
                           class="w-full bg-[#0f172a] p-3 text-white rounded-lg"
                           oninput="validarSenhaEditar(this.value)">
                    <!-- Indicador de força da senha -->
                    <div class="password-strength-meter mt-2 hidden" id="editPasswordStrengthMeter"></div>
                    <!-- Requisitos da senha -->
                    <div class="mt-2 space-y-1 hidden" id="editPasswordRequirements">
                        <div class="password-requirement requirement-not-met" id="editReqLength">
                            <i data-lucide="circle" class="requirement-icon"></i>
                            <span>Mínimo 8 caracteres</span>
                        </div>
                        <div class="password-requirement requirement-not-met" id="editReqUppercase">
                            <i data-lucide="circle" class="requirement-icon"></i>
                            <span>Pelo menos 1 letra maiúscula</span>
                        </div>
                        <div class="password-requirement requirement-not-met" id="editReqLowercase">
                            <i data-lucide="circle" class="requirement-icon"></i>
                            <span>Pelo menos 1 letra minúscula</span>
                        </div>
                        <div class="password-requirement requirement-not-met" id="editReqNumber">
                            <i data-lucide="circle" class="requirement-icon"></i>
                            <span>Pelo menos 1 número</span>
                        </div>
                        <div class="password-requirement requirement-not-met" id="editReqSpecial">
                            <i data-lucide="circle" class="requirement-icon"></i>
                            <span>Pelo menos 1 caractere especial (@#$%&!)</span>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 py-3 rounded-lg text-white font-bold transition-all">
                    Salvar Alterações
                </button>
            </form>
        </div>
    </div>

    <script>
        // Inicializar ícones
        lucide.createIcons();
        
        // Sistema de seleção de alunos
        let selecionarTodosAlunosAtivo = false;
        
        // Funções para exclusão em massa de alunos
        function selecionarTodosAlunos() {
            const selectAll = document.getElementById('selectAllAlunos');
            const checkboxes = document.querySelectorAll('input[name="ids_exclusao[]"]');
            selecionarTodosAlunosAtivo = !selecionarTodosAlunosAtivo;
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = selecionarTodosAlunosAtivo;
            });
            
            atualizarBotaoExcluirAlunos();
            atualizarSelectAll();
        }
        
        function atualizarBotaoExcluirAlunos() {
            const checkboxes = document.querySelectorAll('input[name="ids_exclusao[]"]:checked');
            const qtd = checkboxes.length;
            const btn = document.getElementById('btnExcluirAlunosMassa');
            
            if (btn) {
                btn.disabled = qtd === 0;
                btn.classList.toggle('opacity-50', qtd === 0);
                btn.classList.toggle('cursor-not-allowed', qtd === 0);
            }
        }
        
        function atualizarSelectAll() {
            const checkboxes = document.querySelectorAll('input[name="ids_exclusao[]"]');
            const selectAll = document.getElementById('selectAllAlunos');
            const checkedCount = document.querySelectorAll('input[name="ids_exclusao[]"]:checked').length;
            
            if (selectAll) {
                selectAll.checked = checkedCount === checkboxes.length && checkboxes.length > 0;
                selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
            }
        }
        
        function abrirModalExcluirAlunosMassa() {
            const checkboxes = document.querySelectorAll('input[name="ids_exclusao[]"]:checked');
            const qtd = checkboxes.length;
            const contagemElement = document.getElementById('contagemAlunosExclusao');
            
            if (contagemElement) {
                contagemElement.textContent = `Esta ação excluirá permanentemente ${qtd} aluno(s) selecionado(s).`;
            }
            
            document.getElementById('modalExcluirAlunosMassa').classList.remove('hidden');
            recriarIcones();
        }
        
        function fecharModalExcluirAlunosMassa() {
            document.getElementById('modalExcluirAlunosMassa').classList.add('hidden');
        }
        
        function confirmarExcluirAlunosMassa() {
            document.getElementById('formAlunosMassa').submit();
        }
        
        function abrirModalAluno() { 
            document.getElementById('modalAluno').classList.remove('hidden'); 
            recriarIcones();
            // Limpar validações anteriores
            document.getElementById('senhaAluno').value = '';
            validarSenha('');
        }
        
        function fecharModalAluno() { 
            document.getElementById('modalAluno').classList.add('hidden'); 
        }
        
        function abrirModalEditar(aluno) { 
            document.getElementById('edit_id').value = aluno.id; 
            document.getElementById('edit_nome').value = aluno.nome; 
            document.getElementById('edit_cpf').value = aluno.cpf; 
            document.getElementById('edit_email').value = aluno.email; 
            
            // Formatar telefone para máscara
            let telefoneFormatado = aluno.telefone;
            if (telefoneFormatado && !telefoneFormatado.includes('(')) {
                // Se não tem máscara, aplicar
                telefoneFormatado = aplicarMascaraTelefoneString(telefoneFormatado);
            }
            document.getElementById('edit_telefone').value = telefoneFormatado;
            
            document.getElementById('edit_plano').value = aluno.plano; 
            document.getElementById('edit_objetivo').value = aluno.objetivo || ''; 
            document.getElementById('modalEditar').classList.remove('hidden'); 
            recriarIcones();
            // Limpar senha e validações
            document.getElementById('edit_senha').value = '';
            validarSenhaEditar('');
        }
        
        function aplicarMascaraTelefoneString(telefone) {
            let value = telefone.replace(/\D/g, '');
            
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            
            // Aplica a máscara
            if (value.length > 0 && value.length <= 2) {
                value = value.replace(/(\d{0,2})/, '($1');
            } else if (value.length > 2 && value.length <= 7) {
                value = value.replace(/(\d{2})(\d)/, '($1) $2');
            } else if (value.length > 7) {
                value = value.replace(/(\d{2})(\d{5})(\d)/, '($1) $2-$3');
            }
            
            return value;
        }
        
        function fecharModalEditar() {
            document.getElementById('modalEditar').classList.add('hidden');
        }
        
        function abrirModalEditarProduto(prod) {
            document.getElementById('edit_prod_id').value = prod.id;
            document.getElementById('edit_prod_nome').value = prod.nome;
            document.getElementById('edit_prod_preco').value = "R$ " + parseFloat(prod.preco).toFixed(2).replace('.', ',');
            document.getElementById('edit_prod_estoque').value = prod.estoque;
            document.getElementById('edit_prod_categoria').value = prod.categoria;
            document.getElementById('edit_prod_descricao').value = prod.descricao || '';
            document.getElementById('modalEditarProduto').classList.remove('hidden');
            recriarIcones();
        }
        
        function fecharModalEditarProduto() {
            document.getElementById('modalEditarProduto').classList.add('hidden');
        }

        function abrirModalExcluirMassa() {
            document.getElementById('modalExcluirMassa').classList.remove('hidden');
            recriarIcones();
        }
        
        function fecharModalExcluirMassa() {
            document.getElementById('modalExcluirMassa').classList.add('hidden');
        }
        
        function abrirModalExcluir(id, tipo) {
            document.getElementById('id_exclusao').value = id;
            const inputAcao = document.getElementById('acao_exclusao');
            const titulo = document.getElementById('titulo_exclusao');
            
            if (tipo === 'aluno') { 
                inputAcao.value = 'excluir_aluno'; 
                titulo.innerText = 'Excluir Aluno?'; 
            } else if (tipo === 'produto') { 
                inputAcao.value = 'excluir_produto'; 
                titulo.innerText = 'Excluir Produto?'; 
            } else if (tipo === 'modelo') { 
                inputAcao.value = 'excluir_modelo'; 
                document.getElementById('id_exclusao').value = id;
                titulo.innerText = 'Excluir Modelo?'; 
            } else if (tipo === 'professor') { 
                inputAcao.value = 'excluir_professor'; 
                titulo.innerText = 'Excluir Professor?'; 
            } else if (tipo === 'recepcionista') { 
                inputAcao.value = 'excluir_recepcionista'; 
                titulo.innerText = 'Excluir Recepcionista?'; 
            }
            
            document.getElementById('modalExcluir').classList.remove('hidden');
            recriarIcones();
        }
        
        function fecharModalExcluir() {
            document.getElementById('modalExcluir').classList.add('hidden');
        }

        // FUNÇÕES DE MÁSCARAS E VALIDAÇÃO
        function aplicarMascaraCPF(input) {
            let value = input.value.replace(/\D/g, '');
            
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            
            // Aplica a máscara
            if (value.length > 3 && value.length <= 6) {
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
            } else if (value.length > 6 && value.length <= 9) {
                value = value.replace(/(\d{3})(\d{3})(\d)/, '$1.$2.$3');
            } else if (value.length > 9) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})(\d)/, '$1.$2.$3-$4');
            }
            
            input.value = value;
            
            // Atualiza feedback
            const feedback = document.getElementById('cpfFeedback');
            if (feedback) {
                if (value.length === 14) {
                    feedback.textContent = "✓ CPF válido";
                    feedback.className = "text-xs mt-1 text-green-500";
                } else if (value.length > 0) {
                    feedback.textContent = `${value.length}/14 caracteres`;
                    feedback.className = "text-xs mt-1 text-yellow-500";
                } else {
                    feedback.textContent = "Formato: 000.000.000-00";
                    feedback.className = "text-xs mt-1 text-gray-500";
                }
            }
        }
        
        function aplicarMascaraTelefone(input) {
            let value = input.value.replace(/\D/g, '');
            
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            
            // Aplica a máscara
            if (value.length > 0 && value.length <= 2) {
                value = value.replace(/(\d{0,2})/, '($1');
            } else if (value.length > 2 && value.length <= 7) {
                value = value.replace(/(\d{2})(\d)/, '($1) $2');
            } else if (value.length > 7) {
                value = value.replace(/(\d{2})(\d{5})(\d)/, '($1) $2-$3');
            }
            
            input.value = value;
            
            // Atualiza feedback
            let feedbackId = 'telefoneFeedback';
            if (input.id === 'edit_telefone') {
                feedbackId = 'editTelefoneFeedback';
            }
            
            const feedback = document.getElementById(feedbackId);
            if (feedback) {
                if (value.length === 15) {
                    feedback.textContent = "✓ Telefone válido";
                    feedback.className = "text-xs mt-1 text-green-500";
                } else if (value.length > 0) {
                    feedback.textContent = `${value.length}/15 caracteres`;
                    feedback.className = "text-xs mt-1 text-yellow-500";
                } else {
                    feedback.textContent = "Formato: (00) 00000-0000";
                    feedback.className = "text-xs mt-1 text-gray-500";
                }
            }
        }
        
        function validarSenha(senha) {
            // Verifica requisitos
            const temMinLength = senha.length >= 8;
            const temUppercase = /[A-Z]/.test(senha);
            const temLowercase = /[a-z]/.test(senha);
            const temNumber = /[0-9]/.test(senha);
            const temSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(senha);
            
            // Atualiza ícones dos requisitos
            atualizarRequisito('reqLength', temMinLength);
            atualizarRequisito('reqUppercase', temUppercase);
            atualizarRequisito('reqLowercase', temLowercase);
            atualizarRequisito('reqNumber', temNumber);
            atualizarRequisito('reqSpecial', temSpecial);
            
            // Calcula força da senha
            let strength = 0;
            if (temMinLength) strength++;
            if (temUppercase) strength++;
            if (temLowercase) strength++;
            if (temNumber) strength++;
            if (temSpecial) strength++;
            
            // Atualiza medidor visual
            const meter = document.getElementById('passwordStrengthMeter');
            if (meter) {
                meter.className = 'password-strength-meter mt-2 password-strength-' + strength;
                
                // Cor do medidor
                const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500', 'bg-emerald-500'];
                meter.className += ' ' + (colors[strength - 1] || 'bg-red-500');
            }
        }
        
        function validarSenhaEditar(senha) {
            if (!senha) {
                // Se a senha estiver vazia, esconder validações
                const meter = document.getElementById('editPasswordStrengthMeter');
                if (meter) meter.classList.add('hidden');
                
                // Esconder todos os requisitos
                ['editReqLength', 'editReqUppercase', 'editReqLowercase', 'editReqNumber', 'editReqSpecial'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.classList.add('hidden');
                });
                return;
            }
            
            // Mostrar validações
            const meter = document.getElementById('editPasswordStrengthMeter');
            if (meter) meter.classList.remove('hidden');
            
            // Mostrar todos os requisitos
            ['editReqLength', 'editReqUppercase', 'editReqLowercase', 'editReqNumber', 'editReqSpecial'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.classList.remove('hidden');
            });
            
            // Verifica requisitos
            const temMinLength = senha.length >= 8;
            const temUppercase = /[A-Z]/.test(senha);
            const temLowercase = /[a-z]/.test(senha);
            const temNumber = /[0-9]/.test(senha);
            const temSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(senha);
            
            // Atualiza ícones dos requisitos
            atualizarRequisito('editReqLength', temMinLength);
            atualizarRequisito('editReqUppercase', temUppercase);
            atualizarRequisito('editReqLowercase', temLowercase);
            atualizarRequisito('editReqNumber', temNumber);
            atualizarRequisito('editReqSpecial', temSpecial);
            
            // Calcula força da senha
            let strength = 0;
            if (temMinLength) strength++;
            if (temUppercase) strength++;
            if (temLowercase) strength++;
            if (temNumber) strength++;
            if (temSpecial) strength++;
            
            // Atualiza medidor visual
            if (meter) {
                meter.className = 'password-strength-meter mt-2 password-strength-' + strength;
                
                // Cor do medidor
                const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500', 'bg-emerald-500'];
                meter.className += ' ' + (colors[strength - 1] || 'bg-red-500');
            }
        }
        
        function atualizarRequisito(id, atendido) {
            const element = document.getElementById(id);
            if (element) {
                if (atendido) {
                    element.classList.remove('requirement-not-met');
                    element.classList.add('requirement-met');
                    const icon = element.querySelector('i');
                    if (icon) {
                        icon.setAttribute('data-lucide', 'check-circle');
                        recriarIcones();
                    }
                } else {
                    element.classList.remove('requirement-met');
                    element.classList.add('requirement-not-met');
                    const icon = element.querySelector('i');
                    if (icon) {
                        icon.setAttribute('data-lucide', 'circle');
                        recriarIcones();
                    }
                }
            }
        }
        
        function validarFormularioAluno() {
            // Valida CPF
            const cpf = document.getElementById('cpfAluno').value;
            if (cpf.length !== 14) {
                alert('CPF incompleto! Formato correto: 000.000.000-00');
                document.getElementById('cpfAluno').focus();
                return false;
            }
            
            // Valida telefone
            const telefone = document.getElementById('telefoneAluno').value;
            if (telefone.length < 14) {
                alert('Telefone incompleto! Formato correto: (00) 00000-0000');
                document.getElementById('telefoneAluno').focus();
                return false;
            }
            
            // Valida senha
            const senha = document.getElementById('senhaAluno').value;
            if (senha.length < 8) {
                alert('A senha deve ter pelo menos 8 caracteres!');
                document.getElementById('senhaAluno').focus();
                return false;
            }
            
            // Verifica requisitos da senha
            const temUppercase = /[A-Z]/.test(senha);
            const temLowercase = /[a-z]/.test(senha);
            const temNumber = /[0-9]/.test(senha);
            const temSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(senha);
            
            if (!temUppercase || !temLowercase || !temNumber || !temSpecial) {
                alert('A senha deve conter pelo menos:\n- Uma letra maiúscula\n- Uma letra minúscula\n- Um número\n- Um caractere especial (@#$%&!)');
                document.getElementById('senhaAluno').focus();
                return false;
            }
            
            return true;
        }
        
        function validarFormularioEditarAluno() {
            // Valida telefone
            const telefone = document.getElementById('edit_telefone').value;
            if (telefone.length < 14) {
                alert('Telefone incompleto! Formato correto: (00) 00000-0000');
                document.getElementById('edit_telefone').focus();
                return false;
            }
            
            // Valida senha se for preenchida
            const senha = document.getElementById('edit_senha').value;
            if (senha) {
                if (senha.length < 8) {
                    alert('A senha deve ter pelo menos 8 caracteres!');
                    document.getElementById('edit_senha').focus();
                    return false;
                }
                
                // Verifica requisitos da senha
                const temUppercase = /[A-Z]/.test(senha);
                const temLowercase = /[a-z]/.test(senha);
                const temNumber = /[0-9]/.test(senha);
                const temSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(senha);
                
                if (!temUppercase || !temLowercase || !temNumber || !temSpecial) {
                    alert('A senha deve conter pelo menos:\n- Uma letra maiúscula\n- Uma letra minúscula\n- Um número\n- Um caractere especial (@#$%&!)');
                    document.getElementById('edit_senha').focus();
                    return false;
                }
            }
            
            return true;
        }
        
        function mascaraMoeda(input) {
            let valor = input.value.replace(/\D/g, '');
            if (valor === '') {
                input.value = 'R$ 0,00';
                return;
            }
            valor = (parseInt(valor) / 100).toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            input.value = 'R$ ' + valor;
        }

        function atualizarBotaoExcluir() {
            const qtd = document.querySelectorAll('.check-item:checked').length;
            const btn = document.getElementById('btnExcluirMassa');
            btn.disabled = qtd === 0;
            btn.classList.toggle('opacity-50', qtd === 0);
            btn.classList.toggle('cursor-not-allowed', qtd === 0);
        }

        async function carregarTreino(id, tipo) {
            if(!id) return;
            const prefixo = tipo === 'modelo' ? 'modelo-' : 'container-';
            ['A','B','C'].forEach(d => {
                const container = document.getElementById(prefixo + d);
                if (container) container.innerHTML = '';
            });
            
            try {
                const acao = tipo === 'modelo' ? 'buscar_modelo' : 'buscar_treino';
                const response = await fetch(`adm.php?acao_ajax=${acao}&id=${id}`);
                const data = await response.json();
                
                ['A','B','C'].forEach(div => { 
                    if(data[div] && data[div].length > 0) {
                        data[div].forEach(t => addExercicio(prefixo + div, t.exercicio, t.series)); 
                    }
                });
                
                if(tipo === 'modelo') {
                    const nomeInput = document.querySelector('input[name="nome_modelo"]');
                    if (nomeInput) nomeInput.value = '';
                }
                
                exibirToast(tipo === 'modelo' ? "Modelo carregado!" : "Treino carregado!", "sucesso");
                recriarIcones();
            } catch (error) { 
                console.error("Erro ao carregar treino:", error);
                exibirToast("Erro ao carregar dados", "erro");
            }
        }

        function addExercicio(containerId, nome = '', series = '3x12') {
            const container = document.getElementById(containerId);
            if (!container) return;
            
            const cleanId = containerId.replace('container-', '').replace('modelo-', ''); 
            const div = document.createElement('div');
            div.className = 'flex gap-2 items-center mb-3 p-3 bg-[#0f172a] rounded-lg border border-white/5';
            div.innerHTML = `
                <div class="flex-1 grid grid-cols-2 gap-2">
                    <input type="text" name="treino[${cleanId}][][nome]" value="${nome}" placeholder="Exercício" class="bg-[#1e293b] border border-white/10 rounded px-3 py-2 text-sm text-white">
                    <input type="text" name="treino[${cleanId}][][series]" value="${series}" placeholder="Reps" class="bg-[#1e293b] border border-white/10 rounded px-3 py-2 text-sm text-center text-gray-400">
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-gray-600 hover:text-red-500 p-2">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            `;
            container.appendChild(div);
            recriarIcones();
        }

        function gerarTreinoAutomatico() {
            ['A','B','C'].forEach(d => {
                const container = document.getElementById('container-' + d);
                if (container) container.innerHTML = '';
            });
            
            const tA = [
                {n:'Supino Reto', s:'3x12'},
                {n:'Supino Inclinado', s:'3x12'},
                {n:'Tríceps Corda', s:'3x12'}
            ];
            const tB = [
                {n:'Puxada Alta', s:'3x12'},
                {n:'Remada Baixa', s:'3x12'},
                {n:'Rosca Direta', s:'3x12'}
            ];
            const tC = [
                {n:'Agachamento', s:'3x12'},
                {n:'Leg Press', s:'3x12'},
                {n:'Extensora', s:'3x15'}
            ];
            
            tA.forEach(e => addExercicio('container-A', e.n, e.s)); 
            tB.forEach(e => addExercicio('container-B', e.n, e.s)); 
            tC.forEach(e => addExercicio('container-C', e.n, e.s));
            
            exibirToast("Treino padrão gerado!", "sucesso");
        }
        
        function gerarModeloAutomatico() {
            ['A','B','C'].forEach(d => {
                const container = document.getElementById('modelo-' + d);
                if (container) container.innerHTML = '';
            });
            
            const tA = [
                {n:'Supino Reto', s:'3x12'},
                {n:'Supino Inclinado', s:'3x12'},
                {n:'Tríceps Corda', s:'3x12'}
            ];
            const tB = [
                {n:'Puxada Alta', s:'3x12'},
                {n:'Remada Baixa', s:'3x12'},
                {n:'Rosca Direta', s:'3x12'}
            ];
            const tC = [
                {n:'Agachamento', s:'3x12'},
                {n:'Leg Press', s:'3x12'},
                {n:'Extensora', s:'3x15'}
            ];
            
            tA.forEach(e => addExercicio('modelo-A', e.n, e.s)); 
            tB.forEach(e => addExercicio('modelo-B', e.n, e.s)); 
            tC.forEach(e => addExercicio('modelo-C', e.n, e.s));
            
            exibirToast("Modelo padrão gerado!", "sucesso");
        }

        function switchTab(tabId) { 
            // Esconder todas as abas
            document.querySelectorAll('.tab-content').forEach(el => {
                el.classList.add('hidden');
                el.classList.remove('fade-in');
            });
            
            // Remover estilo ativo de todos os botões
            document.querySelectorAll('.nav-item').forEach(el => { 
                el.classList.remove('bg-tech-primary/10', 'text-tech-primary', 'border-tech-primary/20'); 
                el.classList.add('text-gray-400', 'hover:bg-white/5', 'hover:text-white'); 
            }); 
            
            // Mostrar aba selecionada
            const tabElement = document.getElementById('tab-' + tabId);
            if (tabElement) {
                tabElement.classList.remove('hidden');
                setTimeout(() => {
                    tabElement.classList.add('fade-in');
                }, 10);
            }
            
            // Ativar botão correspondente
            const activeBtn = document.querySelector(`button[onclick="switchTab('${tabId}')"]`);
            if (activeBtn) { 
                activeBtn.classList.remove('text-gray-400', 'hover:bg-white/5', 'hover:text-white'); 
                activeBtn.classList.add('bg-tech-primary/10', 'text-tech-primary', 'border-tech-primary/20'); 
            } 
            
            // Atualizar título da página
            const titulos = {
                'dashboard': 'Dashboard',
                'alunos': 'Alunos',
                'treinos': 'Treinos',
                'loja': 'Loja',
                'financeiro': 'Financeiro',
                'recepcionista': 'Recepção'
            };
            const pageTitle = document.getElementById('pageTitle');
            if (pageTitle) {
                pageTitle.textContent = titulos[tabId] || 'Painel TechFit';
            }
            
            // Atualizar URL
            const url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            window.history.pushState({}, '', url);
            
            // Recriar ícones
            recriarIcones();
        }
        
        function switchSubTab(subTab) { 
            const individualView = document.getElementById('view-individual');
            const padraoView = document.getElementById('view-padrao');
            const btnIndividual = document.getElementById('btn-individual');
            const btnPadrao = document.getElementById('btn-padrao');
            
            if (subTab === 'individual') {
                individualView.classList.remove('hidden');
                padraoView.classList.add('hidden');
                btnIndividual.classList.add('text-tech-primary', 'border-b-2', 'border-tech-primary');
                btnIndividual.classList.remove('text-gray-400');
                btnPadrao.classList.add('text-gray-400');
                btnPadrao.classList.remove('text-tech-primary', 'border-b-2', 'border-tech-primary');
            } else {
                individualView.classList.add('hidden');
                padraoView.classList.remove('hidden');
                btnPadrao.classList.add('text-tech-primary', 'border-b-2', 'border-tech-primary');
                btnPadrao.classList.remove('text-gray-400');
                btnIndividual.classList.add('text-gray-400');
                btnIndividual.classList.remove('text-tech-primary', 'border-b-2', 'border-tech-primary');
            }
            
            // Atualizar URL
            const url = new URL(window.location);
            url.searchParams.set('sub', subTab);
            window.history.pushState({}, '', url);
            
            recriarIcones();
        }
        
        function toggleSidebar() { 
            const sb = document.getElementById('sidebar');
            const icon = document.getElementById('toggleIcon');
            
            if (sb.classList.contains('w-64')) { 
                sb.classList.remove('w-64'); 
                sb.classList.add('w-20', 'sidebar-collapsed'); 
                icon.setAttribute('data-lucide', 'chevron-right'); 
            } else { 
                sb.classList.remove('w-20', 'sidebar-collapsed'); 
                sb.classList.add('w-64'); 
                icon.setAttribute('data-lucide', 'chevron-left'); 
            } 
            recriarIcones(); 
        }
        
        function exibirToast(msg, tipo) { 
            const div = document.createElement('div'); 
            const cor = tipo === 'erro' ? 'border-red-500 text-red-400' : 'border-green-500 text-white'; 
            div.className = `fixed top-5 right-5 z-50 bg-[#1e293b] border-l-4 ${cor} p-4 rounded-lg shadow-2xl flex items-center gap-3 animate-fadeIn`; 
            div.innerHTML = `<i data-lucide="${tipo === 'erro' ? 'alert-circle' : 'check-circle'}"></i> ${msg}`; 
            document.body.appendChild(div); 
            recriarIcones(); 
            setTimeout(() => div.remove(), 4000); 
        }
        
        // Função para recriar ícones
        function recriarIcones() {
            setTimeout(() => {
                try {
                    lucide.createIcons();
                } catch (error) {
                    console.log("Recriando ícones...");
                }
            }, 50);
        }
        
        // Atualizar financeiro automaticamente (simulação)
        function atualizarFinanceiro() {
            // Em produção, faria uma chamada AJAX para atualizar os dados
            console.log("Atualizando dados financeiros...");
            
            // Simular atualização a cada 30 segundos
            setTimeout(atualizarFinanceiro, 30000);
        }
        
        // Inicializar página
        document.addEventListener('DOMContentLoaded', () => { 
            const params = new URLSearchParams(window.location.search);
            const tab = params.get('tab') || 'dashboard';
            switchTab(tab); 
            
            const subTab = params.get('sub');
            if (subTab) switchSubTab(subTab);
            
            // Configurar eventos para checkboxes
            document.addEventListener('change', function(e) {
                // Checkboxes de alunos
                if (e.target.name === 'ids_exclusao[]') {
                    atualizarBotaoExcluirAlunos();
                    atualizarSelectAll();
                }
                
                // Checkboxes de produtos
                if (e.target.classList.contains('check-item')) {
                    atualizarBotaoExcluir();
                }
                
                // Checkbox "Selecionar todos"
                if (e.target.id === 'selectAllAlunos') {
                    selecionarTodosAlunos();
                }
            });
            
            // Recriar ícones após carregar
            setTimeout(() => {
                recriarIcones();
            }, 100);
            
            // Iniciar atualização automática do financeiro
            setTimeout(atualizarFinanceiro, 10000);
            
            <?php if ($msgAdm): ?>
                exibirToast("<?= addslashes($msgAdm) ?>", "<?= $tipoMsgAdm ?>");
            <?php endif; ?>
        });
        
        // Adicionar classe fade-in dinamicamente
        document.addEventListener('animationend', function(e) {
            if (e.animationName === 'fadeIn') {
                e.target.classList.remove('fade-in');
            }
        });
    </script>

    <!-- MODAL PROFESSOR (CORRIGIDO) -->
    <div id="modalProfessor" class="fixed inset-0 z-50 hidden bg-black/90 flex items-center justify-center p-4">
        <div class="bg-[#1e293b] w-full max-w-2xl rounded-2xl border border-white/10 shadow-2xl p-8 relative">
            <button type="button" onclick="fecharModalProfessor()" class="absolute top-4 right-4 text-gray-400 hover:text-white p-2">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
            <h2 class="text-xl font-bold mb-6 text-white">Novo Professor</h2>
            <form method="POST" class="space-y-4" onsubmit="return validarFormularioProfessor()">
                <input type="hidden" name="acao" value="cadastrar_professor">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Nome Completo</label>
                        <input type="text" name="nome" placeholder="Nome Completo" class="w-full bg-[#0f172a] p-3 text-white rounded-lg" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">CPF</label>
                        <input type="text" name="cpf" id="cpfProfessor" placeholder="CPF (000.000.000-00)" 
                               class="w-full bg-[#0f172a] p-3 text-white rounded-lg" 
                               maxlength="14" required
                               oninput="aplicarMascaraCPFProfessor(this)">
                        <div id="cpfFeedbackProfessor" class="text-xs mt-1 text-gray-500"></div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Email</label>
                        <input type="email" name="email" placeholder="Email" class="w-full bg-[#0f172a] p-3 text-white rounded-lg" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Telefone</label>
                        <input type="text" name="telefone" id="telefoneProfessor" placeholder="Telefone ((00) 00000-0000)" 
                               class="w-full bg-[#0f172a] p-3 text-white rounded-lg" 
                               maxlength="15" required
                               oninput="aplicarMascaraTelefoneProfessor(this)">
                        <div id="telefoneFeedbackProfessor" class="text-xs mt-1 text-gray-500"></div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Data de Nascimento</label>
                        <input type="date" name="data_nascimento" class="w-full bg-[#0f172a] p-3 text-white rounded-lg" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">CREF</label>
                        <input type="text" name="cref" placeholder="CREF" class="w-full bg-[#0f172a] p-3 text-white rounded-lg" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Especialidade</label>
                        <select name="especialidade" class="w-full bg-[#0f172a] p-3 text-white rounded-lg" required>
                            <option value="Musculação">Musculação</option>
                            <option value="Crossfit">Crossfit</option>
                            <option value="Funcional">Funcional</option>
                            <option value="Pilates">Pilates</option>
                            <option value="Yoga">Yoga</option>
                            <option value="Nutrição">Nutrição</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Senha</label>
                    <input type="text" name="senha" id="senhaProfessor" placeholder="Senha (mínimo 8 caracteres)" 
                           class="w-full bg-[#0f172a] p-3 text-white rounded-lg" 
                           required oninput="validarSenhaProfessor(this.value)">
                    <!-- Indicador de força da senha -->
                    <div class="password-strength-meter mt-2" id="passwordStrengthMeterProfessor"></div>
                    <!-- Requisitos da senha -->
                    <div class="mt-2 space-y-1" id="passwordRequirementsProfessor">
                        <div class="password-requirement requirement-not-met" id="reqLengthProfessor">
                            <i data-lucide="circle" class="requirement-icon"></i>
                            <span>Mínimo 8 caracteres</span>
                        </div>
                        <div class="password-requirement requirement-not-met" id="reqUppercaseProfessor">
                            <i data-lucide="circle" class="requirement-icon"></i>
                            <span>Pelo menos 1 letra maiúscula</span>
                        </div>
                        <div class="password-requirement requirement-not-met" id="reqLowercaseProfessor">
                            <i data-lucide="circle" class="requirement-icon"></i>
                            <span>Pelo menos 1 letra minúscula</span>
                        </div>
                        <div class="password-requirement requirement-not-met" id="reqNumberProfessor">
                            <i data-lucide="circle" class="requirement-icon"></i>
                            <span>Pelo menos 1 número</span>
                        </div>
                        <div class="password-requirement requirement-not-met" id="reqSpecialProfessor">
                            <i data-lucide="circle" class="requirement-icon"></i>
                            <span>Pelo menos 1 caractere especial (@#$%&!)</span>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-tech-primary hover:bg-orange-600 py-3 rounded-lg text-white font-bold transition-all">
                    Cadastrar Professor
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL EDITAR PROFESSOR (CORRIGIDO) -->
    <div id="modalEditarProfessor" class="fixed inset-0 z-50 hidden bg-black/90 flex items-center justify-center p-4">
        <div class="bg-[#1e293b] w-full max-w-2xl rounded-2xl border border-white/10 shadow-2xl p-8 relative">
            <button type="button" onclick="fecharModalEditarProfessor()" class="absolute top-4 right-4 text-gray-400 hover:text-white p-2">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
            <h2 class="text-xl font-bold mb-6 text-white">Editar Professor</h2>
            <form method="POST" class="space-y-4" onsubmit="return validarFormularioEditarProfessor()">
                <input type="hidden" name="acao" value="editar_professor">
                <input type="hidden" name="id" id="edit_prof_id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Nome Completo</label>
                        <input type="text" name="nome" id="edit_prof_nome" class="w-full bg-[#0f172a] p-3 text-white rounded-lg" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">CPF (somente visualização)</label>
                        <input type="text" id="edit_prof_cpf" class="w-full bg-[#0f172a] p-3 text-gray-400 rounded-lg" disabled>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Email</label>
                        <input type="email" name="email" id="edit_prof_email" class="w-full bg-[#0f172a] p-3 text-white rounded-lg" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Telefone</label>
                        <input type="text" name="telefone" id="edit_prof_telefone" 
                               class="w-full bg-[#0f172a] p-3 text-white rounded-lg" 
                               maxlength="15" required
                               oninput="aplicarMascaraTelefoneProfessorEditar(this)"
                               placeholder="(00) 00000-0000">
                        <div id="editTelefoneFeedbackProfessor" class="text-xs mt-1 text-gray-500"></div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Data de Nascimento</label>
                        <input type="date" name="data_nascimento" id="edit_prof_data_nascimento" class="w-full bg-[#0f172a] p-3 text-white rounded-lg" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">CREF</label>
                        <input type="text" name="cref" id="edit_prof_cref" class="w-full bg-[#0f172a] p-3 text-white rounded-lg" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Especialidade</label>
                        <select name="especialidade" id="edit_prof_especialidade" class="w-full bg-[#0f172a] p-3 text-white rounded-lg" required>
                            <option value="Musculação">Musculação</option>
                            <option value="Crossfit">Crossfit</option>
                            <option value="Funcional">Funcional</option>
                            <option value="Pilates">Pilates</option>
                            <option value="Yoga">Yoga</option>
                            <option value="Nutrição">Nutrição</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Nova Senha (opcional)</label>
                    <input type="text" name="nova_senha" id="edit_prof_senha" 
                           placeholder="Deixe em branco para manter a senha atual" 
                           class="w-full bg-[#0f172a] p-3 text-white rounded-lg"
                           oninput="validarSenhaProfessorEditar(this.value)">
                    <!-- Indicador de força da senha -->
                    <div class="password-strength-meter mt-2 hidden" id="editPasswordStrengthMeterProfessor"></div>
                    <!-- Requisitos da senha -->
                    <div class="mt-2 space-y-1 hidden" id="editPasswordRequirementsProfessor">
                        <div class="password-requirement requirement-not-met" id="editReqLengthProfessor">
                            <i data-lucide="circle" class="requirement-icon"></i>
                            <span>Mínimo 8 caracteres</span>
                        </div>
                        <div class="password-requirement requirement-not-met" id="editReqUppercaseProfessor">
                            <i data-lucide="circle" class="requirement-icon"></i>
                            <span>Pelo menos 1 letra maiúscula</span>
                        </div>
                        <div class="password-requirement requirement-not-met" id="editReqLowercaseProfessor">
                            <i data-lucide="circle" class="requirement-icon"></i>
                            <span>Pelo menos 1 letra minúscula</span>
                        </div>
                        <div class="password-requirement requirement-not-met" id="editReqNumberProfessor">
                            <i data-lucide="circle" class="requirement-icon"></i>
                            <span>Pelo menos 1 número</span>
                        </div>
                        <div class="password-requirement requirement-not-met" id="editReqSpecialProfessor">
                            <i data-lucide="circle" class="requirement-icon"></i>
                            <span>Pelo menos 1 caractere especial (@#$%&!)</span>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 py-3 rounded-lg text-white font-bold transition-all">
                    Salvar Alterações
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL RECEPCIONISTA (CORRIGIDO) -->
    <div id="modalRecepcionista" class="fixed inset-0 z-50 hidden bg-black/90 flex items-center justify-center p-4">
        <div class="bg-[#1e293b] w-full max-w-2xl rounded-2xl border border-white/10 shadow-2xl p-8 relative">
            <button type="button" onclick="fecharModalRecepcionista()" class="absolute top-4 right-4 text-gray-400 hover:text-white p-2">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
            <h2 class="text-xl font-bold mb-6 text-white">Novo Recepcionista</h2>
            <form method="POST" class="space-y-4" onsubmit="return validarFormularioRecepcionista()">
                <input type="hidden" name="acao" value="cadastrar_recepcionista">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Nome Completo</label>
                        <input type="text" name="nome" placeholder="Nome Completo" class="w-full bg-[#0f172a] p-3 text-white rounded-lg" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">CPF</label>
                        <input type="text" name="cpf" id="cpfRecepcionista" placeholder="CPF (000.000.000-00)" 
                               class="w-full bg-[#0f172a] p-3 text-white rounded-lg" 
                               maxlength="14" required
                               oninput="aplicarMascaraCPFRecepcionista(this)">
                        <div id="cpfFeedbackRecepcionista" class="text-xs mt-1 text-gray-500"></div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Email</label>
                        <input type="email" name="email" placeholder="Email" class="w-full bg-[#0f172a] p-3 text-white rounded-lg" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Telefone</label>
                        <input type="text" name="telefone" id="telefoneRecepcionista" placeholder="Telefone ((00) 00000-0000)" 
                               class="w-full bg-[#0f172a] p-3 text-white rounded-lg" 
                               maxlength="15" required
                               oninput="aplicarMascaraTelefoneRecepcionista(this)">
                        <div id="telefoneFeedbackRecepcionista" class="text-xs mt-1 text-gray-500"></div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Data de Nascimento</label>
                        <input type="date" name="data_nascimento" class="w-full bg-[#0f172a] p-3 text-white rounded-lg" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Senha</label>
                        <input type="text" name="senha" id="senhaRecepcionista" placeholder="Senha (mínimo 8 caracteres)" 
                               class="w-full bg-[#0f172a] p-3 text-white rounded-lg" 
                               required oninput="validarSenhaRecepcionista(this.value)">
                        <!-- Indicador de força da senha -->
                        <div class="password-strength-meter mt-2" id="passwordStrengthMeterRecepcionista"></div>
                        <!-- Requisitos da senha -->
                        <div class="mt-2 space-y-1" id="passwordRequirementsRecepcionista">
                            <div class="password-requirement requirement-not-met" id="reqLengthRecepcionista">
                                <i data-lucide="circle" class="requirement-icon"></i>
                                <span>Mínimo 8 caracteres</span>
                            </div>
                            <div class="password-requirement requirement-not-met" id="reqUppercaseRecepcionista">
                                <i data-lucide="circle" class="requirement-icon"></i>
                                <span>Pelo menos 1 letra maiúscula</span>
                            </div>
                            <div class="password-requirement requirement-not-met" id="reqLowercaseRecepcionista">
                                <i data-lucide="circle" class="requirement-icon"></i>
                                <span>Pelo menos 1 letra minúscula</span>
                            </div>
                            <div class="password-requirement requirement-not-met" id="reqNumberRecepcionista">
                                <i data-lucide="circle" class="requirement-icon"></i>
                                <span>Pelo menos 1 número</span>
                            </div>
                            <div class="password-requirement requirement-not-met" id="reqSpecialRecepcionista">
                                <i data-lucide="circle" class="requirement-icon"></i>
                                <span>Pelo menos 1 caractere especial (@#$%&!)</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-tech-primary hover:bg-orange-600 py-3 rounded-lg text-white font-bold transition-all">
                    Cadastrar Recepcionista
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL EDITAR RECEPCIONISTA (CORRIGIDO) -->
    <div id="modalEditarRecepcionista" class="fixed inset-0 z-50 hidden bg-black/90 flex items-center justify-center p-4">
        <div class="bg-[#1e293b] w-full max-w-2xl rounded-2xl border border-white/10 shadow-2xl p-8 relative">
            <button type="button" onclick="fecharModalEditarRecepcionista()" class="absolute top-4 right-4 text-gray-400 hover:text-white p-2">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
            <h2 class="text-xl font-bold mb-6 text-white">Editar Recepcionista</h2>
            <form method="POST" class="space-y-4" onsubmit="return validarFormularioEditarRecepcionista()">
                <input type="hidden" name="acao" value="editar_recepcionista">
                <input type="hidden" name="id" id="edit_rec_id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Nome Completo</label>
                        <input type="text" name="nome" id="edit_rec_nome" class="w-full bg-[#0f172a] p-3 text-white rounded-lg" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">CPF (somente visualização)</label>
                        <input type="text" id="edit_rec_cpf" class="w-full bg-[#0f172a] p-3 text-gray-400 rounded-lg" disabled>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Email</label>
                        <input type="email" name="email" id="edit_rec_email" class="w-full bg-[#0f172a] p-3 text-white rounded-lg" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Telefone</label>
                        <input type="text" name="telefone" id="edit_rec_telefone" 
                               class="w-full bg-[#0f172a] p-3 text-white rounded-lg" 
                               maxlength="15" required
                               oninput="aplicarMascaraTelefoneRecepcionistaEditar(this)"
                               placeholder="(00) 00000-0000">
                        <div id="editTelefoneFeedbackRecepcionista" class="text-xs mt-1 text-gray-500"></div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Data de Nascimento</label>
                        <input type="date" name="data_nascimento" id="edit_rec_data_nascimento" class="w-full bg-[#0f172a] p-3 text-white rounded-lg" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Nova Senha (opcional)</label>
                        <input type="text" name="nova_senha" id="edit_rec_senha" 
                               placeholder="Deixe em branco para manter a senha atual" 
                               class="w-full bg-[#0f172a] p-3 text-white rounded-lg"
                               oninput="validarSenhaRecepcionistaEditar(this.value)">
                        <!-- Indicador de força da senha -->
                        <div class="password-strength-meter mt-2 hidden" id="editPasswordStrengthMeterRecepcionista"></div>
                        <!-- Requisitos da senha -->
                        <div class="mt-2 space-y-1 hidden" id="editPasswordRequirementsRecepcionista">
                            <div class="password-requirement requirement-not-met" id="editReqLengthRecepcionista">
                                <i data-lucide="circle" class="requirement-icon"></i>
                                <span>Mínimo 8 caracteres</span>
                            </div>
                            <div class="password-requirement requirement-not-met" id="editReqUppercaseRecepcionista">
                                <i data-lucide="circle" class="requirement-icon"></i>
                                <span>Pelo menos 1 letra maiúscula</span>
                            </div>
                            <div class="password-requirement requirement-not-met" id="editReqLowercaseRecepcionista">
                                <i data-lucide="circle" class="requirement-icon"></i>
                                <span>Pelo menos 1 letra minúscula</span>
                            </div>
                            <div class="password-requirement requirement-not-met" id="editReqNumberRecepcionista">
                                <i data-lucide="circle" class="requirement-icon"></i>
                                <span>Pelo menos 1 número</span>
                            </div>
                            <div class="password-requirement requirement-not-met" id="editReqSpecialRecepcionista">
                                <i data-lucide="circle" class="requirement-icon"></i>
                                <span>Pelo menos 1 caractere especial (@#$%&!)</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 py-3 rounded-lg text-white font-bold transition-all">
                    Salvar Alterações
                </button>
            </form>
        </div>
    </div>

    <script>
        // Funções de validação específicas para professores
        function aplicarMascaraCPFProfessor(input) {
            let value = input.value.replace(/\D/g, '');
            
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            
            // Aplica a máscara
            if (value.length > 3 && value.length <= 6) {
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
            } else if (value.length > 6 && value.length <= 9) {
                value = value.replace(/(\d{3})(\d{3})(\d)/, '$1.$2.$3');
            } else if (value.length > 9) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})(\d)/, '$1.$2.$3-$4');
            }
            
            input.value = value;
            
            // Atualiza feedback
            const feedback = document.getElementById('cpfFeedbackProfessor');
            if (feedback) {
                if (value.length === 14) {
                    feedback.textContent = "✓ CPF válido";
                    feedback.className = "text-xs mt-1 text-green-500";
                } else if (value.length > 0) {
                    feedback.textContent = `${value.length}/14 caracteres`;
                    feedback.className = "text-xs mt-1 text-yellow-500";
                } else {
                    feedback.textContent = "Formato: 000.000.000-00";
                    feedback.className = "text-xs mt-1 text-gray-500";
                }
            }
        }
        
        function aplicarMascaraTelefoneProfessor(input) {
            let value = input.value.replace(/\D/g, '');
            
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            
            // Aplica a máscara
            if (value.length > 0 && value.length <= 2) {
                value = value.replace(/(\d{0,2})/, '($1');
            } else if (value.length > 2 && value.length <= 7) {
                value = value.replace(/(\d{2})(\d)/, '($1) $2');
            } else if (value.length > 7) {
                value = value.replace(/(\d{2})(\d{5})(\d)/, '($1) $2-$3');
            }
            
            input.value = value;
            
            // Atualiza feedback
            const feedback = document.getElementById('telefoneFeedbackProfessor');
            if (feedback) {
                if (value.length === 15) {
                    feedback.textContent = "✓ Telefone válido";
                    feedback.className = "text-xs mt-1 text-green-500";
                } else if (value.length > 0) {
                    feedback.textContent = `${value.length}/15 caracteres`;
                    feedback.className = "text-xs mt-1 text-yellow-500";
                } else {
                    feedback.textContent = "Formato: (00) 00000-0000";
                    feedback.className = "text-xs mt-1 text-gray-500";
                }
            }
        }
        
        function aplicarMascaraTelefoneProfessorEditar(input) {
            let value = input.value.replace(/\D/g, '');
            
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            
            // Aplica a máscara
            if (value.length > 0 && value.length <= 2) {
                value = value.replace(/(\d{0,2})/, '($1');
            } else if (value.length > 2 && value.length <= 7) {
                value = value.replace(/(\d{2})(\d)/, '($1) $2');
            } else if (value.length > 7) {
                value = value.replace(/(\d{2})(\d{5})(\d)/, '($1) $2-$3');
            }
            
            input.value = value;
            
            // Atualiza feedback
            const feedback = document.getElementById('editTelefoneFeedbackProfessor');
            if (feedback) {
                if (value.length === 15) {
                    feedback.textContent = "✓ Telefone válido";
                    feedback.className = "text-xs mt-1 text-green-500";
                } else if (value.length > 0) {
                    feedback.textContent = `${value.length}/15 caracteres`;
                    feedback.className = "text-xs mt-1 text-yellow-500";
                } else {
                    feedback.textContent = "Formato: (00) 00000-0000";
                    feedback.className = "text-xs mt-1 text-gray-500";
                }
            }
        }
        
        function validarSenhaProfessor(senha) {
            // Verifica requisitos
            const temMinLength = senha.length >= 8;
            const temUppercase = /[A-Z]/.test(senha);
            const temLowercase = /[a-z]/.test(senha);
            const temNumber = /[0-9]/.test(senha);
            const temSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(senha);
            
            // Atualiza ícones dos requisitos
            atualizarRequisitoProfessor('reqLengthProfessor', temMinLength);
            atualizarRequisitoProfessor('reqUppercaseProfessor', temUppercase);
            atualizarRequisitoProfessor('reqLowercaseProfessor', temLowercase);
            atualizarRequisitoProfessor('reqNumberProfessor', temNumber);
            atualizarRequisitoProfessor('reqSpecialProfessor', temSpecial);
            
            // Calcula força da senha
            let strength = 0;
            if (temMinLength) strength++;
            if (temUppercase) strength++;
            if (temLowercase) strength++;
            if (temNumber) strength++;
            if (temSpecial) strength++;
            
            // Atualiza medidor visual
            const meter = document.getElementById('passwordStrengthMeterProfessor');
            if (meter) {
                meter.className = 'password-strength-meter mt-2 password-strength-' + strength;
                const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500', 'bg-emerald-500'];
                meter.className += ' ' + (colors[strength - 1] || 'bg-red-500');
            }
        }
        
        function validarSenhaProfessorEditar(senha) {
            if (!senha) {
                // Se a senha estiver vazia, esconder validações
                const meter = document.getElementById('editPasswordStrengthMeterProfessor');
                if (meter) meter.classList.add('hidden');
                
                // Esconder todos os requisitos
                ['editReqLengthProfessor', 'editReqUppercaseProfessor', 'editReqLowercaseProfessor', 'editReqNumberProfessor', 'editReqSpecialProfessor'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.classList.add('hidden');
                });
                return;
            }
            
            // Mostrar validações
            const meter = document.getElementById('editPasswordStrengthMeterProfessor');
            if (meter) meter.classList.remove('hidden');
            
            // Mostrar todos os requisitos
            ['editReqLengthProfessor', 'editReqUppercaseProfessor', 'editReqLowercaseProfessor', 'editReqNumberProfessor', 'editReqSpecialProfessor'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.classList.remove('hidden');
            });
            
            // Verifica requisitos
            const temMinLength = senha.length >= 8;
            const temUppercase = /[A-Z]/.test(senha);
            const temLowercase = /[a-z]/.test(senha);
            const temNumber = /[0-9]/.test(senha);
            const temSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(senha);
            
            // Atualiza ícones dos requisitos
            atualizarRequisitoProfessor('editReqLengthProfessor', temMinLength);
            atualizarRequisitoProfessor('editReqUppercaseProfessor', temUppercase);
            atualizarRequisitoProfessor('editReqLowercaseProfessor', temLowercase);
            atualizarRequisitoProfessor('editReqNumberProfessor', temNumber);
            atualizarRequisitoProfessor('editReqSpecialProfessor', temSpecial);
            
            // Calcula força da senha
            let strength = 0;
            if (temMinLength) strength++;
            if (temUppercase) strength++;
            if (temLowercase) strength++;
            if (temNumber) strength++;
            if (temSpecial) strength++;
            
            // Atualiza medidor visual
            if (meter) {
                meter.className = 'password-strength-meter mt-2 password-strength-' + strength;
                const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500', 'bg-emerald-500'];
                meter.className += ' ' + (colors[strength - 1] || 'bg-red-500');
            }
        }
        
        function atualizarRequisitoProfessor(id, atendido) {
            const element = document.getElementById(id);
            if (element) {
                if (atendido) {
                    element.classList.remove('requirement-not-met');
                    element.classList.add('requirement-met');
                    const icon = element.querySelector('i');
                    if (icon) {
                        icon.setAttribute('data-lucide', 'check-circle');
                        recriarIcones();
                    }
                } else {
                    element.classList.remove('requirement-met');
                    element.classList.add('requirement-not-met');
                    const icon = element.querySelector('i');
                    if (icon) {
                        icon.setAttribute('data-lucide', 'circle');
                        recriarIcones();
                    }
                }
            }
        }
        
        function validarFormularioProfessor() {
            // Valida CPF
            const cpf = document.getElementById('cpfProfessor').value;
            if (cpf.length !== 14) {
                alert('CPF incompleto! Formato correto: 000.000.000-00');
                document.getElementById('cpfProfessor').focus();
                return false;
            }
            
            // Valida telefone
            const telefone = document.getElementById('telefoneProfessor').value;
            if (telefone.length < 14) {
                alert('Telefone incompleto! Formato correto: (00) 00000-0000');
                document.getElementById('telefoneProfessor').focus();
                return false;
            }
            
            // Valida senha
            const senha = document.getElementById('senhaProfessor').value;
            if (senha.length < 8) {
                alert('A senha deve ter pelo menos 8 caracteres!');
                document.getElementById('senhaProfessor').focus();
                return false;
            }
            
            // Verifica requisitos da senha
            const temUppercase = /[A-Z]/.test(senha);
            const temLowercase = /[a-z]/.test(senha);
            const temNumber = /[0-9]/.test(senha);
            const temSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(senha);
            
            if (!temUppercase || !temLowercase || !temNumber || !temSpecial) {
                alert('A senha deve conter pelo menos:\n- Uma letra maiúscula\n- Uma letra minúscula\n- Um número\n- Um caractere especial (@#$%&!)');
                document.getElementById('senhaProfessor').focus();
                return false;
            }
            
            return true;
        }
        
        function validarFormularioEditarProfessor() {
            // Valida telefone
            const telefone = document.getElementById('edit_prof_telefone').value;
            if (telefone.length < 14) {
                alert('Telefone incompleto! Formato correto: (00) 00000-0000');
                document.getElementById('edit_prof_telefone').focus();
                return false;
            }
            
            // Valida senha se for preenchida
            const senha = document.getElementById('edit_prof_senha').value;
            if (senha) {
                if (senha.length < 8) {
                    alert('A senha deve ter pelo menos 8 caracteres!');
                    document.getElementById('edit_prof_senha').focus();
                    return false;
                }
                
                // Verifica requisitos da senha
                const temUppercase = /[A-Z]/.test(senha);
                const temLowercase = /[a-z]/.test(senha);
                const temNumber = /[0-9]/.test(senha);
                const temSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(senha);
                
                if (!temUppercase || !temLowercase || !temNumber || !temSpecial) {
                    alert('A senha deve conter pelo menos:\n- Uma letra maiúscula\n- Uma letra minúscula\n- Um número\n- Um caractere especial (@#$%&!)');
                    document.getElementById('edit_prof_senha').focus();
                    return false;
                }
            }
            
            return true;
        }
        
        // Funções para Recepcionistas
        function aplicarMascaraCPFRecepcionista(input) {
            let value = input.value.replace(/\D/g, '');
            
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            
            // Aplica a máscara
            if (value.length > 3 && value.length <= 6) {
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
            } else if (value.length > 6 && value.length <= 9) {
                value = value.replace(/(\d{3})(\d{3})(\d)/, '$1.$2.$3');
            } else if (value.length > 9) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})(\d)/, '$1.$2.$3-$4');
            }
            
            input.value = value;
            
            // Atualiza feedback
            const feedback = document.getElementById('cpfFeedbackRecepcionista');
            if (feedback) {
                if (value.length === 14) {
                    feedback.textContent = "✓ CPF válido";
                    feedback.className = "text-xs mt-1 text-green-500";
                } else if (value.length > 0) {
                    feedback.textContent = `${value.length}/14 caracteres`;
                    feedback.className = "text-xs mt-1 text-yellow-500";
                } else {
                    feedback.textContent = "Formato: 000.000.000-00";
                    feedback.className = "text-xs mt-1 text-gray-500";
                }
            }
        }
        
        function aplicarMascaraTelefoneRecepcionista(input) {
            let value = input.value.replace(/\D/g, '');
            
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            
            // Aplica a máscara
            if (value.length > 0 && value.length <= 2) {
                value = value.replace(/(\d{0,2})/, '($1');
            } else if (value.length > 2 && value.length <= 7) {
                value = value.replace(/(\d{2})(\d)/, '($1) $2');
            } else if (value.length > 7) {
                value = value.replace(/(\d{2})(\d{5})(\d)/, '($1) $2-$3');
            }
            
            input.value = value;
            
            // Atualiza feedback
            const feedback = document.getElementById('telefoneFeedbackRecepcionista');
            if (feedback) {
                if (value.length === 15) {
                    feedback.textContent = "✓ Telefone válido";
                    feedback.className = "text-xs mt-1 text-green-500";
                } else if (value.length > 0) {
                    feedback.textContent = `${value.length}/15 caracteres`;
                    feedback.className = "text-xs mt-1 text-yellow-500";
                } else {
                    feedback.textContent = "Formato: (00) 00000-0000";
                    feedback.className = "text-xs mt-1 text-gray-500";
                }
            }
        }
        
        function aplicarMascaraTelefoneRecepcionistaEditar(input) {
            let value = input.value.replace(/\D/g, '');
            
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            
            // Aplica a máscara
            if (value.length > 0 && value.length <= 2) {
                value = value.replace(/(\d{0,2})/, '($1');
            } else if (value.length > 2 && value.length <= 7) {
                value = value.replace(/(\d{2})(\d)/, '($1) $2');
            } else if (value.length > 7) {
                value = value.replace(/(\d{2})(\d{5})(\d)/, '($1) $2-$3');
            }
            
            input.value = value;
            
            // Atualiza feedback
            const feedback = document.getElementById('editTelefoneFeedbackRecepcionista');
            if (feedback) {
                if (value.length === 15) {
                    feedback.textContent = "✓ Telefone válido";
                    feedback.className = "text-xs mt-1 text-green-500";
                } else if (value.length > 0) {
                    feedback.textContent = `${value.length}/15 caracteres`;
                    feedback.className = "text-xs mt-1 text-yellow-500";
                } else {
                    feedback.textContent = "Formato: (00) 00000-0000";
                    feedback.className = "text-xs mt-1 text-gray-500";
                }
            }
        }
        
        function validarSenhaRecepcionista(senha) {
            // Verifica requisitos
            const temMinLength = senha.length >= 8;
            const temUppercase = /[A-Z]/.test(senha);
            const temLowercase = /[a-z]/.test(senha);
            const temNumber = /[0-9]/.test(senha);
            const temSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(senha);
            
            // Atualiza ícones dos requisitos
            atualizarRequisitoRecepcionista('reqLengthRecepcionista', temMinLength);
            atualizarRequisitoRecepcionista('reqUppercaseRecepcionista', temUppercase);
            atualizarRequisitoRecepcionista('reqLowercaseRecepcionista', temLowercase);
            atualizarRequisitoRecepcionista('reqNumberRecepcionista', temNumber);
            atualizarRequisitoRecepcionista('reqSpecialRecepcionista', temSpecial);
            
            // Calcula força da senha
            let strength = 0;
            if (temMinLength) strength++;
            if (temUppercase) strength++;
            if (temLowercase) strength++;
            if (temNumber) strength++;
            if (temSpecial) strength++;
            
            // Atualiza medidor visual
            const meter = document.getElementById('passwordStrengthMeterRecepcionista');
            if (meter) {
                meter.className = 'password-strength-meter mt-2 password-strength-' + strength;
                const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500', 'bg-emerald-500'];
                meter.className += ' ' + (colors[strength - 1] || 'bg-red-500');
            }
        }
        
        function validarSenhaRecepcionistaEditar(senha) {
            if (!senha) {
                // Se a senha estiver vazia, esconder validações
                const meter = document.getElementById('editPasswordStrengthMeterRecepcionista');
                if (meter) meter.classList.add('hidden');
                
                // Esconder todos os requisitos
                ['editReqLengthRecepcionista', 'editReqUppercaseRecepcionista', 'editReqLowercaseRecepcionista', 'editReqNumberRecepcionista', 'editReqSpecialRecepcionista'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.classList.add('hidden');
                });
                return;
            }
            
            // Mostrar validações
            const meter = document.getElementById('editPasswordStrengthMeterRecepcionista');
            if (meter) meter.classList.remove('hidden');
            
            // Mostrar todos os requisitos
            ['editReqLengthRecepcionista', 'editReqUppercaseRecepcionista', 'editReqLowercaseRecepcionista', 'editReqNumberRecepcionista', 'editReqSpecialRecepcionista'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.classList.remove('hidden');
            });
            
            // Verifica requisitos
            const temMinLength = senha.length >= 8;
            const temUppercase = /[A-Z]/.test(senha);
            const temLowercase = /[a-z]/.test(senha);
            const temNumber = /[0-9]/.test(senha);
            const temSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(senha);
            
            // Atualiza ícones dos requisitos
            atualizarRequisitoRecepcionista('editReqLengthRecepcionista', temMinLength);
            atualizarRequisitoRecepcionista('editReqUppercaseRecepcionista', temUppercase);
            atualizarRequisitoRecepcionista('editReqLowercaseRecepcionista', temLowercase);
            atualizarRequisitoRecepcionista('editReqNumberRecepcionista', temNumber);
            atualizarRequisitoRecepcionista('editReqSpecialRecepcionista', temSpecial);
            
            // Calcula força da senha
            let strength = 0;
            if (temMinLength) strength++;
            if (temUppercase) strength++;
            if (temLowercase) strength++;
            if (temNumber) strength++;
            if (temSpecial) strength++;
            
            // Atualiza medidor visual
            if (meter) {
                meter.className = 'password-strength-meter mt-2 password-strength-' + strength;
                const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500', 'bg-emerald-500'];
                meter.className += ' ' + (colors[strength - 1] || 'bg-red-500');
            }
        }
        
        function atualizarRequisitoRecepcionista(id, atendido) {
            const element = document.getElementById(id);
            if (element) {
                if (atendido) {
                    element.classList.remove('requirement-not-met');
                    element.classList.add('requirement-met');
                    const icon = element.querySelector('i');
                    if (icon) {
                        icon.setAttribute('data-lucide', 'check-circle');
                        recriarIcones();
                    }
                } else {
                    element.classList.remove('requirement-met');
                    element.classList.add('requirement-not-met');
                    const icon = element.querySelector('i');
                    if (icon) {
                        icon.setAttribute('data-lucide', 'circle');
                        recriarIcones();
                    }
                }
            }
        }
        
        function validarFormularioRecepcionista() {
            // Valida CPF
            const cpf = document.getElementById('cpfRecepcionista').value;
            if (cpf.length !== 14) {
                alert('CPF incompleto! Formato correto: 000.000.000-00');
                document.getElementById('cpfRecepcionista').focus();
                return false;
            }
            
            // Valida telefone
            const telefone = document.getElementById('telefoneRecepcionista').value;
            if (telefone.length < 14) {
                alert('Telefone incompleto! Formato correto: (00) 00000-0000');
                document.getElementById('telefoneRecepcionista').focus();
                return false;
            }
            
            // Valida senha
            const senha = document.getElementById('senhaRecepcionista').value;
            if (senha.length < 8) {
                alert('A senha deve ter pelo menos 8 caracteres!');
                document.getElementById('senhaRecepcionista').focus();
                return false;
            }
            
            // Verifica requisitos da senha
            const temUppercase = /[A-Z]/.test(senha);
            const temLowercase = /[a-z]/.test(senha);
            const temNumber = /[0-9]/.test(senha);
            const temSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(senha);
            
            if (!temUppercase || !temLowercase || !temNumber || !temSpecial) {
                alert('A senha deve conter pelo menos:\n- Uma letra maiúscula\n- Uma letra minúscula\n- Um número\n- Um caractere especial (@#$%&!)');
                document.getElementById('senhaRecepcionista').focus();
                return false;
            }
            
            return true;
        }
        
        function validarFormularioEditarRecepcionista() {
            // Valida telefone
            const telefone = document.getElementById('edit_rec_telefone').value;
            if (telefone.length < 14) {
                alert('Telefone incompleto! Formato correto: (00) 00000-0000');
                document.getElementById('edit_rec_telefone').focus();
                return false;
            }
            
            // Valida senha se for preenchida
            const senha = document.getElementById('edit_rec_senha').value;
            if (senha) {
                if (senha.length < 8) {
                    alert('A senha deve ter pelo menos 8 caracteres!');
                    document.getElementById('edit_rec_senha').focus();
                    return false;
                }
                
                // Verifica requisitos da senha
                const temUppercase = /[A-Z]/.test(senha);
                const temLowercase = /[a-z]/.test(senha);
                const temNumber = /[0-9]/.test(senha);
                const temSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(senha);
                
                if (!temUppercase || !temLowercase || !temNumber || !temSpecial) {
                    alert('A senha deve conter pelo menos:\n- Uma letra maiúscula\n- Uma letra minúscula\n- Um número\n- Um caractere especial (@#$%&!)');
                    document.getElementById('edit_rec_senha').focus();
                    return false;
                }
            }
            
            return true;
        }
        
        // Funções para abrir modais de edição específicos
        function abrirModalEditarProfessor(prof) { 
            document.getElementById('edit_prof_id').value = prof.id; 
            document.getElementById('edit_prof_nome').value = prof.nome; 
            document.getElementById('edit_prof_cpf').value = prof.cpf; 
            document.getElementById('edit_prof_email').value = prof.email; 
            
            // Formatar telefone para máscara
            let telefoneFormatado = prof.telefone;
            if (telefoneFormatado && !telefoneFormatado.includes('(')) {
                // Se não tem máscara, aplicar
                telefoneFormatado = aplicarMascaraTelefoneString(telefoneFormatado);
            }
            document.getElementById('edit_prof_telefone').value = telefoneFormatado;
            
            document.getElementById('edit_prof_data_nascimento').value = prof.data_nascimento; 
            document.getElementById('edit_prof_cref').value = prof.cref; 
            document.getElementById('edit_prof_especialidade').value = prof.especialidade; 
            document.getElementById('modalEditarProfessor').classList.remove('hidden'); 
            recriarIcones();
            // Limpar senha e validações
            document.getElementById('edit_prof_senha').value = '';
            validarSenhaProfessorEditar('');
        }

        function abrirModalEditarRecepcionista(rec) { 
            document.getElementById('edit_rec_id').value = rec.id; 
            document.getElementById('edit_rec_nome').value = rec.nome; 
            document.getElementById('edit_rec_cpf').value = rec.cpf; 
            document.getElementById('edit_rec_email').value = rec.email; 
            
            // Formatar telefone para máscara
            let telefoneFormatado = rec.telefone;
            if (telefoneFormatado && !telefoneFormatado.includes('(')) {
                // Se não tem máscara, aplicar
                telefoneFormatado = aplicarMascaraTelefoneString(telefoneFormatado);
            }
            document.getElementById('edit_rec_telefone').value = telefoneFormatado;
            
            document.getElementById('edit_rec_data_nascimento').value = rec.data_nascimento; 
            document.getElementById('modalEditarRecepcionista').classList.remove('hidden'); 
            recriarIcones();
            // Limpar senha e validações
            document.getElementById('edit_rec_senha').value = '';
            validarSenhaRecepcionistaEditar('');
        }
        
        function fecharModalEditarProfessor() {
            document.getElementById('modalEditarProfessor').classList.add('hidden');
        }
        
        function fecharModalEditarRecepcionista() {
            document.getElementById('modalEditarRecepcionista').classList.add('hidden');
        }
        
        function fecharModalProfessor() { 
            document.getElementById('modalProfessor').classList.add('hidden'); 
        }
        
        function fecharModalRecepcionista() { 
            document.getElementById('modalRecepcionista').classList.add('hidden'); 
        }
        
        function abrirModalProfessor() { 
            document.getElementById('modalProfessor').classList.remove('hidden'); 
            recriarIcones();
        }

        function abrirModalRecepcionista() { 
            document.getElementById('modalRecepcionista').classList.remove('hidden'); 
            recriarIcones();
        }
    </script>
</body>
</html>