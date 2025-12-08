<?php
session_start();
require_once __DIR__ . '/../Model/TreinoDAO.php';
require_once __DIR__ . '/../Model/AlunoDAO.php';
require_once __DIR__ . '/../Model/ProdutoDAO.php';
require_once __DIR__ . '/../Model/VendaDAO.php';

// SEGURANÇA
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?login_erro=1&msg=Faça login para acessar');
    exit;
}
if (isset($_GET['sair'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$idAluno = $_SESSION['usuario_id'];
$daoAluno = new AlunoDAO();
$treinoDao = new TreinoDAO();
$produtoDao = new ProdutoDAO();
$vendaDao = new VendaDAO();

// --- CARRINHO ---
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// --- PROCESSAMENTO: EDITAR PERFIL ---
$msgCliente = '';
$tipoMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] === 'editar_perfil') {
        $novaSenha = !empty($_POST['nova_senha']) ? $_POST['nova_senha'] : null;
        
        if ($novaSenha && !preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/', $novaSenha)) {
            $msgCliente = "A senha deve ser forte (Letras, números e símbolo)!";
            $tipoMsg = 'erro';
        } else {
            if ($daoAluno->atualizarPerfilAluno($idAluno, $_POST['nome'], $_POST['email'], $_POST['telefone'], $novaSenha)) {
                $_SESSION['usuario_nome'] = $_POST['nome'];
                header("Location: paginacliente.php?tab=perfil&msg=perfil_ok");
                exit;
            } else {
                $msgCliente = "Erro ao atualizar. Tente novamente.";
                $tipoMsg = 'erro';
            }
        }
    }
    
    // ADICIONAR AO CARRINHO
    if ($_POST['acao'] === 'adicionar_carrinho') {
        $produto_id = $_POST['produto_id'];
        $quantidade = $_POST['quantidade'] ?? 1;
        
        // Verifica estoque
        $produto = $produtoDao->buscarPorId($produto_id);
        if ($produto && $produto['estoque'] >= $quantidade) {
            if (isset($_SESSION['carrinho'][$produto_id])) {
                $_SESSION['carrinho'][$produto_id] += $quantidade;
            } else {
                $_SESSION['carrinho'][$produto_id] = $quantidade;
            }
            $_SESSION['msg_carrinho'] = "Produto adicionado ao carrinho!";
            header("Location: paginacliente.php?tab=loja");
            exit;
        } else {
            $msgCliente = "Estoque insuficiente!";
            $tipoMsg = 'erro';
        }
    }
    
    // REMOVER DO CARRINHO
    if ($_POST['acao'] === 'remover_carrinho') {
        $produto_id = $_POST['produto_id'];
        if (isset($_SESSION['carrinho'][$produto_id])) {
            unset($_SESSION['carrinho'][$produto_id]);
            $_SESSION['msg_carrinho'] = "Produto removido do carrinho!";
        }
        header("Location: paginacliente.php?tab=loja&sub=carrinho");
        exit;
    }
    
    // ATUALIZAR QUANTIDADE
    if ($_POST['acao'] === 'atualizar_carrinho') {
        $produto_id = $_POST['produto_id'];
        $quantidade = $_POST['quantidade'];
        
        if ($quantidade > 0) {
            $_SESSION['carrinho'][$produto_id] = $quantidade;
        } else {
            unset($_SESSION['carrinho'][$produto_id]);
        }
        header("Location: paginacliente.php?tab=loja&sub=carrinho");
        exit;
    }
    
    // FINALIZAR COMPRA
    if ($_POST['acao'] === 'finalizar_compra') {
        $forma_pagamento = $_POST['forma_pagamento'];
        $total = 0;
        $erros = [];
        
        foreach ($_SESSION['carrinho'] as $produto_id => $quantidade) {
            $produto = $produtoDao->buscarPorId($produto_id);
            if (!$produto) {
                $erros[] = "Produto não encontrado!";
                continue;
            }
            
            if ($produto['estoque'] < $quantidade) {
                $erros[] = "Estoque insuficiente para {$produto['nome']}";
                continue;
            }
            
            // Registrar venda
            if ($vendaDao->registrarVenda($idAluno, $produto_id, $quantidade, $produto['preco'], $forma_pagamento)) {
                // Atualizar estoque
                $novoEstoque = $produto['estoque'] - $quantidade;
                $produtoDao->atualizarEstoque($produto_id, $novoEstoque);
                $total += $produto['preco'] * $quantidade;
            }
        }
        
        if (empty($erros)) {
            $_SESSION['carrinho'] = [];
            $_SESSION['msg_compra'] = "Compra realizada com sucesso! Total: R$ " . number_format($total, 2, ',', '.');
            header("Location: paginacliente.php?tab=loja&msg=compra_ok");
            exit;
        } else {
            $msgCliente = implode("<br>", $erros);
            $tipoMsg = 'erro';
        }
    }
}

// --- PROCESSAMENTO: FREQUÊNCIA (ENTRADA/SAÍDA) ---
if (isset($_POST['acao_frequencia'])) {
    if ($_POST['acao_frequencia'] == 'entrada') {
        $daoAluno->registrarEntrada($idAluno);
        header("Location: paginacliente.php?msg=entrada_ok&open_qr=true");
        exit;
    }
    if ($_POST['acao_frequencia'] == 'saida') { 
        $daoAluno->registrarSaida($idAluno);
        header("Location: paginacliente.php?msg=saida_ok&open_qr=true");
        exit;
    }
}

// --- DADOS ---
$dadosAluno = $daoAluno->buscarPorId($idAluno);
$meusTreinos = $treinoDao->buscarPorAluno($idAluno);
$nomeCompleto = $dadosAluno['nome'];
$primeiroNome = explode(' ', $nomeCompleto)[0];
$planoAluno = $dadosAluno['plano'];

// --- DADOS DA LOJA ---
$listaProdutos = $produtoDao->listar();
$termoBuscaProd = $_GET['busca_produto'] ?? '';
$categoriaFiltro = $_GET['categoria'] ?? '';

// Filtrar produtos
if ($termoBuscaProd || $categoriaFiltro) {
    $listaProdutos = array_filter($listaProdutos, function($p) use ($termoBuscaProd, $categoriaFiltro) {
        $matchNome = !$termoBuscaProd || stripos($p['nome'], $termoBuscaProd) !== false || stripos($p['categoria'], $termoBuscaProd) !== false;
        $matchCategoria = !$categoriaFiltro || $p['categoria'] === $categoriaFiltro;
        return $matchNome && $matchCategoria;
    });
}

// Calcular total do carrinho
$totalCarrinho = 0;
$itensCarrinho = [];
foreach ($_SESSION['carrinho'] as $produto_id => $quantidade) {
    $produto = $produtoDao->buscarPorId($produto_id);
    if ($produto) {
        $itensCarrinho[] = [
            'id' => $produto_id,
            'nome' => $produto['nome'],
            'preco' => $produto['preco'],
            'quantidade' => $quantidade,
            'subtotal' => $produto['preco'] * $quantidade,
            'estoque' => $produto['estoque']
        ];
        $totalCarrinho += $produto['preco'] * $quantidade;
    }
}

// STATUS FREQUÊNCIA
$statusFrequencia = $daoAluno->getStatusFrequenciaHoje($idAluno); 
$treinoBloqueado = ($statusFrequencia !== 'treinando'); // Bloqueia se não estiver treinando

// Verificar se pode ver treinos (só se registrou entrada)
$podeVerTreinos = ($statusFrequencia === 'treinando' || $statusFrequencia === 'finalizado');

$tempoHoje = ($statusFrequencia === 'finalizado') ? $daoAluno->getTempoTreinoHoje($idAluno) : '';

// Obter hora de entrada se estiver treinando
$horaEntrada = '';
if ($statusFrequencia === 'treinando') {
    $hoje = date('Y-m-d');
    $stmt = $daoAluno->conn->prepare("SELECT hora_entrada FROM frequencia WHERE aluno_id = :id AND data_treino = :data");
    $stmt->execute([':id' => $idAluno, ':data' => $hoje]);
    $horaEntrada = $stmt->fetchColumn();
}

// CALENDÁRIO
$mesAtual = date('m');
$anoAtual = date('Y');
$diasTreinados = $daoAluno->getFrequenciaMes($idAluno, $mesAtual, $anoAtual);
$totalDiasMes = date('t');
$diaHoje = date('j');

// MENSAGENS
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'perfil_ok') { $msgCliente = "Perfil atualizado com sucesso!"; $tipoMsg = 'sucesso'; }
    if ($_GET['msg'] == 'entrada_ok') { $msgCliente = "Bem-vindo! Treino iniciado."; $tipoMsg = 'sucesso'; }
    if ($_GET['msg'] == 'saida_ok') { $msgCliente = "Treino finalizado! Bom descanso."; $tipoMsg = 'sucesso'; }
    if ($_GET['msg'] == 'compra_ok') { $msgCliente = $_SESSION['msg_compra'] ?? "Compra realizada com sucesso!"; $tipoMsg = 'sucesso'; }
}

if (isset($_SESSION['msg_carrinho'])) {
    $msgCliente = $_SESSION['msg_carrinho'];
    $tipoMsg = 'sucesso';
    unset($_SESSION['msg_carrinho']);
}

$abrirQR = isset($_GET['open_qr']);
$subTab = $_GET['sub'] ?? 'produtos';
?>
<!DOCTYPE html>
<html lang="pt-br" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Aluno - TechFit</title>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { 
                extend: { 
                    colors: { 
                        tech: { 
                            900: '#0B0F19', 
                            800: '#151b2b', 
                            700: '#2d3748', 
                            primary: '#ea580c' 
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
        
        .check-custom:checked + div { 
            background-color: #10b981; 
            border-color: #10b981; 
        }
        
        .check-custom:checked + div svg { 
            display: block; 
        }
        
        input, select, textarea { 
            background-color: #0f172a !important; 
            color: white !important; 
            border-color: #334155 !important; 
        }
        
        input:focus, select:focus, textarea:focus { 
            border-color: #ea580c !important; 
            box-shadow: 0 0 0 1px #ea580c !important; 
            outline: none;
        }
        
        /* Calendário */
        .day-presente { 
            background-color: rgba(16, 185, 129, 0.2); 
            border-color: #10b981; 
            color: #10b981; 
        }
        
        .day-falta { 
            background-color: rgba(239, 68, 68, 0.1); 
            border-color: #ef4444; 
            color: #ef4444; 
            opacity: 0.7; 
        }
        
        .day-hoje { 
            border-color: #ea580c; 
            box-shadow: 0 0 10px rgba(234, 88, 12, 0.2); 
        }
        
        .day-futuro { 
            opacity: 0.3; 
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
        }
        
        /* Sidebar styles */
        .logo-text { 
            transition: opacity 0.3s; 
        }
        
        .sidebar-collapsed .logo-text { 
            opacity: 0; 
            width: 0; 
            overflow: hidden; 
        }
        
        .sidebar-collapsed .logo-container { 
            justify-content: center; 
            padding-left: 0.75rem;
        }
        
        .sidebar-collapsed .nav-text { 
            display: none; 
        }
        
        .sidebar-collapsed .nav-item { 
            justify-content: center; 
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        
        /* Card styles */
        .card {
            background-color: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 1rem;
            padding: 1.5rem;
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
            border-color: #ea580c;
            outline: none;
            box-shadow: 0 0 0 2px rgba(234, 88, 12, 0.2);
        }
        
        /* Button styles */
        .btn-primary {
            background-color: #ea580c;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        
        .btn-primary:hover {
            background-color: #c2410c;
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
            
            .text-2xl {
                font-size: 1.5rem;
            }
            
            .text-3xl {
                font-size: 1.75rem;
            }
        }
        
        /* Garantir que ícones sejam visíveis */
        [data-lucide] {
            display: inline-block !important;
            vertical-align: middle;
        }
        
        /* Modal overlay */
        .modal-overlay {
            z-index: 9999;
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
            background-color: #10b981;
            border-color: #10b981;
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
                        <button onclick="switchTab('dashboard')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl bg-tech-primary/10 text-tech-primary shadow-sm border border-tech-primary/20">
                            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                            <span class="nav-text">Home</span>
                        </button>
                        <button onclick="switchTab('treinos')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all <?= !$podeVerTreinos ? 'cursor-not-allowed opacity-50' : '' ?>">
                            <i data-lucide="biceps-flexed" class="w-5 h-5"></i>
                            <span class="nav-text">Meus Treinos</span>
                        </button>
                        <button onclick="switchTab('loja')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all">
                            <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                            <span class="nav-text">Loja</span>
                        </button>
                        <button onclick="switchTab('perfil')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all">
                            <i data-lucide="user-cog" class="w-5 h-5"></i>
                            <span class="nav-text">Meu Perfil</span>
                        </button>
                        <button onclick="abrirCarteirinha()" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all">
                            <i data-lucide="qr-code" class="w-5 h-5"></i>
                            <span class="nav-text">Carteirinha</span>
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
                        <h2 id="pageTitle" class="text-xl md:text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white to-gray-400">Olá, <?= htmlspecialchars($primeiroNome) ?></h2>
                        <p class="text-xs text-gray-500 mt-0.5">Bons treinos hoje!</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-bold text-white"><?= htmlspecialchars($nomeCompleto) ?></p>
                            <span class="text-[10px] uppercase font-bold tracking-wider bg-tech-primary/20 text-tech-primary px-2 py-0.5 rounded-full"><?= htmlspecialchars($planoAluno) ?></span>
                        </div>
                        <div onclick="abrirCarteirinha()" class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-700 to-gray-900 border border-white/10 flex items-center justify-center shadow-lg cursor-pointer hover:border-tech-primary transition-colors">
                            <i data-lucide="qr-code" class="w-5 h-5 text-white"></i>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <div class="page-content no-scrollbar">
                    <!-- Dashboard -->
                    <div id="tab-dashboard" class="tab-content fade-in">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                            <div onclick="abrirCarteirinha()" class="card cursor-pointer hover:border-tech-primary/50 transition-all group">
                                <p class="text-gray-400 text-xs font-bold uppercase mb-2">Status Hoje</p>
                                <div class="flex justify-between items-end">
                                    <div>
                                        <?php if($statusFrequencia == 'nao_entrou'): ?>
                                            <h3 class="text-xl md:text-2xl font-bold text-white">Check-in</h3>
                                            <p class="text-xs text-gray-500">Toque para entrar</p>
                                        <?php elseif($statusFrequencia == 'treinando'): ?>
                                            <h3 class="text-xl md:text-2xl font-bold text-green-400 animate-pulse">Treinando</h3>
                                            <p class="text-xs text-gray-500">
                                                Entrada: <?= date('H:i', strtotime($horaEntrada)) ?>
                                                <?php if($horaEntrada): ?>
                                                    <br><small>Tempo: <span id="tempoDecorrido"></span></small>
                                                <?php endif; ?>
                                            </p>
                                        <?php else: ?>
                                            <h3 class="text-xl md:text-2xl font-bold text-red-400">Concluído</h3>
                                            <p class="text-xs text-gray-500">Tempo: <?= $tempoHoje ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <i data-lucide="activity" class="w-8 h-8 text-tech-primary opacity-80"></i>
                                </div>
                            </div>
                            <div onclick="switchTab('treinos')" class="card cursor-pointer hover:border-tech-primary/50 transition-all <?= !$podeVerTreinos ? 'opacity-50 cursor-not-allowed' : '' ?>">
                                <p class="text-gray-400 text-xs font-bold uppercase mb-2">Treino Atual</p>
                                <div class="flex justify-between items-end">
                                    <h3 class="text-xl md:text-2xl font-bold <?= $podeVerTreinos ? 'text-white' : 'text-gray-500' ?>"><?= $podeVerTreinos ? 'Ver Ficha' : 'Registre entrada' ?></h3>
                                    <i data-lucide="dumbbell" class="w-8 h-8 <?= $podeVerTreinos ? 'text-blue-500' : 'text-gray-500' ?> opacity-80"></i>
                                </div>
                            </div>
                            <div class="card">
                                <p class="text-gray-400 text-xs font-bold uppercase mb-2">Assinatura</p>
                                <div class="flex justify-between items-end">
                                    <h3 class="text-xl md:text-2xl font-bold text-green-400">Ativa</h3>
                                    <i data-lucide="check-circle" class="w-8 h-8 text-green-500 opacity-80"></i>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                                <i data-lucide="calendar-check" class="w-5 h-5 text-tech-primary"></i> Histórico (<?= date('M/Y') ?>)
                            </h3>
                            <div class="grid grid-cols-7 gap-2 text-center">
                                <?php for($i=1; $i<=$totalDiasMes; $i++): 
                                    $classeDia = ''; $icone = '';
                                    if (in_array($i, $diasTreinados)) { 
                                        $classeDia = 'day-presente'; 
                                        $icone = '<i data-lucide="check" class="w-3 h-3 mt-1"></i>'; 
                                    } elseif ($i < $diaHoje) { 
                                        $classeDia = 'day-falta'; 
                                        $icone = '<i data-lucide="x" class="w-3 h-3 mt-1"></i>'; 
                                    } elseif ($i == $diaHoje) { 
                                        $classeDia = 'day-hoje'; 
                                    } else { 
                                        $classeDia = 'day-futuro'; 
                                    }
                                ?>
                                    <div class="p-2 rounded-lg border bg-[#0f172a] text-gray-300 text-sm font-bold flex flex-col justify-center items-center h-14 <?= $classeDia ?>">
                                        <span><?= $i ?></span><?= $icone ?>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Treinos -->
                    <div id="tab-treinos" class="tab-content hidden fade-in">
                        <?php if(!$podeVerTreinos): ?>
                            <div class="flex flex-col items-center justify-center py-12 text-center">
                                <div class="w-24 h-24 bg-red-500/10 rounded-full flex items-center justify-center mb-6 shadow-glow border border-red-500/30">
                                    <i data-lucide="lock" class="w-12 h-12 text-red-500"></i>
                                </div>
                                <h2 class="text-2xl font-bold text-white mb-2">Treino Bloqueado</h2>
                                <p class="text-gray-400 max-w-md mb-4">Você precisa registrar sua entrada na academia para acessar sua ficha de treino.</p>
                                <button onclick="abrirCarteirinha()" class="px-6 py-3 rounded-xl bg-tech-primary hover:bg-orange-600 text-white font-bold shadow-lg transition-all flex items-center justify-center gap-2">
                                    <i data-lucide="qr-code" class="w-5 h-5"></i> REGISTRAR ENTRADA
                                </button>
                            </div>
                        <?php elseif($statusFrequencia === 'finalizado'): ?>
                            <div class="flex flex-col items-center justify-center py-12 text-center">
                                <div class="w-24 h-24 bg-green-500/10 rounded-full flex items-center justify-center mb-6 shadow-glow border border-green-500/30">
                                    <i data-lucide="check-check" class="w-12 h-12 text-green-500"></i>
                                </div>
                                <h2 class="text-2xl font-bold text-white mb-2">Bom Descanso, <?= htmlspecialchars($primeiroNome) ?>!</h2>
                                <p class="text-gray-400 max-w-md mb-4">Treino finalizado. Volte amanhã.</p>
                                <div class="p-4 bg-[#1e293b] rounded-xl border border-white/10">
                                    <p class="text-sm text-gray-500">Tempo hoje:</p>
                                    <p class="text-xl font-mono text-white font-bold"><?= $tempoHoje ?></p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="mb-4 bg-blue-500/10 border border-blue-500/30 p-4 rounded-xl text-blue-400">
                                <div class="flex items-center gap-3">
                                    <i data-lucide="clock" class="w-5 h-5"></i>
                                    <div>
                                        <h4 class="font-bold">Treinando desde <?= date('H:i', strtotime($horaEntrada)) ?></h4>
                                        <p class="text-sm">Tempo decorrido: <span id="tempoTreino" class="font-mono font-bold"></span></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex gap-4 mb-6 border-b border-white/10 pb-1 overflow-x-auto">
                                <button onclick="mudarFicha('A')" id="btn-A" class="pb-3 text-sm font-bold text-tech-primary border-b-2 border-tech-primary transition-all whitespace-nowrap px-4">Treino A</button>
                                <button onclick="mudarFicha('B')" id="btn-B" class="pb-3 text-sm font-bold text-gray-400 hover:text-white transition-all whitespace-nowrap px-4">Treino B</button>
                                <button onclick="mudarFicha('C')" id="btn-C" class="pb-3 text-sm font-bold text-gray-400 hover:text-white transition-all whitespace-nowrap px-4">Treino C</button>
                            </div>
                            <div id="listaExercicios" class="space-y-3 min-h-[300px]"></div>
                            <div class="mt-8 border-t border-white/10 pt-6 text-center">
                                <button onclick="abrirModalConclusao()" class="bg-green-600 hover:bg-green-700 text-white px-8 py-4 rounded-xl font-bold shadow-lg flex items-center justify-center gap-2 mx-auto transition-all">
                                    <i data-lucide="check-circle-2" class="w-6 h-6"></i> CONCLUIR TREINO
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Loja -->
                    <div id="tab-loja" class="tab-content hidden fade-in">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                            <h3 class="text-xl md:text-2xl font-bold text-white">Loja TechFit</h3>
                            <div class="flex items-center gap-3">
                                <span class="bg-tech-primary/20 text-tech-primary px-3 py-1 rounded-full text-xs font-bold">10% OFF</span>
                                <button onclick="switchSubTabLoja('carrinho')" class="relative">
                                    <div class="card p-2 hover:border-tech-primary transition-all">
                                        <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                                        <?php if(count($_SESSION['carrinho']) > 0): ?>
                                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center">
                                                <?= count($_SESSION['carrinho']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </button>
                            </div>
                        </div>
                        
                        <div class="flex gap-4 mb-6 border-b border-white/10 pb-1 overflow-x-auto">
                            <button onclick="switchSubTabLoja('produtos')" id="btn-produtos" class="pb-3 text-sm font-bold <?= $subTab == 'produtos' ? 'text-tech-primary border-b-2 border-tech-primary' : 'text-gray-400 hover:text-white' ?> transition-all whitespace-nowrap px-4">Produtos</button>
                            <button onclick="switchSubTabLoja('carrinho')" id="btn-carrinho" class="pb-3 text-sm font-bold <?= $subTab == 'carrinho' ? 'text-tech-primary border-b-2 border-tech-primary' : 'text-gray-400 hover:text-white' ?> transition-all whitespace-nowrap px-4">Carrinho (<?= count($_SESSION['carrinho']) ?>)</button>
                        </div>
                        
                        <!-- Produtos -->
                        <div id="view-produtos" class="<?= $subTab == 'produtos' ? '' : 'hidden' ?>">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                                <form method="GET" class="relative w-full md:w-96">
                                    <input type="hidden" name="tab" value="loja">
                                    <input type="hidden" name="sub" value="produtos">
                                    <input type="text" name="busca_produto" value="<?= htmlspecialchars($termoBuscaProd) ?>" placeholder="Buscar produto..." class="w-full bg-[#1e293b] border border-white/10 rounded-xl pl-12 pr-4 py-3 focus:border-tech-primary outline-none text-sm text-white">
                                    <button type="submit" class="absolute left-4 top-3 text-gray-500 hover:text-white">
                                        <i data-lucide="search" class="w-5 h-5"></i>
                                    </button>
                                </form>
                                <form method="GET" class="w-full md:w-auto">
                                    <input type="hidden" name="tab" value="loja">
                                    <input type="hidden" name="sub" value="produtos">
                                    <input type="hidden" name="busca_produto" value="<?= htmlspecialchars($termoBuscaProd) ?>">
                                    <select name="categoria" onchange="this.form.submit()" class="w-full bg-[#0f172a] border border-white/10 rounded-lg px-4 py-3 text-sm text-white">
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
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <?php if(empty($listaProdutos)): ?>
                                    <div class="col-span-3 text-center py-12 opacity-50">
                                        <i data-lucide="package" class="w-16 h-16 mx-auto text-gray-500 mb-4"></i>
                                        <p class="text-gray-400">Nenhum produto encontrado</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach($listaProdutos as $prod): 
                                        $icone='shopping-bag'; $cor='text-gray-400'; $bg='bg-white/5';
                                        if($prod['categoria']=='Suplemento'){$icone='zap';$cor='text-purple-400';$bg='bg-purple-500/10';}
                                        if($prod['categoria']=='Roupa'){$icone='shirt';$cor='text-blue-400';$bg='bg-blue-500/10';}
                                        if($prod['categoria']=='Bebida'){$icone='glass-water';$cor='text-cyan-400';$bg='bg-cyan-500/10';}
                                        if($prod['categoria']=='Equipamento'){$icone='dumbbell';$cor='text-orange-400';$bg='bg-orange-500/10';}
                                        if($prod['categoria']=='Alimento'){$icone='apple';$cor='text-green-400';$bg='bg-green-500/10';}
                                        $semEstoque = $prod['estoque'] <= 0;
                                    ?>
                                    <div class="card <?= $semEstoque ? 'opacity-50' : '' ?>">
                                        <div class="h-40 bg-gray-800 rounded-lg flex items-center justify-center relative mb-4">
                                            <i data-lucide="<?= $icone ?>" class="w-12 h-12 <?= $cor ?>"></i>
                                            <?php if($semEstoque): ?>
                                                <div class="absolute inset-0 bg-black/50 rounded-lg flex items-center justify-center">
                                                    <span class="bg-red-500 text-white text-sm font-bold px-3 py-1 rounded">ESGOTADO</span>
                                                </div>
                                            <?php elseif($prod['estoque'] < 5): ?>
                                                <span class="absolute top-2 right-2 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded">Últimas Unidades</span>
                                            <?php endif; ?>
                                        </div>
                                        <h4 class="font-bold text-white text-lg mb-1"><?= htmlspecialchars($prod['nome']) ?></h4>
                                        <p class="text-gray-400 text-sm mb-3"><?= htmlspecialchars($prod['categoria']) ?> • Estoque: <?= $prod['estoque'] ?></p>
                                        <?php if(!empty($prod['descricao'])): ?>
                                            <p class="text-gray-500 text-xs mb-3"><?= htmlspecialchars($prod['descricao']) ?></p>
                                        <?php endif; ?>
                                        <div class="flex justify-between items-center">
                                            <span class="text-lg font-bold text-tech-primary">R$ <?= number_format($prod['preco'], 2, ',', '.') ?></span>
                                            <?php if($semEstoque): ?>
                                                <button disabled class="p-2 bg-gray-700 rounded-lg text-gray-400 cursor-not-allowed">
                                                    <i data-lucide="x" class="w-4 h-4"></i>
                                                </button>
                                            <?php else: ?>
                                                <button onclick="abrirModalAdicionar(<?= $prod['id'] ?>, '<?= htmlspecialchars(addslashes($prod['nome'])) ?>', <?= $prod['estoque'] ?>)" class="p-2 bg-tech-primary rounded-lg text-white hover:bg-orange-600 transition-colors">
                                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Carrinho -->
                        <div id="view-carrinho" class="<?= $subTab == 'carrinho' ? '' : 'hidden' ?>">
                            <?php if(empty($itensCarrinho)): ?>
                                <div class="text-center py-12 opacity-50">
                                    <i data-lucide="shopping-cart" class="w-20 h-20 mx-auto text-gray-500 mb-4"></i>
                                    <h3 class="text-xl font-bold text-gray-400 mb-2">Carrinho Vazio</h3>
                                    <p class="text-gray-500 mb-6">Adicione produtos para começar suas compras!</p>
                                    <button onclick="switchSubTabLoja('produtos')" class="bg-tech-primary hover:bg-orange-600 text-white px-6 py-3 rounded-xl font-bold transition-all">
                                        Ver Produtos
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                    <div class="lg:col-span-2">
                                        <div class="card">
                                            <div class="mb-6 pb-4 border-b border-white/5">
                                                <h3 class="text-lg font-bold text-white">Seu Carrinho</h3>
                                                <p class="text-gray-400 text-sm"><?= count($itensCarrinho) ?> ite<?= count($itensCarrinho) > 1 ? 'ns' : 'm' ?></p>
                                            </div>
                                            <div class="space-y-4">
                                                <?php foreach($itensCarrinho as $item): ?>
                                                <div class="flex items-center gap-4 p-4 bg-[#0f172a] rounded-lg border border-white/5">
                                                    <div class="w-12 h-12 bg-white/5 rounded-lg flex items-center justify-center flex-shrink-0">
                                                        <i data-lucide="package" class="w-6 h-6 text-gray-400"></i>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <h4 class="font-bold text-white truncate"><?= htmlspecialchars($item['nome']) ?></h4>
                                                        <p class="text-tech-primary font-bold text-sm">R$ <?= number_format($item['preco'], 2, ',', '.') ?> cada</p>
                                                    </div>
                                                    <div class="flex items-center gap-3">
                                                        <form method="POST" class="flex items-center gap-2">
                                                            <input type="hidden" name="acao" value="atualizar_carrinho">
                                                            <input type="hidden" name="produto_id" value="<?= $item['id'] ?>">
                                                            <button type="button" onclick="atualizarQuantidade(this, -1)" class="w-8 h-8 bg-[#1e293b] border border-white/10 rounded flex items-center justify-center hover:bg-white/5 transition-colors" <?= $item['quantidade'] <= 1 ? 'disabled' : '' ?>>
                                                                <i data-lucide="minus" class="w-4 h-4"></i>
                                                            </button>
                                                            <input type="number" name="quantidade" value="<?= $item['quantidade'] ?>" min="1" max="<?= $item['estoque'] ?>" class="w-16 text-center bg-[#1e293b] border border-white/10 rounded py-1" onchange="this.form.submit()">
                                                            <button type="button" onclick="atualizarQuantidade(this, 1)" class="w-8 h-8 bg-[#1e293b] border border-white/10 rounded flex items-center justify-center hover:bg-white/5 transition-colors" <?= $item['quantidade'] >= $item['estoque'] ? 'disabled' : '' ?>>
                                                                <i data-lucide="plus" class="w-4 h-4"></i>
                                                            </button>
                                                        </form>
                                                        <form method="POST" class="inline">
                                                            <input type="hidden" name="acao" value="remover_carrinho">
                                                            <input type="hidden" name="produto_id" value="<?= $item['id'] ?>">
                                                            <button type="submit" class="p-2 text-red-400 hover:bg-red-500/20 rounded transition-colors">
                                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="text-gray-400 text-sm">Subtotal</p>
                                                        <p class="text-lg font-bold text-white">R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></p>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="lg:col-span-1">
                                        <div class="card sticky top-24">
                                            <h3 class="text-lg font-bold text-white mb-6">Resumo do Pedido</h3>
                                            <div class="space-y-3 mb-6">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-400">Subtotal</span>
                                                    <span class="text-white">R$ <?= number_format($totalCarrinho, 2, ',', '.') ?></span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-400">Frete</span>
                                                    <span class="text-green-400">Grátis</span>
                                                </div>
                                                <div class="border-t border-white/10 pt-3">
                                                    <div class="flex justify-between">
                                                        <span class="text-white font-bold">Total</span>
                                                        <span class="text-tech-primary text-xl font-bold">R$ <?= number_format($totalCarrinho, 2, ',', '.') ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <button onclick="abrirModalPagamento(<?= $totalCarrinho ?>)" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-xl shadow-lg flex items-center justify-center gap-2 transition-all">
                                                <i data-lucide="credit-card" class="w-5 h-5"></i> FINALIZAR COMPRA
                                            </button>
                                            <button onclick="switchSubTabLoja('produtos')" class="w-full mt-3 border border-white/10 text-gray-300 hover:bg-white/5 py-3 rounded-xl transition-all">
                                                Continuar Comprando
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Perfil -->
                    <div id="tab-perfil" class="tab-content hidden fade-in">
                        <div class="max-w-2xl mx-auto">
                            <div class="card">
                                <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                                    <i data-lucide="user-cog" class="w-6 h-6 text-tech-primary"></i> Editar Meus Dados
                                </h3>
                                <form method="POST" id="formPerfil">
                                    <input type="hidden" name="acao" value="editar_perfil">
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Nome Completo</label>
                                            <input type="text" name="nome" value="<?= htmlspecialchars($dadosAluno['nome']) ?>" required class="form-input">
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Email</label>
                                                <input type="email" name="email" value="<?= htmlspecialchars($dadosAluno['email']) ?>" required class="form-input">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Telefone</label>
                                                <input type="text" name="telefone" id="telefoneInput" value="<?= htmlspecialchars($dadosAluno['telefone']) ?>" oninput="mascaraTelefone(this)" maxlength="15" required class="form-input" placeholder="(00) 00000-0000">
                                            </div>
                                        </div>
                                        <div class="border-t border-white/10 pt-4">
                                            <label class="block text-xs font-bold text-tech-primary uppercase mb-2">Nova Senha (Opcional)</label>
                                            <input type="password" name="nova_senha" id="novaSenhaInput" placeholder="Min 8 caracteres, letra, número, símbolo" class="form-input">
                                            <p id="msgSenha" class="text-xs text-red-400 mt-2 hidden font-bold">A senha deve ter 8+ caracteres, letras, números e símbolo!</p>
                                        </div>
                                        <button type="submit" id="btnSalvarPerfil" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg transition-all">
                                            Salvar Alterações
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- MODAL ADICIONAR AO CARRINHO -->
    <div id="modalAdicionar" class="fixed inset-0 z-50 hidden bg-black/90 flex items-center justify-center p-4">
        <div class="bg-[#1e293b] w-full max-w-md rounded-2xl border border-white/10 shadow-2xl p-8 relative">
            <button onclick="document.getElementById('modalAdicionar').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-white p-2">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
            <h2 id="modalProdutoNome" class="text-xl font-bold mb-6 text-white"></h2>
            <form method="POST" class="space-y-6">
                <input type="hidden" name="acao" value="adicionar_carrinho">
                <input type="hidden" name="produto_id" id="modalProdutoId">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Quantidade</label>
                    <div class="flex items-center gap-4">
                        <button type="button" onclick="alterarQuantidadeModal(-1)" class="w-12 h-12 bg-[#0f172a] border border-white/10 rounded flex items-center justify-center hover:bg-white/5 transition-colors">
                            <i data-lucide="minus" class="w-6 h-6"></i>
                        </button>
                        <input type="number" name="quantidade" id="modalQuantidade" value="1" min="1" max="10" class="flex-1 text-center bg-[#0f172a] border border-white/10 rounded-xl p-4 text-white text-xl">
                        <button type="button" onclick="alterarQuantidadeModal(1)" class="w-12 h-12 bg-[#0f172a] border border-white/10 rounded flex items-center justify-center hover:bg-white/5 transition-colors">
                            <i data-lucide="plus" class="w-6 h-6"></i>
                        </button>
                    </div>
                    <p id="modalEstoqueInfo" class="text-xs text-gray-500 mt-2 text-center"></p>
                </div>
                <button type="submit" class="w-full bg-tech-primary hover:bg-orange-600 text-white font-bold py-4 rounded-xl shadow-lg flex items-center justify-center gap-2 transition-all">
                    <i data-lucide="shopping-cart" class="w-5 h-5"></i> ADICIONAR AO CARRINHO
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL PAGAMENTO -->
    <div id="modalPagamento" class="fixed inset-0 z-50 hidden bg-black/90 flex items-center justify-center p-4">
        <div class="bg-[#1e293b] w-full max-w-md rounded-2xl border border-white/10 shadow-2xl p-8 relative">
            <button onclick="document.getElementById('modalPagamento').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-white p-2">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
            <h2 class="text-xl font-bold mb-6 text-white">Finalizar Compra</h2>
            <form method="POST" class="space-y-6">
                <input type="hidden" name="acao" value="finalizar_compra">
                <div class="bg-[#0f172a] p-4 rounded-xl border border-white/10">
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-400">Valor Total:</span>
                        <span id="modalTotalValor" class="text-tech-primary text-xl font-bold"></span>
                    </div>
                    <p class="text-xs text-gray-500">Frete grátis incluso</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Forma de Pagamento</label>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 p-4 bg-[#0f172a] border border-white/10 rounded-xl cursor-pointer hover:border-tech-primary/50 transition-all">
                            <input type="radio" name="forma_pagamento" value="Cartão de Crédito" checked class="w-5 h-5 text-tech-primary">
                            <i data-lucide="credit-card" class="w-5 h-5 text-blue-400"></i>
                            <span class="flex-1">Cartão de Crédito</span>
                        </label>
                        <label class="flex items-center gap-3 p-4 bg-[#0f172a] border border-white/10 rounded-xl cursor-pointer hover:border-tech-primary/50 transition-all">
                            <input type="radio" name="forma_pagamento" value="Cartão de Débito" class="w-5 h-5 text-tech-primary">
                            <i data-lucide="credit-card" class="w-5 h-5 text-green-400"></i>
                            <span class="flex-1">Cartão de Débito</span>
                        </label>
                        <label class="flex items-center gap-3 p-4 bg-[#0f172a] border border-white/10 rounded-xl cursor-pointer hover:border-tech-primary/50 transition-all">
                            <input type="radio" name="forma_pagamento" value="PIX" class="w-5 h-5 text-tech-primary">
                            <i data-lucide="qrcode" class="w-5 h-5 text-purple-400"></i>
                            <span class="flex-1">PIX</span>
                        </label>
                    </div>
                </div>
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-xl shadow-lg flex items-center justify-center gap-2 transition-all">
                    <i data-lucide="check-circle" class="w-5 h-5"></i> CONFIRMAR PAGAMENTO
                </button>
            </form>
        </div>
    </div>

    <!-- MODAIS EXISTENTES -->
    <div id="modalConclusao" class="fixed inset-0 z-50 hidden bg-black/90 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-[#1e293b] w-full max-w-sm rounded-2xl border border-green-500/30 shadow-2xl p-8 text-center relative overflow-hidden">
            <div id="confetti-container" class="absolute inset-0 pointer-events-none"></div>
            <div class="w-16 h-16 bg-green-500/10 rounded-full flex items-center justify-center mx-auto mb-6 text-green-500">
                <i data-lucide="trophy" class="w-8 h-8"></i>
            </div>
            <h2 class="text-xl font-bold text-white mb-2">Já acabou?</h2>
            <p class="text-gray-400 mb-6">Confirmando, você registra saída e finaliza por hoje.</p>
            <div class="flex gap-3 justify-center">
                <button onclick="document.getElementById('modalConclusao').classList.add('hidden')" class="px-6 py-3 rounded-xl border border-white/10 text-gray-300 hover:bg-white/5 transition-colors">Voltar</button>
                <form method="POST">
                    <input type="hidden" name="acao_frequencia" value="saida">
                    <button type="submit" class="px-6 py-3 rounded-xl bg-green-600 hover:bg-green-700 text-white font-bold shadow-lg transition-colors">Sim, Finalizar!</button>
                </form>
            </div>
        </div>
    </div>

    <div id="modalCarteirinha" class="fixed inset-0 z-50 <?php echo $abrirQR ? '' : 'hidden'; ?> bg-black/90 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-[#1e293b] w-full max-w-sm rounded-2xl border border-white/10 shadow-2xl p-8 text-center relative">
            <button onclick="document.getElementById('modalCarteirinha').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-white p-2">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
            <h2 class="text-xl font-bold text-white mb-1">Carteirinha Digital</h2>
            <p class="text-xs text-gray-500 mb-6">ID: #<?= str_pad($idAluno, 6, '0', STR_PAD_LEFT) ?></p>
            <div class="bg-white p-4 rounded-xl inline-block shadow-glow mb-6">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=Aluno-<?= $idAluno ?>-Acesso" alt="QR Code" class="w-40 h-40">
            </div>
            <div class="border-t border-white/10 pt-6">
                <?php if($statusFrequencia == 'nao_entrou'): ?>
                    <form method="POST">
                        <input type="hidden" name="acao_frequencia" value="entrada">
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-xl shadow-lg flex items-center justify-center gap-2 transition-all">
                            <i data-lucide="log-in" class="w-5 h-5"></i> REGISTRAR ENTRADA
                        </button>
                    </form>
                <?php elseif($statusFrequencia == 'treinando'): ?>
                    <div class="mb-4 bg-green-500/10 border border-green-500/30 p-4 rounded-xl text-green-400">
                        <h4 class="font-bold mb-1">Você está treinando!</h4>
                        <p class="text-sm">Entrada: <?= date('H:i', strtotime($horaEntrada)) ?></p>
                        <p class="text-sm font-bold mt-1">Tempo: <span id="tempoCarteirinha"></span></p>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="acao_frequencia" value="saida">
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-4 rounded-xl shadow-lg flex items-center justify-center gap-2 transition-all">
                            <i data-lucide="log-out" class="w-5 h-5"></i> REGISTRAR SAÍDA
                        </button>
                    </form>
                <?php else: ?>
                    <div class="bg-red-500/10 border border-red-500/30 p-4 rounded-xl text-red-400">
                        <h4 class="font-bold mb-1">Acesso Encerrado</h4>
                        <p class="text-xs">Volte amanhã.</p>
                        <p class="text-sm mt-2">Tempo treino: <?= $tempoHoje ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Dados dos treinos
        const treinosData = <?php echo json_encode($meusTreinos); ?>;
        const horaEntrada = "<?= $horaEntrada ?>";
        const statusFrequencia = "<?= $statusFrequencia ?>";

        // Função para calcular tempo decorrido
        function calcularTempoDecorrido() {
            if (!horaEntrada || statusFrequencia !== 'treinando') return;
            
            const entrada = new Date(horaEntrada);
            const agora = new Date();
            const diffMs = agora - entrada;
            
            const horas = Math.floor(diffMs / (1000 * 60 * 60));
            const minutos = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
            const segundos = Math.floor((diffMs % (1000 * 60)) / 1000);
            
            const tempoFormatado = `${horas.toString().padStart(2, '0')}:${minutos.toString().padStart(2, '0')}:${segundos.toString().padStart(2, '0')}`;
            
            // Atualizar em todos os lugares
            const tempoTreino = document.getElementById('tempoTreino');
            const tempoDecorrido = document.getElementById('tempoDecorrido');
            const tempoCarteirinha = document.getElementById('tempoCarteirinha');
            
            if (tempoTreino) tempoTreino.textContent = tempoFormatado;
            if (tempoDecorrido) tempoDecorrido.textContent = tempoFormatado;
            if (tempoCarteirinha) tempoCarteirinha.textContent = tempoFormatado;
        }

        // Função para recriar ícones
        function recriarIcones() {
            setTimeout(() => {
                try {
                    lucide.createIcons();
                    // Garantir que todos os ícones sejam visíveis
                    document.querySelectorAll('[data-lucide]').forEach(icon => {
                        icon.style.display = 'inline-block';
                    });
                } catch (error) {
                    console.log("Recriando ícones...");
                }
            }, 50);
        }

        // Função para alternar entre abas principais
        function switchTab(tabId) {
            // Verificar se pode acessar treinos
            if (tabId === 'treinos' && statusFrequencia !== 'treinando' && statusFrequencia !== 'finalizado') {
                exibirToast("Você precisa registrar entrada para ver os treinos!", "erro");
                abrirCarteirinha();
                return;
            }
            
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
                'dashboard': 'Home',
                'treinos': 'Meus Treinos',
                'loja': 'Loja',
                'perfil': 'Meu Perfil'
            };
            const pageTitle = document.getElementById('pageTitle');
            if (pageTitle) {
                pageTitle.textContent = titulos[tabId] || 'TechFit';
            }
            
            // Se for aba de treinos, carregar treino A
            if (tabId === 'treinos' && statusFrequencia === 'treinando') {
                setTimeout(() => mudarFicha('A'), 200);
            }
            
            // Atualizar URL
            const url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            window.history.pushState({}, '', url);
            
            // Recriar ícones
            recriarIcones();
        }

        // Função para alternar sub-aba da loja
        function switchSubTabLoja(subTab) {
            const produtosView = document.getElementById('view-produtos');
            const carrinhoView = document.getElementById('view-carrinho');
            const btnProdutos = document.getElementById('btn-produtos');
            const btnCarrinho = document.getElementById('btn-carrinho');
            
            if (subTab === 'produtos') {
                produtosView.classList.remove('hidden');
                carrinhoView.classList.add('hidden');
                btnProdutos.classList.add('text-tech-primary', 'border-b-2', 'border-tech-primary');
                btnProdutos.classList.remove('text-gray-400');
                btnCarrinho.classList.add('text-gray-400');
                btnCarrinho.classList.remove('text-tech-primary', 'border-b-2', 'border-tech-primary');
            } else {
                produtosView.classList.add('hidden');
                carrinhoView.classList.remove('hidden');
                btnCarrinho.classList.add('text-tech-primary', 'border-b-2', 'border-tech-primary');
                btnCarrinho.classList.remove('text-gray-400');
                btnProdutos.classList.add('text-gray-400');
                btnProdutos.classList.remove('text-tech-primary', 'border-b-2', 'border-tech-primary');
            }
            
            // Atualizar URL
            const url = new URL(window.location);
            url.searchParams.set('sub', subTab);
            window.history.pushState({}, '', url);
            
            // Recriar ícones
            recriarIcones();
        }

        // Função para mudar a ficha de treino
        function mudarFicha(letra) {
            // Atualizar botões ativos
            ['A','B','C'].forEach(l => { 
                const btn = document.getElementById(`btn-${l}`); 
                if (l === letra) {
                    btn.className = 'pb-3 text-sm font-bold text-tech-primary border-b-2 border-tech-primary transition-all whitespace-nowrap px-4'; 
                } else {
                    btn.className = 'pb-3 text-sm font-bold text-gray-400 hover:text-white transition-all whitespace-nowrap px-4'; 
                }
            });
            
            // Limpar lista de exercícios
            const lista = document.getElementById('listaExercicios'); 
            lista.innerHTML = '';
            
            // Obter exercícios da letra selecionada
            const exercicios = treinosData[letra];
            
            if (!exercicios || exercicios.length === 0) { 
                lista.innerHTML = `
                    <div class="text-center py-12 opacity-50">
                        <i data-lucide="coffee" class="w-16 h-16 mx-auto text-gray-500 mb-4"></i>
                        <p class="text-gray-400">Descanso (Sem treino)</p>
                    </div>
                `; 
            } else {
                // Adicionar cada exercício
                exercicios.forEach((ex, i) => {
                    const html = `
                        <div class="card flex flex-col md:flex-row md:items-center justify-between gap-4 hover:border-tech-primary/30 transition-all">
                            <div class="flex items-center gap-4">
                                <label class="relative flex items-center cursor-pointer">
                                    <input type="checkbox" class="peer sr-only check-custom" onchange="toggleExercicio(this)">
                                    <div class="w-6 h-6 border-2 border-gray-500 rounded flex items-center justify-center peer-checked:bg-green-500 peer-checked:border-green-500 transition-all">
                                        <i data-lucide="check" class="w-4 h-4 text-white hidden"></i>
                                    </div>
                                </label>
                                <div>
                                    <h4 class="font-bold text-white text-lg">${ex.exercicio}</h4>
                                    <div class="flex gap-3 mt-1 text-xs text-gray-400">
                                        <span class="bg-white/5 px-2 py-1 rounded border border-white/5 flex items-center gap-1">
                                            <i data-lucide="repeat" class="w-3 h-3 text-tech-primary"></i> ${ex.series}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <button onclick="iniciarDescanso(this)" class="btn-descanso bg-[#0f172a] border border-white/10 text-gray-400 hover:text-white px-4 py-2 rounded-lg text-xs font-bold flex items-center justify-center gap-2 transition-all w-full md:w-auto">
                                <i data-lucide="timer" class="w-4 h-4"></i> 
                                <span class="timer-text">60s</span>
                            </button>
                        </div>
                    `;
                    lista.insertAdjacentHTML('beforeend', html);
                });
            }
            
            // Recriar ícones
            recriarIcones();
        }

        // Função para marcar/desmarcar exercício
        function toggleExercicio(checkbox) { 
            const exercicioDiv = checkbox.closest('.card');
            if (exercicioDiv) {
                exercicioDiv.classList.toggle('opacity-50'); 
                exercicioDiv.classList.toggle('grayscale'); 
            }
        }
        
        // Sistema de timer
        let timers = new Map();

        function iniciarDescanso(btn) {
            const span = btn.querySelector('.timer-text');
            
            // Se já está rodando, pausar
            if (btn.classList.contains('running')) {
                clearInterval(timers.get(btn));
                btn.classList.remove('running', 'bg-tech-primary', 'text-white', 'border-transparent');
                btn.classList.add('bg-[#0f172a]', 'text-gray-400');
                
                const tempoAtual = parseInt(span.innerText.replace(/\D/g, ''));
                span.innerText = `Pausado (${tempoAtual}s)`;
                return;
            }

            // Se já finalizado, não fazer nada
            if (btn.classList.contains('bg-green-500')) return;

            // Iniciar timer
            btn.classList.add('running', 'bg-tech-primary', 'text-white', 'border-transparent');
            btn.classList.remove('bg-[#0f172a]', 'text-gray-400');
            
            let tempo = parseInt(span.innerText.replace(/\D/g, '')) || 60;
            if (span.innerText.includes('Descanso')) tempo = 60;

            const interval = setInterval(() => {
                tempo--;
                span.innerText = `${tempo}s`;
                
                if (tempo <= 0) {
                    clearInterval(interval);
                    span.innerText = "FIM";
                    btn.classList.remove('bg-tech-primary', 'running');
                    btn.classList.add('bg-green-500', 'cursor-not-allowed');
                    // Vibração (se suportada)
                    if (navigator.vibrate) navigator.vibrate([200, 100, 200]);
                }
            }, 1000);
            
            timers.set(btn, interval);
        }

        // --- FUNÇÕES DO CARRINHO ---
        function abrirModalAdicionar(produtoId, produtoNome, estoque) {
            document.getElementById('modalProdutoId').value = produtoId;
            document.getElementById('modalProdutoNome').innerText = produtoNome;
            document.getElementById('modalQuantidade').value = 1;
            document.getElementById('modalQuantidade').max = Math.min(estoque, 10);
            document.getElementById('modalEstoqueInfo').innerText = estoque + " unidades disponíveis";
            document.getElementById('modalAdicionar').classList.remove('hidden');
            recriarIcones();
        }
        
        function alterarQuantidadeModal(valor) {
            const input = document.getElementById('modalQuantidade');
            const novoValor = parseInt(input.value) + valor;
            const max = parseInt(input.max);
            const min = parseInt(input.min);
            
            if (novoValor >= min && novoValor <= max) {
                input.value = novoValor;
            }
        }
        
        function abrirModalPagamento(total) {
            document.getElementById('modalTotalValor').innerText = "R$ " + total.toFixed(2).replace('.', ',');
            document.getElementById('modalPagamento').classList.remove('hidden');
            recriarIcones();
        }
        
        function atualizarQuantidade(btn, valor) {
            const form = btn.closest('form');
            const input = form.querySelector('input[name="quantidade"]');
            const novoValor = parseInt(input.value) + valor;
            const max = parseInt(input.max);
            const min = parseInt(input.min);
            
            if (novoValor >= min && novoValor <= max) {
                input.value = novoValor;
                form.submit();
            }
        }

        // --- VALIDAÇÃO DO PERFIL ---
        function mascaraTelefone(input) {
            let v = input.value.replace(/\D/g,"");
            v = v.replace(/^(\d{2})(\d)/g,"($1) $2");
            v = v.replace(/(\d)(\d{4})$/,"$1-$2");
            input.value = v;
        }

        // Configurar validação de senha
        document.addEventListener('DOMContentLoaded', function() {
            const senhaInput = document.getElementById('novaSenhaInput');
            const btnSalvar = document.getElementById('btnSalvarPerfil');
            const msgSenha = document.getElementById('msgSenha');

            if(senhaInput && btnSalvar && msgSenha) {
                senhaInput.addEventListener('input', function() {
                    const s = this.value;
                    if(s.length > 0) {
                        const forte = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/.test(s);
                        if(!forte) {
                            msgSenha.classList.remove('hidden');
                            this.classList.add('border-red-500');
                            btnSalvar.disabled = true;
                            btnSalvar.classList.add('opacity-50', 'cursor-not-allowed');
                        } else {
                            msgSenha.classList.add('hidden');
                            this.classList.remove('border-red-500');
                            this.classList.add('border-green-500');
                            btnSalvar.disabled = false;
                            btnSalvar.classList.remove('opacity-50', 'cursor-not-allowed');
                        }
                    } else {
                        msgSenha.classList.add('hidden');
                        this.classList.remove('border-red-500', 'border-green-500');
                        btnSalvar.disabled = false;
                        btnSalvar.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                });
            }
        });

        // Funções dos modais
        function abrirModalConclusao() { 
            document.getElementById('modalConclusao').classList.remove('hidden'); 
            recriarIcones();
        }
        
        function abrirCarteirinha() { 
            document.getElementById('modalCarteirinha').classList.remove('hidden'); 
            recriarIcones();
            // Atualizar tempo se estiver treinando
            if (statusFrequencia === 'treinando') {
                calcularTempoDecorrido();
            }
        }
        
        // Função para recolher/expandir sidebar
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
        
        // Função para exibir mensagens toast
        function exibirToast(msg, tipo) { 
            const div = document.createElement('div'); 
            const cor = tipo === 'erro' ? 'border-red-500 text-red-400' : 'border-green-500 text-white'; 
            div.className = `fixed top-5 right-5 z-50 bg-[#1e293b] border-l-4 ${cor} p-4 rounded-lg shadow-2xl flex items-center gap-3 animate-fadeIn`; 
            div.innerHTML = `<i data-lucide="${tipo === 'erro' ? 'alert-circle' : 'check-circle'}"></i> ${msg}`; 
            document.body.appendChild(div); 
            recriarIcones(); 
            setTimeout(() => div.remove(), 4000); 
        }
        
        // Inicializar página
        document.addEventListener('DOMContentLoaded', () => { 
            // Carregar parâmetros da URL
            const params = new URLSearchParams(window.location.search);
            const tab = params.get('tab') || 'dashboard';
            const subTab = params.get('sub') || 'produtos';
            
            // Configurar abas
            switchTab(tab);
            
            if (tab === 'loja') {
                switchSubTabLoja(subTab);
            }
            
            // Inicializar treino A (se necessário)
            if (tab === 'treinos' && statusFrequencia === 'treinando') {
                setTimeout(() => mudarFicha('A'), 200);
            }
            
            // Iniciar contador de tempo se estiver treinando
            if (statusFrequencia === 'treinando') {
                calcularTempoDecorrido();
                setInterval(calcularTempoDecorrido, 1000);
            }
            
            // Recriar ícones após carregar
            setTimeout(() => {
                recriarIcones();
            }, 300);
            
            // Exibir mensagens
            <?php if ($msgCliente): ?>
                setTimeout(() => {
                    exibirToast("<?= addslashes($msgCliente) ?>", "<?= $tipoMsg ?>");
                }, 500);
            <?php endif; ?>
            
            <?php if ($abrirQR): ?>
                setTimeout(() => {
                    abrirCarteirinha();
                }, 600);
            <?php endif; ?>
        });
    </script>
</body>
</html>