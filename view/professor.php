<?php
session_start();
require_once __DIR__ . '/../Model/AlunoDAO.php';
require_once __DIR__ . '/../Model/TreinoDAO.php';
require_once __DIR__ . '/../Model/ProfessorDAO.php';

// SEGURANÇA - Verifica se é professor logado
if (!isset($_SESSION['professor_id'])) { 
    header('Location: ../index.php'); 
    exit; 
}

$dao = new AlunoDAO();
$treinoDao = new TreinoDAO();
$professorDao = new ProfessorDAO();

// Carrega dados do professor logado
$professor = $professorDao->buscarPorId($_SESSION['professor_id']);
if (!$professor) {
    session_destroy();
    header('Location: ../index.php');
    exit;
}

// Atualizar informações da sessão
if (!isset($_SESSION['professor_cref']) && isset($professor['cref'])) {
    $_SESSION['professor_cref'] = $professor['cref'];
    $_SESSION['professor_especialidade'] = $professor['especialidade'];
    $_SESSION['professor_email'] = $professor['email'];
    $_SESSION['tipo'] = 'professor';
}

$msg = ''; $tipoMsg = '';

// Determinar qual aba mostrar
$aba = $_GET['aba'] ?? 'home';
$alunoIdSelecionado = $_GET['aluno_id'] ?? null;
$modeloId = $_GET['modelo'] ?? null;

// AJAX (buscar treino e modelo)
if (isset($_GET['acao_ajax'])) {
    header('Content-Type: application/json');
    if ($_GET['acao_ajax'] === 'buscar_treino') {
        echo json_encode($treinoDao->buscarPorAluno($_GET['id']));
    }
    if ($_GET['acao_ajax'] === 'buscar_modelo') {
        echo json_encode($treinoDao->buscarModeloPorId($_GET['id']));
    }
    exit;
}

// POST (Salvar Treino)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao']) && $_POST['acao'] === 'salvar_treino') {
        $alunoId = $_POST['aluno_id_treino'] ?? null;
        $treinoData = $_POST['treino'] ?? [];
        
        if ($alunoId && !empty($treinoData)) {
            if ($treinoDao->salvarTreino($alunoId, $treinoData)) {
                $msg = "Treino atualizado com sucesso!";
                $tipoMsg = 'sucesso';
                $aba = 'treinos';
            } else {
                $msg = "Erro ao salvar o treino.";
                $tipoMsg = 'erro';
                $aba = 'treinos';
            }
        } else {
            $msg = "Selecione um aluno e adicione exercícios.";
            $tipoMsg = 'erro';
            $aba = 'treinos';
        }
    }
    
    // Salvar Modelo
    if (isset($_POST['acao']) && $_POST['acao'] === 'salvar_modelo') {
        $nomeModelo = $_POST['nome_modelo'] ?? '';
        $treinoData = $_POST['treino'] ?? [];
        
        if (!empty($nomeModelo) && !empty($treinoData)) {
            if ($treinoDao->salvarModelo($nomeModelo, $treinoData)) {
                $msg = "Modelo salvo na biblioteca!";
                $tipoMsg = 'sucesso';
                $aba = 'biblioteca';
            } else {
                $msg = "Erro ao salvar modelo.";
                $tipoMsg = 'erro';
                $aba = 'treinos';
            }
        }
    }
    
    // Excluir Modelo
    if (isset($_POST['excluir_modelo'])) {
        $modeloId = $_POST['modelo_id'] ?? null;
        if ($modeloId && $treinoDao->excluirModelo($modeloId)) {
            $msg = "Modelo excluído com sucesso!";
            $tipoMsg = 'sucesso';
            $aba = 'biblioteca';
        }
    }
    
    // Logout
    if (isset($_POST['logout'])) {
        session_destroy();
        header('Location: ../index.php');
        exit;
    }
}

// Dados para as abas
$listaAlunos = $dao->buscarRecentes(100);
$listaModelos = $treinoDao->listarModelos();

// Estatísticas para Home
$totalAlunos = $dao->contarTotal();
$alunosAtivos = $dao->contarPorStatus('Ativo');
$ultimosAlunos = $dao->buscarRecentes(5);

// Dados para aba Alunos
$termoBusca = $_GET['busca'] ?? '';
$statusFiltro = $_GET['status'] ?? '';

if ($termoBusca) {
    $alunosFiltrados = $dao->pesquisar($termoBusca);
} else {
    $alunosFiltrados = $dao->buscarRecentes(50);
}

// Filtrar por status se especificado
if ($statusFiltro && $statusFiltro !== 'todos') {
    $alunosFiltrados = array_filter($alunosFiltrados, function($aluno) use ($statusFiltro) {
        return $aluno['status'] == $statusFiltro;
    });
}

// Dados para Agenda (simulados)
$agendaHoje = [
    ['hora' => '08:00', 'aluno' => 'Carlos Silva', 'tipo' => 'Personal', 'status' => 'confirmado'],
    ['hora' => '10:00', 'aluno' => 'Maria Santos', 'tipo' => 'Avaliação', 'status' => 'confirmado'],
    ['hora' => '14:00', 'aluno' => 'João Pereira', 'tipo' => 'Treino', 'status' => 'pendente'],
    ['hora' => '16:00', 'aluno' => 'Ana Oliveira', 'tipo' => 'Personal', 'status' => 'confirmado'],
    ['hora' => '18:00', 'aluno' => 'Pedro Costa', 'tipo' => 'Treino', 'status' => 'cancelado'],
];

// Dados para Relatórios (simulados)
$relatorioMensal = [
    'total_aulas' => 42,
    'alunos_atendidos' => 28,
    'faturamento' => 12500,
    'treinos_prescritos' => 67,
    'evolucao' => '+18%',
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icons/halter.png">
    <title>Professor - TechFit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        tech: {
                            900: '#0B0F19', 
                            800: '#151b2b', 
                            700: '#374151', 
                            primary: '#ea580c', 
                            primaryHover: '#c2410c',
                            text: '#f3f4f6', 
                            muted: '#9ca3af' 
                        },
                        prof: {
                            primary: '#3b82f6',
                            dark: '#1e40af',
                            light: '#60a5fa'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out forwards',
                        'slide-up': 'slideUp 0.5s ease-out forwards',
                        'slide-in-right': 'slideInRight 0.3s ease-out',
                        'slide-out-left': 'slideOutLeft 0.3s ease-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        slideInRight: {
                            '0%': { opacity: '0', transform: 'translateX(-20px)' },
                            '100%': { opacity: '1', transform: 'translateX(0)' },
                        },
                        slideOutLeft: {
                            '0%': { opacity: '1', transform: 'translateX(0)' },
                            '100%': { opacity: '0', transform: 'translateX(-20px)' },
                        }
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0B0F19;
            color: #f3f4f6;
            overflow-x: hidden;
        }

        .tech-input {
            background-color: #151b2b;
            border: 1px solid #374151;
            color: white;
            transition: all 0.3s ease;
        }
        .tech-input:focus {
            border-color: #ea580c;
            box-shadow: 0 0 0 2px rgba(234, 88, 12, 0.2);
            outline: none;
        }

        /* Estilos para a sidebar */
        .sidebar {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            width: 260px;
            flex-shrink: 0;
        }
        
        .sidebar-collapsed {
            width: 80px;
        }
        
        .main-content {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            flex-grow: 1;
        }
        
        .sidebar-collapsed + .main-content {
            margin-left: 0;
        }
        
        /* Estilos para mensagens */
        .toast-success {
            background-color: #0B0F19;
            border-left: 4px solid #10b981;
            color: white;
        }
        
        .toast-error {
            background-color: #0B0F19;
            border-left: 4px solid #ef4444;
            color: white;
        }
        
        /* Estilo para cards */
        .tech-card {
            background: linear-gradient(135deg, #151b2b 0%, #0B0F19 100%);
            border: 1px solid #374151;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .tech-card:hover {
            border-color: #ea580c;
            box-shadow: 0 10px 25px -5px rgba(234, 88, 12, 0.1);
        }
        
        /* Scrollbar custom */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #0B0F19;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #374151;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #ea580c;
        }
        
        /* Animações de transição entre abas */
        .tab-transition {
            animation: slideInRight 0.3s ease-out;
        }
        
        /* Status colors */
        .status-confirmado {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border-color: #10b981;
        }
        
        .status-pendente {
            background-color: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
            border-color: #f59e0b;
        }
        
        .status-cancelado {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border-color: #ef4444;
        }
        
        /* Loading spinner */
        .loader {
            border: 3px solid rgba(255, 255, 255, 0.1);
            border-left-color: #ffffff;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="min-h-screen flex">
    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar h-screen bg-tech-900 border-r border-tech-800 flex flex-col fixed left-0 top-0 z-40">
        <!-- Logo -->
        <div class="p-6 border-b border-tech-800">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-tech-primary p-2 rounded-lg">
                        <i data-lucide="graduation-cap" class="w-6 h-6 text-white"></i>
                    </div>
                    <span id="logo-text" class="font-black text-xl tracking-tight text-white">
                        TECH<span class="text-tech-primary">FIT</span>
                    </span>
                </div>
                <button id="toggle-sidebar" class="text-tech-muted hover:text-white p-1">
                    <i id="sidebar-icon" data-lucide="chevron-left" class="w-5 h-5"></i>
                </button>
            </div>
        </div>
        
        <!-- Perfil Professor -->
        <div class="p-4 border-b border-tech-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-tech-primary to-orange-600 rounded-full flex items-center justify-center">
                    <span class="font-bold text-white text-sm"><?php echo substr($professor['nome'], 0, 1); ?></span>
                </div>
                <div id="profile-info" class="flex-1">
                    <p class="text-sm font-bold text-white truncate"><?= htmlspecialchars($professor['nome']) ?></p>
                    <span class="text-xs text-tech-primary"><?= htmlspecialchars($professor['cref']) ?></span>
                </div>
            </div>
            <div id="specialty-info" class="mt-2">
                <span class="text-xs bg-tech-primary/20 text-tech-primary px-2 py-1 rounded-full">
                    <?= htmlspecialchars($professor['especialidade']) ?>
                </span>
            </div>
        </div>
        
        <!-- Menu de Navegação -->
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
            <a href="?aba=home" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg <?= $aba == 'home' ? 'bg-tech-primary/10 text-tech-primary' : 'text-tech-muted hover:bg-tech-800 hover:text-white' ?> transition-all">
                <i data-lucide="home" class="w-5 h-5"></i>
                <span id="menu-home" class="whitespace-nowrap">Home</span>
            </a>
            
            <a href="?aba=treinos" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg <?= $aba == 'treinos' ? 'bg-tech-primary/10 text-tech-primary' : 'text-tech-muted hover:bg-tech-800 hover:text-white' ?> transition-all">
                <i data-lucide="biceps-flexed" class="w-5 h-5"></i>
                <span id="menu-treinos" class="whitespace-nowrap">Prescrever Treino</span>
            </a>
            
            <a href="?aba=alunos" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg <?= $aba == 'alunos' ? 'bg-tech-primary/10 text-tech-primary' : 'text-tech-muted hover:bg-tech-800 hover:text-white' ?> transition-all">
                <i data-lucide="users" class="w-5 h-5"></i>
                <span id="menu-alunos" class="whitespace-nowrap">Meus Alunos</span>
            </a>
            
            <a href="?aba=biblioteca" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg <?= $aba == 'biblioteca' ? 'bg-tech-primary/10 text-tech-primary' : 'text-tech-muted hover:bg-tech-800 hover:text-white' ?> transition-all">
                <i data-lucide="book-open" class="w-5 h-5"></i>
                <span id="menu-biblioteca" class="whitespace-nowrap">Biblioteca</span>
            </a>
            
            <a href="?aba=agenda" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg <?= $aba == 'agenda' ? 'bg-tech-primary/10 text-tech-primary' : 'text-tech-muted hover:bg-tech-800 hover:text-white' ?> transition-all">
                <i data-lucide="calendar" class="w-5 h-5"></i>
                <span id="menu-agenda" class="whitespace-nowrap">Agenda</span>
            </a>
            
            <a href="?aba=relatorios" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg <?= $aba == 'relatorios' ? 'bg-tech-primary/10 text-tech-primary' : 'text-tech-muted hover:bg-tech-800 hover:text-white' ?> transition-all">
                <i data-lucide="bar-chart" class="w-5 h-5"></i>
                <span id="menu-relatorios" class="whitespace-nowrap">Relatórios</span>
            </a>
        </nav>
        
        <!-- Logout -->
        <div class="p-4 border-t border-tech-800">
            <form method="POST" class="w-full">
                <input type="hidden" name="logout" value="true">
                <button type="submit" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-red-400 hover:bg-red-500/10 rounded-lg w-full transition-all">
                    <i data-lucide="log-out" class="w-5 h-5"></i>
                    <span id="menu-logout" class="whitespace-nowrap">Sair</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Conteúdo Principal -->
    <main id="main-content" class="main-content h-screen overflow-y-auto transition-all duration-300 ml-[260px]">
        <!-- Top Bar -->
        <div class="sticky top-0 z-30 bg-tech-900/90 backdrop-blur-md border-b border-tech-800 px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button id="mobile-toggle" class="md:hidden text-tech-muted hover:text-white">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                    <div>
                        <h1 class="text-xl font-bold text-white flex items-center gap-2">
                            <?php if($aba == 'home'): ?>
                                <i data-lucide="home" class="w-5 h-5 text-tech-primary"></i>
                                Dashboard do Professor
                            <?php elseif($aba == 'treinos'): ?>
                                <i data-lucide="biceps-flexed" class="w-5 h-5 text-tech-primary"></i>
                                Prescrever Treino
                            <?php elseif($aba == 'alunos'): ?>
                                <i data-lucide="users" class="w-5 h-5 text-tech-primary"></i>
                                Meus Alunos
                            <?php elseif($aba == 'biblioteca'): ?>
                                <i data-lucide="book-open" class="w-5 h-5 text-tech-primary"></i>
                                Biblioteca de Treinos
                            <?php elseif($aba == 'agenda'): ?>
                                <i data-lucide="calendar" class="w-5 h-5 text-tech-primary"></i>
                                Minha Agenda
                            <?php elseif($aba == 'relatorios'): ?>
                                <i data-lucide="bar-chart" class="w-5 h-5 text-tech-primary"></i>
                                Relatórios
                            <?php endif; ?>
                        </h1>
                        <p class="text-sm text-tech-muted">
                            <?php if($aba == 'home'): ?>
                                Bem-vindo, <?= htmlspecialchars($professor['nome']) ?>!
                            <?php elseif($aba == 'treinos'): ?>
                                Crie ou edite treinos personalizados
                            <?php elseif($aba == 'alunos'): ?>
                                Gerencie e acompanhe seus alunos
                            <?php elseif($aba == 'biblioteca'): ?>
                                Modelos de treino pré-configurados
                            <?php elseif($aba == 'agenda'): ?>
                                Gerencie suas aulas e compromissos
                            <?php elseif($aba == 'relatorios'): ?>
                                Visualize suas métricas e desempenho
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="hidden md:flex items-center gap-2 bg-tech-800 px-3 py-2 rounded-lg">
                        <i data-lucide="users" class="w-4 h-4 text-tech-primary"></i>
                        <span class="text-sm text-white"><?= $alunosAtivos ?> alunos ativos</span>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <div class="text-right hidden md:block">
                            <p class="text-sm font-bold text-white"><?= htmlspecialchars($professor['nome']) ?></p>
                            <span class="text-xs bg-tech-primary/20 text-tech-primary px-2 py-1 rounded-full">
                                <?= htmlspecialchars($professor['especialidade']) ?>
                            </span>
                        </div>
                        <div class="w-8 h-8 bg-gradient-to-br from-tech-primary to-orange-600 rounded-full flex items-center justify-center cursor-pointer">
                            <span class="font-bold text-white text-sm"><?php echo substr($professor['nome'], 0, 1); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conteúdo das Abas -->
        <div class="p-6 md:p-8 tab-transition">
            <?php if ($msg): ?>
                <div class="mb-6 animate-fade-in">
                    <div class="tech-card p-4 flex items-center gap-3 <?= $tipoMsg == 'sucesso' ? 'border-l-4 border-green-500' : 'border-l-4 border-red-500' ?>">
                        <i data-lucide="<?= $tipoMsg == 'sucesso' ? 'check-circle' : 'alert-circle' ?>" class="w-5 h-5 <?= $tipoMsg == 'sucesso' ? 'text-green-500' : 'text-red-500' ?>"></i>
                        <span><?= htmlspecialchars($msg) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ABA: HOME -->
            <div id="tab-home" class="<?= $aba == 'home' ? 'block' : 'hidden' ?>">
                <!-- Estatísticas -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="tech-card p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-tech-muted">Total de Alunos</p>
                                <h3 class="text-3xl font-bold text-white mt-2"><?= $totalAlunos ?></h3>
                            </div>
                            <div class="bg-tech-primary/20 p-3 rounded-xl">
                                <i data-lucide="users" class="w-6 h-6 text-tech-primary"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tech-card p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-tech-muted">Alunos Ativos</p>
                                <h3 class="text-3xl font-bold text-white mt-2"><?= $alunosAtivos ?></h3>
                            </div>
                            <div class="bg-green-500/20 p-3 rounded-xl">
                                <i data-lucide="user-check" class="w-6 h-6 text-green-500"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tech-card p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-tech-muted">Treinos Hoje</p>
                                <h3 class="text-3xl font-bold text-white mt-2">8</h3>
                            </div>
                            <div class="bg-blue-500/20 p-3 rounded-xl">
                                <i data-lucide="dumbbell" class="w-6 h-6 text-blue-500"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tech-card p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-tech-muted">Faturamento</p>
                                <h3 class="text-3xl font-bold text-white mt-2">R$ 12.5k</h3>
                            </div>
                            <div class="bg-purple-500/20 p-3 rounded-xl">
                                <i data-lucide="dollar-sign" class="w-6 h-6 text-purple-500"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Últimos Alunos e Ações Rápidas -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Últimos Alunos -->
                    <div class="tech-card p-6">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                    <i data-lucide="users" class="w-5 h-5 text-tech-primary"></i> 
                                    Últimos Alunos
                                </h3>
                                <p class="text-sm text-tech-muted mt-1">Alunos mais recentes no sistema</p>
                            </div>
                            <a href="?aba=alunos" class="text-sm font-bold text-tech-primary hover:text-white border border-tech-primary/30 px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                                Ver Todos
                            </a>
                        </div>

                        <div class="space-y-3">
                            <?php foreach($ultimosAlunos as $aluno): ?>
                            <div class="flex items-center justify-between p-3 rounded-lg bg-tech-800 hover:bg-tech-700 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-gradient-to-br from-tech-primary to-orange-600 rounded-full flex items-center justify-center">
                                        <span class="font-bold text-white text-xs"><?php echo substr($aluno['nome'], 0, 1); ?></span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-white text-sm"><?= htmlspecialchars($aluno['nome']) ?></p>
                                        <p class="text-xs text-tech-muted"><?= htmlspecialchars($aluno['email']) ?></p>
                                    </div>
                                </div>
                                <a href="?aba=treinos&aluno_id=<?= $aluno['id'] ?>" 
                                   class="text-tech-primary hover:text-white p-2 hover:bg-tech-primary/10 rounded transition-colors"
                                   title="Prescrever Treino">
                                    <i data-lucide="dumbbell" class="w-4 h-4"></i>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Ações Rápidas -->
                    <div class="tech-card p-6">
                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <i data-lucide="zap" class="w-5 h-5 text-tech-primary"></i>
                            Ações Rápidas
                        </h3>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <a href="?aba=treinos" class="flex flex-col items-center justify-center p-4 rounded-lg bg-tech-800 hover:bg-tech-700 transition-colors group">
                                <i data-lucide="biceps-flexed" class="w-6 h-6 text-tech-primary mb-2 group-hover:scale-110 transition-transform"></i>
                                <span class="text-sm font-medium">Prescrever Treino</span>
                            </a>
                            
                            <a href="?aba=agenda" class="flex flex-col items-center justify-center p-4 rounded-lg bg-tech-800 hover:bg-tech-700 transition-colors group">
                                <i data-lucide="calendar" class="w-6 h-6 text-blue-500 mb-2 group-hover:scale-110 transition-transform"></i>
                                <span class="text-sm font-medium">Agendar Aula</span>
                            </a>
                            
                            <a href="?aba=biblioteca" class="flex flex-col items-center justify-center p-4 rounded-lg bg-tech-800 hover:bg-tech-700 transition-colors group">
                                <i data-lucide="book-open" class="w-6 h-6 text-purple-500 mb-2 group-hover:scale-110 transition-transform"></i>
                                <span class="text-sm font-medium">Biblioteca</span>
                            </a>
                            
                            <a href="?aba=alunos" class="flex flex-col items-center justify-center p-4 rounded-lg bg-tech-800 hover:bg-tech-700 transition-colors group">
                                <i data-lucide="users" class="w-6 h-6 text-green-500 mb-2 group-hover:scale-110 transition-transform"></i>
                                <span class="text-sm font-medium">Ver Alunos</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Agenda de Hoje -->
                <div class="tech-card p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                <i data-lucide="calendar" class="w-5 h-5 text-tech-primary"></i> 
                                Agenda de Hoje
                            </h3>
                            <p class="text-sm text-tech-muted mt-1"><?= date('d/m/Y') ?></p>
                        </div>
                        <a href="?aba=agenda" class="text-sm font-bold text-tech-primary hover:text-white border border-tech-primary/30 px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                            Ver Agenda Completa
                        </a>
                    </div>

                    <div class="space-y-3">
                        <?php foreach($agendaHoje as $compromisso): ?>
                        <div class="flex items-center justify-between p-3 rounded-lg bg-tech-800">
                            <div class="flex items-center gap-4">
                                <div class="text-center">
                                    <p class="text-lg font-bold text-white"><?= $compromisso['hora'] ?></p>
                                </div>
                                <div>
                                    <p class="font-medium text-white"><?= $compromisso['aluno'] ?></p>
                                    <p class="text-sm text-tech-muted"><?= $compromisso['tipo'] ?></p>
                                </div>
                            </div>
                            <span class="px-3 py-1 text-xs rounded-full border <?= 'status-' . $compromisso['status'] ?>">
                                <?= ucfirst($compromisso['status']) ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- ABA: TREINOS -->
            <div id="tab-treinos" class="<?= $aba == 'treinos' ? 'block' : 'hidden' ?>">
                <div class="tech-card p-6 mb-6">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                                <i data-lucide="biceps-flexed" class="w-6 h-6 text-tech-primary"></i> 
                                Prescrever Treino
                            </h2>
                            <p class="text-sm text-tech-muted mt-1">Crie ou edite treinos personalizados para seus alunos</p>
                        </div>
                        <div class="flex gap-3">
                            <button onclick="gerarTreinoAutomatico()" 
                                    class="text-sm font-bold text-purple-400 hover:text-white border border-purple-500/30 px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                                <i data-lucide="zap" class="w-4 h-4"></i>
                                Gerar Modelo
                            </button>
                            <button onclick="carregarDaBiblioteca()" 
                                    class="text-sm font-bold text-tech-primary hover:text-white border border-tech-primary/30 px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                                <i data-lucide="book-open" class="w-4 h-4"></i>
                                Biblioteca
                            </button>
                        </div>
                    </div>

                    <form method="POST" id="formTreino">
                        <input type="hidden" name="acao" value="salvar_treino">
                        
                        <div class="mb-8">
                            <label class="block text-sm font-medium text-tech-muted mb-2">Selecione o Aluno</label>
                            <div class="flex flex-col md:flex-row gap-4">
                                <div class="flex-1 relative">
                                    <i data-lucide="search" class="absolute left-4 top-1/2 transform -translate-y-1/2 w-4 h-4 text-tech-muted"></i>
                                    <select id="selectAluno" name="aluno_id_treino" onchange="carregarTreino(this.value)" 
                                            class="w-full pl-10 tech-input rounded-lg p-4 appearance-none">
                                        <option value="">-- Selecione um Aluno --</option>
                                        <?php foreach($listaAlunos as $a): ?>
                                            <option value="<?= $a['id'] ?>" <?= $alunoIdSelecionado == $a['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($a['nome']) ?> 
                                                <span class="text-tech-muted text-sm">(<?= htmlspecialchars($a['plano'] ?? 'Sem plano') ?>)</span>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" 
                                        class="bg-gradient-to-r from-green-600 to-emerald-700 hover:from-green-700 hover:to-emerald-800 text-white px-6 py-4 rounded-lg font-bold flex gap-2 items-center shadow-lg transition-all">
                                    <i data-lucide="save" class="w-5 h-5"></i> 
                                    Salvar Treino
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                            <?php foreach(['A','B','C'] as $div): ?>
                            <div class="tech-card flex flex-col h-full">
                                <div class="p-4 border-b border-tech-800 flex justify-between items-center">
                                    <h3 class="font-bold text-white flex items-center gap-2">
                                        <i data-lucide="dumbbell" class="w-4 h-4 text-tech-primary"></i>
                                        Treino <?= $div ?>
                                    </h3>
                                    <button type="button" onclick="addExercicio('container-<?= $div ?>')" 
                                            class="text-tech-primary hover:text-white text-xs font-bold flex items-center gap-1">
                                        <i data-lucide="plus" class="w-3 h-3"></i>
                                        Adicionar
                                    </button>
                                </div>
                                <div id="container-<?= $div ?>" class="p-4 space-y-3 min-h-[400px] flex-1">
                                    <div class="text-center py-8 text-tech-muted">
                                        <i data-lucide="dumbbell" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                                        <p>Clique em "Adicionar" para começar</p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Salvar como Modelo -->
                        <div class="tech-card p-4">
                            <h4 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
                                <i data-lucide="bookmark" class="w-5 h-5 text-purple-500"></i>
                                Salvar na Biblioteca
                            </h4>
                            <div class="flex gap-3">
                                <input type="text" name="nome_modelo" placeholder="Nome do modelo (ex: Hipertrofia Iniciante)" 
                                       class="flex-1 tech-input rounded-lg px-4 py-3 focus:border-purple-500">
                                <button type="button" onclick="salvarComoModelo()" 
                                        class="bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white px-6 py-3 rounded-lg font-bold flex gap-2 items-center transition-all">
                                    <i data-lucide="save" class="w-5 h-5"></i>
                                    Salvar Modelo
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ABA: ALUNOS -->
            <div id="tab-alunos" class="<?= $aba == 'alunos' ? 'block' : 'hidden' ?>">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                        <i data-lucide="users" class="w-6 h-6 text-tech-primary"></i> 
                        Meus Alunos
                    </h2>
                    <p class="text-sm text-tech-muted mt-1">Gerencie e acompanhe todos os alunos</p>
                </div>

                <!-- Estatísticas -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="tech-card p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-tech-muted">Total</p>
                                <h3 class="text-3xl font-bold text-white mt-2"><?= $totalAlunos ?></h3>
                            </div>
                            <div class="bg-blue-500/20 p-3 rounded-xl">
                                <i data-lucide="users" class="w-6 h-6 text-blue-500"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tech-card p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-tech-muted">Ativos</p>
                                <h3 class="text-3xl font-bold text-white mt-2"><?= $alunosAtivos ?></h3>
                            </div>
                            <div class="bg-green-500/20 p-3 rounded-xl">
                                <i data-lucide="user-check" class="w-6 h-6 text-green-500"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tech-card p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-tech-muted">Inativos</p>
                                <h3 class="text-3xl font-bold text-white mt-2"><?= $totalAlunos - $alunosAtivos ?></h3>
                            </div>
                            <div class="bg-red-500/20 p-3 rounded-xl">
                                <i data-lucide="user-x" class="w-6 h-6 text-red-500"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tech-card p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-tech-muted">Presenças Hoje</p>
                                <h3 class="text-3xl font-bold text-white mt-2"><?= rand(15, 40) ?></h3>
                            </div>
                            <div class="bg-purple-500/20 p-3 rounded-xl">
                                <i data-lucide="calendar-check" class="w-6 h-6 text-purple-500"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros e Busca -->
                <div class="tech-card p-6 mb-6">
                    <form method="GET" class="flex flex-col md:flex-row gap-4">
                        <input type="hidden" name="aba" value="alunos">
                        <div class="flex-1 relative">
                            <i data-lucide="search" class="absolute left-4 top-1/2 transform -translate-y-1/2 w-4 h-4 text-tech-muted"></i>
                            <input type="text" name="busca" value="<?= htmlspecialchars($termoBusca) ?>" 
                                   placeholder="Buscar aluno por nome, email ou CPF..." 
                                   class="w-full pl-10 tech-input rounded-lg p-4">
                        </div>
                        
                        <div class="flex gap-3">
                            <select name="status" class="tech-input rounded-lg p-4">
                                <option value="todos">Todos os status</option>
                                <option value="Ativo" <?= $statusFiltro == 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                                <option value="Inativo" <?= $statusFiltro == 'Inativo' ? 'selected' : '' ?>>Inativo</option>
                                <option value="Pendente" <?= $statusFiltro == 'Pendente' ? 'selected' : '' ?>>Pendente</option>
                            </select>
                            
                            <button type="submit" class="bg-tech-primary hover:bg-tech-primaryHover text-white px-6 py-4 rounded-lg font-bold flex gap-2 items-center transition-colors">
                                <i data-lucide="search" class="w-5 h-5"></i>
                                Buscar
                            </button>
                            
                            <a href="?aba=alunos" 
                               class="bg-tech-800 hover:bg-tech-700 text-white px-4 py-4 rounded-lg font-bold flex gap-2 items-center transition-colors">
                                <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Lista de Alunos -->
                <div class="tech-card overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-sm text-tech-muted border-b border-tech-800">
                                    <th class="pb-3 px-6">Aluno</th>
                                    <th class="pb-3 px-6">Contato</th>
                                    <th class="pb-3 px-6">Plano</th>
                                    <th class="pb-3 px-6">Status</th>
                                    <th class="pb-3 px-6">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($alunosFiltrados as $aluno): ?>
                                <tr class="border-b border-tech-800 hover:bg-tech-800 transition-colors">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-gradient-to-br from-tech-primary to-orange-600 rounded-full flex items-center justify-center">
                                                <span class="font-bold text-white"><?php echo substr($aluno['nome'], 0, 1); ?></span>
                                            </div>
                                            <div>
                                                <p class="font-bold text-white"><?= htmlspecialchars($aluno['nome']) ?></p>
                                                <p class="text-xs text-tech-muted">ID: <?= $aluno['id'] ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="text-sm text-white"><?= htmlspecialchars($aluno['email']) ?></p>
                                        <p class="text-xs text-tech-muted"><?= htmlspecialchars($aluno['telefone'] ?? 'Não informado') ?></p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="text-xs px-3 py-1 rounded-full bg-blue-500/20 text-blue-400 border border-blue-500/30">
                                            <?= htmlspecialchars($aluno['plano'] ?? 'Básico') ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="text-xs px-3 py-1 rounded-full <?php 
                                            echo $aluno['status'] == 'Ativo' ? 'bg-green-500/20 text-green-400 border border-green-500/30' : 
                                                   ($aluno['status'] == 'Inativo' ? 'bg-red-500/20 text-red-400 border border-red-500/30' : 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30');
                                        ?>">
                                            <?= htmlspecialchars($aluno['status']) ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex gap-2">
                                            <a href="?aba=treinos&aluno_id=<?= $aluno['id'] ?>" 
                                               class="text-tech-primary hover:text-white p-2 hover:bg-tech-primary/10 rounded transition-colors" 
                                               title="Prescrever Treino">
                                                <i data-lucide="dumbbell" class="w-4 h-4"></i>
                                            </a>
                                            <button onclick="verDetalhesAluno(<?= $aluno['id'] ?>)" 
                                                    class="text-tech-muted hover:text-white p-2 hover:bg-tech-700 rounded transition-colors" 
                                                    title="Ver Detalhes">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($alunosFiltrados)): ?>
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-tech-muted">
                                        <i data-lucide="users" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                                        <p class="text-lg">Nenhum aluno encontrado</p>
                                        <p class="text-sm mt-1">Tente ajustar seus filtros de busca</p>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ABA: BIBLIOTECA -->
            <div id="tab-biblioteca" class="<?= $aba == 'biblioteca' ? 'block' : 'hidden' ?>">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                        <i data-lucide="book-open" class="w-6 h-6 text-tech-primary"></i> 
                        Biblioteca de Treinos
                    </h2>
                    <p class="text-sm text-tech-muted mt-1">Modelos de treino pré-configurados</p>
                </div>

                <!-- Lista de Modelos -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach($listaModelos as $modelo): ?>
                    <div class="tech-card p-6 hover:border-tech-primary transition-colors">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-white mb-1"><?= htmlspecialchars($modelo['nome']) ?></h3>
                                <p class="text-sm text-tech-muted">Modelo de treino personalizado</p>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="usarModelo(<?= $modelo['id'] ?>)" 
                                       class="text-tech-primary hover:text-white p-2 hover:bg-tech-primary/10 rounded transition-colors" 
                                       title="Usar este modelo">
                                    <i data-lucide="play" class="w-4 h-4"></i>
                                </button>
                                <form method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este modelo?')">
                                    <input type="hidden" name="modelo_id" value="<?= $modelo['id'] ?>">
                                    <button type="submit" name="excluir_modelo" 
                                            class="text-red-400 hover:text-red-300 p-2 hover:bg-red-500/10 rounded transition-colors" 
                                            title="Excluir modelo">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center gap-2 text-sm">
                                <i data-lucide="dumbbell" class="w-4 h-4 text-green-500"></i>
                                <span class="text-tech-muted">Treino A: <span class="text-white">4 exercícios</span></span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <i data-lucide="dumbbell" class="w-4 h-4 text-blue-500"></i>
                                <span class="text-tech-muted">Treino B: <span class="text-white">5 exercícios</span></span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <i data-lucide="dumbbell" class="w-4 h-4 text-purple-500"></i>
                                <span class="text-tech-muted">Treino C: <span class="text-white">5 exercícios</span></span>
                            </div>
                        </div>
                        
                        <button onclick="usarModelo(<?= $modelo['id'] ?>)" 
                               class="w-full bg-tech-primary/20 hover:bg-tech-primary/30 text-tech-primary hover:text-white border border-tech-primary/30 rounded-lg py-2 px-4 text-sm font-medium flex items-center justify-center gap-2 transition-colors">
                            <i data-lucide="play" class="w-4 h-4"></i>
                            Usar este Modelo
                        </button>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($listaModelos)): ?>
                    <div class="col-span-3">
                        <div class="tech-card p-12 text-center">
                            <i data-lucide="book-open" class="w-16 h-16 text-tech-muted mx-auto mb-4"></i>
                            <h3 class="text-xl font-bold text-tech-muted mb-2">Biblioteca vazia</h3>
                            <p class="text-tech-muted mb-6">Nenhum modelo de treino salvo ainda</p>
                            <a href="?aba=treinos" class="inline-flex items-center gap-2 bg-tech-primary hover:bg-tech-primaryHover text-white px-6 py-3 rounded-lg font-medium transition-colors">
                                <i data-lucide="plus" class="w-5 h-5"></i>
                                Criar Primeiro Modelo
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ABA: AGENDA -->
            <div id="tab-agenda" class="<?= $aba == 'agenda' ? 'block' : 'hidden' ?>">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                        <i data-lucide="calendar" class="w-6 h-6 text-tech-primary"></i> 
                        Minha Agenda
                    </h2>
                    <p class="text-sm text-tech-muted mt-1">Gerencie suas aulas e compromissos</p>
                </div>

                <!-- Controles da Agenda -->
                <div class="tech-card p-6 mb-6">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div class="flex items-center gap-4">
                            <div>
                                <h3 class="text-lg font-bold text-white"><?= date('F Y') ?></h3>
                                <p class="text-sm text-tech-muted"><?= date('d/m/Y') ?></p>
                            </div>
                            <div class="flex gap-2">
                                <button class="p-2 rounded-lg bg-tech-800 hover:bg-tech-700 text-tech-muted hover:text-white">
                                    <i data-lucide="chevron-left" class="w-5 h-5"></i>
                                </button>
                                <button class="px-4 py-2 rounded-lg bg-tech-primary text-white font-medium">
                                    Hoje
                                </button>
                                <button class="p-2 rounded-lg bg-tech-800 hover:bg-tech-700 text-tech-muted hover:text-white">
                                    <i data-lucide="chevron-right" class="w-5 h-5"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="flex gap-3">
                            <button onclick="novoAgendamento()" 
                                    class="bg-tech-primary hover:bg-tech-primaryHover text-white px-4 py-2 rounded-lg font-medium flex items-center gap-2">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                                Novo Agendamento
                            </button>
                            <button class="border border-tech-800 hover:bg-tech-800 text-white px-4 py-2 rounded-lg font-medium flex items-center gap-2">
                                <i data-lucide="filter" class="w-4 h-4"></i>
                                Filtrar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Visão da Semana -->
                <div class="tech-card p-6 mb-6">
                    <h3 class="text-lg font-bold text-white mb-4">Esta Semana</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-7 gap-4">
                        <?php 
                        $diasSemana = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
                        for($i = 0; $i < 7; $i++): 
                            $data = date('Y-m-d', strtotime("+$i days"));
                            $diaNum = date('d', strtotime($data));
                            $hoje = date('Y-m-d') == $data;
                        ?>
                        <div class="text-center">
                            <div class="<?= $hoje ? 'bg-tech-primary text-white' : 'bg-tech-800 text-tech-muted' ?> p-3 rounded-lg">
                                <p class="text-sm"><?= $diasSemana[$i] ?></p>
                                <p class="text-2xl font-bold"><?= $diaNum ?></p>
                            </div>
                            <div class="mt-2">
                                <p class="text-xs text-tech-muted"><?= rand(1, 5) ?> aulas</p>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Lista de Agendamentos -->
                <div class="tech-card p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-white">Agendamentos de Hoje</h3>
                        <div class="flex gap-2">
                            <span class="text-xs px-2 py-1 rounded-full bg-green-500/20 text-green-400">Confirmados</span>
                            <span class="text-xs px-2 py-1 rounded-full bg-yellow-500/20 text-yellow-400">Pendentes</span>
                            <span class="text-xs px-2 py-1 rounded-full bg-red-500/20 text-red-400">Cancelados</span>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <?php foreach($agendaHoje as $compromisso): ?>
                        <div class="flex items-center justify-between p-4 rounded-lg bg-tech-800 hover:bg-tech-700 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="text-center min-w-[60px]">
                                    <p class="text-lg font-bold text-white"><?= $compromisso['hora'] ?></p>
                                    <p class="text-xs text-tech-muted">60 min</p>
                                </div>
                                <div class="flex-1">
                                    <p class="font-bold text-white"><?= $compromisso['aluno'] ?></p>
                                    <p class="text-sm text-tech-muted"><?= $compromisso['tipo'] ?> • Personal Trainer</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-4">
                                <span class="px-3 py-1 text-xs rounded-full border <?= 'status-' . $compromisso['status'] ?>">
                                    <?= ucfirst($compromisso['status']) ?>
                                </span>
                                
                                <div class="flex gap-2">
                                    <button class="text-tech-primary hover:text-white p-2 hover:bg-tech-primary/10 rounded transition-colors">
                                        <i data-lucide="check" class="w-4 h-4"></i>
                                    </button>
                                    <button class="text-red-400 hover:text-red-300 p-2 hover:bg-red-500/10 rounded transition-colors">
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </button>
                                    <button class="text-tech-muted hover:text-white p-2 hover:bg-tech-700 rounded transition-colors">
                                        <i data-lucide="more-vertical" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- ABA: RELATÓRIOS -->
            <div id="tab-relatorios" class="<?= $aba == 'relatorios' ? 'block' : 'hidden' ?>">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                        <i data-lucide="bar-chart" class="w-6 h-6 text-tech-primary"></i> 
                        Relatórios
                    </h2>
                    <p class="text-sm text-tech-muted mt-1">Visualize suas métricas e desempenho</p>
                </div>

                <!-- Filtros de Período -->
                <div class="tech-card p-6 mb-6">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-white">Relatório Mensal</h3>
                            <p class="text-sm text-tech-muted"><?= date('F Y') ?></p>
                        </div>
                        
                        <div class="flex gap-3">
                            <select class="tech-input rounded-lg px-4 py-2">
                                <option>Últimos 30 dias</option>
                                <option>Este mês</option>
                                <option>Últimos 3 meses</option>
                                <option>Este ano</option>
                            </select>
                            <button class="border border-tech-800 hover:bg-tech-800 text-white px-4 py-2 rounded-lg font-medium flex items-center gap-2">
                                <i data-lucide="download" class="w-4 h-4"></i>
                                Exportar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Métricas Principais -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="tech-card p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-tech-muted">Total de Aulas</p>
                                <h3 class="text-3xl font-bold text-white mt-2"><?= $relatorioMensal['total_aulas'] ?></h3>
                                <p class="text-xs text-green-500 mt-1">↑ 12% vs mês anterior</p>
                            </div>
                            <div class="bg-blue-500/20 p-3 rounded-xl">
                                <i data-lucide="calendar" class="w-6 h-6 text-blue-500"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tech-card p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-tech-muted">Alunos Atendidos</p>
                                <h3 class="text-3xl font-bold text-white mt-2"><?= $relatorioMensal['alunos_atendidos'] ?></h3>
                                <p class="text-xs text-green-500 mt-1">↑ 8% vs mês anterior</p>
                            </div>
                            <div class="bg-green-500/20 p-3 rounded-xl">
                                <i data-lucide="users" class="w-6 h-6 text-green-500"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tech-card p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-tech-muted">Faturamento</p>
                                <h3 class="text-3xl font-bold text-white mt-2">R$ <?= number_format($relatorioMensal['faturamento'], 0, ',', '.') ?></h3>
                                <p class="text-xs text-green-500 mt-1">↑ 18% vs mês anterior</p>
                            </div>
                            <div class="bg-purple-500/20 p-3 rounded-xl">
                                <i data-lucide="dollar-sign" class="w-6 h-6 text-purple-500"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tech-card p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-tech-muted">Treinos Prescritos</p>
                                <h3 class="text-3xl font-bold text-white mt-2"><?= $relatorioMensal['treinos_prescritos'] ?></h3>
                                <p class="text-xs text-green-500 mt-1">↑ 15% vs mês anterior</p>
                            </div>
                            <div class="bg-orange-500/20 p-3 rounded-xl">
                                <i data-lucide="dumbbell" class="w-6 h-6 text-orange-500"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos e Detalhes -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Gráfico de Evolução -->
                    <div class="tech-card p-6">
                        <h3 class="text-lg font-bold text-white mb-4">Evolução de Faturamento</h3>
                        <div class="h-64 flex items-end justify-between gap-2">
                            <?php for($i = 1; $i <= 12; $i++): 
                                $height = rand(40, 100);
                                $month = date('M', mktime(0, 0, 0, $i, 1));
                            ?>
                            <div class="flex-1 flex flex-col items-center">
                                <div class="w-full bg-gradient-to-t from-tech-primary to-orange-600 rounded-t-lg" style="height: <?= $height ?>%;"></div>
                                <p class="text-xs text-tech-muted mt-2"><?= $month ?></p>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <!-- Distribuição por Plano -->
                    <div class="tech-card p-6">
                        <h3 class="text-lg font-bold text-white mb-4">Distribuição por Plano</h3>
                        <div class="flex items-center justify-center h-64">
                            <div class="relative w-48 h-48">
                                <!-- Gráfico de pizza simulado -->
                                <div class="absolute inset-0 rounded-full border-8 border-blue-500"></div>
                                <div class="absolute inset-0 rounded-full border-8 border-green-500" style="clip-path: polygon(50% 50%, 50% 0%, 100% 0%, 100% 100%, 0% 100%, 0% 50%);"></div>
                                <div class="absolute inset-0 rounded-full border-8 border-purple-500" style="clip-path: polygon(50% 50%, 100% 0%, 100% 100%);"></div>
                                
                                <div class="absolute inset-0 flex items-center justify-center flex-col">
                                    <p class="text-2xl font-bold text-white"><?= $alunosAtivos ?></p>
                                    <p class="text-sm text-tech-muted">Total Alunos</p>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-4 mt-6">
                            <div class="text-center">
                                <div class="w-3 h-3 bg-blue-500 rounded-full mx-auto mb-1"></div>
                                <p class="text-sm text-white">Start</p>
                                <p class="text-xs text-tech-muted">45%</p>
                            </div>
                            <div class="text-center">
                                <div class="w-3 h-3 bg-green-500 rounded-full mx-auto mb-1"></div>
                                <p class="text-sm text-white">Pro</p>
                                <p class="text-xs text-tech-muted">35%</p>
                            </div>
                            <div class="text-center">
                                <div class="w-3 h-3 bg-purple-500 rounded-full mx-auto mb-1"></div>
                                <p class="text-sm text-white">VIP</p>
                                <p class="text-xs text-tech-muted">20%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabela de Performance -->
                <div class="tech-card p-6">
                    <h3 class="text-lg font-bold text-white mb-4">Performance por Aluno</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-sm text-tech-muted border-b border-tech-800">
                                    <th class="pb-3 px-4">Aluno</th>
                                    <th class="pb-3 px-4">Frequência</th>
                                    <th class="pb-3 px-4">Treinos Completos</th>
                                    <th class="pb-3 px-4">Evolução</th>
                                    <th class="pb-3 px-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $alunosPerformance = array_slice($alunosFiltrados, 0, 5);
                                foreach($alunosPerformance as $aluno): 
                                    $frequencia = rand(70, 100);
                                    $treinos = rand(15, 30);
                                    $evolucao = rand(-5, 20);
                                ?>
                                <tr class="border-b border-tech-800 hover:bg-tech-800 transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-gradient-to-br from-tech-primary to-orange-600 rounded-full flex items-center justify-center">
                                                <span class="font-bold text-white text-xs"><?php echo substr($aluno['nome'], 0, 1); ?></span>
                                            </div>
                                            <p class="font-medium text-white"><?= htmlspecialchars($aluno['nome']) ?></p>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 bg-tech-800 rounded-full h-2">
                                                <div class="bg-green-500 h-2 rounded-full" style="width: <?= $frequencia ?>%;"></div>
                                            </div>
                                            <span class="text-sm text-white"><?= $frequencia ?>%</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <p class="text-white"><?= $treinos ?> treinos</p>
                                    </td>
                                    <td class="py-3 px-4">
                                        <p class="text-sm <?= $evolucao >= 0 ? 'text-green-500' : 'text-red-500' ?>">
                                            <?= $evolucao >= 0 ? '+' : '' ?><?= $evolucao ?>%
                                        </p>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="text-xs px-2 py-1 rounded-full <?= $frequencia >= 80 ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400' ?>">
                                            <?= $frequencia >= 80 ? 'Excelente' : 'Regular' ?>
                                        </span>
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

    <!-- Modal Biblioteca -->
    <div id="modalBiblioteca" class="fixed inset-0 z-50 hidden" aria-modal="true">
        <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" onclick="fecharModal()"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="relative w-full max-w-2xl bg-tech-900 border border-tech-800 rounded-2xl shadow-2xl">
                <div class="flex items-center justify-between px-6 py-4 border-b border-tech-800 bg-tech-800/50 rounded-t-2xl">
                    <h3 class="text-lg font-bold text-white">Biblioteca de Treinos</h3>
                    <button type="button" class="text-tech-muted hover:text-white" onclick="fecharModal()">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <div class="p-6 max-h-[60vh] overflow-y-auto">
                    <?php if (empty($listaModelos)): ?>
                        <div class="text-center py-8 text-tech-muted">
                            <i data-lucide="book-open" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                            <p>Nenhum modelo salvo ainda</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 gap-3">
                            <?php foreach($listaModelos as $modelo): ?>
                                <div class="tech-card p-4 hover:border-tech-primary transition-colors cursor-pointer" 
                                     onclick="carregarModelo(<?= $modelo['id'] ?>)">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <h4 class="font-bold text-white"><?= htmlspecialchars($modelo['nome']) ?></h4>
                                            <p class="text-sm text-tech-muted mt-1">Clique para carregar</p>
                                        </div>
                                        <i data-lucide="chevron-right" class="w-5 h-5 text-tech-muted"></i>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalhes Aluno -->
    <div id="modalDetalhes" class="fixed inset-0 z-50 hidden" aria-modal="true">
        <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" onclick="fecharModalDetalhes()"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="relative w-full max-w-2xl bg-tech-900 border border-tech-800 rounded-2xl shadow-2xl">
                <div class="flex items-center justify-between px-6 py-4 border-b border-tech-800 bg-tech-800/50 rounded-t-2xl">
                    <h3 class="text-lg font-bold text-white">Detalhes do Aluno</h3>
                    <button type="button" class="text-tech-muted hover:text-white" onclick="fecharModalDetalhes()">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <div class="p-6 max-h-[60vh] overflow-y-auto" id="detalhesConteudo">
                    <!-- Conteúdo via JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Messages -->
    <div id="toastContainer" class="fixed top-5 right-5 z-50 space-y-2"></div>

    <script>
        // Inicializar ícones
        lucide.createIcons();
        
        // ========================================
        // 1. TOGGLE DA SIDEBAR
        // ========================================
        let sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const toggleIcon = document.getElementById('sidebar-icon');
            const logoText = document.getElementById('logo-text');
            
            sidebarCollapsed = !sidebarCollapsed;
            localStorage.setItem('sidebarCollapsed', sidebarCollapsed);
            
            if (sidebarCollapsed) {
                sidebar.classList.add('sidebar-collapsed');
                mainContent.classList.add('ml-[80px]');
                toggleIcon.setAttribute('data-lucide', 'chevron-right');
                logoText.classList.add('hidden');
                
                // Esconder textos dos itens do menu
                document.querySelectorAll('#profile-info, #specialty-info, #menu-home, #menu-treinos, #menu-alunos, #menu-biblioteca, #menu-agenda, #menu-relatorios, #menu-logout').forEach(el => {
                    el.classList.add('hidden');
                });
            } else {
                sidebar.classList.remove('sidebar-collapsed');
                mainContent.classList.remove('ml-[80px]');
                toggleIcon.setAttribute('data-lucide', 'chevron-left');
                logoText.classList.remove('hidden');
                
                // Mostrar textos dos itens do menu
                document.querySelectorAll('#profile-info, #specialty-info, #menu-home, #menu-treinos, #menu-alunos, #menu-biblioteca, #menu-agenda, #menu-relatorios, #menu-logout').forEach(el => {
                    el.classList.remove('hidden');
                });
            }
            
            lucide.createIcons();
        }
        
        // Toggle mobile
        document.getElementById('mobile-toggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('hidden');
        });
        
        // Aplicar estado inicial
        if (sidebarCollapsed) {
            toggleSidebar(); // Chama uma vez para aplicar
        }
        
        document.getElementById('toggle-sidebar').addEventListener('click', toggleSidebar);
        
        // ========================================
        // 2. FUNÇÕES DO SISTEMA DE TREINOS
        // ========================================
        document.addEventListener('DOMContentLoaded', function() {
            <?php if($aba == 'treinos' && $alunoIdSelecionado): ?>
                document.getElementById('selectAluno').value = <?= $alunoIdSelecionado ?>;
                setTimeout(() => {
                    carregarTreino(<?= $alunoIdSelecionado ?>);
                }, 500);
            <?php endif; ?>
            
            <?php if($aba == 'treinos' && $modeloId): ?>
                setTimeout(() => {
                    carregarModelo(<?= $modeloId ?>);
                }, 500);
            <?php endif; ?>
            
            <?php if($aba == 'treinos' && !$alunoIdSelecionado && !$modeloId): ?>
                ['A','B','C'].forEach(div => {
                    const container = document.getElementById('container-'+div);
                    if(container.children.length === 1) {
                        addExercicio('container-'+div, '', '3x12');
                    }
                });
            <?php endif; ?>
        });
        
        async function carregarTreino(alunoId) {
            if(!alunoId) return;
            
            ['A','B','C'].forEach(d => {
                const container = document.getElementById('container-'+d);
                container.innerHTML = '';
            });
            
            try {
                const response = await fetch(`professor.php?acao_ajax=buscar_treino&id=${alunoId}`);
                const data = await response.json();
                
                if (response.ok && data) {
                    ['A','B','C'].forEach(div => {
                        if(data[div] && data[div].length > 0) {
                            data[div].forEach(t => {
                                addExercicio('container-'+div, t.exercicio || t.nome, t.series);
                            });
                        } else {
                            addExercicio('container-'+div, '', '3x12');
                        }
                    });
                    exibirToast("Treino carregado com sucesso!", "sucesso");
                } else {
                    ['A','B','C'].forEach(div => {
                        addExercicio('container-'+div, '', '3x12');
                    });
                    exibirToast("Nenhum treino encontrado para este aluno", "info");
                }
            } catch (error) { 
                console.error(error);
                exibirToast("Erro ao carregar treino", "erro");
            }
        }

        async function carregarModelo(modeloId) {
            try {
                const response = await fetch(`professor.php?acao_ajax=buscar_modelo&id=${modeloId}`);
                const data = await response.json();
                
                if (response.ok && data) {
                    ['A','B','C'].forEach(d => {
                        const container = document.getElementById('container-'+d);
                        container.innerHTML = '';
                    });
                    
                    ['A','B','C'].forEach(div => {
                        if(data[div] && data[div].length > 0) {
                            data[div].forEach(t => {
                                addExercicio('container-'+div, t.exercicio, t.series);
                            });
                        } else {
                            addExercicio('container-'+div, '', '3x12');
                        }
                    });
                    
                    exibirToast("Modelo carregado com sucesso!", "sucesso");
                    fecharModal();
                }
            } catch (error) {
                console.error(error);
                exibirToast("Erro ao carregar modelo", "erro");
            }
        }

        function usarModelo(modeloId) {
            window.location.href = `?aba=treinos&modelo=${modeloId}`;
        }

        function addExercicio(containerId, nome = '', series = '3x12') {
            const container = document.getElementById(containerId);
            const cleanId = containerId.replace('container-', '');
            const exerciseCount = container.querySelectorAll('.exercise-item').length;
            const exerciseNumber = exerciseCount + 1;
            
            const initialMessage = container.querySelector('.text-center');
            if (initialMessage && initialMessage.querySelector('i[data-lucide="dumbbell"]')) {
                initialMessage.remove();
            }
            
            const div = document.createElement('div');
            div.className = 'exercise-item flex gap-2 items-center group bg-tech-800 p-3 rounded-lg border border-tech-700 mb-2';
            div.innerHTML = `
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs text-tech-muted font-mono">${exerciseNumber}.</span>
                        <input type="text" name="treino[${cleanId}][${exerciseNumber}][nome]" 
                               value="${nome}" 
                               placeholder="Nome do exercício" 
                               class="w-full bg-transparent border-none text-white text-sm focus:outline-none focus:ring-0" required>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-tech-muted">Reps:</span>
                        <input type="text" name="treino[${cleanId}][${exerciseNumber}][series]" 
                               value="${series}" 
                               placeholder="3x12" 
                               class="w-20 bg-tech-900 border border-tech-700 rounded px-2 py-1 text-sm text-center text-white focus:border-tech-primary focus:outline-none" required>
                    </div>
                </div>
                <button type="button" onclick="removerExercicio(this)" 
                        class="text-red-400 hover:text-red-300 p-1 hover:bg-red-500/10 rounded transition-colors opacity-0 group-hover:opacity-100">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            `;
            container.appendChild(div);
            lucide.createIcons();
        }
        
        function removerExercicio(button) {
            const exerciseDiv = button.closest('.exercise-item');
            const container = exerciseDiv.parentElement;
            exerciseDiv.remove();
            atualizarNumeros(container.id);
            
            if (container.querySelectorAll('.exercise-item').length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8 text-tech-muted">
                        <i data-lucide="dumbbell" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                        <p>Clique em "Adicionar" para começar</p>
                    </div>
                `;
                lucide.createIcons();
            }
        }
        
        function atualizarNumeros(containerId) {
            const container = document.getElementById(containerId);
            const exercises = container.querySelectorAll('.exercise-item');
            exercises.forEach((exercise, index) => {
                const numberSpan = exercise.querySelector('.font-mono');
                if (numberSpan) {
                    numberSpan.textContent = `${index + 1}.`;
                }
                const nomeInput = exercise.querySelector('input[name*="[nome]"]');
                const seriesInput = exercise.querySelector('input[name*="[series]"]');
                if (nomeInput && seriesInput) {
                    const cleanId = containerId.replace('container-', '');
                    const cleanName = `treino[${cleanId}][${index + 1}][nome]`;
                    const cleanSeries = `treino[${cleanId}][${index + 1}][series]`;
                    nomeInput.name = cleanName;
                    seriesInput.name = cleanSeries;
                }
            });
        }

        function gerarTreinoAutomatico() {
            ['A','B','C'].forEach(d => {
                const container = document.getElementById('container-'+d);
                container.innerHTML = '';
            });
            
            const tA = [
                {n:'Supino Reto', s:'4x10'},
                {n:'Supino Inclinado', s:'3x12'},
                {n:'Crucifixo', s:'3x15'},
                {n:'Tríceps Corda', s:'4x12'}
            ];
            const tB = [
                {n:'Puxada Alta', s:'4x10'},
                {n:'Remada Curvada', s:'3x12'},
                {n:'Remada Baixa', s:'3x12'},
                {n:'Rosca Direta', s:'4x12'},
                {n:'Rosca Martelo', s:'3x15'}
            ];
            const tC = [
                {n:'Agachamento Livre', s:'4x8'},
                {n:'Leg Press 45°', s:'3x12'},
                {n:'Cadeira Extensora', s:'4x15'},
                {n:'Mesa Flexora', s:'4x12'},
                {n:'Panturrilha Sentado', s:'5x20'}
            ];
            
            tA.forEach(e => addExercicio('container-A', e.n, e.s));
            tB.forEach(e => addExercicio('container-B', e.n, e.s));
            tC.forEach(e => addExercicio('container-C', e.n, e.s));
            
            exibirToast("Modelo padrão gerado com sucesso!", "sucesso");
        }

        function carregarDaBiblioteca() {
            document.getElementById('modalBiblioteca').classList.remove('hidden');
        }

        function fecharModal() {
            document.getElementById('modalBiblioteca').classList.add('hidden');
        }

        function salvarComoModelo() {
            const nomeModelo = document.querySelector('input[name="nome_modelo"]').value;
            if (!nomeModelo.trim()) {
                exibirToast("Digite um nome para o modelo", "erro");
                return;
            }
            
            document.querySelector('input[name="acao"]').value = 'salvar_modelo';
            document.getElementById('formTreino').submit();
        }

        function verDetalhesAluno(alunoId) {
            // Simulação de dados do aluno
            document.getElementById('detalhesConteudo').innerHTML = `
                <div class="space-y-6">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 bg-gradient-to-br from-tech-primary to-orange-600 rounded-full flex items-center justify-center">
                            <span class="font-bold text-white text-xl">A</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">Aluno #${alunoId}</h3>
                            <p class="text-tech-muted">ID: ${alunoId}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="tech-card p-4">
                            <p class="text-xs text-tech-muted">Email</p>
                            <p class="font-bold text-white mt-1">aluno${alunoId}@email.com</p>
                        </div>
                        <div class="tech-card p-4">
                            <p class="text-xs text-tech-muted">Telefone</p>
                            <p class="font-bold text-white mt-1">(11) 99999-9999</p>
                        </div>
                        <div class="tech-card p-4">
                            <p class="text-xs text-tech-muted">Plano</p>
                            <p class="font-bold text-white mt-1">Premium</p>
                        </div>
                        <div class="tech-card p-4">
                            <p class="text-xs text-tech-muted">Status</p>
                            <p class="font-bold text-green-500 mt-1">Ativo</p>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="text-lg font-bold text-white mb-3">Estatísticas do Mês</h4>
                        <div class="grid grid-cols-3 gap-3">
                            <div class="tech-card p-4 text-center">
                                <p class="text-2xl font-bold text-white">15</p>
                                <p class="text-xs text-tech-muted">Dias Treino</p>
                            </div>
                            <div class="tech-card p-4 text-center">
                                <p class="text-2xl font-bold text-white">45</p>
                                <p class="text-xs text-tech-muted">Min/Dia Médio</p>
                            </div>
                            <div class="tech-card p-4 text-center">
                                <p class="text-2xl font-bold text-white">92%</p>
                                <p class="text-xs text-tech-muted">Frequência</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex gap-3">
                        <a href="?aba=treinos&aluno_id=${alunoId}" 
                           class="flex-1 bg-tech-primary hover:bg-tech-primaryHover text-white text-center py-3 rounded-lg font-medium transition-colors">
                            Prescrever Treino
                        </a>
                        <button onclick="fecharModalDetalhes()" 
                                class="flex-1 bg-tech-800 hover:bg-tech-700 text-white py-3 rounded-lg font-medium transition-colors">
                            Fechar
                        </button>
                    </div>
                </div>
            `;
            
            document.getElementById('modalDetalhes').classList.remove('hidden');
        }
        
        function fecharModalDetalhes() {
            document.getElementById('modalDetalhes').classList.add('hidden');
        }

        function novoAgendamento() {
            exibirToast("Funcionalidade de novo agendamento em desenvolvimento", "info");
        }
        
        // ========================================
        // 3. SISTEMA DE TOAST
        // ========================================
        function exibirToast(mensagem, tipo = 'info') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            
            let classes = 'px-6 py-4 rounded-lg shadow-2xl flex items-center gap-4 min-w-[300px] transform transition-all duration-500 translate-x-full';
            let icon = '';
            let title = '';
            
            switch(tipo) {
                case 'sucesso':
                    classes += ' bg-tech-900 border-l-4 border-green-500';
                    icon = '<i data-lucide="check-circle" class="w-6 h-6 text-green-500"></i>';
                    title = 'Sucesso';
                    break;
                case 'erro':
                    classes += ' bg-tech-900 border-l-4 border-red-500';
                    icon = '<i data-lucide="alert-circle" class="w-6 h-6 text-red-500"></i>';
                    title = 'Erro';
                    break;
                case 'info':
                default:
                    classes += ' bg-tech-900 border-l-4 border-blue-500';
                    icon = '<i data-lucide="info" class="w-6 h-6 text-blue-500"></i>';
                    title = 'Informação';
            }
            
            toast.className = classes;
            toast.innerHTML = `
                <div class="p-2 rounded-full">${icon}</div>
                <div>
                    <h4 class="font-bold text-sm text-white">${title}</h4>
                    <p class="text-xs text-tech-muted">${mensagem}</p>
                </div>
            `;
            
            container.appendChild(toast);
            lucide.createIcons();
            
            // Animação de entrada
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
            }, 10);
            
            // Animação de saída
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => {
                    if(toast.parentNode) toast.parentNode.removeChild(toast);
                }, 500);
            }, 4000);
        }

        // ========================================
        // 4. INICIALIZAÇÃO
        // ========================================
        window.addEventListener('resize', function() {
            if (window.innerWidth < 768) {
                document.getElementById('sidebar').classList.add('hidden');
            } else {
                document.getElementById('sidebar').classList.remove('hidden');
            }
        });
    </script>
</body>
</html>