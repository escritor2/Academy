<?php
session_start();
require_once __DIR__ . '/../Model/AlunoDAO.php';
require_once __DIR__ . '/../Model/TreinoDAO.php';
require_once __DIR__ . '/../Model/ProfessorDAO.php';

// SEGURANÇA - Verifica se é professor logado (usando a nova sessão)
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

// Atualizar informações da sessão com dados completos
if (!isset($_SESSION['professor_cref']) && isset($professor['cref'])) {
    $_SESSION['professor_cref'] = $professor['cref'];
    $_SESSION['professor_especialidade'] = $professor['especialidade'];
    $_SESSION['professor_email'] = $professor['email'];
    $_SESSION['tipo'] = 'professor'; // ← ADICIONE ESTA LINHA
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
    
    // Logout
    if (isset($_POST['logout'])) {
        session_destroy();
        header('Location: ../index.php');
        exit;
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
                        },
                        prof: {
                            primary: '#3b82f6',
                            dark: '#1e40af',
                            light: '#60a5fa'
                        }
                    }, 
                    boxShadow: { 
                        'glow': '0 0 15px rgba(249, 115, 22, 0.3)',
                        'glow-blue': '0 0 15px rgba(59, 130, 246, 0.3)'
                    } 
                } 
            } 
        }
    </script>
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; } 
        input, select { background-color: #0f172a !important; color: white !important; border-color: #334155 !important; }
        
        /* Animações */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn { animation: fadeIn 0.3s ease-out; }
    </style>
</head>
<body class="bg-[#0b1120] text-gray-100 font-sans h-screen flex overflow-hidden">

    <aside class="w-64 bg-[#111827] border-r border-white/5 flex flex-col justify-between hidden md:flex">
        <div>
            <div class="h-20 flex items-center px-6 border-b border-white/5">
                <div class="bg-gradient-to-br from-blue-500 to-blue-700 p-2 rounded-lg shadow-lg shrink-0">
                    <i data-lucide="graduation-cap" class="w-6 h-6 text-white"></i>
                </div>
                <span class="text-xl font-bold ml-3 tracking-wide">TECH<span class="text-prof-primary">FIT</span></span>
            </div>
            
            <!-- Perfil Professor -->
            <div class="p-4 border-b border-white/5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                        <span class="font-bold text-white"><?php echo substr($professor['nome'], 0, 1); ?></span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-white truncate"><?= htmlspecialchars($professor['nome']) ?></p>
                        <span class="text-xs text-blue-300"><?= htmlspecialchars($professor['cref']) ?></span>
                    </div>
                </div>
                <div class="mt-2">
                    <span class="text-xs bg-blue-500/20 text-blue-400 px-2 py-1 rounded-full">
                        <?= htmlspecialchars($professor['especialidade']) ?>
                    </span>
                </div>
            </div>
            
            <nav class="mt-4 px-4 space-y-1">
                <button class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl bg-prof-primary/10 text-prof-primary shadow-glow-blue">
                    <i data-lucide="biceps-flexed" class="w-5 h-5"></i> Gerenciar Treinos
                </button>
                <a href="#" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-colors">
                    <i data-lucide="calendar" class="w-5 h-5"></i> Minha Agenda
                </a>
                <a href="#" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-colors">
                    <i data-lucide="users" class="w-5 h-5"></i> Meus Alunos
                </a>
                <a href="#" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-colors">
                    <i data-lucide="bar-chart" class="w-5 h-5"></i> Relatórios
                </a>
            </nav>
        </div>
        
        <!-- Sair -->
        <div class="p-4 border-t border-white/5">
            <form method="POST" class="w-full">
                <input type="hidden" name="logout" value="true">
                <button type="submit" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-red-400 hover:bg-red-500/10 rounded-xl w-full transition-colors">
                    <i data-lucide="log-out" class="w-5 h-5"></i> Sair
                </button>
            </form>
        </div>
    </aside>

    <main class="flex-1 flex flex-col h-screen overflow-hidden relative bg-[#0b1120]">
        <!-- Top Bar -->
        <div class="h-16 bg-[#111827]/50 border-b border-white/5 px-6 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-white flex items-center gap-2">
                    <i data-lucide="dumbbell" class="w-5 h-5 text-prof-primary"></i>
                    Painel do Professor
                </h1>
                <p class="text-xs text-gray-400">Gerencie treinos e acompanhe seus alunos</p>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="hidden md:flex items-center gap-2 bg-blue-900/30 px-3 py-2 rounded-lg">
                    <i data-lucide="zap" class="w-4 h-4 text-blue-400"></i>
                    <span class="text-sm text-blue-300">42 alunos ativos</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="text-right hidden md:block">
                        <p class="text-sm font-bold text-white"><?= htmlspecialchars($professor['nome']) ?></p>
                        <span class="text-xs bg-blue-500/20 text-blue-400 px-2 py-1 rounded-full">
                            <?= htmlspecialchars($professor['especialidade']) ?>
                        </span>
                    </div>
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center cursor-pointer">
                        <span class="font-bold text-white text-sm"><?php echo substr($professor['nome'], 0, 1); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 md:p-8 no-scrollbar">
            <?php if ($msg): ?>
                <div class="mb-6 animate-fadeIn">
                    <div class="<?php echo $tipoMsg == 'sucesso' ? 'bg-green-500/20 border-green-500/50 text-green-300' : 'bg-red-500/20 border-red-500/50 text-red-300'; ?> border p-4 rounded-xl flex items-center gap-3">
                        <i data-lucide="<?php echo $tipoMsg == 'sucesso' ? 'check-circle' : 'alert-circle'; ?>" class="w-5 h-5"></i>
                        <span><?= htmlspecialchars($msg) ?></span>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="bg-[#1e293b] p-6 rounded-2xl border border-white/5 shadow-xl mb-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-white flex items-center gap-2">
                            <i data-lucide="edit" class="w-5 h-5 text-prof-primary"></i> 
                            Prescrever Treino
                        </h3>
                        <p class="text-sm text-gray-400 mt-1">Crie ou edite treinos personalizados para seus alunos</p>
                    </div>
                    <button onclick="gerarTreinoAutomatico()" 
                            class="text-sm font-bold text-purple-400 hover:text-white border border-purple-500/30 px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                        <i data-lucide="zap" class="w-4 h-4"></i>
                        Usar Modelo Padrão
                    </button>
                </div>

                <form method="POST" id="formTreino">
                    <input type="hidden" name="acao" value="salvar_treino">
                    
                    <div class="mb-8">
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Selecione o Aluno</label>
                        <div class="flex flex-col md:flex-row gap-4">
                            <div class="flex-1 relative">
                                <i data-lucide="search" class="absolute left-4 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-500"></i>
                                <select name="aluno_id_treino" onchange="carregarTreino(this.value)" 
                                        class="w-full pl-10 bg-[#0f172a] border border-white/10 rounded-xl p-4 text-white focus:border-prof-primary outline-none appearance-none">
                                    <option value="">-- Buscar Aluno --</option>
                                    <?php foreach($listaAlunos as $a): ?>
                                        <option value="<?= $a['id'] ?>">
                                            <?= htmlspecialchars($a['nome']) ?> 
                                            <span class="text-gray-500 text-sm">(<?= htmlspecialchars($a['plano'] ?? 'Sem plano') ?>)</span>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" 
                                    class="bg-gradient-to-r from-green-600 to-emerald-700 hover:from-green-700 hover:to-emerald-800 text-white px-6 py-4 rounded-xl font-bold flex gap-2 items-center shadow-lg transition-all">
                                <i data-lucide="save" class="w-5 h-5"></i> 
                                Salvar Treino
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <?php foreach(['A','B','C'] as $div): ?>
                        <div class="bg-[#0f172a] rounded-xl border border-white/5 flex flex-col h-full overflow-hidden">
                            <div class="p-4 border-b border-white/5 flex justify-between items-center bg-gradient-to-r from-blue-900/20 to-transparent">
                                <h3 class="font-bold text-white flex items-center gap-2">
                                    <i data-lucide="dumbbell" class="w-4 h-4 text-prof-primary"></i>
                                    Treino <?= $div ?>
                                </h3>
                                <button type="button" onclick="addExercicio('container-<?= $div ?>')" 
                                        class="text-prof-primary hover:text-white text-xs font-bold flex items-center gap-1">
                                    <i data-lucide="plus" class="w-3 h-3"></i>
                                    Adicionar
                                </button>
                            </div>
                            <div id="container-<?= $div ?>" class="p-4 space-y-3 min-h-[300px] flex-1 overflow-y-auto">
                                <!-- Exercícios serão adicionados aqui -->
                            </div>
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
            
            // Limpar containers
            ['A','B','C'].forEach(d => {
                const container = document.getElementById('container-'+d);
                container.innerHTML = '';
                container.classList.remove('bg-gradient-to-b', 'from-green-900/10', 'to-transparent');
            });
            
            try {
                const response = await fetch(`professor.php?acao_ajax=buscar_treino&id=${id}`);
                const data = await response.json();
                
                if (response.ok && data) {
                    ['A','B','C'].forEach(div => {
                        if(data[div] && data[div].length > 0) {
                            const container = document.getElementById('container-'+div);
                            container.classList.add('bg-gradient-to-b', 'from-green-900/10', 'to-transparent');
                            data[div].forEach(t => {
                                addExercicio('container-'+div, t.exercicio || t.nome, t.series);
                            });
                        }
                    });
                    exibirToast("Treino carregado com sucesso!", "sucesso");
                } else {
                    exibirToast("Nenhum treino encontrado para este aluno", "info");
                }
            } catch (error) { 
                console.error(error);
                exibirToast("Erro ao carregar treino", "erro");
            }
        }

        function addExercicio(containerId, nome = '', series = '3x12') {
            const container = document.getElementById(containerId);
            const cleanId = containerId.replace('container-', '');
            const exerciseCount = container.children.length;
            const exerciseNumber = exerciseCount + 1;
            
            const div = document.createElement('div');
            div.className = 'flex gap-2 items-center animate-fadeIn group bg-[#1e293b]/50 p-3 rounded-lg border border-white/5';
            div.innerHTML = `
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs text-gray-500 font-mono">${exerciseNumber}.</span>
                        <input type="text" name="treino[${cleanId}][${exerciseNumber}][nome]" 
                               value="${nome}" 
                               placeholder="Nome do exercício" 
                               class="w-full bg-transparent border-none text-white text-sm focus:outline-none focus:ring-0">
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-500">Reps:</span>
                        <input type="text" name="treino[${cleanId}][${exerciseNumber}][series]" 
                               value="${series}" 
                               placeholder="3x12" 
                               class="w-20 bg-[#0f172a] border border-white/10 rounded px-2 py-1 text-sm text-center text-gray-300 focus:border-prof-primary focus:outline-none">
                    </div>
                </div>
                <button type="button" onclick="this.parentElement.remove(); atualizarNumeros('${containerId}')" 
                        class="text-red-500 hover:text-red-400 p-1 hover:bg-red-500/10 rounded transition-colors opacity-0 group-hover:opacity-100">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            `;
            container.appendChild(div);
            lucide.createIcons();
        }
        
        function atualizarNumeros(containerId) {
            const container = document.getElementById(containerId);
            const exercises = container.querySelectorAll('.animate-fadeIn');
            exercises.forEach((exercise, index) => {
                const numberSpan = exercise.querySelector('.font-mono');
                if (numberSpan) {
                    numberSpan.textContent = `${index + 1}.`;
                }
                // Atualizar nomes dos inputs
                const nomeInput = exercise.querySelector('input[name*="[nome]"]');
                const seriesInput = exercise.querySelector('input[name*="[series]"]');
                if (nomeInput && seriesInput) {
                    const cleanName = nomeInput.name.replace(/\[\d+\]\[nome\]$/, `[${index + 1}][nome]`);
                    const cleanSeries = seriesInput.name.replace(/\[\d+\]\[series\]$/, `[${index + 1}][series]`);
                    nomeInput.name = cleanName;
                    seriesInput.name = cleanSeries;
                }
            });
        }

        function gerarTreinoAutomatico() {
            ['A','B','C'].forEach(d => {
                const container = document.getElementById('container-'+d);
                container.innerHTML = '';
                container.classList.remove('bg-gradient-to-b', 'from-green-900/10', 'to-transparent');
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

        function exibirToast(msg, tipo) {
            const colors = {
                sucesso: 'border-green-500 text-green-300 bg-green-500/10',
                erro: 'border-red-500 text-red-300 bg-red-500/10',
                info: 'border-blue-500 text-blue-300 bg-blue-500/10'
            };
            
            const div = document.createElement('div');
            div.className = `fixed top-5 right-5 z-50 border-l-4 ${colors[tipo] || colors.info} p-4 rounded shadow-2xl flex items-center gap-3 animate-fadeIn`;
            
            const icon = tipo === 'sucesso' ? 'check-circle' : tipo === 'erro' ? 'alert-circle' : 'info';
            div.innerHTML = `<i data-lucide="${icon}" class="w-5 h-5"></i> <span class="text-sm font-medium">${msg}</span>`;
            
            document.body.appendChild(div);
            lucide.createIcons();
            
            setTimeout(() => {
                div.style.opacity = '0';
                div.style.transform = 'translateX(100%)';
                setTimeout(() => div.remove(), 300);
            }, 4000);
        }
    </script>
</body>
</html>