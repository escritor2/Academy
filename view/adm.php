<?php
session_start();
// --- 1. IMPORTAÇÕES ---
require_once __DIR__ . '/../Model/AlunoDAO.php';
require_once __DIR__ . '/../Model/TreinoDAO.php';
require_once __DIR__ . '/../Controller/AlunoController.php';

// --- 2. API AJAX ---
if (isset($_GET['acao_ajax'])) {
    header('Content-Type: application/json');
    if (!isset($_SESSION['admin_logado'])) { echo json_encode([]); exit; }
    $treinoDao = new TreinoDAO();

    if ($_GET['acao_ajax'] === 'buscar_treino') {
        echo json_encode($treinoDao->buscarPorAluno($_GET['id']));
    }
    if ($_GET['acao_ajax'] === 'buscar_modelo') {
        echo json_encode($treinoDao->buscarModeloPorId($_GET['id']));
    }
    exit;
}

// --- 3. SEGURANÇA ---
if (!isset($_SESSION['admin_logado']) || $_SESSION['admin_logado'] !== true) {
    header('Location: admin_login.php'); exit;
}
if (isset($_GET['sair'])) {
    session_destroy(); header('Location: admin_login.php'); exit;
}

$nomeAdmin = $_SESSION['admin_nome'] ?? 'Administrador';
$dao = new AlunoDAO();
$treinoDao = new TreinoDAO();
$msgAdm = ''; $tipoMsgAdm = '';

// --- 4. PROCESSAMENTO POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    // ALUNOS
    if ($acao === 'cadastrar_aluno') {
        try {
            $c = new AlunoController();
            $c->cadastrar($_POST['nome'], $_POST['data_nascimento'], $_POST['email'], $_POST['telefone'], $_POST['cpf'], $_POST['genero'], $_POST['senha'], 'Indefinido', $_POST['plano']);
            header("Location: adm.php?tab=alunos&msg=cad_sucesso"); exit;
        } catch (Exception $e) { $msgAdm = "Erro: " . $e->getMessage(); $tipoMsgAdm = 'erro'; }
    }
    if ($acao === 'editar_aluno') {
        try {
            $senha = !empty($_POST['nova_senha_adm']) ? $_POST['nova_senha_adm'] : null;
            $dao->atualizarDadosAdmin($_POST['id'], $_POST['nome'], $_POST['email'], $_POST['telefone'], $_POST['plano'], $_POST['objetivo'], $senha);
            header("Location: adm.php?tab=alunos&msg=edit_sucesso"); exit;
        } catch (Exception $e) { $msgAdm = "Erro: " . $e->getMessage(); $tipoMsgAdm = 'erro'; }
    }
    if ($acao === 'alterar_status') {
        $dao->atualizarStatus($_POST['id_aluno'], $_POST['novo_status']);
        $origem = $_POST['origem'] ?? 'alunos';
        $busca = !empty($_POST['busca_atual']) ? "&busca=".$_POST['busca_atual'] : "";
        header("Location: adm.php?tab=$origem$busca"); exit;
    }
    if ($acao === 'excluir_aluno') {
        $dao->excluirAluno($_POST['id_aluno']);
        header("Location: adm.php?tab=alunos&msg=del_sucesso"); exit;
    }

    // TREINOS
    if ($acao === 'salvar_treino') {
        if ($treinoDao->salvarTreino($_POST['aluno_id_treino'], $_POST['treino'] ?? [])) {
            header("Location: adm.php?tab=treinos&msg=treino_sucesso"); exit;
        }
    }

    // BIBLIOTECA
    if ($acao === 'salvar_modelo') {
        if ($treinoDao->salvarModelo($_POST['nome_modelo'], $_POST['treino'] ?? [])) {
            header("Location: adm.php?tab=treinos&sub=padrao&msg=modelo_salvo"); exit;
        }
    }
    if ($acao === 'excluir_modelo') {
        $treinoDao->excluirModelo($_POST['id_modelo']);
        header("Location: adm.php?tab=treinos&sub=padrao&msg=modelo_del"); exit;
    }
}

// --- 5. DADOS ---
$totalAlunos = $dao->contarTotal();
$totalAtivos = $dao->contarPorStatus('Ativo');
$totalInativos = $totalAlunos - $totalAtivos;
$dashAlunos = $dao->buscarRecentes(5);
$termoBusca = $_GET['busca'] ?? '';
$listaAlunos = $termoBusca ? $dao->pesquisar($termoBusca) : $dao->buscarRecentes(50);
$listaSelectTreino = $dao->buscarRecentes(100);
$listaModelos = $treinoDao->listarModelos();

// Mensagens Toast
if (isset($_GET['msg'])) {
    $m = [
        'cad_sucesso'=>'Aluno cadastrado com sucesso!', 'edit_sucesso'=>'Dados atualizados com sucesso!', 
        'del_sucesso'=>'Aluno excluído permanentemente!', 'treino_sucesso'=>'Treino salvo e atualizado!', 
        'modelo_salvo'=>'Modelo salvo na biblioteca!', 'modelo_del'=>'Modelo excluído da biblioteca!'
    ];
    if(isset($m[$_GET['msg']])) { $msgAdm = $m[$_GET['msg']]; $tipoMsgAdm = 'sucesso'; }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo - TechFit</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { tech: { 900: '#0f172a', 800: '#1e293b', 700: '#334155', primary: '#f97316' } },
                    boxShadow: { 'glow': '0 0 15px rgba(249, 115, 22, 0.3)' }
                }
            }
        }
    </script>
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .fade-in { animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .sidebar-collapsed .nav-text, .sidebar-collapsed .logo-text { display: none; }
        .sidebar-collapsed .nav-item { justify-content: center; padding: 0; }
        .sidebar-collapsed .logo-container { justify-content: center; padding-left: 0; }
        
        input, select { background-color: #0f172a !important; color: white !important; border-color: #334155 !important; }
        input:focus, select:focus { border-color: #f97316 !important; box-shadow: 0 0 0 1px #f97316 !important; }
        input::placeholder { color: #64748b !important; }
        ::-webkit-calendar-picker-indicator { filter: invert(1); cursor: pointer; opacity: 0.7; }
    </style>
</head>
<body class="bg-[#0b1120] text-gray-100 font-sans h-screen flex overflow-hidden">

    <aside id="sidebar" class="w-64 bg-[#111827] border-r border-white/5 flex flex-col justify-between hidden md:flex transition-all duration-300 relative z-20">
        <button onclick="toggleSidebar()" class="absolute -right-3 top-24 bg-tech-primary text-white p-1.5 rounded-full shadow-glow z-50 hover:bg-orange-600 transition-transform hover:scale-110"><i id="toggleIcon" data-lucide="chevron-left" class="w-3 h-3"></i></button>
        <div>
            <div class="h-20 flex items-center px-6 border-b border-white/5 logo-container">
                <div class="bg-gradient-to-br from-orange-500 to-red-600 p-2 rounded-lg shadow-lg shrink-0"><i data-lucide="dumbbell" class="w-6 h-6 text-white"></i></div>
                <span class="text-xl font-bold ml-3 logo-text tracking-wide">TECH<span class="text-tech-primary">FIT</span></span>
            </div>
            <nav class="mt-8 px-4 space-y-1.5 overflow-y-auto max-h-[calc(100vh-160px)] no-scrollbar">
                <p class="px-4 text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 nav-text">Principal</p>
                <button onclick="switchTab('dashboard')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl bg-tech-primary/10 text-tech-primary shadow-sm border border-tech-primary/20"><i data-lucide="layout-dashboard" class="w-5 h-5"></i><span class="nav-text">Dashboard</span></button>
                <button onclick="switchTab('alunos')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all"><i data-lucide="users" class="w-5 h-5"></i><span class="nav-text">Alunos</span></button>
                <button onclick="switchTab('treinos')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all"><i data-lucide="biceps-flexed" class="w-5 h-5"></i><span class="nav-text">Treinos</span></button>
                <p class="px-4 text-xs font-bold text-gray-500 uppercase tracking-wider mt-6 mb-2 nav-text">Gestão</p>
                <button onclick="switchTab('professores')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all"><i data-lucide="graduation-cap" class="w-5 h-5"></i><span class="nav-text">Professores</span></button>
                <button onclick="switchTab('financeiro')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all"><i data-lucide="dollar-sign" class="w-5 h-5"></i><span class="nav-text">Financeiro</span></button>
                <p class="px-4 text-xs font-bold text-gray-500 uppercase tracking-wider mt-6 mb-2 nav-text">Suporte</p>
                <button onclick="switchTab('recepcionista')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all"><i data-lucide="headset" class="w-5 h-5"></i><span class="nav-text">Recepção</span></button>
                <button onclick="switchTab('contato')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all"><i data-lucide="message-square" class="w-5 h-5"></i><span class="nav-text">Mensagens</span></button>
            </nav>
        </div>
        <div class="p-4 border-t border-white/5"><a href="?sair=true" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-red-400 hover:bg-red-500/10 rounded-xl transition-colors"><i data-lucide="log-out" class="w-5 h-5"></i><span class="nav-text">Sair do Sistema</span></a></div>
    </aside>

    <main class="flex-1 flex flex-col h-screen overflow-hidden relative bg-[#0b1120]">
        <header class="h-20 bg-[#111827]/80 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 z-10 sticky top-0">
            <div><h2 id="pageTitle" class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white to-gray-400">Visão Geral</h2><p class="text-xs text-gray-500 mt-0.5">Painel de Controle</p></div>
            <div class="flex items-center gap-4"><div class="text-right hidden sm:block"><p class="text-sm font-bold text-white"><?= htmlspecialchars($nomeAdmin) ?></p><span class="text-[10px] uppercase font-bold tracking-wider bg-tech-primary/20 text-tech-primary px-2 py-0.5 rounded-full">Gerente</span></div><div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-700 to-gray-900 border border-white/10 flex items-center justify-center shadow-lg"><i data-lucide="shield" class="w-5 h-5 text-tech-primary"></i></div></div>
        </header>

        <div class="flex-1 overflow-y-auto p-8 no-scrollbar relative">
            
            <div id="tab-dashboard" class="tab-content fade-in">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-[#1e293b] p-6 rounded-2xl border border-white/5 shadow-lg group"><p class="text-gray-400 text-xs font-bold uppercase mb-2">Total de Alunos</p><div class="flex justify-between items-end"><h3 class="text-3xl font-bold text-white"><?= $totalAlunos ?></h3><i data-lucide="users" class="w-8 h-8 text-tech-primary opacity-50"></i></div></div>
                    <div class="bg-[#1e293b] p-6 rounded-2xl border border-white/5 shadow-lg group"><p class="text-gray-400 text-xs font-bold uppercase mb-2">Faturamento</p><div class="flex justify-between items-end"><h3 class="text-3xl font-bold text-white">R$ <?= number_format($totalAlunos * 89, 2, ',', '.') ?></h3><i data-lucide="wallet" class="w-8 h-8 text-green-500 opacity-50"></i></div></div>
                    <div class="bg-[#1e293b] p-6 rounded-2xl border border-white/5 shadow-lg group"><p class="text-gray-400 text-xs font-bold uppercase mb-2">Alunos Ativos</p><h3 class="text-3xl font-bold text-green-400"><?= $totalAtivos ?></h3><div class="w-full bg-gray-700 h-1 mt-3 rounded-full"><div class="bg-green-500 h-full" style="width: <?= ($totalAlunos>0)?($totalAtivos/$totalAlunos)*100:0 ?>%"></div></div></div>
                    <div class="bg-[#1e293b] p-6 rounded-2xl border border-white/5 shadow-lg group"><p class="text-gray-400 text-xs font-bold uppercase mb-2">Inativos</p><h3 class="text-3xl font-bold text-red-400"><?= $totalInativos ?></h3><div class="w-full bg-gray-700 h-1 mt-3 rounded-full"><div class="bg-red-500 h-full" style="width: <?= ($totalAlunos>0)?($totalInativos/$totalAlunos)*100:0 ?>%"></div></div></div>
                </div>
                <div class="bg-[#1e293b] rounded-2xl border border-white/5 overflow-hidden shadow-xl">
                    <div class="p-6 border-b border-white/5 flex justify-between items-center bg-white/5"><h3 class="font-bold text-lg text-white flex items-center gap-2"><i data-lucide="clock" class="w-5 h-5 text-tech-primary"></i> Últimas Matrículas</h3><button onclick="switchTab('alunos')" class="text-xs font-bold text-tech-primary hover:text-white uppercase">Ver Todos</button></div>
                    <table class="w-full text-left text-sm text-gray-300"><thead class="bg-[#0f172a] uppercase text-xs font-bold text-gray-500"><tr><th class="px-6 py-4">Aluno</th><th class="px-6 py-4">Plano</th><th class="px-6 py-4">Status</th><th class="px-6 py-4 text-right">Ação</th></tr></thead><tbody class="divide-y divide-white/5">
                        <?php foreach($dashAlunos as $aluno): ?>
                        <tr class="hover:bg-white/5 transition-colors"><td class="px-6 py-4 font-medium text-white"><?= $aluno['nome'] ?></td><td class="px-6 py-4"><span class="px-2.5 py-1 rounded-md text-xs font-bold bg-white/5 border border-white/10 text-white"><?= $aluno['plano'] ?></span></td><td class="px-6 py-4"><span class="px-2.5 py-1 rounded-full text-xs font-bold <?= $aluno['status'] == 'Ativo' ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20' ?>"><?= $aluno['status'] ?></span></td><td class="px-6 py-4 text-right"><form method="POST" class="inline"><input type="hidden" name="acao" value="alterar_status"><input type="hidden" name="id_aluno" value="<?= $aluno['id'] ?>"><input type="hidden" name="origem" value="dashboard"><button type="submit" name="novo_status" value="<?= $aluno['status'] == 'Ativo' ? 'Inativo' : 'Ativo' ?>" class="p-2 rounded-lg hover:bg-white/10 transition-colors"><i data-lucide="<?= $aluno['status'] == 'Ativo' ? 'lock' : 'unlock' ?>" class="w-4 h-4 <?= $aluno['status'] == 'Ativo' ? 'text-red-400' : 'text-green-400' ?>"></i></button></form></td></tr>
                        <?php endforeach; ?>
                    </tbody></table>
                </div>
            </div>

            <div id="tab-alunos" class="tab-content hidden fade-in">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-[#1e293b] p-5 rounded-xl border border-white/5 flex items-center justify-between shadow-lg"><div><p class="text-gray-400 text-xs font-bold uppercase">Total Cadastrados</p><h3 class="text-2xl font-bold text-white"><?= $totalAlunos ?></h3></div><div class="w-10 h-10 bg-tech-primary/10 rounded-full flex items-center justify-center text-tech-primary"><i data-lucide="users" class="w-5 h-5"></i></div></div>
                    <div class="bg-[#1e293b] p-5 rounded-xl border border-white/5 flex items-center justify-between shadow-lg"><div><p class="text-gray-400 text-xs font-bold uppercase">Alunos Ativos</p><h3 class="text-2xl font-bold text-green-400"><?= $totalAtivos ?></h3></div><div class="w-10 h-10 bg-green-500/10 rounded-full flex items-center justify-center text-green-500"><i data-lucide="check-circle" class="w-5 h-5"></i></div></div>
                    <div class="bg-[#1e293b] p-5 rounded-xl border border-white/5 flex items-center justify-between shadow-lg"><div><p class="text-gray-400 text-xs font-bold uppercase">Inativos</p><h3 class="text-2xl font-bold text-red-400"><?= $totalInativos ?></h3></div><div class="w-10 h-10 bg-red-500/10 rounded-full flex items-center justify-center text-red-500"><i data-lucide="x-circle" class="w-5 h-5"></i></div></div>
                </div>

                <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                    <div class="relative w-full md:w-96"><form method="GET"><input type="hidden" name="tab" value="alunos"><input type="text" name="busca" value="<?= htmlspecialchars($termoBusca) ?>" placeholder="Buscar aluno por nome ou CPF..." class="w-full bg-[#1e293b] border border-white/10 rounded-xl pl-12 pr-4 py-3 focus:border-tech-primary outline-none text-sm text-white shadow-lg"><button type="submit" class="absolute left-4 top-3 text-gray-500 hover:text-white"><i data-lucide="search" class="w-5 h-5"></i></button></form></div>
                    <button onclick="abrirModalAluno()" class="bg-gradient-to-r from-tech-primary to-orange-600 hover:to-orange-500 text-white px-6 py-3 rounded-xl font-bold text-sm flex items-center gap-2 shadow-glow transition-all transform hover:scale-105"><i data-lucide="user-plus" class="w-5 h-5"></i> Novo Aluno</button>
                </div>

                <div class="bg-[#1e293b] rounded-2xl border border-white/5 overflow-hidden shadow-xl">
                    <table class="w-full text-left text-sm text-gray-300"><thead class="bg-[#0f172a] uppercase text-xs font-bold text-gray-500"><tr><th class="px-6 py-4">Aluno</th><th class="px-6 py-4">Desde</th><th class="px-6 py-4">Plano</th><th class="px-6 py-4">Status</th><th class="px-6 py-4 text-right">Ações</th></tr></thead><tbody class="divide-y divide-white/5">
                        <?php foreach($listaAlunos as $aluno): ?>
                        <tr class="hover:bg-white/5 transition-colors group">
                            <td class="px-6 py-4"><div class="flex items-center gap-3"><div class="w-10 h-10 rounded-full bg-tech-primary/10 flex items-center justify-center text-tech-primary font-bold"><?= strtoupper(substr($aluno['nome'], 0, 1)) ?></div><div><p class="font-bold text-white"><?= $aluno['nome'] ?></p><p class="text-xs text-gray-500"><?= $aluno['email'] ?></p></div></div></td>
                            <td class="px-6 py-4 font-mono text-gray-400"><?= date('d/m/y', strtotime($aluno['criado_em'] ?? 'now')) ?></td>
                            <td class="px-6 py-4"><span class="px-2.5 py-1 rounded-md text-xs font-bold bg-white/5 border border-white/10 text-white"><?= $aluno['plano'] ?></span></td>
                            <td class="px-6 py-4"><span class="px-2.5 py-1 rounded-full text-xs font-bold <?= $aluno['status'] == 'Ativo' ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20' ?>"><?= $aluno['status'] ?></span></td>
                            <td class="px-6 py-4 text-right"><div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity"><button onclick='abrirModalEditar(<?= json_encode($aluno) ?>)' class="p-2 hover:bg-blue-500/20 text-blue-400 rounded-lg transition-colors"><i data-lucide="pencil" class="w-4 h-4"></i></button><form method="POST" class="inline"><input type="hidden" name="acao" value="alterar_status"><input type="hidden" name="id_aluno" value="<?= $aluno['id'] ?>"><input type="hidden" name="origem" value="alunos"><input type="hidden" name="busca_atual" value="<?= htmlspecialchars($termoBusca) ?>"><button type="submit" name="novo_status" value="<?= $aluno['status'] == 'Ativo' ? 'Inativo' : 'Ativo' ?>" class="p-2 hover:bg-white/10 rounded-lg transition-colors"><i data-lucide="<?= $aluno['status'] == 'Ativo' ? 'lock' : 'unlock' ?>" class="w-4 h-4 <?= $aluno['status'] == 'Ativo' ? 'text-red-400' : 'text-green-400' ?>"></i></button></form><button onclick="abrirModalExcluir(<?= $aluno['id'] ?>)" class="p-2 hover:bg-red-500/20 text-red-400 rounded-lg transition-colors"><i data-lucide="trash-2" class="w-4 h-4"></i></button></div></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody></table>
                </div>
            </div>

            <div id="tab-treinos" class="tab-content hidden fade-in">
                <div class="flex gap-6 mb-8 border-b border-white/10 pb-1">
                    <button onclick="switchSubTab('individual')" id="btn-individual" class="pb-3 text-sm font-bold text-tech-primary border-b-2 border-tech-primary transition-all flex items-center gap-2"><i data-lucide="user" class="w-4 h-4"></i> Aluno Específico</button>
                    <button onclick="switchSubTab('padrao')" id="btn-padrao" class="pb-3 text-sm font-bold text-gray-400 hover:text-white transition-all flex items-center gap-2"><i data-lucide="library" class="w-4 h-4"></i> Biblioteca de Modelos</button>
                </div>

                <div id="view-individual">
                    <form method="POST" id="formTreino"><input type="hidden" name="acao" value="salvar_treino">
                        <div class="flex flex-col xl:flex-row justify-between items-end gap-6 mb-8 bg-[#1e293b] p-6 rounded-2xl border border-white/5 shadow-lg">
                            <div class="w-full xl:w-1/2"><label class="block text-xs font-bold text-gray-400 uppercase mb-2 tracking-wider">Selecione o Aluno</label><div class="relative"><select name="aluno_id_treino" onchange="carregarTreino(this.value, 'aluno')" class="w-full bg-[#0f172a] border border-white/10 rounded-xl p-4 pl-12 cursor-pointer shadow-inner"><option value="">-- Escolha na lista --</option><?php foreach($listaSelectTreino as $a): ?><option value="<?= $a['id'] ?>"><?= $a['nome'] ?></option><?php endforeach; ?></select><i data-lucide="search" class="absolute left-4 top-4 w-5 h-5 text-gray-500 pointer-events-none"></i></div></div>
                            <div class="flex gap-3 w-full xl:w-auto"><button type="button" onclick="gerarTreinoAutomatico()" class="flex-1 xl:flex-none bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-xl font-bold flex items-center justify-center gap-2 shadow-lg shadow-purple-900/30 transition-all"><i data-lucide="wand-2" class="w-5 h-5"></i> Mágica Automática</button><button type="submit" class="flex-1 xl:flex-none bg-tech-primary hover:bg-orange-600 text-white px-8 py-3 rounded-xl font-bold flex items-center justify-center gap-2 shadow-glow transition-all"><i data-lucide="save" class="w-5 h-5"></i> Salvar Treino</button></div>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <?php foreach(['A','B','C'] as $div): ?>
                            <div class="bg-[#1e293b] rounded-2xl border border-white/5 flex flex-col h-full shadow-lg"><div class="p-4 bg-white/5 border-b border-white/5 flex justify-between items-center"><h3 class="font-bold text-white flex items-center gap-3"><span class="bg-tech-primary text-white w-8 h-8 rounded-lg flex items-center justify-center font-bold shadow-lg"><?= $div ?></span> Treino</h3><button type="button" onclick="addExercicio('container-<?= $div ?>')" class="p-1.5 bg-white/10 text-gray-300 rounded-lg hover:bg-white/20 hover:text-white"><i data-lucide="plus" class="w-4 h-4"></i></button></div><div id="container-<?= $div ?>" class="p-4 space-y-3 flex-1 min-h-[300px]"></div></div>
                            <?php endforeach; ?>
                        </div>
                    </form>
                </div>

                <div id="view-padrao" class="hidden">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="md:col-span-1 bg-[#1e293b] rounded-2xl border border-white/5 p-4 h-fit max-h-[600px] overflow-y-auto no-scrollbar">
                            <h3 class="text-xs font-bold text-gray-400 uppercase mb-4 tracking-wider">Modelos Disponíveis</h3>
                            <ul class="space-y-2">
                                <?php if(empty($listaModelos)): ?>
                                    <li class="text-sm text-gray-600 text-center py-4">Nenhum modelo salvo.</li>
                                <?php else: ?>
                                    <?php foreach($listaModelos as $mod): ?>
                                    <li class="flex justify-between items-center bg-[#0f172a] p-3 rounded-lg hover:bg-white/5 group transition-colors border border-transparent hover:border-white/10"><button onclick="carregarTreino(<?= $mod['id'] ?>, 'modelo')" class="text-sm text-gray-300 hover:text-white flex-1 text-left font-medium"><?= $mod['nome'] ?></button><form method="POST" onsubmit="return confirm('Apagar este modelo?');"><input type="hidden" name="acao" value="excluir_modelo"><input type="hidden" name="id_modelo" value="<?= $mod['id'] ?>"><button type="submit" class="text-gray-600 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity p-1"><i data-lucide="trash-2" class="w-4 h-4"></i></button></form></li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <div class="md:col-span-3">
                            <form method="POST" id="formModelo">
                                <input type="hidden" name="acao" value="salvar_modelo">
                                <div class="flex flex-col md:flex-row justify-between items-end gap-4 mb-6 bg-[#1e293b] p-6 rounded-2xl border border-white/5 shadow-lg">
                                    <div class="w-full md:w-1/2"><label class="block text-xs font-bold text-gray-400 uppercase mb-2">Nome do Modelo</label><input type="text" name="nome_modelo" required placeholder="Ex: Hipertrofia Avançado" class="w-full bg-[#0f172a] border border-white/10 rounded-xl p-4 outline-none focus:border-tech-primary text-white"></div>
                                    
                                    <div class="flex gap-3">
                                        <button type="button" onclick="gerarModeloAutomatico()" class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-4 rounded-xl font-bold flex gap-2 shadow-lg shadow-purple-900/30 transition-all">
                                            <i data-lucide="wand-2" class="w-5 h-5"></i> Mágica
                                        </button>
                                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-4 rounded-xl font-bold flex gap-2 shadow-lg transition-all"><i data-lucide="save" class="w-5 h-5"></i> Salvar</button>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 opacity-80 hover:opacity-100 transition-opacity">
                                    <?php foreach(['A','B','C'] as $div): ?>
                                    <div class="bg-[#1e293b] rounded-2xl border border-dashed border-white/20 flex flex-col h-full"><div class="p-4 border-b border-white/5 flex justify-between items-center"><h3 class="font-bold text-gray-300">Treino <?= $div ?></h3><button type="button" onclick="addExercicio('modelo-<?= $div ?>')" class="text-blue-400 text-xs font-bold hover:text-blue-300">+ ITEM</button></div><div id="modelo-<?= $div ?>" class="p-4 space-y-2 min-h-[150px]"><p class="text-xs text-gray-600 text-center mt-8 italic">Vazio...</p></div></div>
                                    <?php endforeach; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div id="tab-professores" class="tab-content hidden fade-in"><div class="bg-[#1e293b] p-20 rounded-2xl border border-white/5 text-center"><i data-lucide="graduation-cap" class="w-16 h-16 text-gray-600 mx-auto mb-4"></i><h3 class="text-xl font-bold text-white">Professores</h3><p class="text-gray-500">Em breve.</p></div></div>
            <div id="tab-recepcionista" class="tab-content hidden fade-in"><div class="bg-[#1e293b] p-20 rounded-2xl border border-white/5 text-center"><i data-lucide="headset" class="w-16 h-16 text-gray-600 mx-auto mb-4"></i><h3 class="text-xl font-bold text-white">Recepção</h3><p class="text-gray-500">Em breve.</p></div></div>
            <div id="tab-contato" class="tab-content hidden fade-in"><div class="bg-[#1e293b] p-20 rounded-2xl border border-white/5 text-center"><i data-lucide="message-square" class="w-16 h-16 text-gray-600 mx-auto mb-4"></i><h3 class="text-xl font-bold text-white">Mensagens</h3><p class="text-gray-500">Em breve.</p></div></div>
            <div id="tab-financeiro" class="tab-content hidden fade-in"><div class="bg-[#1e293b] p-20 rounded-2xl border border-white/5 text-center"><i data-lucide="dollar-sign" class="w-16 h-16 text-gray-600 mx-auto mb-4"></i><h3 class="text-xl font-bold text-white">Financeiro</h3><p class="text-gray-500">Em breve.</p></div></div>
        </div>
    </main>

    <div id="modalEditar" class="fixed inset-0 z-50 hidden bg-black/90 backdrop-blur-md flex items-center justify-center p-4"><div class="bg-[#1e293b] w-full max-w-2xl rounded-2xl border border-white/10 shadow-2xl p-8 relative"><button onclick="document.getElementById('modalEditar').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-white"><i data-lucide="x"></i></button><h2 class="text-2xl font-bold mb-8 flex items-center gap-3 text-white"><i data-lucide="pencil" class="w-6 h-6 text-tech-primary"></i> Editar Aluno</h2><form method="POST" class="space-y-5"><input type="hidden" name="acao" value="editar_aluno"><input type="hidden" name="id" id="edit_id"><div class="grid grid-cols-2 gap-5"><div><label class="block text-xs font-bold text-gray-400 uppercase mb-1.5">Nome</label><input type="text" name="nome" id="edit_nome" required class="w-full bg-[#0f172a] border border-white/10 rounded-xl p-3.5 outline-none focus:border-tech-primary text-white"></div><div><label class="block text-xs font-bold text-gray-400 uppercase mb-1.5">CPF</label><input type="text" id="edit_cpf" disabled class="w-full bg-[#0f172a]/50 border border-white/5 rounded-xl p-3.5 text-gray-500 cursor-not-allowed"></div></div><div class="grid grid-cols-2 gap-5"><div><label class="block text-xs font-bold text-gray-400 uppercase mb-1.5">Email</label><input type="email" name="email" id="edit_email" required class="w-full bg-[#0f172a] border border-white/10 rounded-xl p-3.5 outline-none focus:border-tech-primary text-white"></div><div><label class="block text-xs font-bold text-gray-400 uppercase mb-1.5">Telefone</label><input type="text" name="telefone" id="edit_telefone" required class="w-full bg-[#0f172a] border border-white/10 rounded-xl p-3.5 outline-none focus:border-tech-primary text-white"></div></div><div class="grid grid-cols-2 gap-5"><div><label class="block text-xs font-bold text-gray-400 uppercase mb-1.5">Plano</label><select name="plano" id="edit_plano" class="w-full bg-[#0f172a] border border-white/10 rounded-xl p-3.5 outline-none focus:border-tech-primary text-white"><option value="Start">Start</option><option value="Pro">Pro</option><option value="Black">Black</option></select></div><div><label class="block text-xs font-bold text-gray-400 uppercase mb-1.5">Objetivo</label><input type="text" name="objetivo" id="edit_objetivo" class="w-full bg-[#0f172a] border border-white/10 rounded-xl p-3.5 outline-none focus:border-tech-primary text-white"></div></div><div class="border-t border-white/10 pt-5 mt-2"><label class="block text-xs font-bold text-tech-primary uppercase mb-2 flex items-center gap-2"><i data-lucide="key" class="w-4 h-4"></i> Redefinir Senha</label><input type="text" name="nova_senha_adm" placeholder="Nova senha (opcional)" class="w-full bg-[#0f172a] border border-white/10 rounded-xl p-3.5 outline-none focus:border-tech-primary placeholder-gray-600 text-white"></div><div class="pt-6 flex justify-end gap-3"><button type="button" onclick="document.getElementById('modalEditar').classList.add('hidden')" class="px-6 py-3 rounded-xl text-gray-400 hover:text-white font-medium">Cancelar</button><button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-blue-900/30">Salvar</button></div></form></div></div>
    <div id="modalAluno" class="fixed inset-0 z-50 hidden bg-black/90 backdrop-blur-md flex items-center justify-center p-4"><div class="bg-[#1e293b] w-full max-w-2xl rounded-2xl border border-white/10 shadow-2xl p-8 relative"><button onclick="fecharModalAluno()" class="absolute top-4 right-4 text-gray-400 hover:text-white"><i data-lucide="x"></i></button><h2 class="text-2xl font-bold mb-8 flex items-center gap-3 text-white"><i data-lucide="user-plus" class="w-6 h-6 text-tech-primary"></i> Novo Aluno</h2><form method="POST" class="space-y-5"><input type="hidden" name="acao" value="cadastrar_aluno"><div class="grid grid-cols-2 gap-5"><div><label class="block text-xs font-bold text-gray-400 uppercase mb-1.5">Nome</label><input type="text" name="nome" required class="w-full bg-[#0f172a] border border-white/10 rounded-xl p-3.5 outline-none focus:border-tech-primary text-white"></div><div><label class="block text-xs font-bold text-gray-400 uppercase mb-1.5">CPF</label><input type="text" name="cpf" required class="w-full bg-[#0f172a] border border-white/10 rounded-xl p-3.5 outline-none focus:border-tech-primary text-white"></div></div><div class="grid grid-cols-2 gap-5"><div><label class="block text-xs font-bold text-gray-400 uppercase mb-1.5">Email</label><input type="email" name="email" required class="w-full bg-[#0f172a] border border-white/10 rounded-xl p-3.5 outline-none focus:border-tech-primary text-white"></div><div><label class="block text-xs font-bold text-gray-400 uppercase mb-1.5">Telefone</label><input type="text" name="telefone" required class="w-full bg-[#0f172a] border border-white/10 rounded-xl p-3.5 outline-none focus:border-tech-primary text-white"></div></div><div class="grid grid-cols-3 gap-5"><div><label class="block text-xs font-bold text-gray-400 uppercase mb-1.5">Nascimento</label><input type="date" name="data_nascimento" required class="w-full bg-[#0f172a] border border-white/10 rounded-xl p-3.5 outline-none focus:border-tech-primary text-white"></div><div><label class="block text-xs font-bold text-gray-400 uppercase mb-1.5">Gênero</label><select name="genero" class="w-full bg-[#0f172a] border border-white/10 rounded-xl p-3.5 outline-none focus:border-tech-primary text-white"><option value="masculino">Masculino</option><option value="feminino">Feminino</option></select></div><div><label class="block text-xs font-bold text-gray-400 uppercase mb-1.5">Plano</label><select name="plano" class="w-full bg-[#0f172a] border border-white/10 rounded-xl p-3.5 outline-none focus:border-tech-primary text-white"><option value="Start">Start</option><option value="Pro">Pro</option><option value="Black">Black</option></select></div></div><div><label class="block text-xs font-bold text-gray-400 uppercase mb-1.5">Senha Provisória</label><input type="text" name="senha" value="techfit123" required class="w-full bg-[#0f172a] border border-white/10 rounded-xl p-3.5 outline-none focus:border-tech-primary text-tech-primary font-mono"></div><div class="pt-6 flex justify-end gap-3"><button type="button" onclick="fecharModalAluno()" class="px-6 py-3 rounded-xl text-gray-400 hover:text-white font-medium">Cancelar</button><button type="submit" class="bg-tech-primary hover:bg-orange-600 text-white px-8 py-3 rounded-xl font-bold shadow-glow transition-all">Confirmar</button></div></form></div></div>
    <div id="modalExcluir" class="fixed inset-0 z-50 hidden bg-black/90 backdrop-blur-md flex items-center justify-center p-4"><div class="bg-[#1e293b] w-full max-w-md rounded-2xl border border-red-500/30 shadow-2xl p-8 text-center relative"><div class="w-20 h-20 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-6 text-red-500 border border-red-500/20"><i data-lucide="alert-triangle" class="w-10 h-10"></i></div><h2 class="text-2xl font-bold text-white mb-2">Excluir Aluno?</h2><p class="text-gray-400 mb-8 leading-relaxed">Esta ação removerá permanentemente o acesso e histórico.</p><form method="POST" class="flex gap-4 justify-center"><input type="hidden" name="acao" value="excluir_aluno"><input type="hidden" name="id_aluno" id="id_exclusao"><button type="button" onclick="document.getElementById('modalExcluir').classList.add('hidden')" class="w-full py-3.5 rounded-xl border border-white/10 text-gray-300 hover:bg-white/5 transition-colors font-bold">Cancelar</button><button type="submit" class="w-full py-3.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold shadow-lg shadow-red-900/30 transition-colors">Sim, Excluir</button></form></div></div>

    <script>
        lucide.createIcons();
        function abrirModalAluno() { document.getElementById('modalAluno').classList.remove('hidden'); }
        function fecharModalAluno() { document.getElementById('modalAluno').classList.add('hidden'); }
        function abrirModalEditar(aluno) { document.getElementById('edit_id').value = aluno.id; document.getElementById('edit_nome').value = aluno.nome; document.getElementById('edit_cpf').value = aluno.cpf; document.getElementById('edit_email').value = aluno.email; document.getElementById('edit_telefone').value = aluno.telefone; document.getElementById('edit_plano').value = aluno.plano; document.getElementById('edit_objetivo').value = aluno.objetivo || ''; document.getElementById('modalEditar').classList.remove('hidden'); }
        function abrirModalExcluir(id) { document.getElementById('id_exclusao').value = id; document.getElementById('modalExcluir').classList.remove('hidden'); }

        function switchSubTab(subTab) {
            document.getElementById('view-individual').classList.add('hidden'); document.getElementById('view-padrao').classList.add('hidden');
            document.getElementById('btn-individual').className = 'pb-3 text-sm font-bold text-gray-400 hover:text-white transition-all flex items-center gap-2';
            document.getElementById('btn-padrao').className = 'pb-3 text-sm font-bold text-gray-400 hover:text-white transition-all flex items-center gap-2';
            document.getElementById('view-' + subTab).classList.remove('hidden');
            document.getElementById('btn-' + subTab).className = 'pb-3 text-sm font-bold text-tech-primary border-b-2 border-tech-primary transition-all flex items-center gap-2';
        }

        async function carregarTreino(id, tipo) {
            if(!id) return;
            const prefixo = tipo === 'modelo' ? 'modelo-' : 'container-';
            ['A','B','C'].forEach(d => document.getElementById(prefixo+d).innerHTML = '');
            try {
                const acao = tipo === 'modelo' ? 'buscar_modelo' : 'buscar_treino';
                const response = await fetch(`adm.php?acao_ajax=${acao}&id=${id}`);
                const data = await response.json();
                ['A','B','C'].forEach(div => {
                    if(data[div] && data[div].length > 0) {
                        data[div].forEach(t => addExercicio(prefixo + div, t.exercicio, t.series));
                    }
                });
                if(tipo==='modelo') document.querySelector('input[name="nome_modelo"]').value = '';
                exibirToast(tipo === 'modelo' ? "Modelo carregado!" : "Treino carregado!", "sucesso");
            } catch (error) { console.error(error); }
        }

        function addExercicio(containerId, nome = '', series = '3x12') {
            const container = document.getElementById(containerId);
            if(container.querySelector('p')) container.querySelector('p').remove();
            const cleanId = containerId.replace('container-', '').replace('modelo-', ''); 
            const div = document.createElement('div');
            div.className = 'flex gap-2 items-center animate-fadeIn group mb-2';
            div.innerHTML = `<div class="grid grid-cols-[1fr_80px] gap-2 w-full"><input type="text" name="treino[${cleanId}][][nome]" value="${nome}" placeholder="Exercício" class="bg-[#0f172a] border border-white/10 rounded-lg p-2.5 text-sm text-white focus:border-tech-primary outline-none"><input type="text" name="treino[${cleanId}][][series]" value="${series}" placeholder="Reps" class="bg-[#0f172a] border border-white/10 rounded-lg p-2.5 text-sm text-center text-gray-400 focus:border-tech-primary outline-none"></div><button type="button" onclick="this.parentElement.remove()" class="text-gray-600 hover:text-red-500 transition-colors p-1"><i data-lucide="trash-2" class="w-4 h-4"></i></button>`;
            container.appendChild(div);
            lucide.createIcons();
        }

        // --- FUNÇÃO PARA MÁGICA AUTOMÁTICA NOS MODELOS ---
        function gerarModeloAutomatico() {
            ['A','B','C'].forEach(d => document.getElementById('modelo-'+d).innerHTML = '');
            const tA = [{n:'Supino Reto',s:'4x10'},{n:'Supino Inclinado',s:'3x12'},{n:'Tríceps Corda',s:'4x12'}];
            const tB = [{n:'Puxada Alta',s:'4x10'},{n:'Remada Baixa',s:'3x12'},{n:'Rosca Direta',s:'3x12'}];
            const tC = [{n:'Agachamento',s:'4x10'},{n:'Leg Press',s:'3x12'},{n:'Extensora',s:'3x15'}];
            tA.forEach(e => addExercicio('modelo-A', e.n, e.s)); 
            tB.forEach(e => addExercicio('modelo-B', e.n, e.s)); 
            tC.forEach(e => addExercicio('modelo-C', e.n, e.s));
            exibirToast("Modelo padrão gerado!", "sucesso");
        }

        function gerarTreinoAutomatico() {
            ['A','B','C'].forEach(d => document.getElementById('container-'+d).innerHTML = '');
            const tA = [{n:'Supino Reto',s:'4x10'},{n:'Supino Inclinado',s:'3x12'},{n:'Tríceps Corda',s:'4x12'}];
            const tB = [{n:'Puxada Alta',s:'4x10'},{n:'Remada Baixa',s:'3x12'},{n:'Rosca Direta',s:'3x12'}];
            const tC = [{n:'Agachamento',s:'4x10'},{n:'Leg Press',s:'3x12'},{n:'Extensora',s:'3x15'}];
            tA.forEach(e => addExercicio('container-A', e.n, e.s)); 
            tB.forEach(e => addExercicio('container-B', e.n, e.s)); 
            tC.forEach(e => addExercicio('container-C', e.n, e.s));
            exibirToast("Treino padrão gerado!", "sucesso");
        }

        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.nav-item').forEach(el => { el.classList.remove('bg-tech-primary/10', 'text-tech-primary', 'border-tech-primary/20'); el.classList.add('text-gray-400', 'hover:bg-white/5', 'hover:text-white'); });
            document.getElementById('tab-' + tabId).classList.remove('hidden');
            const activeBtn = document.querySelector(`button[onclick="switchTab('${tabId}')"]`);
            if (activeBtn) { activeBtn.classList.remove('text-gray-400', 'hover:bg-white/5', 'hover:text-white'); activeBtn.classList.add('bg-tech-primary/10', 'text-tech-primary', 'border-tech-primary/20'); }
            document.getElementById('pageTitle').textContent = tabId.charAt(0).toUpperCase() + tabId.slice(1);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');
            if(tabParam) {
                switchTab(tabParam);
                if(urlParams.get('sub')) switchSubTab(urlParams.get('sub'));
                window.history.replaceState({}, document.title, window.location.pathname);
            } else { switchTab('dashboard'); }
        });

        function exibirToast(mensagem, tipo = 'erro') {
            const toast = document.createElement('div');
            let cores = tipo === 'sucesso' ? 'bg-[#1e293b] border-l-4 border-green-500 text-white shadow-glow' : 'bg-[#1e293b] border-l-4 border-red-500 text-white';
            toast.className = `${cores} fixed top-5 right-5 z-50 px-6 py-4 rounded-xl shadow-2xl flex items-center gap-4 min-w-[300px] transform transition-all duration-500 translate-x-full border border-white/10`;
            toast.innerHTML = `<div class="${tipo==='sucesso'?'bg-green-500/20':'bg-red-500/20'} p-2 rounded-full"><i data-lucide="${tipo==='sucesso'?'check-circle':'alert-circle'}" class="w-5 h-5 ${tipo==='sucesso'?'text-green-500':'text-red-500'}"></i></div><div><h4 class="font-bold text-sm">${tipo==='sucesso'?'Sucesso':'Atenção'}</h4><p class="text-xs text-gray-400">${mensagem}</p></div>`;
            document.body.appendChild(toast);
            lucide.createIcons();
            requestAnimationFrame(() => toast.classList.remove('translate-x-full'));
            setTimeout(() => { toast.classList.add('translate-x-full', 'opacity-0'); setTimeout(() => toast.remove(), 500); }, 4000);
        }
        <?php if ($msgAdm): ?>exibirToast("<?= $msgAdm ?>", "<?= $tipoMsgAdm ?>");<?php endif; ?>

        let isSidebarOpen = true;
        function toggleSidebar() {
            const sb = document.getElementById('sidebar');
            const icon = document.getElementById('toggleIcon');
            if(isSidebarOpen) { sb.classList.remove('w-64'); sb.classList.add('w-20', 'sidebar-collapsed'); icon.setAttribute('data-lucide', 'chevron-right'); }
            else { sb.classList.remove('w-20', 'sidebar-collapsed'); sb.classList.add('w-64'); icon.setAttribute('data-lucide', 'chevron-left'); }
            isSidebarOpen = !isSidebarOpen;
            lucide.createIcons();
        }
    </script>
</body>
</html>