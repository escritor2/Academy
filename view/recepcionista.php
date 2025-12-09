<?php
session_start();
require_once __DIR__ . '/../Model/AlunoDAO.php';
require_once __DIR__ . '/../Model/RecepcionistaDAO.php';

// Verifica se é recepcionista logado
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'recepcionista') { 
    header('Location: ../index.php'); 
    exit; 
}

$dao = new AlunoDAO();
$recepcionistaDao = new RecepcionistaDAO();

// Carrega dados do recepcionista logado
$recepcionista = $recepcionistaDao->buscarPorId($_SESSION['usuario_id']);
if (!$recepcionista) {
    session_destroy();
    header('Location: ../index.php');
    exit;
}

$msg = ''; 
$tipoMsg = '';
$alunoInfo = null;

// ... restante do código continua igual ...

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. LIBERAR ACESSO (CATRACA)
    if (isset($_POST['acao']) && $_POST['acao'] === 'liberar_acesso') {
        $id = $_POST['identificacao'];
        $aluno = $dao->buscarPorId($id); // Busca pelo ID digitado

        if ($aluno) {
            if ($aluno['status'] === 'Ativo') {
                $statusFreq = $dao->getStatusFrequenciaHoje($aluno['id']);
                
                if ($statusFreq == 'nao_entrou') {
                    if($dao->registrarEntrada($aluno['id'])) {
                        $msg = "ENTRADA LIBERADA";
                        $tipoMsg = 'entrada';
                    }
                } elseif ($statusFreq == 'treinando') {
                    if($dao->registrarSaida($aluno['id'])) {
                        $msg = "SAÍDA REGISTRADA";
                        $tipoMsg = 'saida';
                    }
                } else {
                    $msg = "ACESSO JÁ FINALIZADO HOJE";
                    $tipoMsg = 'erro';
                }
                $alunoInfo = $aluno;
            } else {
                $msg = "BLOQUEADO: PLANO " . strtoupper($aluno['status']);
                $tipoMsg = 'erro';
                $alunoInfo = $aluno;
            }
        } else {
            $msg = "ALUNO NÃO ENCONTRADO";
            $tipoMsg = 'erro';
        }
    }

    // 2. CADASTRAR ALUNO NA RECEPÇÃO
    if (isset($_POST['acao']) && $_POST['acao'] === 'cadastrar_aluno') {
        $dao->cadastrarUsuario($_POST['nome'], $_POST['data_nascimento'], $_POST['email'], $_POST['telefone'], $_POST['cpf'], $_POST['genero'], $_POST['senha'], 'Indefinido', $_POST['plano'], 'Aluno');
        $msg = "Aluno cadastrado com sucesso!";
        $tipoMsg = 'sucesso';
    }
}

$ativos = $dao->contarPorStatus('Ativo');
$recentes = $dao->buscarRecentes(10);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Recepção - TechFit</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { tech: { 900: '#0f172a', 800: '#1e293b', 700: '#334155', primary: '#f97316' } } } } }</script>
    <style>.no-scrollbar::-webkit-scrollbar { display: none; } input:focus { border-color: #f97316 !important; box-shadow: 0 0 0 1px #f97316 !important; }</style>
</head>
<body class="bg-[#0b1120] text-gray-100 font-sans h-screen flex overflow-hidden">
    <aside class="w-64 bg-[#111827] border-r border-white/5 flex flex-col justify-between hidden md:flex">
        <div>
            <div class="h-20 flex items-center px-6 border-b border-white/5"><span class="text-xl font-bold ml-3 tracking-wide">TECH<span class="text-tech-primary">FIT</span></span></div>
            <nav class="mt-8 px-4 space-y-2">
                <button onclick="switchTab('balcao')" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-bold rounded-xl bg-tech-primary/10 text-tech-primary"><i data-lucide="monitor" class="w-5 h-5"></i> Balcão</button>
                <button onclick="switchTab('alunos')" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-bold rounded-xl text-gray-400 hover:text-white"><i data-lucide="users" class="w-5 h-5"></i> Alunos</button>
                <button onclick="abrirModalAluno()" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-bold rounded-xl text-green-400 hover:bg-green-500/10"><i data-lucide="user-plus" class="w-5 h-5"></i> Novo Aluno</button>
            </nav>
        </div>
        <div class="p-4"><a href="index.php?sair=true" class="flex items-center gap-3 text-red-400 text-sm font-bold px-4"><i data-lucide="log-out" class="w-5 h-5"></i> Sair</a></div>
    </aside>

    <main class="flex-1 p-8 overflow-y-auto no-scrollbar relative">
        <div id="tab-balcao">
            <div class="bg-[#1e293b] p-10 rounded-2xl border border-white/5 text-center shadow-2xl max-w-2xl mx-auto mt-10">
                <h2 class="text-3xl font-bold text-white mb-2">Controle de Acesso</h2>
                <p class="text-gray-400 mb-8">Digite o ID do aluno (ex: 1, 2, 15) para registrar.</p>
                
                <form method="POST" class="relative max-w-md mx-auto">
                    <input type="hidden" name="acao" value="liberar_acesso">
                    <input type="text" name="identificacao" id="inputCatraca" class="w-full bg-[#0f172a] border-2 border-white/10 rounded-2xl p-5 pl-14 text-2xl text-center text-white outline-none focus:border-tech-primary transition-all" placeholder="ID do Aluno" autofocus autocomplete="off">
                    <i data-lucide="scan-barcode" class="absolute left-5 top-6 w-8 h-8 text-gray-500"></i>
                    <button type="submit" class="hidden">Enviar</button>
                </form>

                <?php if($msg): ?>
                <div class="mt-8 p-6 rounded-xl border flex items-center justify-center gap-4 animate-bounce <?= $tipoMsg=='erro'?'bg-red-500/10 border-red-500 text-red-400':($tipoMsg=='saida'?'bg-blue-500/10 border-blue-500 text-blue-400':'bg-green-500/10 border-green-500 text-green-400') ?>">
                    <i data-lucide="<?= $tipoMsg=='erro'?'x-circle':($tipoMsg=='saida'?'log-out':'check-circle') ?>" class="w-10 h-10"></i>
                    <div class="text-left">
                        <h3 class="text-2xl font-black uppercase"><?= $msg ?></h3>
                        <?php if($alunoInfo): ?><p class="text-lg"><?= $alunoInfo['nome'] ?> (ID: <?= $alunoInfo['id'] ?>)</p><?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="mt-8 text-gray-500 text-sm">Alunos Ativos Total: <?= $ativos ?></div>
            </div>
        </div>

        <div id="tab-alunos" class="hidden">
            <h2 class="text-2xl font-bold text-white mb-6">Últimos Cadastros</h2>
            <div class="bg-[#1e293b] rounded-xl border border-white/5 overflow-hidden">
                <table class="w-full text-left text-sm text-gray-300">
                    <thead class="bg-[#0f172a] uppercase font-bold"><tr><th class="p-4">ID</th><th class="p-4">Nome</th><th class="p-4">Plano</th><th class="p-4">Status</th></tr></thead>
                    <tbody class="divide-y divide-white/5"><?php foreach($recentes as $a): ?><tr><td class="p-4">#<?= $a['id'] ?></td><td class="p-4"><?= $a['nome'] ?></td><td class="p-4"><?= $a['plano'] ?></td><td class="p-4"><?= $a['status'] ?></td></tr><?php endforeach; ?></tbody>
                </table>
            </div>
        </div>
    </main>
    
    <div id="modalAluno" class="fixed inset-0 z-50 hidden bg-black/90 flex items-center justify-center p-4"><div class="bg-[#1e293b] w-full max-w-2xl rounded-2xl border border-white/10 p-8 relative"><button onclick="document.getElementById('modalAluno').classList.add('hidden')" class="absolute top-4 right-4 text-white">X</button><h2 class="text-2xl font-bold mb-6 text-white">Novo Aluno</h2><form method="POST" class="space-y-4"><input type="hidden" name="acao" value="cadastrar_aluno"><div class="grid grid-cols-2 gap-4"><input type="text" name="nome" placeholder="Nome" class="w-full bg-[#0f172a] p-3 text-white rounded-lg"><input type="text" name="cpf" placeholder="CPF" class="w-full bg-[#0f172a] p-3 text-white rounded-lg"></div><div class="grid grid-cols-2 gap-4"><input type="email" name="email" placeholder="Email" class="w-full bg-[#0f172a] p-3 text-white rounded-lg"><input type="text" name="telefone" placeholder="Telefone" class="w-full bg-[#0f172a] p-3 text-white rounded-lg"></div><div class="grid grid-cols-3 gap-4"><input type="date" name="data_nascimento" class="w-full bg-[#0f172a] p-3 text-white rounded-lg"><select name="genero" class="w-full bg-[#0f172a] p-3 text-white rounded-lg"><option value="M">Masculino</option><option value="F">Feminino</option></select><select name="plano" class="w-full bg-[#0f172a] p-3 text-white rounded-lg"><option value="Start">Start</option><option value="Pro">Pro</option><option value="VIP">VIP</option></select></div><input type="text" name="senha" value="123456" class="w-full bg-[#0f172a] p-3 text-white rounded-lg"><button type="submit" class="w-full bg-tech-primary py-3 rounded-lg text-white font-bold hover:bg-orange-600">CADASTRAR</button></form></div></div>

    <script>
        lucide.createIcons();
        function switchTab(id) { document.getElementById('tab-balcao').classList.add('hidden'); document.getElementById('tab-alunos').classList.add('hidden'); document.getElementById('tab-'+id).classList.remove('hidden'); }
        function abrirModalAluno() { document.getElementById('modalAluno').classList.remove('hidden'); }
        document.getElementById('inputCatraca').focus();
    </script>
</body>
</html>