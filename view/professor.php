<?php
session_start();
require_once __DIR__ . '/../Model/AlunoDAO.php';
require_once __DIR__ . '/../Model/TreinoDAO.php';
require_once __DIR__ . '/../Model/ProfessorDAO.php';

// SEGURANÇA - Verifica se é professor logado
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'professor') { 
    header('Location: ../index.php'); 
    exit; 
}

$dao = new AlunoDAO();
$treinoDao = new TreinoDAO();
$professorDao = new ProfessorDAO();

// Carrega dados do professor logado
$professor = $professorDao->buscarPorId($_SESSION['usuario_id']);
if (!$professor) {
    session_destroy();
    header('Location: ../index.php');
    exit;
}

$msg = ''; $tipoMsg = '';

// AJAX (Mesmo do Admin)
if (isset($_GET['acao_ajax'])) {
    header('Content-Type: application/json');
    if ($_GET['acao_ajax'] === 'buscar_treino') echo json_encode($treinoDao->buscarPorAluno($_GET['id']));
    if ($_GET['acao_ajax'] === 'buscar_modelo') echo json_encode($treinoDao->buscarModeloPorId($_GET['id']));
    exit;
}

// POST (Salvar Treino)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao']) && $_POST['acao'] === 'salvar_treino') {
        if ($treinoDao->salvarTreino($_POST['aluno_id_treino'], $_POST['treino'] ?? [])) {
            $msg = "Treino atualizado com sucesso!";
            $tipoMsg = 'sucesso';
        } else {
            $msg = "Erro ao salvar.";
            $tipoMsg = 'erro';
        }
    }
}

// Dados
$listaAlunos = $dao->buscarRecentes(100);
$listaModelos = $treinoDao->listarModelos();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="icons/halter.png">
    <title>Professor - TechFit</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { tech: { 900: '#0f172a', 800: '#1e293b', 700: '#334155', primary: '#f97316' } }, boxShadow: { 'glow': '0 0 15px rgba(249, 115, 22, 0.3)' } } } }</script>
    <style>.no-scrollbar::-webkit-scrollbar { display: none; } input, select { background-color: #0f172a !important; color: white !important; border-color: #334155 !important; }</style>
</head>
<body class="bg-[#0b1120] text-gray-100 font-sans h-screen flex overflow-hidden">

    <aside class="w-64 bg-[#111827] border-r border-white/5 flex flex-col justify-between hidden md:flex">
        <div>
            <div class="h-20 flex items-center px-6 border-b border-white/5"><div class="bg-gradient-to-br from-orange-500 to-red-600 p-2 rounded-lg shadow-lg shrink-0"><i data-lucide="dumbbell" class="w-6 h-6 text-white"></i></div><span class="text-xl font-bold ml-3 tracking-wide">TECH<span class="text-tech-primary">FIT</span></span></div>
            <nav class="mt-8 px-4 space-y-2">
                <button class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl bg-tech-primary/10 text-tech-primary"><i data-lucide="biceps-flexed" class="w-5 h-5"></i> Gerenciar Treinos</button>
                <button class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white"><i data-lucide="calendar" class="w-5 h-5"></i> Minha Agenda</button>
            </nav>
        </div>
        <div class="p-4 border-t border-white/5"><a href="index.php" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-red-400 hover:bg-red-500/10 rounded-xl"><i data-lucide="log-out" class="w-5 h-5"></i> Sair</a></div>
    </aside>

    <main class="flex-1 flex flex-col h-screen overflow-hidden relative bg-[#0b1120]">
        <div class="text-right">
    <p class="text-sm font-bold text-white"><?= htmlspecialchars($professor['nome']) ?></p>
    <span class="text-[10px] bg-purple-500/20 text-purple-400 px-2 py-0.5 rounded-full">
        <?= htmlspecialchars($professor['especialidade']) ?>
    </span>
</div>

        <div class="flex-1 overflow-y-auto p-8 no-scrollbar">
            
            <div class="bg-[#1e293b] p-6 rounded-2xl border border-white/5 shadow-xl">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-white flex items-center gap-2"><i data-lucide="edit" class="w-5 h-5 text-tech-primary"></i> Prescrever Treino</h3>
                    <button onclick="gerarTreinoAutomatico()" class="text-xs font-bold text-purple-400 hover:text-white border border-purple-500/30 px-3 py-2 rounded-lg transition-colors">Usar Modelo Padrão</button>
                </div>

                <form method="POST" id="formTreino">
                    <input type="hidden" name="acao" value="salvar_treino">
                    
                    <div class="mb-8">
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Selecione o Aluno</label>
                        <div class="flex gap-4">
                            <select name="aluno_id_treino" onchange="carregarTreino(this.value)" class="w-full bg-[#0f172a] border border-white/10 rounded-xl p-4 text-white focus:border-tech-primary outline-none">
                                <option value="">-- Buscar Aluno --</option>
                                <?php foreach($listaAlunos as $a): ?>
                                    <option value="<?= $a['id'] ?>"><?= $a['nome'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-xl font-bold flex gap-2 items-center shadow-lg"><i data-lucide="save" class="w-5 h-5"></i> Salvar</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <?php foreach(['A','B','C'] as $div): ?>
                        <div class="bg-[#0f172a] rounded-xl border border-white/5 flex flex-col h-full">
                            <div class="p-4 border-b border-white/5 flex justify-between items-center">
                                <h3 class="font-bold text-white">Treino <?= $div ?></h3>
                                <button type="button" onclick="addExercicio('container-<?= $div ?>')" class="text-tech-primary hover:text-white text-xs font-bold">+ Adicionar</button>
                            </div>
                            <div id="container-<?= $div ?>" class="p-4 space-y-2 min-h-[300px]"></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </form>
            </div>

        </div>
    </main>

    <script>
        lucide.createIcons();
        
        async function carregarTreino(id) {
            if(!id) return;
            ['A','B','C'].forEach(d => document.getElementById('container-'+d).innerHTML = '');
            try {
                const response = await fetch(`professor.php?acao_ajax=buscar_treino&id=${id}`);
                const data = await response.json();
                ['A','B','C'].forEach(div => { if(data[div]) data[div].forEach(t => addExercicio('container-'+div, t.exercicio, t.series)); });
                exibirToast("Treino carregado!", "sucesso");
            } catch (error) { console.error(error); }
        }

        function addExercicio(containerId, nome = '', series = '3x12') {
            const container = document.getElementById(containerId);
            const cleanId = containerId.replace('container-', '');
            const div = document.createElement('div');
            div.className = 'flex gap-2 items-center animate-fadeIn group';
            div.innerHTML = `<div class="grid grid-cols-[1fr_80px] gap-2 w-full"><input type="text" name="treino[${cleanId}][][nome]" value="${nome}" placeholder="Exercício" class="bg-[#1e293b] border border-white/10 rounded-lg p-2 text-sm text-white focus:border-tech-primary outline-none"><input type="text" name="treino[${cleanId}][][series]" value="${series}" placeholder="Reps" class="bg-[#1e293b] border border-white/10 rounded-lg p-2 text-sm text-center text-gray-400 focus:border-tech-primary outline-none"></div><button type="button" onclick="this.parentElement.remove()" class="text-red-500 p-1 hover:bg-red-500/10 rounded"><i data-lucide="trash-2" class="w-4 h-4"></i></button>`;
            container.appendChild(div);
            lucide.createIcons();
        }

        function gerarTreinoAutomatico() {
            ['A','B','C'].forEach(d => document.getElementById('container-'+d).innerHTML = '');
            const tA = [{n:'Supino Reto',s:'3x12'},{n:'Supino Inclinado',s:'3x12'},{n:'Tríceps Corda',s:'3x12'}];
            const tB = [{n:'Puxada Alta',s:'3x12'},{n:'Remada Baixa',s:'3x12'},{n:'Rosca Direta',s:'3x12'}];
            const tC = [{n:'Agachamento',s:'3x12'},{n:'Leg Press',s:'3x12'},{n:'Extensora',s:'3x15'}];
            tA.forEach(e => addExercicio('container-A', e.n, e.s)); tB.forEach(e => addExercicio('container-B', e.n, e.s)); tC.forEach(e => addExercicio('container-C', e.n, e.s));
        }

        function exibirToast(msg, tipo) {
            const div = document.createElement('div'); div.className = `fixed top-5 right-5 z-50 bg-[#1e293b] border-l-4 ${tipo==='erro'?'border-red-500 text-red-400':'border-green-500 text-white'} p-4 rounded shadow-2xl flex items-center gap-3 animate-bounce`; div.innerHTML = `<i data-lucide="check-circle"></i> ${msg}`; document.body.appendChild(div); lucide.createIcons(); setTimeout(() => div.remove(), 4000);
        }
        <?php if ($msg): ?>exibirToast("<?= $msg ?>", "<?= $tipoMsg ?>");<?php endif; ?>
    </script>
</body>
</html>