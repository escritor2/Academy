<?php
session_start();
require_once __DIR__ . '/../Model/TreinoDAO.php';
require_once __DIR__ . '/../Model/AlunoDAO.php';

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

// --- PROCESSAMENTO: EDITAR PERFIL ---
$msgCliente = '';
$tipoMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'editar_perfil') {
    $novaSenha = !empty($_POST['nova_senha']) ? $_POST['nova_senha'] : null;
    
    // Validação extra no PHP para garantir
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

// --- PROCESSAMENTO: FREQUÊNCIA (ENTRADA/SAÍDA) ---
if (isset($_POST['acao_frequencia'])) {
    if ($_POST['acao_frequencia'] == 'entrada') {
        $daoAluno->registrarEntrada($idAluno);
        header("Location: paginacliente.php?msg=entrada_ok&open_qr=true");
    }
    if ($_POST['acao_frequencia'] == 'saida') { 
        $daoAluno->registrarSaida($idAluno);
        header("Location: paginacliente.php?msg=saida_ok&open_qr=true");
    }
    exit;
}

// --- DADOS ---
$dadosAluno = $daoAluno->buscarPorId($idAluno);
$meusTreinos = $treinoDao->buscarPorAluno($idAluno);
$nomeCompleto = $dadosAluno['nome'];
$primeiroNome = explode(' ', $nomeCompleto)[0];
$planoAluno = $dadosAluno['plano'];

// STATUS FREQUÊNCIA
$statusFrequencia = $daoAluno->getStatusFrequenciaHoje($idAluno); 
$treinoBloqueado = ($statusFrequencia === 'finalizado');
$tempoHoje = ($statusFrequencia === 'finalizado') ? $daoAluno->getTempoTreinoHoje($idAluno) : '';

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
}
$abrirQR = isset($_GET['open_qr']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Aluno - TechFit</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { tech: { 900: '#0B0F19', 800: '#151b2b', 700: '#2d3748', primary: '#ea580c' } }, boxShadow: { 'glow': '0 0 15px rgba(249, 115, 22, 0.3)' } } }
        }
    </script>
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .fade-in { animation: fadeIn 0.4s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .check-custom:checked + div { background-color: #10b981; border-color: #10b981; }
        .check-custom:checked + div svg { display: block; }
        .blocked-mode { pointer-events: none; opacity: 0.5; filter: grayscale(100%); }
        input, select { background-color: #0f172a !important; color: white !important; border-color: #334155 !important; }
        
        /* Calendário */
        .day-presente { background-color: rgba(16, 185, 129, 0.2); border-color: #10b981; color: #10b981; }
        .day-falta { background-color: rgba(239, 68, 68, 0.1); border-color: #ef4444; color: #ef4444; opacity: 0.7; }
        .day-hoje { border-color: #ea580c; box-shadow: 0 0 10px rgba(234, 88, 12, 0.2); }
        .day-futuro { opacity: 0.3; }
    </style>
</head>
<body class="bg-[#0b1120] text-gray-100 font-sans h-screen flex overflow-hidden">

    <aside id="sidebar" class="w-64 bg-[#111827] border-r border-white/5 flex flex-col justify-between hidden md:flex transition-all duration-300 relative z-20">
        <button onclick="toggleSidebar()" class="absolute -right-3 top-24 bg-tech-primary text-white p-1.5 rounded-full shadow-glow z-50 hover:bg-orange-600 transition-transform hover:scale-110"><i id="toggleIcon" data-lucide="chevron-left" class="w-3 h-3"></i></button>
        <div>
            <div class="h-20 flex items-center px-6 border-b border-white/5 logo-container"><div class="bg-gradient-to-br from-orange-500 to-red-600 p-2 rounded-lg shadow-lg shrink-0"><i data-lucide="dumbbell" class="w-6 h-6 text-white"></i></div><span class="text-xl font-bold ml-3 tracking-wide">TECH<span class="text-tech-primary">FIT</span></span></div>
            <nav class="mt-8 px-4 space-y-1.5 overflow-y-auto max-h-[calc(100vh-160px)] no-scrollbar">
                <button onclick="switchTab('dashboard')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl bg-tech-primary/10 text-tech-primary shadow-sm border border-tech-primary/20"><i data-lucide="layout-dashboard" class="w-5 h-5"></i><span class="nav-text">Home</span></button>
                <button onclick="switchTab('treinos')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all"><i data-lucide="biceps-flexed" class="w-5 h-5"></i><span class="nav-text">Meus Treinos</span></button>
                <button onclick="switchTab('loja')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all"><i data-lucide="shopping-bag" class="w-5 h-5"></i><span class="nav-text">Loja</span></button>
                <button onclick="switchTab('perfil')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all"><i data-lucide="user-cog" class="w-5 h-5"></i><span class="nav-text">Meu Perfil</span></button>
                <button onclick="abrirCarteirinha()" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all"><i data-lucide="qr-code" class="w-5 h-5"></i><span class="nav-text">Carteirinha</span></button>
            </nav>
        </div>
        <div class="p-4 border-t border-white/5"><a href="?sair=true" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-red-400 hover:bg-red-500/10 rounded-xl transition-colors"><i data-lucide="log-out" class="w-5 h-5"></i><span class="nav-text">Sair</span></a></div>
    </aside>

    <main class="flex-1 flex flex-col h-screen overflow-hidden relative bg-[#0b1120]">
        <header class="h-20 bg-[#111827]/80 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 z-10 sticky top-0">
            <div><h2 id="pageTitle" class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white to-gray-400">Olá, <?= $primeiroNome ?></h2><p class="text-xs text-gray-500 mt-0.5">Bons treinos hoje!</p></div>
            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block"><p class="text-sm font-bold text-white"><?= $nomeCompleto ?></p><span class="text-[10px] uppercase font-bold tracking-wider bg-tech-primary/20 text-tech-primary px-2 py-0.5 rounded-full"><?= $planoAluno ?></span></div>
                <div onclick="abrirCarteirinha()" class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-700 to-gray-900 border border-white/10 flex items-center justify-center shadow-lg cursor-pointer hover:border-tech-primary transition-colors"><i data-lucide="qr-code" class="w-5 h-5 text-white"></i></div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8 no-scrollbar relative">
            
            <div id="tab-dashboard" class="tab-content fade-in">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div onclick="abrirCarteirinha()" class="bg-[#1e293b] p-6 rounded-2xl border border-white/5 shadow-lg cursor-pointer hover:border-tech-primary/50 transition-all group">
                        <p class="text-gray-400 text-xs font-bold uppercase mb-2">Status Hoje</p>
                        <div class="flex justify-between items-end relative z-10">
                            <div>
                                <?php if($statusFrequencia == 'nao_entrou'): ?>
                                    <h3 class="text-2xl font-bold text-white">Check-in</h3>
                                    <p class="text-xs text-gray-500">Toque para entrar</p>
                                <?php elseif($statusFrequencia == 'treinando'): ?>
                                    <h3 class="text-2xl font-bold text-green-400 animate-pulse">Treinando</h3>
                                    <p class="text-xs text-gray-500">Toque para sair</p>
                                <?php else: ?>
                                    <h3 class="text-2xl font-bold text-red-400">Concluído</h3>
                                    <p class="text-xs text-gray-500">Tempo: <?= $tempoHoje ?></p>
                                <?php endif; ?>
                            </div>
                            <i data-lucide="activity" class="w-8 h-8 text-tech-primary opacity-80 group-hover:scale-110 transition-transform"></i>
                        </div>
                    </div>
                    <div onclick="switchTab('treinos')" class="bg-[#1e293b] p-6 rounded-2xl border border-white/5 shadow-lg cursor-pointer hover:border-tech-primary/50 transition-all"><p class="text-gray-400 text-xs font-bold uppercase mb-2">Treino Atual</p><div class="flex justify-between items-end"><h3 class="text-2xl font-bold text-white">Ver Ficha</h3><i data-lucide="dumbbell" class="w-8 h-8 text-blue-500 opacity-80"></i></div></div>
                    <div class="bg-[#1e293b] p-6 rounded-2xl border border-white/5 shadow-lg"><p class="text-gray-400 text-xs font-bold uppercase mb-2">Assinatura</p><div class="flex justify-between items-end"><h3 class="text-2xl font-bold text-green-400">Ativa</h3><i data-lucide="check-circle" class="w-8 h-8 text-green-500 opacity-80"></i></div></div>
                </div>

                <div class="bg-[#1e293b] rounded-2xl border border-white/5 p-6 mb-8">
                    <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2"><i data-lucide="calendar-check" class="w-5 h-5 text-tech-primary"></i> Histórico (<?= date('M/Y') ?>)</h3>
                    <div class="grid grid-cols-7 gap-2 md:gap-4 text-center">
                        <?php for($i=1; $i<=$totalDiasMes; $i++): 
                            $classeDia = ''; $icone = '';
                            if (in_array($i, $diasTreinados)) { $classeDia = 'day-presente'; $icone = '<i data-lucide="check" class="w-3 h-3 mt-1"></i>'; } 
                            elseif ($i < $diaHoje) { $classeDia = 'day-falta'; $icone = '<i data-lucide="x" class="w-3 h-3 mt-1"></i>'; } 
                            elseif ($i == $diaHoje) { $classeDia = 'day-hoje'; } else { $classeDia = 'day-futuro'; }
                        ?>
                            <div class="p-2 rounded-lg border border-white/5 bg-[#0f172a] text-gray-300 text-sm font-bold flex flex-col justify-center items-center h-16 relative <?= $classeDia ?>"><span><?= $i ?></span><?= $icone ?></div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <div id="tab-treinos" class="tab-content hidden fade-in">
                <?php if($treinoBloqueado): ?>
                    <div class="flex flex-col items-center justify-center py-20 text-center fade-in">
                        <div class="w-32 h-32 bg-green-500/10 rounded-full flex items-center justify-center mb-6 shadow-glow border border-green-500/30"><i data-lucide="check-check" class="w-16 h-16 text-green-500"></i></div>
                        <h2 class="text-3xl font-bold text-white mb-2">Bom Descanso!</h2>
                        <p class="text-gray-400 max-w-md">Treino finalizado. Volte amanhã.</p>
                        <div class="mt-6 p-4 bg-[#1e293b] rounded-xl border border-white/10"><p class="text-sm text-gray-500">Tempo hoje:</p><p class="text-xl font-mono text-white font-bold"><?= $tempoHoje ?></p></div>
                    </div>
                <?php else: ?>
                    <div class="flex gap-4 mb-6 border-b border-white/10 pb-1">
                        <button onclick="mudarFicha('A')" id="btn-A" class="pb-3 text-sm font-bold text-tech-primary border-b-2 border-tech-primary transition-all px-6">Treino A</button>
                        <button onclick="mudarFicha('B')" id="btn-B" class="pb-3 text-sm font-bold text-gray-400 hover:text-white transition-all px-6">Treino B</button>
                        <button onclick="mudarFicha('C')" id="btn-C" class="pb-3 text-sm font-bold text-gray-400 hover:text-white transition-all px-6">Treino C</button>
                    </div>
                    <div id="listaExercicios" class="space-y-3 min-h-[300px]"></div>
                    <div class="mt-8 border-t border-white/10 pt-6 text-center">
                        <button onclick="abrirModalConclusao()" class="bg-green-600 hover:bg-green-700 text-white px-8 py-4 rounded-xl font-bold shadow-lg shadow-green-900/20 transition-all flex items-center justify-center gap-2 mx-auto"><i data-lucide="check-circle-2" class="w-6 h-6"></i> CONCLUIR TREINO</button>
                    </div>
                <?php endif; ?>
            </div>

            <div id="tab-loja" class="tab-content hidden fade-in">
                <div class="flex justify-between items-center mb-6"><h3 class="text-2xl font-bold text-white">Loja TechFit</h3><div class="bg-tech-primary/20 text-tech-primary px-3 py-1 rounded-full text-xs font-bold">10% OFF</div></div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-[#1e293b] rounded-2xl border border-white/5 overflow-hidden group"><div class="h-40 bg-gray-800 flex items-center justify-center relative"><i data-lucide="shopping-bag" class="w-16 h-16 text-gray-600"></i><span class="absolute top-2 right-2 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded">Promo</span></div><div class="p-4"><h4 class="font-bold text-white text-lg">Whey Protein</h4><p class="text-gray-400 text-sm mb-3">900g</p><div class="flex justify-between items-center"><span class="text-xl font-bold text-tech-primary">R$ 149,90</span><button class="p-2 bg-tech-primary rounded-lg text-white hover:bg-orange-600"><i data-lucide="plus" class="w-4 h-4"></i></button></div></div></div>
                    <div class="bg-[#1e293b] rounded-2xl border border-white/5 overflow-hidden group"><div class="h-40 bg-gray-800 flex items-center justify-center"><i data-lucide="shirt" class="w-16 h-16 text-gray-600"></i></div><div class="p-4"><h4 class="font-bold text-white text-lg">Camiseta</h4><p class="text-gray-400 text-sm mb-3">Dry-Fit</p><div class="flex justify-between items-center"><span class="text-xl font-bold text-tech-primary">R$ 59,90</span><button class="p-2 bg-tech-primary rounded-lg text-white hover:bg-orange-600"><i data-lucide="plus" class="w-4 h-4"></i></button></div></div></div>
                </div>
            </div>

            <div id="tab-perfil" class="tab-content hidden fade-in">
                <div class="max-w-2xl mx-auto bg-[#1e293b] rounded-2xl border border-white/5 p-8 shadow-xl">
                    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2"><i data-lucide="user-cog" class="w-6 h-6 text-tech-primary"></i> Editar Meus Dados</h3>
                    <form method="POST" id="formPerfil">
                        <input type="hidden" name="acao" value="editar_perfil">
                        <div class="grid grid-cols-1 gap-6">
                            <div><label class="block text-xs font-bold text-gray-400 uppercase mb-2">Nome Completo</label><input type="text" name="nome" value="<?= htmlspecialchars($dadosAluno['nome']) ?>" required class="w-full bg-[#0f172a] border border-white/10 rounded-xl p-4 text-white focus:border-tech-primary outline-none"></div>
                            <div class="grid grid-cols-2 gap-6">
                                <div><label class="block text-xs font-bold text-gray-400 uppercase mb-2">Email</label><input type="email" name="email" value="<?= htmlspecialchars($dadosAluno['email']) ?>" required class="w-full bg-[#0f172a] border border-white/10 rounded-xl p-4 text-white focus:border-tech-primary outline-none"></div>
                                <div><label class="block text-xs font-bold text-gray-400 uppercase mb-2">Telefone</label><input type="text" name="telefone" id="telefoneInput" value="<?= htmlspecialchars($dadosAluno['telefone']) ?>" oninput="mascaraTelefone(this)" maxlength="15" required class="w-full bg-[#0f172a] border border-white/10 rounded-xl p-4 text-white focus:border-tech-primary outline-none" placeholder="(00) 00000-0000"></div>
                            </div>
                            <div class="border-t border-white/10 pt-6">
                                <label class="block text-xs font-bold text-tech-primary uppercase mb-2">Nova Senha (Opcional)</label>
                                <input type="password" name="nova_senha" id="novaSenhaInput" placeholder="Min 8 caracteres, letra, número, símbolo" class="w-full bg-[#0f172a] border border-white/10 rounded-xl p-4 text-white focus:border-tech-primary outline-none">
                                <p id="msgSenha" class="text-xs text-red-400 mt-2 hidden font-bold">A senha deve ter 8+ caracteres, letras, números e símbolo!</p>
                            </div>
                            <button type="submit" id="btnSalvarPerfil" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg transition-all">Salvar Alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <div id="modalConclusao" class="fixed inset-0 z-50 hidden bg-black/90 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-[#1e293b] w-full max-w-sm rounded-3xl border border-green-500/30 shadow-2xl p-8 text-center relative overflow-hidden">
            <div id="confetti-container" class="absolute inset-0 pointer-events-none"></div>
            <div class="w-20 h-20 bg-green-500/10 rounded-full flex items-center justify-center mx-auto mb-6 text-green-500 animate-pulse"><i data-lucide="trophy" class="w-10 h-10"></i></div>
            <h2 class="text-2xl font-bold text-white mb-2">Já acabou?</h2>
            <p class="text-gray-400 mb-8">Confirmando, você registra saída e finaliza por hoje.</p>
            <div class="flex gap-3 justify-center">
                <button onclick="document.getElementById('modalConclusao').classList.add('hidden')" class="px-6 py-3 rounded-xl border border-white/10 text-gray-300 hover:bg-white/5">Voltar</button>
                <form method="POST"><input type="hidden" name="acao_frequencia" value="saida"><button type="submit" class="px-6 py-3 rounded-xl bg-green-600 hover:bg-green-700 text-white font-bold shadow-lg">Sim, Finalizar!</button></form>
            </div>
        </div>
    </div>

    <div id="modalCarteirinha" class="fixed inset-0 z-50 <?php echo $abrirQR ? '' : 'hidden'; ?> bg-black/90 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-[#1e293b] w-full max-w-sm rounded-3xl border border-white/10 shadow-2xl p-8 text-center relative">
            <button onclick="document.getElementById('modalCarteirinha').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-white"><i data-lucide="x" class="w-6 h-6"></i></button>
            <h2 class="text-2xl font-bold text-white mb-1">Carteirinha Digital</h2>
            <p class="text-xs text-gray-500 mb-6">ID: #<?= str_pad($idAluno, 6, '0', STR_PAD_LEFT) ?></p>
            <div class="bg-white p-4 rounded-xl inline-block shadow-glow mb-6"><img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=Aluno-<?= $idAluno ?>-Acesso" alt="QR Code" class="w-48 h-48"></div>
            <div class="border-t border-white/10 pt-6">
                <?php if($statusFrequencia == 'nao_entrou'): ?>
                    <form method="POST"><input type="hidden" name="acao_frequencia" value="entrada"><button type="submit" class="w-full py-4 rounded-xl bg-green-600 hover:bg-green-700 text-white font-bold shadow-lg flex items-center justify-center gap-2"><i data-lucide="log-in" class="w-5 h-5"></i> REGISTRAR ENTRADA</button></form>
                <?php elseif($statusFrequencia == 'treinando'): ?>
                    <div class="mb-4 text-green-400 font-bold animate-pulse">Você está treinando!</div>
                    <form method="POST"><input type="hidden" name="acao_frequencia" value="saida"><button type="submit" class="w-full py-4 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold shadow-lg flex items-center justify-center gap-2"><i data-lucide="log-out" class="w-5 h-5"></i> REGISTRAR SAÍDA</button></form>
                <?php else: ?>
                    <div class="bg-red-500/10 border border-red-500/30 p-4 rounded-xl text-red-400"><h4 class="font-bold mb-1">Acesso Encerrado</h4><p class="text-xs">Volte amanhã.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        const treinosData = <?php echo json_encode($meusTreinos); ?>;

        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.nav-item').forEach(el => { el.classList.remove('bg-tech-primary/10', 'text-tech-primary', 'border-tech-primary/20'); el.classList.add('text-gray-400', 'hover:bg-white/5', 'hover:text-white'); });
            document.getElementById('tab-' + tabId).classList.remove('hidden');
            const activeBtn = document.querySelector(`button[onclick="switchTab('${tabId}')"]`);
            if (activeBtn) { activeBtn.classList.remove('text-gray-400', 'hover:bg-white/5', 'hover:text-white'); activeBtn.classList.add('bg-tech-primary/10', 'text-tech-primary', 'border-tech-primary/20'); }
            const titulos = {'dashboard': 'Home', 'treinos': 'Meus Treinos', 'loja': 'Loja', 'perfil': 'Meu Perfil'};
            document.getElementById('pageTitle').innerText = titulos[tabId] || 'TechFit';
        }

        function mudarFicha(letra) {
            ['A','B','C'].forEach(l => { const btn = document.getElementById(`btn-${l}`); if (l === letra) btn.className = 'pb-3 text-sm font-bold text-tech-primary border-b-2 border-tech-primary transition-all px-6'; else btn.className = 'pb-3 text-sm font-bold text-gray-400 hover:text-white transition-all px-6'; });
            const lista = document.getElementById('listaExercicios'); lista.innerHTML = '';
            const exercicios = treinosData[letra];
            if (!exercicios || exercicios.length === 0) { lista.innerHTML = `<div class="text-center py-12 opacity-50"><i data-lucide="coffee" class="w-16 h-16 mx-auto text-gray-500 mb-4"></i><p class="text-gray-400">Descanso (Sem treino)</p></div>`; } 
            else {
                exercicios.forEach((ex, i) => {
                    const html = `<div class="bg-[#1e293b] border border-white/5 rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:border-tech-primary/30 transition-all fade-in"><div class="flex items-center gap-4"><label class="relative flex items-center cursor-pointer"><input type="checkbox" class="peer sr-only check-custom" onchange="toggleExercicio(this)"><div class="w-6 h-6 border-2 border-gray-500 rounded flex items-center justify-center peer-checked:bg-green-500 peer-checked:border-green-500 transition-all"><i data-lucide="check" class="w-4 h-4 text-white hidden"></i></div></label><div><h4 class="font-bold text-white text-lg">${ex.exercicio}</h4><div class="flex gap-3 mt-1 text-xs text-gray-400"><span class="bg-white/5 px-2 py-1 rounded border border-white/5 flex items-center gap-1"><i data-lucide="repeat" class="w-3 h-3 text-tech-primary"></i> ${ex.series}</span>${ex.carga && ex.carga !== '---' ? `<span class="bg-white/5 px-2 py-1 rounded border border-white/5 flex items-center gap-1"><i data-lucide="weight" class="w-3 h-3 text-tech-primary"></i> ${ex.carga}</span>` : ''}</div></div></div><button onclick="iniciarDescanso(this)" class="btn-descanso bg-[#0f172a] border border-white/10 text-gray-400 hover:text-white px-4 py-2 rounded-lg text-xs font-bold flex items-center justify-center gap-2 transition-all w-full md:w-auto"><i data-lucide="timer" class="w-4 h-4"></i> <span class="timer-text">60s</span></button></div>`;
                    lista.insertAdjacentHTML('beforeend', html);
                });
            }
            lucide.createIcons();
        }

        function toggleExercicio(checkbox) { checkbox.closest('.bg-[#1e293b]').classList.toggle('opacity-50'); checkbox.closest('.bg-[#1e293b]').classList.toggle('grayscale'); }
        
        // --- TIMER INTELIGENTE (CORRIGIDO) ---
        let timers = new Map(); // Mapa para guardar intervalos de cada botão

        function iniciarDescanso(btn) {
            const span = btn.querySelector('.timer-text');
            
            // Se já está rodando, PAUSA
            if (btn.classList.contains('running')) {
                clearInterval(timers.get(btn));
                btn.classList.remove('running', 'bg-tech-primary', 'text-white', 'border-transparent');
                btn.classList.add('bg-[#0f172a]', 'text-gray-400');
                
                // Extrai o tempo atual do texto para não perder
                const tempoAtual = parseInt(span.innerText.replace(/\D/g, ''));
                span.innerText = `Pausado (${tempoAtual}s)`;
                return;
            }

            // Se o botão já foi finalizado (verde), não faz nada
            if (btn.classList.contains('bg-green-500')) return;

            // Inicia ou Retoma
            btn.classList.add('running', 'bg-tech-primary', 'text-white', 'border-transparent');
            btn.classList.remove('bg-[#0f172a]', 'text-gray-400');
            
            // Pega o tempo do texto (se pausado) ou reseta para 60
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
                    if(navigator.vibrate) navigator.vibrate([200, 100, 200]);
                }
            }, 1000);
            
            timers.set(btn, interval);
        }

        // --- VALIDAÇÃO PERFIL (MÁSCARA E SENHA) ---
        function mascaraTelefone(input) {
            let v = input.value.replace(/\D/g,"");
            v = v.replace(/^(\d{2})(\d)/g,"($1) $2");
            v = v.replace(/(\d)(\d{4})$/,"$1-$2");
            input.value = v;
        }

        const senhaInput = document.getElementById('novaSenhaInput');
        const btnSalvar = document.getElementById('btnSalvarPerfil');
        const msgSenha = document.getElementById('msgSenha');

        if(senhaInput) {
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

        // Modais e Toasts
        function abrirModalConclusao() { document.getElementById('modalConclusao').classList.remove('hidden'); if(document.getElementById('confetti-container')) soltarConfete(); }
        function abrirCarteirinha() { document.getElementById('modalCarteirinha').classList.remove('hidden'); }
        function toggleSidebar() { const sb = document.getElementById('sidebar'); const icon = document.getElementById('toggleIcon'); if(sb.classList.contains('w-64')) { sb.classList.remove('w-64'); sb.classList.add('w-20'); icon.setAttribute('data-lucide', 'chevron-right'); document.querySelectorAll('.nav-text, .logo-text').forEach(el=>el.style.display='none'); } else { sb.classList.remove('w-20'); sb.classList.add('w-64'); icon.setAttribute('data-lucide', 'chevron-left'); document.querySelectorAll('.nav-text, .logo-text').forEach(el=>el.style.display='inline'); } lucide.createIcons(); }
        function exibirToast(msg, tipo) { const div = document.createElement('div'); const cor = tipo === 'erro' ? 'border-red-500 text-red-400' : 'border-green-500 text-white'; div.className = `fixed top-5 right-5 z-50 bg-[#1e293b] border-l-4 ${cor} p-4 rounded shadow-2xl flex items-center gap-3 animate-bounce`; div.innerHTML = `<i data-lucide="${tipo==='erro'?'alert-circle':'check-circle'}"></i> ${msg}`; document.body.appendChild(div); lucide.createIcons(); setTimeout(() => div.remove(), 4000); }
        
        // Efeito Confete (Simples)
        function soltarConfete() {
            const c = document.getElementById('confetti-container'); c.innerHTML = '';
            for(let i=0; i<30; i++) { const p = document.createElement('div'); p.className='absolute w-2 h-2 bg-tech-primary top-0'; p.style.left = Math.random()*100+'%'; p.style.animation = `fadeIn ${Math.random()+1}s ease-out forwards`; c.appendChild(p); }
        }

        <?php if ($msgCliente): ?>exibirToast("<?= $msgCliente ?>", "<?= $tipoMsg ?>");<?php endif; ?>
        <?php if ($abrirQR): ?>abrirCarteirinha();<?php endif; ?>

        document.addEventListener('DOMContentLoaded', () => { 
            const params = new URLSearchParams(window.location.search);
            const tab = params.get('tab') || 'dashboard';
            switchTab(tab); 
            mudarFicha('A'); 
            lucide.createIcons(); 
        });
    </script>
</body>
</html>