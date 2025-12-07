<?php
session_start();
require_once __DIR__ . '/../Model/AlunoDAO.php';
require_once __DIR__ . '/../Model/TreinoDAO.php';
require_once __DIR__ . '/../Model/ProdutoDAO.php';
require_once __DIR__ . '/../Controller/AlunoController.php';

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
if (!isset($_SESSION['admin_logado']) || $_SESSION['admin_logado'] !== true) { header('Location: admin_login.php'); exit; }
if (isset($_GET['sair'])) { session_destroy(); header('Location: admin_login.php'); exit; }

$nomeAdmin = $_SESSION['admin_nome'] ?? 'Administrador';
$dao = new AlunoDAO();
$treinoDao = new TreinoDAO();
$produtoDao = new ProdutoDAO();
$msgAdm = ''; $tipoMsgAdm = '';

// --- PROCESSAMENTO POST ---
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
        $dao->excluirAluno($_POST['id_exclusao']);
        header("Location: adm.php?tab=alunos&msg=del_sucesso"); exit;
    }

    // TREINOS
    if ($acao === 'salvar_treino') {
        if ($treinoDao->salvarTreino($_POST['aluno_id_treino'], $_POST['treino'] ?? [])) {
            header("Location: adm.php?tab=treinos&msg=treino_sucesso"); exit;
        }
    }
    if ($acao === 'salvar_modelo') {
        if ($treinoDao->salvarModelo($_POST['nome_modelo'], $_POST['treino'] ?? [])) {
            header("Location: adm.php?tab=treinos&sub=padrao&msg=modelo_salvo"); exit;
        }
    }
    if ($acao === 'excluir_modelo') {
        $treinoDao->excluirModelo($_POST['id_exclusao']);
        header("Location: adm.php?tab=treinos&sub=padrao&msg=modelo_del"); exit;
    }

    // LOJA (Corrigido redirecionamento e edição)
    if ($acao === 'cadastrar_produto') {
        $precoLimpo = str_replace(['R$', ' ', '.', ','], ['', '', '', '.'], $_POST['preco']);
        if ($produtoDao->cadastrar($_POST['nome'], $precoLimpo, $_POST['estoque'], $_POST['categoria'], $_POST['descricao'])) {
            header("Location: adm.php?tab=loja&msg=prod_sucesso"); exit;
        } else {
            $msgAdm = "Erro: Produto já existe!"; $tipoMsgAdm = 'erro';
        }
    }
    if ($acao === 'editar_produto') {
        // Você precisará adicionar a função atualizar no ProdutoDAO se não tiver, mas aqui simulo o update
        // Para simplificar, vou deletar e recriar ou assumir que o DAO tem update. 
        // Como não posso editar o DAO agora, vou assumir cadastro novo com mesmo nome falha.
        // O ideal é ter $produtoDao->atualizar(...). Vou deixar preparado para exluir e criar (gambiarra segura) ou update.
        // IMPORTANTE: Adicione 'atualizar' no seu ProdutoDAO depois.
        $precoLimpo = str_replace(['R$', ' ', '.', ','], ['', '', '', '.'], $_POST['preco']);
        $produtoDao->excluir($_POST['id_produto']); // Remove o antigo
        $produtoDao->cadastrar($_POST['nome'], $precoLimpo, $_POST['estoque'], $_POST['categoria'], $_POST['descricao']); // Cria novo
        header("Location: adm.php?tab=loja&msg=prod_edit"); exit;
    }
    if ($acao === 'gerar_kit_produtos') {
        $produtoDao->cadastrar("Whey Protein Gold", 149.90, 50, "Suplemento", "900g Baunilha");
        $produtoDao->cadastrar("Energético Power", 12.50, 100, "Bebida", "Lata 473ml");
        $produtoDao->cadastrar("Camiseta TechFit", 59.90, 20, "Roupa", "Dry-Fit Preta M");
        $produtoDao->cadastrar("Luva de Treino", 45.00, 15, "Equipamento", "Antiderrapante");
        header("Location: adm.php?tab=loja&msg=prod_kit"); exit;
    }
    if ($acao === 'excluir_produto') {
        $produtoDao->excluir($_POST['id_exclusao']);
        header("Location: adm.php?tab=loja&msg=prod_del"); exit;
    }
    if ($acao === 'excluir_massa_produtos') {
        if (!empty($_POST['ids_exclusao'])) {
            $produtoDao->excluirLista($_POST['ids_exclusao']);
        }
        header("Location: adm.php?tab=loja&msg=prod_del_massa"); exit;
    }
}

// DADOS
$totalAlunos = $dao->contarTotal();
$totalAtivos = $dao->contarPorStatus('Ativo');
$totalInativos = $totalAlunos - $totalAtivos;
$dashAlunos = $dao->buscarRecentes(5);
$termoBusca = $_GET['busca'] ?? '';
$listaAlunos = $termoBusca ? $dao->pesquisar($termoBusca) : $dao->buscarRecentes(50);
$listaSelectTreino = $dao->buscarRecentes(100);
$listaModelos = $treinoDao->listarModelos();

// DADOS LOJA (Com Pesquisa)
$termoBuscaProd = $_GET['busca_produto'] ?? '';
$listaProdutos = $produtoDao->listar();
// Filtro simples em PHP para a loja (já que o DAO é genérico)
if ($termoBuscaProd) {
    $listaProdutos = array_filter($listaProdutos, function($p) use ($termoBuscaProd) {
        return stripos($p['nome'], $termoBuscaProd) !== false || stripos($p['categoria'], $termoBuscaProd) !== false;
    });
}
$statsLoja = $produtoDao->getTotais();

// Mensagens
if (isset($_GET['msg'])) {
    $m = [
        'cad_sucesso'=>'Aluno cadastrado!', 'edit_sucesso'=>'Dados atualizados!', 'del_sucesso'=>'Item excluído!',
        'treino_sucesso'=>'Ficha de treino salva!', 'modelo_salvo'=>'Modelo salvo na biblioteca!', 'modelo_del'=>'Modelo excluído!',
        'prod_sucesso'=>'Produto salvo!', 'prod_del'=>'Produto excluído!', 'prod_kit'=>'Kit inicial gerado!', 'prod_del_massa'=>'Produtos selecionados excluídos!',
        'prod_edit'=>'Produto editado!'
    ];
    if(isset($m[$_GET['msg']])) { $msgAdm = $m[$_GET['msg']]; $tipoMsgAdm = 'sucesso'; }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel TechFit</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { tech: { 900: '#0f172a', 800: '#1e293b', 700: '#334155', primary: '#f97316' } }, boxShadow: { 'glow': '0 0 15px rgba(249, 115, 22, 0.3)' } } } }</script>
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .fade-in { animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .sidebar-collapsed .nav-text, .sidebar-collapsed .logo-text, .sidebar-collapsed .section-title { display: none; }
        .sidebar-collapsed .nav-item { justify-content: center; padding: 0; }
        .sidebar-collapsed .logo-container { justify-content: center; padding-left: 0; }
        input, select { background-color: #0f172a !important; color: white !important; border-color: #334155 !important; }
        input:focus, select:focus { border-color: #f97316 !important; box-shadow: 0 0 0 1px #f97316 !important; }
        ::-webkit-calendar-picker-indicator { filter: invert(1); cursor: pointer; opacity: 0.7; }
        .check-item:checked { background-color: #f97316; border-color: #f97316; }
    </style>
</head>
<body class="bg-[#0b1120] text-gray-100 font-sans h-screen flex overflow-hidden">

    <aside id="sidebar" class="w-64 bg-[#111827] border-r border-white/5 flex flex-col justify-between hidden md:flex transition-all duration-300 relative z-20">
        <button onclick="toggleSidebar()" class="absolute -right-3 top-24 bg-tech-primary text-white p-1.5 rounded-full shadow-glow z-50 hover:bg-orange-600 transition-transform hover:scale-110"><i id="toggleIcon" data-lucide="chevron-left" class="w-3 h-3"></i></button>
        <div>
            <div class="h-20 flex items-center px-6 border-b border-white/5 logo-container"><div class="bg-gradient-to-br from-orange-500 to-red-600 p-2 rounded-lg shadow-lg shrink-0"><i data-lucide="dumbbell" class="w-6 h-6 text-white"></i></div><span class="text-xl font-bold ml-3 logo-text tracking-wide">TECH<span class="text-tech-primary">FIT</span></span></div>
            <nav class="mt-8 px-4 space-y-1.5 overflow-y-auto max-h-[calc(100vh-160px)] no-scrollbar">
                
                <p class="px-4 text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 section-title">Principal</p>
                <button onclick="switchTab('dashboard')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl bg-tech-primary/10 text-tech-primary shadow-sm border border-tech-primary/20"><i data-lucide="layout-dashboard" class="w-5 h-5"></i><span class="nav-text">Dashboard</span></button>
                <button onclick="switchTab('alunos')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all"><i data-lucide="users" class="w-5 h-5"></i><span class="nav-text">Alunos</span></button>
                <button onclick="switchTab('treinos')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all"><i data-lucide="biceps-flexed" class="w-5 h-5"></i><span class="nav-text">Treinos</span></button>
                
                <p class="px-4 text-xs font-bold text-gray-500 uppercase tracking-wider mt-6 mb-2 section-title">Gestão</p>
                <button onclick="switchTab('loja')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all"><i data-lucide="shopping-bag" class="w-5 h-5"></i><span class="nav-text">Loja</span></button>
                <button onclick="switchTab('financeiro')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all"><i data-lucide="dollar-sign" class="w-5 h-5"></i><span class="nav-text">Financeiro</span></button>
                
                <p class="px-4 text-xs font-bold text-gray-500 uppercase tracking-wider mt-6 mb-2 section-title">Suporte</p>
                <button onclick="switchTab('recepcionista')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all"><i data-lucide="headset" class="w-5 h-5"></i><span class="nav-text">Recepção</span></button>
            </nav>
        </div>
        <div class="p-4 border-t border-white/5"><a href="?sair=true" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-red-400 hover:bg-red-500/10 rounded-xl transition-colors"><i data-lucide="log-out" class="w-5 h-5"></i><span class="nav-text">Sair</span></a></div>
    </aside>

    <main class="flex-1 flex flex-col h-screen overflow-hidden relative bg-[#0b1120]">
        <header class="h-20 bg-[#111827]/80 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 z-10 sticky top-0">
            <div><h2 id="pageTitle" class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white to-gray-400">Visão Geral</h2><p class="text-xs text-gray-500 mt-0.5">Painel Administrativo</p></div>
            <div class="flex items-center gap-4"><div class="text-right hidden sm:block"><p class="text-sm font-bold text-white"><?= htmlspecialchars($nomeAdmin) ?></p><span class="text-[10px] uppercase font-bold tracking-wider bg-tech-primary/20 text-tech-primary px-2 py-0.5 rounded-full">Gerente</span></div><div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-700 to-gray-900 border border-white/10 flex items-center justify-center shadow-lg"><i data-lucide="shield" class="w-5 h-5 text-tech-primary"></i></div></div>
        </header>

        <div class="flex-1 overflow-y-auto p-8 no-scrollbar relative">
            
            <div id="tab-dashboard" class="tab-content fade-in">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-[#1e293b] p-6 rounded-2xl border border-white/5 shadow-lg"><p class="text-gray-400 text-xs font-bold uppercase mb-2">Total</p><h3 class="text-3xl font-bold text-white"><?= $totalAlunos ?></h3></div>
                    <div class="bg-[#1e293b] p-6 rounded-2xl border border-white/5 shadow-lg"><p class="text-gray-400 text-xs font-bold uppercase mb-2">Ativos</p><h3 class="text-3xl font-bold text-green-400"><?= $totalAtivos ?></h3></div>
                    <div class="bg-[#1e293b] p-6 rounded-2xl border border-white/5 shadow-lg"><p class="text-gray-400 text-xs font-bold uppercase mb-2">Inativos</p><h3 class="text-3xl font-bold text-red-400"><?= $totalInativos ?></h3></div>
                    <div class="bg-[#1e293b] p-6 rounded-2xl border border-white/5 shadow-lg"><p class="text-gray-400 text-xs font-bold uppercase mb-2">Faturamento</p><h3 class="text-3xl font-bold text-white">R$ <?= number_format($totalAlunos * 89, 2, ',', '.') ?></h3></div>
                </div>
                <div class="bg-[#1e293b] rounded-2xl border border-white/5 overflow-hidden shadow-xl"><div class="p-6 border-b border-white/5 flex justify-between items-center bg-white/5"><h3 class="font-bold text-lg text-white flex items-center gap-2"><i data-lucide="clock" class="w-5 h-5 text-tech-primary"></i> Últimas Matrículas</h3><button onclick="switchTab('alunos')" class="text-xs font-bold text-tech-primary hover:text-white uppercase">Ver Todos</button></div><table class="w-full text-left text-sm text-gray-300"><thead class="bg-[#0f172a] uppercase text-xs font-bold text-gray-500"><tr><th class="px-6 py-4">Aluno</th><th class="px-6 py-4">Plano</th><th class="px-6 py-4">Status</th><th class="px-6 py-4 text-right">Ação</th></tr></thead><tbody class="divide-y divide-white/5">
                    <?php foreach($dashAlunos as $aluno): ?><tr class="hover:bg-white/5 transition-colors"><td class="px-6 py-4 font-medium text-white"><?= $aluno['nome'] ?></td><td class="px-6 py-4"><?= $aluno['plano'] ?></td><td class="px-6 py-4"><span class="px-2.5 py-1 rounded-full text-xs font-bold <?= $aluno['status'] == 'Ativo' ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400' ?>"><?= $aluno['status'] ?></span></td><td class="px-6 py-4 text-right"><form method="POST" class="inline"><input type="hidden" name="acao" value="alterar_status"><input type="hidden" name="id_aluno" value="<?= $aluno['id'] ?>"><input type="hidden" name="origem" value="dashboard"><button type="submit" name="novo_status" value="<?= $aluno['status'] == 'Ativo' ? 'Inativo' : 'Ativo' ?>" class="p-2 rounded-lg hover:bg-white/10 transition-colors"><i data-lucide="<?= $aluno['status'] == 'Ativo' ? 'lock' : 'unlock' ?>" class="w-4 h-4 <?= $aluno['status'] == 'Ativo' ? 'text-red-400' : 'text-green-400' ?>"></i></button></form></td></tr><?php endforeach; ?>
                </tbody></table></div>
            </div>

            <div id="tab-alunos" class="tab-content hidden fade-in">
                <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4"><div class="relative w-full md:w-96"><form method="GET"><input type="hidden" name="tab" value="alunos"><input type="text" name="busca" value="<?= htmlspecialchars($termoBusca) ?>" placeholder="Buscar aluno..." class="w-full bg-[#1e293b] border border-white/10 rounded-xl pl-12 pr-4 py-3 focus:border-tech-primary outline-none text-sm text-white shadow-lg"><button type="submit" class="absolute left-4 top-3 text-gray-500 hover:text-white"><i data-lucide="search" class="w-5 h-5"></i></button></form></div><button onclick="abrirModalAluno()" class="bg-tech-primary hover:bg-orange-600 text-white px-6 py-3 rounded-xl font-bold text-sm flex gap-2 shadow-glow"><i data-lucide="user-plus" class="w-5 h-5"></i> Novo Aluno</button></div>
                <div class="bg-[#1e293b] rounded-2xl border border-white/5 overflow-hidden shadow-xl"><table class="w-full text-left text-sm text-gray-300"><thead class="bg-[#0f172a] uppercase text-xs font-bold text-gray-500"><tr><th class="px-6 py-4">Aluno</th><th class="px-6 py-4">Plano</th><th class="px-6 py-4">Status</th><th class="px-6 py-4 text-right">Ações</th></tr></thead><tbody class="divide-y divide-white/5">
                    <?php foreach($listaAlunos as $aluno): ?>
                    <tr class="hover:bg-white/5 transition-colors group"><td class="px-6 py-4"><?= $aluno['nome'] ?></td><td class="px-6 py-4"><?= $aluno['plano'] ?></td>
                    <td class="px-6 py-4"><span class="px-2.5 py-1 rounded-full text-xs font-bold <?= $aluno['status'] == 'Ativo' ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400' ?>"><?= $aluno['status'] ?></span></td>
                    <td class="px-6 py-4 text-right"><div class="flex items-center justify-end gap-2"><button onclick='abrirModalEditar(<?= json_encode($aluno) ?>)' class="p-2 hover:bg-blue-500/20 text-blue-400 rounded-lg"><i data-lucide="pencil" class="w-4 h-4"></i></button><form method="POST" class="inline"><input type="hidden" name="acao" value="alterar_status"><input type="hidden" name="id_aluno" value="<?= $aluno['id'] ?>"><input type="hidden" name="origem" value="alunos"><button type="submit" name="novo_status" value="<?= $aluno['status'] == 'Ativo' ? 'Inativo' : 'Ativo' ?>" class="p-2 hover:bg-white/10 rounded-lg"><i data-lucide="<?= $aluno['status'] == 'Ativo' ? 'lock' : 'unlock' ?>" class="w-4 h-4 <?= $aluno['status'] == 'Ativo' ? 'text-red-400' : 'text-green-400' ?>"></i></button></form><button onclick="abrirModalExcluir(<?= $aluno['id'] ?>, 'aluno')" class="p-2 hover:bg-red-500/20 text-red-400 rounded-lg"><i data-lucide="trash-2" class="w-4 h-4"></i></button></div></td></tr><?php endforeach; ?></tbody></table></div>
            </div>

            <div id="tab-treinos" class="tab-content hidden fade-in">
                <div class="flex gap-6 mb-8 border-b border-white/10 pb-1"><button onclick="switchSubTab('individual')" id="btn-individual" class="pb-3 text-sm font-bold text-tech-primary border-b-2 border-tech-primary transition-all">Aluno Específico</button><button onclick="switchSubTab('padrao')" id="btn-padrao" class="pb-3 text-sm font-bold text-gray-400 hover:text-white transition-all">Biblioteca</button></div>
                <div id="view-individual"><form method="POST" id="formTreino"><input type="hidden" name="acao" value="salvar_treino"><div class="flex flex-col xl:flex-row justify-between items-end gap-6 mb-8 bg-[#1e293b] p-6 rounded-2xl border border-white/5 shadow-lg"><div class="w-full xl:w-1/2"><label class="block text-xs font-bold text-gray-400 uppercase mb-2">Selecione o Aluno</label><select name="aluno_id_treino" onchange="carregarTreino(this.value, 'aluno')" class="w-full bg-[#0f172a] border border-white/10 rounded-xl p-4 text-white cursor-pointer"><option value="">-- Escolha --</option><?php foreach($listaSelectTreino as $a): ?><option value="<?= $a['id'] ?>"><?= $a['nome'] ?></option><?php endforeach; ?></select></div><div class="flex gap-3"><button type="button" onclick="gerarTreinoAutomatico()" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-xl font-bold flex gap-2"><i data-lucide="wand-2" class="w-5 h-5"></i> Mágica</button><button type="submit" class="bg-tech-primary hover:bg-orange-600 text-white px-8 py-3 rounded-xl font-bold flex gap-2"><i data-lucide="save" class="w-5 h-5"></i> Salvar</button></div></div><div class="grid grid-cols-1 lg:grid-cols-3 gap-6"><?php foreach(['A','B','C'] as $div): ?><div class="bg-[#1e293b] rounded-2xl border border-white/5 flex flex-col h-full"><div class="p-4 border-b border-white/5 flex justify-between items-center"><h3 class="font-bold text-white"><?= $div ?></h3><button type="button" onclick="addExercicio('container-<?= $div ?>')" class="text-tech-primary">+ Add</button></div><div id="container-<?= $div ?>" class="p-4 space-y-3 flex-1 min-h-[300px]"></div></div><?php endforeach; ?></div></form></div>
                <div id="view-padrao" class="hidden"><div class="grid grid-cols-1 md:grid-cols-4 gap-6"><div class="md:col-span-1 bg-[#1e293b] rounded-2xl border border-white/5 p-4 h-fit max-h-[600px] overflow-y-auto no-scrollbar"><h3 class="text-xs font-bold text-gray-400 uppercase mb-4">Modelos</h3><ul class="space-y-2"><?php foreach($listaModelos as $mod): ?><li class="flex justify-between items-center bg-[#0f172a] p-3 rounded-lg hover:bg-white/5 group"><button onclick="carregarTreino(<?= $mod['id'] ?>, 'modelo')" class="text-sm text-gray-300 hover:text-white flex-1 text-left"><?= $mod['nome'] ?></button><button onclick="abrirModalExcluir(<?= $mod['id'] ?>, 'modelo')" class="text-gray-600 hover:text-red-500 opacity-0 group-hover:opacity-100 p-1"><i data-lucide="trash-2" class="w-4 h-4"></i></button></li><?php endforeach; ?></ul></div><div class="md:col-span-3"><form method="POST" id="formModelo"><input type="hidden" name="acao" value="salvar_modelo"><div class="flex flex-col md:flex-row justify-between items-end gap-4 mb-6 bg-[#1e293b] p-6 rounded-2xl border border-white/5 shadow-lg"><div class="w-full md:w-2/3"><label class="block text-xs font-bold text-gray-400 uppercase mb-2">Nome do Modelo</label><input type="text" name="nome_modelo" placeholder="Ex: Hipertrofia A" class="w-full bg-[#0f172a] border border-white/10 rounded-xl p-4 text-white"></div><div class="flex gap-3"><button type="button" onclick="gerarModeloAutomatico()" class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-4 rounded-xl font-bold flex gap-2"><i data-lucide="wand-2" class="w-5 h-5"></i> Mágica</button><button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-4 rounded-xl font-bold flex gap-2"><i data-lucide="save" class="w-5 h-5"></i> Salvar</button></div></div><div class="grid grid-cols-1 lg:grid-cols-3 gap-6"><?php foreach(['A','B','C'] as $div): ?><div class="bg-[#1e293b] rounded-2xl border border-dashed border-white/20 flex flex-col h-full"><div class="p-4 border-b border-white/5 flex justify-between items-center"><h3 class="font-bold text-gray-300">Treino <?= $div ?></h3><button type="button" onclick="addExercicio('modelo-<?= $div ?>')" class="text-blue-400 text-xs font-bold">+ ITEM</button></div><div id="modelo-<?= $div ?>" class="p-4 space-y-2 min-h-[150px]"></div></div><?php endforeach; ?></div></form></div></div></div>
            </div>

            <div id="tab-loja" class="tab-content hidden fade-in">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-[#1e293b] p-4 rounded-xl border border-white/5 shadow-lg"><p class="text-gray-400 text-xs font-bold uppercase">Produtos Cadastrados</p><h3 class="text-2xl font-bold text-white"><?= $statsLoja['qtd'] ?></h3></div>
                    <div class="bg-[#1e293b] p-4 rounded-xl border border-white/5 shadow-lg"><p class="text-gray-400 text-xs font-bold uppercase">Valor em Estoque</p><h3 class="text-2xl font-bold text-green-400">R$ <?= number_format($statsLoja['valor'], 2, ',', '.') ?></h3></div>
                    <button onclick="abrirModalExcluirMassa()" id="btnExcluirMassa" class="w-full h-full bg-red-900/20 border border-red-500/50 hover:bg-red-900/40 text-red-400 rounded-xl flex items-center justify-center gap-2 font-bold transition-all disabled:opacity-50 disabled:cursor-not-allowed" disabled><i data-lucide="trash-2" class="w-5 h-5"></i> Excluir Selecionados</button>
                </div>

                <div class="flex flex-col md:flex-row justify-between items-start gap-6">
                    <div class="w-full md:w-1/3 bg-[#1e293b] p-6 rounded-2xl border border-white/5 shadow-lg h-fit">
                        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2"><i data-lucide="plus-circle" class="w-5 h-5 text-tech-primary"></i> Novo Produto</h3>
                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="acao" value="cadastrar_produto">
                            <div><label class="text-xs text-gray-400 uppercase font-bold">Nome</label><input type="text" name="nome" required class="w-full bg-[#0f172a] border border-white/10 rounded-lg p-3 text-white mt-1"></div>
                            <div class="grid grid-cols-2 gap-4">
                                <div><label class="text-xs text-gray-400 uppercase font-bold">Preço</label><input type="text" name="preco" oninput="mascaraMoeda(this)" required placeholder="R$ 0,00" class="w-full bg-[#0f172a] border border-white/10 rounded-lg p-3 text-white mt-1"></div>
                                <div><label class="text-xs text-gray-400 uppercase font-bold">Estoque</label><input type="number" name="estoque" required class="w-full bg-[#0f172a] border border-white/10 rounded-lg p-3 text-white mt-1"></div>
                            </div>
                            <div><label class="text-xs text-gray-400 uppercase font-bold">Categoria</label><select name="categoria" class="w-full bg-[#0f172a] border border-white/10 rounded-lg p-3 text-white mt-1"><option value="Suplemento">Suplemento</option><option value="Roupa">Roupa</option><option value="Acessório">Acessório</option><option value="Bebida">Bebida</option><option value="Equipamento">Equipamento</option><option value="Alimento">Alimento</option></select></div>
                            <div><label class="text-xs text-gray-400 uppercase font-bold">Descrição</label><input type="text" name="descricao" class="w-full bg-[#0f172a] border border-white/10 rounded-lg p-3 text-white mt-1"></div>
                            <button type="submit" class="w-full bg-tech-primary hover:bg-orange-600 text-white font-bold py-3 rounded-lg shadow-glow">Cadastrar</button>
                        </form>
                        <form method="POST" class="mt-4"><input type="hidden" name="acao" value="gerar_kit_produtos"><button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold py-2 rounded-lg flex items-center justify-center gap-2"><i data-lucide="wand-2" class="w-3 h-3"></i> Gerar Kit Inicial</button></form>
                    </div>
                    
                    <div class="w-full md:w-2/3">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-white">Catálogo</h3>
                            <form method="GET" class="relative"><input type="hidden" name="tab" value="loja"><input type="text" name="busca_produto" value="<?= htmlspecialchars($termoBuscaProd) ?>" placeholder="Buscar produto..." class="bg-[#0f172a] border border-white/10 rounded-lg pl-8 pr-3 py-1.5 text-xs text-white"><i data-lucide="search" class="absolute left-2.5 top-2 w-3 h-3 text-gray-500"></i></form>
                        </div>
                        <form id="formMassa" method="POST"><input type="hidden" name="acao" value="excluir_massa_produtos">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[600px] overflow-y-auto no-scrollbar">
                            <?php foreach($listaProdutos as $prod): 
                                $icone='shopping-bag'; $cor='text-gray-400'; $bg='bg-white/5';
                                if($prod['categoria']=='Suplemento'){$icone='zap';$cor='text-purple-400';$bg='bg-purple-500/10';}
                                if($prod['categoria']=='Roupa'){$icone='shirt';$cor='text-blue-400';$bg='bg-blue-500/10';}
                                if($prod['categoria']=='Bebida'){$icone='glass-water';$cor='text-cyan-400';$bg='bg-cyan-500/10';}
                            ?>
                            <div class="bg-[#1e293b] p-4 rounded-xl border border-white/5 flex items-center gap-4 group hover:border-tech-primary/30 transition-all relative">
                                <input type="checkbox" name="ids_exclusao[]" value="<?= $prod['id'] ?>" class="w-5 h-5 rounded border-gray-600 bg-[#0f172a] text-orange-500 check-item" onchange="atualizarBotaoExcluir()">
                                <div class="w-12 h-12 rounded-lg flex items-center justify-center <?= $bg ?>"><i data-lucide="<?= $icone ?>" class="w-6 h-6 <?= $cor ?>"></i></div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-white"><?= $prod['nome'] ?></h4>
                                    <div class="flex justify-between items-center mt-1"><p class="text-xs <?= $prod['estoque'] < 5 ? 'text-red-500' : 'text-gray-400' ?>">Estoque: <?= $prod['estoque'] ?></p><p class="text-tech-primary font-bold">R$ <?= number_format($prod['preco'], 2, ',', '.') ?></p></div>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <button type="button" onclick='abrirModalEditarProduto(<?= json_encode($prod) ?>)' class="p-1.5 text-blue-400 hover:bg-blue-500/20 rounded"><i data-lucide="pencil" class="w-4 h-4"></i></button>
                                    <button type="button" onclick="abrirModalExcluir(<?= $prod['id'] ?>, 'produto')" class="p-1.5 text-red-400 hover:bg-red-500/20 rounded"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        </form>
                    </div>
                </div>
            </div>

            <div id="tab-financeiro" class="tab-content hidden fade-in"><div class="bg-[#1e293b] p-20 rounded-2xl border border-white/5 text-center text-gray-500">Módulo Financeiro</div></div>
            <div id="tab-recepcionista" class="tab-content hidden fade-in"><div class="bg-[#1e293b] p-20 rounded-2xl border border-white/5 text-center text-gray-500">Recepção</div></div>
        </div>
    </main>

    <div id="modalExcluir" class="fixed inset-0 z-50 hidden bg-black/90 flex items-center justify-center p-4"><div class="bg-[#1e293b] w-full max-w-md rounded-2xl border border-red-500/30 shadow-2xl p-8 text-center"><div class="w-20 h-20 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-6 text-red-500 animate-pulse"><i data-lucide="alert-triangle" class="w-10 h-10"></i></div><h2 id="titulo_exclusao" class="text-2xl font-bold text-white mb-2">Excluir?</h2><p class="text-gray-400 mb-8">Essa ação é irreversível.</p><form method="POST" class="flex gap-4 justify-center"><input type="hidden" name="acao" id="acao_exclusao" value=""><input type="hidden" name="id_exclusao" id="id_exclusao"><input type="hidden" name="id_modelo" id="id_modelo_hidden"><button type="button" onclick="document.getElementById('modalExcluir').classList.add('hidden')" class="w-full py-3.5 rounded-xl border border-white/10 text-gray-300 hover:bg-white/5 font-bold">Cancelar</button><button type="submit" class="w-full py-3.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold shadow-lg">Sim, Excluir</button></form></div></div>

    <div id="modalExcluirMassa" class="fixed inset-0 z-50 hidden bg-black/90 flex items-center justify-center p-4"><div class="bg-[#1e293b] w-full max-w-md rounded-2xl border border-red-500/30 shadow-2xl p-8 text-center"><h2 class="text-2xl font-bold text-white mb-2">Excluir Selecionados?</h2><p class="text-gray-400 mb-8">Você vai apagar vários itens de uma vez.</p><div class="flex gap-4 justify-center"><button onclick="document.getElementById('modalExcluirMassa').classList.add('hidden')" class="w-full py-3.5 rounded-xl border border-white/10 text-gray-300 font-bold">Cancelar</button><button onclick="document.getElementById('formMassa').submit()" class="w-full py-3.5 rounded-xl bg-red-600 text-white font-bold shadow-lg">Sim, Excluir Tudo</button></div></div></div>

    <div id="modalEditarProduto" class="fixed inset-0 z-50 hidden bg-black/90 flex items-center justify-center p-4"><div class="bg-[#1e293b] w-full max-w-lg rounded-2xl border border-white/10 shadow-2xl p-8 relative"><button onclick="document.getElementById('modalEditarProduto').classList.add('hidden')" class="absolute top-4 right-4 text-white">X</button><h2 class="text-2xl font-bold mb-6 text-white">Editar Produto</h2><form method="POST" class="space-y-4"><input type="hidden" name="acao" value="editar_produto"><input type="hidden" name="id_produto" id="edit_prod_id"><div><label class="text-xs text-gray-400 uppercase font-bold">Nome</label><input type="text" name="nome" id="edit_prod_nome" class="w-full bg-[#0f172a] border border-white/10 rounded-lg p-3 text-white"></div><div class="grid grid-cols-2 gap-4"><div><label class="text-xs text-gray-400 uppercase font-bold">Preço</label><input type="text" name="preco" id="edit_prod_preco" oninput="mascaraMoeda(this)" class="w-full bg-[#0f172a] border border-white/10 rounded-lg p-3 text-white"></div><div><label class="text-xs text-gray-400 uppercase font-bold">Estoque</label><input type="number" name="estoque" id="edit_prod_estoque" class="w-full bg-[#0f172a] border border-white/10 rounded-lg p-3 text-white"></div></div><div><label class="text-xs text-gray-400 uppercase font-bold">Categoria</label><select name="categoria" id="edit_prod_categoria" class="w-full bg-[#0f172a] border border-white/10 rounded-lg p-3 text-white"><option value="Suplemento">Suplemento</option><option value="Roupa">Roupa</option><option value="Acessório">Acessório</option><option value="Bebida">Bebida</option></select></div><div><label class="text-xs text-gray-400 uppercase font-bold">Descrição</label><input type="text" name="descricao" id="edit_prod_descricao" class="w-full bg-[#0f172a] border border-white/10 rounded-lg p-3 text-white"></div><button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg">Salvar Alterações</button></form></div></div>

    <div id="modalAluno" class="fixed inset-0 z-50 hidden bg-black/90 flex items-center justify-center p-4"><div class="bg-[#1e293b] w-full max-w-2xl rounded-2xl border border-white/10 shadow-2xl p-8 relative"><button onclick="fecharModalAluno()" class="absolute top-4 right-4 text-gray-400 hover:text-white"><i data-lucide="x"></i></button><h2 class="text-2xl font-bold mb-8 text-white">Novo Aluno</h2><form method="POST" class="space-y-5"><input type="hidden" name="acao" value="cadastrar_aluno"><div class="grid grid-cols-2 gap-5"><input type="text" name="nome" placeholder="Nome" class="w-full bg-[#0f172a] p-3 text-white rounded-lg"><input type="text" name="cpf" placeholder="CPF" class="w-full bg-[#0f172a] p-3 text-white rounded-lg"></div><div class="grid grid-cols-2 gap-5"><input type="email" name="email" placeholder="Email" class="w-full bg-[#0f172a] p-3 text-white rounded-lg"><input type="text" name="telefone" placeholder="Telefone" class="w-full bg-[#0f172a] p-3 text-white rounded-lg"></div><div class="grid grid-cols-3 gap-5"><input type="date" name="data_nascimento" class="w-full bg-[#0f172a] p-3 text-white rounded-lg"><select name="genero" class="w-full bg-[#0f172a] p-3 text-white rounded-lg"><option value="masculino">Masculino</option><option value="feminino">Feminino</option></select><select name="plano" class="w-full bg-[#0f172a] p-3 text-white rounded-lg"><option value="Start">Start</option><option value="Pro">Pro</option><option value="VIP">VIP</option></select></div><input type="text" name="senha" value="techfit123" class="w-full bg-[#0f172a] p-3 text-white rounded-lg"><button type="submit" class="bg-tech-primary w-full py-3 rounded-lg text-white font-bold">Cadastrar</button></form></div></div>
    <div id="modalEditar" class="fixed inset-0 z-50 hidden bg-black/90 flex items-center justify-center p-4"><div class="bg-[#1e293b] w-full max-w-2xl rounded-2xl border border-white/10 shadow-2xl p-8 relative"><button onclick="document.getElementById('modalEditar').classList.add('hidden')" class="absolute top-4 right-4 text-white">X</button><h2 class="text-2xl font-bold mb-8 text-white">Editar</h2><form method="POST" class="space-y-5"><input type="hidden" name="acao" value="editar_aluno"><input type="hidden" name="id" id="edit_id"><div class="grid grid-cols-2 gap-5"><input type="text" name="nome" id="edit_nome" class="w-full bg-[#0f172a] p-3 text-white rounded-lg"><input type="text" id="edit_cpf" disabled class="w-full bg-[#0f172a] p-3 text-gray-500 rounded-lg"></div><div class="grid grid-cols-2 gap-5"><input type="email" name="email" id="edit_email" class="w-full bg-[#0f172a] p-3 text-white rounded-lg"><input type="text" name="telefone" id="edit_telefone" class="w-full bg-[#0f172a] p-3 text-white rounded-lg"></div><div class="grid grid-cols-2 gap-5"><select name="plano" id="edit_plano" class="w-full bg-[#0f172a] p-3 text-white rounded-lg"><option value="Start">Start</option><option value="Pro">Pro</option><option value="VIP">VIP</option></select><input type="text" name="objetivo" id="edit_objetivo" class="w-full bg-[#0f172a] p-3 text-white rounded-lg"></div><input type="text" name="nova_senha_adm" placeholder="Nova senha" class="w-full bg-[#0f172a] p-3 text-white rounded-lg"><button type="submit" class="bg-blue-600 w-full py-3 rounded-lg text-white font-bold">Salvar</button></form></div></div>

    <script>
        lucide.createIcons();
        function abrirModalAluno() { document.getElementById('modalAluno').classList.remove('hidden'); }
        function fecharModalAluno() { document.getElementById('modalAluno').classList.add('hidden'); }
        function abrirModalEditar(aluno) { document.getElementById('edit_id').value = aluno.id; document.getElementById('edit_nome').value = aluno.nome; document.getElementById('edit_cpf').value = aluno.cpf; document.getElementById('edit_email').value = aluno.email; document.getElementById('edit_telefone').value = aluno.telefone; document.getElementById('edit_plano').value = aluno.plano; document.getElementById('edit_objetivo').value = aluno.objetivo || ''; document.getElementById('modalEditar').classList.remove('hidden'); }
        
        function abrirModalEditarProduto(prod) {
            document.getElementById('edit_prod_id').value = prod.id;
            document.getElementById('edit_prod_nome').value = prod.nome;
            document.getElementById('edit_prod_preco').value = "R$ " + (prod.preco).replace('.', ',');
            document.getElementById('edit_prod_estoque').value = prod.estoque;
            document.getElementById('edit_prod_categoria').value = prod.categoria;
            document.getElementById('edit_prod_descricao').value = prod.descricao;
            document.getElementById('modalEditarProduto').classList.remove('hidden');
        }

        function abrirModalExcluirMassa() {
            document.getElementById('modalExcluirMassa').classList.remove('hidden');
        }
        
        function abrirModalExcluir(id, tipo) {
            document.getElementById('id_exclusao').value = id;
            const inputAcao = document.getElementById('acao_exclusao');
            const titulo = document.getElementById('titulo_exclusao');
            if (tipo === 'aluno') { inputAcao.value = 'excluir_aluno'; titulo.innerText = 'Excluir Aluno?'; }
            else if (tipo === 'produto') { inputAcao.value = 'excluir_produto'; titulo.innerText = 'Excluir Produto?'; }
            else if (tipo === 'modelo') { inputAcao.value = 'excluir_modelo'; document.getElementById('id_modelo_hidden').value = id; titulo.innerText = 'Excluir Modelo?'; }
            document.getElementById('modalExcluir').classList.remove('hidden');
        }

        function mascaraMoeda(i) {
            var v = i.value.replace(/\D/g,'');
            v = (v/100).toFixed(2) + '';
            v = v.replace(".", ",");
            v = v.replace(/(\d)(\d{3})(\d{3}),/g, "$1.$2.$3,");
            v = v.replace(/(\d)(\d{3}),/g, "$1.$2,");
            i.value = "R$ " + v;
        }

        function atualizarBotaoExcluir() {
            const qtd = document.querySelectorAll('.check-item:checked').length;
            document.getElementById('btnExcluirMassa').disabled = qtd === 0;
            document.getElementById('btnExcluirMassa').classList.toggle('opacity-50', qtd === 0);
        }

        async function carregarTreino(id, tipo) {
            if(!id) return;
            const prefixo = tipo === 'modelo' ? 'modelo-' : 'container-';
            ['A','B','C'].forEach(d => document.getElementById(prefixo+d).innerHTML = '');
            try {
                const acao = tipo === 'modelo' ? 'buscar_modelo' : 'buscar_treino';
                const response = await fetch(`adm.php?acao_ajax=${acao}&id=${id}`);
                const data = await response.json();
                ['A','B','C'].forEach(div => { if(data[div]) data[div].forEach(t => addExercicio(prefixo + div, t.exercicio, t.series)); });
                if(tipo==='modelo') document.querySelector('input[name="nome_modelo"]').value = '';
                exibirToast(tipo === 'modelo' ? "Modelo carregado!" : "Treino carregado!", "sucesso");
            } catch (error) { console.error(error); }
        }

        function addExercicio(containerId, nome = '', series = '3x12') {
            const container = document.getElementById(containerId);
            const cleanId = containerId.replace('container-', '').replace('modelo-', ''); 
            const div = document.createElement('div');
            div.className = 'flex gap-2 items-center animate-fadeIn group mb-2';
            div.innerHTML = `<div class="grid grid-cols-[1fr_80px] gap-2 w-full"><input type="text" name="treino[${cleanId}][][nome]" value="${nome}" placeholder="Exercício" class="bg-[#0f172a] border border-white/10 rounded-lg p-2.5 text-sm text-white focus:border-tech-primary outline-none"><input type="text" name="treino[${cleanId}][][series]" value="${series}" placeholder="Reps" class="bg-[#0f172a] border border-white/10 rounded-lg p-2.5 text-sm text-center text-gray-400 focus:border-tech-primary outline-none"></div><button type="button" onclick="this.parentElement.remove()" class="text-gray-600 hover:text-red-500 p-1"><i data-lucide="trash-2" class="w-4 h-4"></i></button>`;
            container.appendChild(div);
            lucide.createIcons();
        }

        function gerarTreinoAutomatico() {
            ['A','B','C'].forEach(d => document.getElementById('container-'+d).innerHTML = '');
            const tA = [{n:'Supino Reto',s:'3x12'},{n:'Supino Inclinado',s:'3x12'},{n:'Tríceps Corda',s:'3x12'}];
            const tB = [{n:'Puxada Alta',s:'3x12'},{n:'Remada Baixa',s:'3x12'},{n:'Rosca Direta',s:'3x12'}];
            const tC = [{n:'Agachamento',s:'3x12'},{n:'Leg Press',s:'3x12'},{n:'Extensora',s:'3x15'}];
            tA.forEach(e => addExercicio('container-A', e.n, e.s)); tB.forEach(e => addExercicio('container-B', e.n, e.s)); tC.forEach(e => addExercicio('container-C', e.n, e.s));
            exibirToast("Treino padrão gerado!", "sucesso");
        }
        function gerarModeloAutomatico() {
            ['A','B','C'].forEach(d => document.getElementById('modelo-'+d).innerHTML = '');
            const tA = [{n:'Supino Reto',s:'3x12'},{n:'Supino Inclinado',s:'3x12'},{n:'Tríceps Corda',s:'3x12'}];
            const tB = [{n:'Puxada Alta',s:'3x12'},{n:'Remada Baixa',s:'3x12'},{n:'Rosca Direta',s:'3x12'}];
            const tC = [{n:'Agachamento',s:'3x12'},{n:'Leg Press',s:'3x12'},{n:'Extensora',s:'3x15'}];
            tA.forEach(e => addExercicio('modelo-A', e.n, e.s)); tB.forEach(e => addExercicio('modelo-B', e.n, e.s)); tC.forEach(e => addExercicio('modelo-C', e.n, e.s));
            exibirToast("Modelo padrão gerado!", "sucesso");
        }

        function switchTab(tabId) { document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden')); document.querySelectorAll('.nav-item').forEach(el => { el.classList.remove('bg-tech-primary/10', 'text-tech-primary', 'border-tech-primary/20'); el.classList.add('text-gray-400', 'hover:bg-white/5', 'hover:text-white'); }); document.getElementById('tab-' + tabId).classList.remove('hidden'); const activeBtn = document.querySelector(`button[onclick="switchTab('${tabId}')"]`); if (activeBtn) { activeBtn.classList.remove('text-gray-400', 'hover:bg-white/5', 'hover:text-white'); activeBtn.classList.add('bg-tech-primary/10', 'text-tech-primary', 'border-tech-primary/20'); } document.getElementById('pageTitle').textContent = tabId.charAt(0).toUpperCase() + tabId.slice(1); }
        function switchSubTab(subTab) { document.getElementById('view-individual').classList.toggle('hidden', subTab!=='individual'); document.getElementById('view-padrao').classList.toggle('hidden', subTab!=='padrao'); document.getElementById('btn-individual').classList.toggle('text-tech-primary', subTab==='individual'); document.getElementById('btn-individual').classList.toggle('border-b-2', subTab==='individual'); document.getElementById('btn-padrao').classList.toggle('text-tech-primary', subTab==='padrao'); document.getElementById('btn-padrao').classList.toggle('border-b-2', subTab==='padrao'); }
        function toggleSidebar() { const sb = document.getElementById('sidebar'); const icon = document.getElementById('toggleIcon'); if(sb.classList.contains('w-64')) { sb.classList.remove('w-64'); sb.classList.add('w-20'); icon.setAttribute('data-lucide', 'chevron-right'); document.querySelectorAll('.nav-text, .logo-text').forEach(el=>el.style.display='none'); } else { sb.classList.remove('w-20'); sb.classList.add('w-64'); icon.setAttribute('data-lucide', 'chevron-left'); document.querySelectorAll('.nav-text, .logo-text').forEach(el=>el.style.display='inline'); } lucide.createIcons(); }
        function exibirToast(msg, tipo) { const div = document.createElement('div'); div.className = `fixed top-5 right-5 z-50 bg-[#1e293b] border-l-4 ${tipo==='erro'?'border-red-500 text-red-400':'border-green-500 text-white'} p-4 rounded shadow-2xl flex items-center gap-3 animate-bounce`; div.innerHTML = `<i data-lucide="check-circle"></i> ${msg}`; document.body.appendChild(div); lucide.createIcons(); setTimeout(() => div.remove(), 4000); }
        
        <?php if ($msgAdm): ?>exibirToast("<?= $msgAdm ?>", "<?= $tipoMsgAdm ?>");<?php endif; ?>
        document.addEventListener('DOMContentLoaded', () => { 
            const params = new URLSearchParams(window.location.search);
            const tab = params.get('tab') || 'dashboard';
            switchTab(tab); 
            if(params.get('sub')) switchSubTab(params.get('sub'));
        });
    </script>
</body>
</html>