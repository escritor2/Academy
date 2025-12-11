<?php
// Exibir erros para debug (remova em produção)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// --- SEGURANÇA ---
// Verifica se passou pela verificação de segurança
if (!isset($_SESSION['recepcionista_logado']) || $_SESSION['recepcionista_logado'] !== true) {
    header('Location: recepcionista_verificacao.php'); 
    exit;
}

// --- INCLUDES ---
require_once __DIR__ . '/../Model/AlunoDAO.php';
require_once __DIR__ . '/../Model/Aluno.php';
require_once __DIR__ . '/../Model/ProdutoDAO.php';
require_once __DIR__ . '/../Model/VendaDAO.php';

// Instanciar DAOs
$alunoDao = new AlunoDAO();
$produtoDao = new ProdutoDAO();
$vendaDao = new VendaDAO();

$msgSistema = '';
$tipoMsg = '';

// --- PROCESSAMENTO POST (Formulários) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Cadastrar Aluno
    if (isset($_POST['acao']) && $_POST['acao'] === 'cadastrar_aluno') {
        try {
            $senha = !empty($_POST['senha']) ? $_POST['senha'] : 'TechFit123!';
            $novoAluno = new Aluno(
                $_POST['nome'],
                $_POST['data_nascimento'],
                $_POST['email'],
                $_POST['telefone'],
                $_POST['cpf'],
                $_POST['genero'],
                $senha,
                $_POST['objetivo'],
                $_POST['plano']
            );
            $alunoDao->criarAluno($novoAluno);
            $msgSistema = "Aluno cadastrado com sucesso!";
            $tipoMsg = 'sucesso';
        } catch (Exception $e) {
            $msgSistema = "Erro: " . $e->getMessage();
            $tipoMsg = 'erro';
        }
    }

    // 2. Cadastrar Produto
    if (isset($_POST['acao']) && $_POST['acao'] === 'cadastrar_produto') {
        try {
            $produtoDao->cadastrar(
                $_POST['nome'],
                $_POST['preco'],
                $_POST['estoque'],
                $_POST['categoria'],
                $_POST['descricao']
            );
            $msgSistema = "Produto adicionado ao catálogo!";
            $tipoMsg = 'sucesso';
        } catch (Exception $e) {
            $msgSistema = "Erro: " . $e->getMessage();
            $tipoMsg = 'erro';
        }
    }
}

// --- DADOS PARA AS VIEWS ---
$totalAlunos = $alunoDao->contarTotal();
$totalAtivos = $alunoDao->contarPorStatus('Ativo');
$listaAlunos = $alunoDao->buscarRecentes(50); // Lista para a tabela estilo ADM

// Financeiro
$faturamentoTotal = 0;
$listaVendas = [];
try {
    $faturamentoTotal = $vendaDao->getFaturamentoTotal();
    $listaVendas = $vendaDao->buscarRecentes(20);
} catch(Exception $e) { /* Tabela pode não existir ainda */ }

$listaProdutos = $produtoDao->listar();

// Cálculos Rápidos
$totalInativos = $totalAlunos - $totalAtivos;
$vendasHoje = 0;
$hoje = date('Y-m-d');
foreach($listaVendas as $v) {
    if(isset($v['data_venda']) && substr($v['data_venda'], 0, 10) == $hoje) $vendasHoje++;
}
?>

<!DOCTYPE html>
<html lang="pt-br" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icons/halter.png">
    <title>Painel Recepção | TechFit</title>
    
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { 
                extend: { 
                    colors: { 
                        tech: { 
                            primary: '#ea580c', // Laranja TechFit (Igual Professor/Adm)
                            hover: '#c2410c',   
                            dark: '#0f172a',    // Slate 900 (Fundo Professor)
                            card: '#1e293b',    // Slate 800 (Cards Professor)
                            muted: '#94a3b8',   // Slate 400
                            border: '#334155'   // Slate 700
                        } 
                    }
                } 
            }
        }
    </script>
    <style>
        body { background-color: #0f172a; color: #f8fafc; font-family: 'Inter', sans-serif; }
        
        /* Animações */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in { animation: fadeIn 0.4s ease-out forwards; }
        
        /* Sidebar */
        .sidebar { transition: width 0.3s ease; }
        .sidebar-collapsed { width: 5rem; }
        .sidebar-expanded { width: 16rem; }
        
        .sidebar-collapsed .nav-text, 
        .sidebar-collapsed .logo-text,
        .sidebar-collapsed .group-label { display: none; }
        
        .sidebar-collapsed .nav-item { justify-content: center; padding-left: 0; padding-right: 0; }
        
        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #ea580c; }
        
        .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05); }
    </style>
</head>
<body class="flex h-screen overflow-hidden bg-tech-dark text-slate-50 selection:bg-orange-500/30">

    <aside id="sidebar" class="sidebar sidebar-expanded bg-tech-card border-r border-tech-border flex flex-col z-30 shadow-2xl relative">
        <div class="h-16 flex items-center justify-between px-4 border-b border-tech-border bg-gray-900/20">
            <div class="flex items-center gap-3 overflow-hidden whitespace-nowrap">
                <div class="w-8 h-8 min-w-[2rem] bg-gradient-to-br from-orange-500 to-red-600 rounded-lg flex items-center justify-center text-white font-bold shadow-lg">TF</div>
                <span class="font-bold text-lg tracking-wider logo-text text-white">TECHFIT</span>
            </div>
            <button onclick="toggleSidebar()" class="p-1.5 rounded-lg text-gray-400 hover:text-white hover:bg-gray-700/50 transition-colors">
                <i data-lucide="chevrons-left" id="toggleIcon" class="w-5 h-5"></i>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto py-6 space-y-1">
            <div class="px-6 mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider group-label">Principal</div>
            
            <button onclick="switchTab('dashboard')" id="btn-dashboard" class="nav-item w-full flex items-center px-6 py-3 text-gray-400 hover:text-white border-l-4 border-transparent hover:bg-white/5 transition-all active-nav">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <span class="ml-3 font-medium nav-text whitespace-nowrap">Dashboard</span>
            </button>

            <div class="my-4 border-t border-tech-border mx-6"></div>
            <div class="px-6 mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider group-label">Gestão</div>

            <button onclick="switchTab('alunos')" id="btn-alunos" class="nav-item w-full flex items-center px-6 py-3 text-gray-400 hover:text-white border-l-4 border-transparent hover:bg-white/5 transition-all">
                <i data-lucide="users" class="w-5 h-5"></i>
                <span class="ml-3 font-medium nav-text whitespace-nowrap">Alunos</span>
            </button>

            <button onclick="switchTab('financeiro')" id="btn-financeiro" class="nav-item w-full flex items-center px-6 py-3 text-gray-400 hover:text-white border-l-4 border-transparent hover:bg-white/5 transition-all">
                <i data-lucide="wallet" class="w-5 h-5"></i>
                <span class="ml-3 font-medium nav-text whitespace-nowrap">Financeiro</span>
            </button>

            <div class="my-4 border-t border-tech-border mx-6"></div>
            <div class="px-6 mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider group-label">Loja</div>

            <button onclick="switchTab('loja')" id="btn-loja" class="nav-item w-full flex items-center px-6 py-3 text-gray-400 hover:text-white border-l-4 border-transparent hover:bg-white/5 transition-all">
                <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                <span class="ml-3 font-medium nav-text whitespace-nowrap">Catálogo</span>
            </button>
        </nav>

        <div class="p-4 border-t border-tech-border bg-gray-900/50">
            <div class="flex items-center gap-3 overflow-hidden">
                <div class="w-9 h-9 rounded-full bg-slate-700 flex items-center justify-center border border-slate-600"><i data-lucide="user"></i></div>
                <div class="nav-text whitespace-nowrap">
                    <p class="text-sm font-medium text-white"><?= $_SESSION['usuario_nome'] ?? 'Recepcionista' ?></p>
                    <a href="index.php?sair=true" class="text-xs text-red-400 hover:text-red-300">Sair</a>
                </div>
            </div>
        </div>
    </aside>

    <main class="flex-1 h-full overflow-y-auto relative bg-tech-dark p-6 md:p-8 scroll-smooth">
        
        <?php if ($msgSistema): ?>
        <div id="toast" class="fixed top-6 right-6 z-50 flex items-center w-full max-w-xs p-4 rounded-xl shadow-2xl border border-tech-border bg-tech-card animate-fade-in">
            <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg <?= $tipoMsg == 'sucesso' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' ?>">
                <i data-lucide="<?= $tipoMsg == 'sucesso' ? 'check' : 'alert-circle' ?>" class="w-5 h-5"></i>
            </div>
            <div class="ml-3 text-sm font-medium text-white"><?= $msgSistema ?></div>
            <button onclick="document.getElementById('toast').remove()" class="ml-auto text-gray-400 hover:text-white"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        <script>setTimeout(() => { const t = document.getElementById('toast'); if(t) t.remove(); }, 4000);</script>
        <?php endif; ?>

        <section id="tab-dashboard" class="space-y-8 animate-fade-in">
            <header class="flex justify-between items-end">
                <div>
                    <h1 class="text-3xl font-bold text-white tracking-tight">Dashboard</h1>
                    <p class="text-tech-muted mt-1">Visão geral do dia.</p>
                </div>
                <div class="text-sm font-medium text-tech-muted bg-tech-card px-4 py-2 rounded-full border border-tech-border">
                    <?= date('d \d\e F, Y') ?>
                </div>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-tech-card rounded-2xl p-6 border border-tech-border shadow-lg group hover:border-tech-primary/30 transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-tech-muted text-sm font-medium">Total Alunos</p>
                            <h3 class="text-3xl font-bold text-white mt-2"><?= $totalAlunos ?></h3>
                        </div>
                        <div class="p-3 bg-blue-500/10 rounded-xl text-blue-500"><i data-lucide="users" class="w-6 h-6"></i></div>
                    </div>
                </div>
                
                <div class="bg-tech-card rounded-2xl p-6 border border-tech-border shadow-lg group hover:border-green-500/30 transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-tech-muted text-sm font-medium">Ativos</p>
                            <h3 class="text-3xl font-bold text-green-500 mt-2"><?= $totalAtivos ?></h3>
                        </div>
                        <div class="p-3 bg-green-500/10 rounded-xl text-green-500"><i data-lucide="activity" class="w-6 h-6"></i></div>
                    </div>
                </div>

                <div class="bg-tech-card rounded-2xl p-6 border border-tech-border shadow-lg group hover:border-orange-500/30 transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-tech-muted text-sm font-medium">Faturamento</p>
                            <h3 class="text-3xl font-bold text-white mt-2">R$ <?= number_format($faturamentoTotal, 2, ',', '.') ?></h3>
                        </div>
                        <div class="p-3 bg-orange-500/10 rounded-xl text-orange-500"><i data-lucide="dollar-sign" class="w-6 h-6"></i></div>
                    </div>
                </div>

                <div class="bg-tech-card rounded-2xl p-6 border border-tech-border shadow-lg group hover:border-purple-500/30 transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-tech-muted text-sm font-medium">Vendas Hoje</p>
                            <h3 class="text-3xl font-bold text-purple-400 mt-2"><?= $vendasHoje ?></h3>
                        </div>
                        <div class="p-3 bg-purple-500/10 rounded-xl text-purple-500"><i data-lucide="shopping-cart" class="w-6 h-6"></i></div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-tech-card p-6 rounded-2xl border border-tech-border shadow-lg">
                    <h3 class="text-lg font-bold text-white mb-6">Financeiro Recente</h3>
                    <div class="h-64 w-full relative"><canvas id="chartFinanceiro"></canvas></div>
                </div>
                <div class="bg-tech-card p-6 rounded-2xl border border-tech-border shadow-lg">
                    <h3 class="text-lg font-bold text-white mb-6">Status dos Alunos</h3>
                    <div class="h-64 w-full relative flex justify-center"><canvas id="chartAlunos"></canvas></div>
                </div>
            </div>
        </section>

        <section id="tab-alunos" class="hidden space-y-6 animate-fade-in">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-white">Gerenciar Alunos</h2>
                <button onclick="toggleModal('modal-aluno')" class="bg-tech-primary hover:bg-tech-hover text-white px-4 py-2 rounded-lg flex items-center gap-2 font-medium shadow-lg shadow-orange-500/20 transition-all">
                    <i data-lucide="plus"></i> Novo Aluno
                </button>
            </div>
            
            <div class="bg-tech-card rounded-xl border border-tech-border overflow-hidden shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-900/50 border-b border-tech-border text-xs uppercase text-slate-400 tracking-wider">
                                <th class="p-4 font-semibold">Aluno</th>
                                <th class="p-4 font-semibold">Contato</th>
                                <th class="p-4 font-semibold">Plano</th>
                                <th class="p-4 font-semibold">Status</th>
                                <th class="p-4 font-semibold text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-tech-border text-sm">
                            <?php foreach($listaAlunos as $aluno): ?>
                            <tr class="hover:bg-slate-700/30 transition-colors group">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-slate-700 flex items-center justify-center text-tech-primary font-bold border border-slate-600">
                                            <?= strtoupper(substr($aluno['nome'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="font-medium text-white"><?= $aluno['nome'] ?></div>
                                            <div class="text-xs text-slate-500">CPF: <?= $aluno['cpf'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 text-slate-400">
                                    <div><?= $aluno['email'] ?></div>
                                    <div class="text-xs text-slate-600"><?= $aluno['telefone'] ?></div>
                                </td>
                                <td class="p-4">
                                    <span class="px-2 py-1 bg-blue-500/10 text-blue-400 rounded text-xs font-medium border border-blue-500/20"><?= $aluno['plano'] ?></span>
                                </td>
                                <td class="p-4">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold <?= $aluno['status'] == 'Ativo' ? 'bg-green-500/10 text-green-500 border border-green-500/20' : 'bg-red-500/10 text-red-500 border border-red-500/20' ?>">
                                        <span class="w-1.5 h-1.5 rounded-full <?= $aluno['status'] == 'Ativo' ? 'bg-green-500' : 'bg-red-500' ?>"></span>
                                        <?= $aluno['status'] ?>
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <button class="text-slate-400 hover:text-tech-primary p-2 hover:bg-slate-700 rounded transition-colors"><i data-lucide="edit-3" class="w-4 h-4"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section id="tab-financeiro" class="hidden space-y-6 animate-fade-in">
            <h2 class="text-2xl font-bold text-white">Histórico Financeiro</h2>
            
            <div class="bg-tech-card rounded-xl border border-tech-border overflow-hidden shadow-xl">
                <div class="p-4 border-b border-tech-border bg-slate-900/30 flex justify-between items-center">
                    <h3 class="font-bold text-white text-sm uppercase tracking-wider">Últimas Transações</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-slate-400 border-b border-tech-border bg-slate-900/50 uppercase text-xs">
                                <th class="p-4 font-semibold">Data</th>
                                <th class="p-4 font-semibold">Cliente</th>
                                <th class="p-4 font-semibold">Produto / Serviço</th>
                                <th class="p-4 font-semibold">Valor Total</th>
                                <th class="p-4 font-semibold text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-tech-border text-slate-300">
                            <?php foreach($listaVendas as $venda): ?>
                            <tr class="hover:bg-slate-700/30 transition-colors">
                                <td class="p-4 text-slate-400 font-mono text-xs"><?= date('d/m/y H:i', strtotime($venda['data_venda'])) ?></td>
                                <td class="p-4 font-medium text-white"><?= $venda['nome_cliente'] ?></td>
                                <td class="p-4">
                                    <?= $venda['nome_produto'] ?>
                                    <span class="text-xs bg-slate-800 px-1.5 py-0.5 rounded text-slate-500 ml-1">x<?= $venda['quantidade'] ?></span>
                                </td>
                                <td class="p-4 text-green-400 font-bold tracking-wide">R$ <?= number_format($venda['valor_total'], 2, ',', '.') ?></td>
                                <td class="p-4 text-right">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-green-500/10 text-green-500 border border-green-500/20">
                                        <i data-lucide="check" class="w-3 h-3"></i> Pago
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section id="tab-loja" class="hidden space-y-6 animate-fade-in">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-white">Catálogo de Produtos</h2>
                    <p class="text-tech-muted text-sm">Gerencie o estoque e realize vendas.</p>
                </div>
                <button onclick="toggleModal('modal-produto')" class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 border border-slate-600 transition-all hover:border-slate-500">
                    <i data-lucide="plus"></i> Novo Produto
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                <?php foreach($listaProdutos as $prod): ?>
                <div class="bg-tech-card border border-tech-border rounded-xl p-5 hover:border-tech-primary/50 transition-all group flex flex-col justify-between h-full shadow-lg">
                    <div>
                        <div class="flex justify-between items-start mb-3">
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded bg-slate-900 text-slate-400 border border-slate-700"><?= $prod['categoria'] ?></span>
                            <span class="text-xs font-mono font-medium <?= $prod['estoque']>0?'text-green-400':'text-red-400' ?>"><?= $prod['estoque'] ?> un</span>
                        </div>
                        <h3 class="font-bold text-white text-lg leading-tight mb-2 group-hover:text-tech-primary transition-colors"><?= $prod['nome'] ?></h3>
                        <p class="text-xs text-slate-500 line-clamp-2 mb-4"><?= $prod['descricao'] ?></p>
                    </div>
                    
                    <div class="flex items-center justify-between border-t border-tech-border pt-4 mt-auto">
                        <span class="text-xl font-bold text-white">R$ <?= number_format($prod['preco'], 2, ',', '.') ?></span>
                        
                        <button onclick="abrirModalEmBreve('<?= $prod['nome'] ?>')" class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-tech-primary/10 hover:bg-tech-primary text-tech-primary hover:text-white transition-all text-sm font-medium">
                            <i data-lucide="shopping-cart" class="w-4 h-4"></i> Vender
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

    </main>

    <div id="modal-em-breve" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-tech-card w-full max-w-sm rounded-2xl border border-tech-border p-6 shadow-2xl animate-fade-in text-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-orange-500 to-red-600"></div>
            
            <div class="w-16 h-16 bg-orange-500/10 rounded-full flex items-center justify-center mx-auto mb-4 text-tech-primary">
                <i data-lucide="clock" class="w-8 h-8"></i>
            </div>
            
            <h3 class="text-xl font-bold text-white mb-2">Funcionalidade em Breve!</h3>
            <p class="text-slate-400 text-sm mb-6">
                A venda do produto <span id="nome-produto-aviso" class="text-white font-semibold"></span> estará disponível na próxima atualização do sistema TechFit.
            </p>
            
            <button onclick="document.getElementById('modal-em-breve').classList.add('hidden')" class="w-full bg-slate-700 hover:bg-slate-600 text-white font-medium py-2.5 rounded-lg transition-colors">
                Entendido
            </button>
        </div>
    </div>

    <div id="modal-aluno" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-tech-card w-full max-w-2xl rounded-2xl border border-tech-border p-6 shadow-2xl animate-fade-in max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6 border-b border-tech-border pb-4">
                <h3 class="text-xl font-bold text-white flex items-center gap-2"><i data-lucide="user-plus" class="text-tech-primary"></i> Novo Aluno</h3>
                <button onclick="toggleModal('modal-aluno')" class="text-slate-400 hover:text-white"><i data-lucide="x"></i></button>
            </div>
            <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="hidden" name="acao" value="cadastrar_aluno">
                <div class="col-span-2 md:col-span-1 space-y-1">
                    <label class="text-xs text-slate-400 uppercase font-bold">Nome</label>
                    <input type="text" name="nome" required class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-white focus:border-tech-primary focus:outline-none transition-colors">
                </div>
                <div class="col-span-2 md:col-span-1 space-y-1">
                    <label class="text-xs text-slate-400 uppercase font-bold">CPF</label>
                    <input type="text" name="cpf" required class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-white focus:border-tech-primary focus:outline-none transition-colors">
                </div>
                <div class="space-y-1">
                    <label class="text-xs text-slate-400 uppercase font-bold">Email</label>
                    <input type="email" name="email" required class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-white focus:border-tech-primary focus:outline-none transition-colors">
                </div>
                <div class="space-y-1">
                    <label class="text-xs text-slate-400 uppercase font-bold">Telefone</label>
                    <input type="text" name="telefone" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-white focus:border-tech-primary focus:outline-none transition-colors">
                </div>
                <div class="space-y-1">
                    <label class="text-xs text-slate-400 uppercase font-bold">Nascimento</label>
                    <input type="date" name="data_nascimento" required class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-white focus:border-tech-primary focus:outline-none transition-colors">
                </div>
                <div class="space-y-1">
                    <label class="text-xs text-slate-400 uppercase font-bold">Gênero</label>
                    <select name="genero" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-white focus:border-tech-primary focus:outline-none">
                        <option>Masculino</option><option>Feminino</option><option>Outro</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-xs text-slate-400 uppercase font-bold">Plano</label>
                    <select name="plano" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-white focus:border-tech-primary focus:outline-none">
                        <option>Mensal</option><option>Trimestral</option><option>Anual</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-xs text-slate-400 uppercase font-bold">Objetivo</label>
                    <select name="objetivo" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-white focus:border-tech-primary focus:outline-none">
                        <option>Emagrecimento</option><option>Hipertrofia</option><option>Saúde</option>
                    </select>
                </div>
                <div class="col-span-2 flex justify-end gap-3 mt-4 pt-4 border-t border-tech-border">
                    <button type="button" onclick="toggleModal('modal-aluno')" class="px-4 py-2 text-slate-400 hover:text-white">Cancelar</button>
                    <button type="submit" class="bg-tech-primary hover:bg-orange-600 text-white px-6 py-2 rounded-lg font-bold shadow-lg shadow-orange-500/20 transition-all">Salvar Aluno</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-produto" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-tech-card w-full max-w-lg rounded-2xl border border-tech-border p-6 shadow-2xl animate-fade-in">
            <div class="flex justify-between items-center mb-6 border-b border-tech-border pb-4">
                <h3 class="text-xl font-bold text-white flex items-center gap-2"><i data-lucide="package-plus" class="text-tech-primary"></i> Novo Produto</h3>
                <button onclick="toggleModal('modal-produto')" class="text-slate-400 hover:text-white"><i data-lucide="x"></i></button>
            </div>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="acao" value="cadastrar_produto">
                <div class="space-y-1">
                    <label class="text-xs text-slate-400 uppercase font-bold">Nome</label>
                    <input type="text" name="nome" required class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-white focus:border-tech-primary focus:outline-none transition-colors">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs text-slate-400 uppercase font-bold">Preço</label>
                        <input type="number" step="0.01" name="preco" required class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-white focus:border-tech-primary focus:outline-none transition-colors">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs text-slate-400 uppercase font-bold">Estoque</label>
                        <input type="number" name="estoque" required class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-white focus:border-tech-primary focus:outline-none transition-colors">
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="text-xs text-slate-400 uppercase font-bold">Categoria</label>
                    <select name="categoria" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-white focus:border-tech-primary focus:outline-none">
                        <option>Suplemento</option><option>Bebida</option><option>Roupa</option><option>Acessório</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-xs text-slate-400 uppercase font-bold">Descrição</label>
                    <textarea name="descricao" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-white focus:border-tech-primary focus:outline-none transition-colors"></textarea>
                </div>
                <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-tech-border">
                    <button type="button" onclick="toggleModal('modal-produto')" class="px-4 py-2 text-slate-400 hover:text-white">Cancelar</button>
                    <button type="submit" class="bg-tech-primary hover:bg-orange-600 text-white px-6 py-2 rounded-lg font-bold shadow-lg shadow-orange-500/20 transition-all">Salvar Produto</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();

        // Alternar Sidebar
        function toggleSidebar() {
            const sb = document.getElementById('sidebar');
            const icon = document.getElementById('toggleIcon');
            if (sb.classList.contains('sidebar-expanded')) {
                sb.classList.replace('sidebar-expanded', 'sidebar-collapsed');
                icon.setAttribute('data-lucide', 'chevrons-right');
            } else {
                sb.classList.replace('sidebar-collapsed', 'sidebar-expanded');
                icon.setAttribute('data-lucide', 'chevrons-left');
            }
            lucide.createIcons();
        }

        // Navegação de Abas
        function switchTab(id) {
            document.querySelectorAll('section[id^="tab-"]').forEach(el => el.classList.add('hidden'));
            document.getElementById('tab-' + id).classList.remove('hidden');
            
            // Resetar botões
            document.querySelectorAll('.nav-item').forEach(el => {
                el.classList.remove('text-white', 'border-tech-primary', 'bg-white/5');
                el.classList.add('text-gray-400', 'border-transparent');
            });
            
            // Ativar botão atual
            const btn = document.getElementById('btn-' + id);
            btn.classList.add('text-white', 'border-tech-primary', 'bg-white/5');
            btn.classList.remove('text-gray-400', 'border-transparent');
        }

        // Modais Gerais
        function toggleModal(id) {
            document.getElementById(id).classList.toggle('hidden');
        }

        // Modal "Em Breve" Específico
        function abrirModalEmBreve(nomeProduto) {
            document.getElementById('nome-produto-aviso').innerText = nomeProduto;
            document.getElementById('modal-em-breve').classList.remove('hidden');
        }

        // Inicialização dos Gráficos
        document.addEventListener('DOMContentLoaded', () => {
            switchTab('dashboard'); // Aba padrão

            try {
                // Configuração Global ChartJS
                Chart.defaults.color = '#94a3b8';
                Chart.defaults.borderColor = '#334155';

                // Dados Mockados de exemplo baseados no PHP (seguro contra falhas)
                const vendasData = <?= json_encode(!empty($listaVendas) ? array_slice($listaVendas, 0, 10) : []) ?>;
                const labels = vendasData.map(v => new Date(v.data_venda).toLocaleDateString().slice(0,5)).reverse();
                const dadosValores = vendasData.map(v => parseFloat(v.valor_total)).reverse();

                // 1. Gráfico Financeiro (Barra)
                new Chart(document.getElementById('chartFinanceiro'), {
                    type: 'bar',
                    data: {
                        labels: labels.length ? labels : ['Sem dados'],
                        datasets: [{
                            label: 'Vendas (R$)',
                            data: dadosValores.length ? dadosValores : [0],
                            backgroundColor: '#ea580c', // Tech Primary
                            borderRadius: 4,
                            barThickness: 20
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { 
                            y: { grid: { color: '#334155' } },
                            x: { grid: { display: false } }
                        }
                    }
                });

                // 2. Gráfico Alunos (Doughnut)
                new Chart(document.getElementById('chartAlunos'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Ativos', 'Inativos'],
                        datasets: [{
                            data: [<?= $totalAtivos ?>, <?= $totalInativos ?>],
                            backgroundColor: ['#22c55e', '#334155'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '75%',
                        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } } }
                    }
                });
            } catch (error) { console.log('Erro gráficos:', error); }
        });
    </script>
</body>
</html>