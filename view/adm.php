<?php
// =================================================================================
// MOCK DATA (Simulando Banco de Dados)
// =================================================================================

// Lista de Alunos
$alunos = [
    ['id' => 1, 'nome' => 'Carlos Silva', 'email' => 'carlos@email.com', 'plano' => 'Premium', 'status' => 'Ativo'],
    ['id' => 2, 'nome' => 'Ana Souza', 'email' => 'ana@email.com', 'plano' => 'Básico', 'status' => 'Pendente'],
    ['id' => 3, 'nome' => 'Roberto Firmino', 'email' => 'beto@email.com', 'plano' => 'Premium', 'status' => 'Inativo'],
    ['id' => 4, 'nome' => 'Julia Roberts', 'email' => 'ju@email.com', 'plano' => 'Básico', 'status' => 'Ativo'],
];

// Lista de Professores (NOVO)
$professores = [
    ['id' => 101, 'nome' => 'Marcos Vinicius', 'email' => 'marcos@techfit.com', 'especialidade' => 'Musculação', 'status' => 'Ativo'],
    ['id' => 102, 'nome' => 'Fernanda Lima', 'email' => 'fernanda@techfit.com', 'especialidade' => 'Funcional', 'status' => 'Férias'],
    ['id' => 103, 'nome' => 'Pedro Álvares', 'email' => 'pedro@techfit.com', 'especialidade' => 'Natação', 'status' => 'Ativo'],
];

// Produtos / Estoque
$produtos = [
    ['nome' => 'Whey Protein (Pote)', 'cat' => 'Suplemento', 'qtd' => 12, 'preco' => 180.00],
    ['nome' => 'Barra de Proteína', 'cat' => 'Snack', 'qtd' => 45, 'preco' => 12.00],
    ['nome' => 'Garrafa TechFit', 'cat' => 'Acessório', 'qtd' => 4, 'preco' => 35.00], // Baixo estoque
    ['nome' => 'Energético Lata', 'cat' => 'Bebida', 'qtd' => 20, 'preco' => 15.00],
];

// Exercícios para o Gerador (Reestruturado para Treinos A, B, C)
$treino_exercicios = [
    'A' => [
        'Peito' => ['Supino Reto', 'Supino Inclinado', 'Crucifixo', 'Crossover'],
        'Tríceps' => ['Tríceps Corda', 'Tríceps Testa', 'Tríceps Francês'],
    ],
    'B' => [
        'Costas' => ['Puxada Alta', 'Remada Curvada', 'Serrote', 'Levantamento Terra'],
        'Bíceps' => ['Rosca Direta', 'Rosca Alternada', 'Rosca Martelo'],
    ],
    'C' => [
        'Pernas' => ['Agachamento Livre', 'Leg Press 45', 'Cadeira Extensora', 'Stiff'],
        'Ombro' => ['Desenvolvimento Halteres', 'Elevação Lateral', 'Elevação Frontal'],
    ],
];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFit - Painel Administrativo</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        /* ================== CSS BASE (IDENTIDADE VISUAL ALUNO) ================== */
        :root {
            --bg-dark: #111827; /* gray-900 */
            --bg-sidebar: #0f172a; /* slate-900 um pouco diferente */
            --primary: #f97316; /* orange-500 */
            --text-white: #f3f4f6;
            --text-gray: #9ca3af;
            --card-bg: #1f2937; /* gray-800 */
            --border-color: #374151;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; outline: none; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-dark); color: var(--text-white); height: 100vh; display: flex; overflow: hidden; }

        /* Sidebar */
        .sidebar { width: 260px; background-color: var(--bg-sidebar); display: flex; flex-direction: column; padding: 20px; border-right: 1px solid var(--border-color); }
        .sidebar-header { margin-bottom: 40px; display: flex; align-items: center; gap: 10px; }
        .sidebar-nav { flex: 1; display: flex; flex-direction: column; gap: 10px; }
        .nav-title { font-size: 0.75rem; text-transform: uppercase; color: var(--text-gray); margin-bottom: 10px; letter-spacing: 1px; font-weight: 600; }
        
        .nav-item {
            display: flex; align-items: center; padding: 12px 16px; border-radius: 8px;
            color: var(--text-gray); text-decoration: none; font-weight: 500; cursor: pointer; transition: all 0.2s; background: transparent; border: none; width: 100%; font-size: 0.95rem;
        }
        .nav-item:hover { background-color: rgba(255,255,255,0.05); color: white; }
        .nav-item.active { background-color: var(--primary); color: white; }

        .sidebar-footer { margin-top: auto; padding-top: 20px; border-top: 1px solid var(--border-color); display: flex; flex-direction: column; gap: 15px; }
        .user-info-container { display: flex; align-items: center; gap: 12px; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; background-color: var(--card-bg); display: flex; align-items: center; justify-content: center; font-weight: bold; color: var(--primary); border: 1px solid var(--border-color); }
        .btn-logout {
            width: 100%; background: #374151; color: var(--text-white); border: none; padding: 10px 20px; border-radius: 8px;
            font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s;
        }
        .btn-logout:hover { background: #4b5563; }

        /* Main Content */
        .main-content { flex: 1; padding: 30px; overflow-y: auto; position: relative; }
        .section-header { margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        
        /* Cards & Tables */
        .card { background-color: var(--card-bg); border-radius: 12px; border: 1px solid var(--border-color); padding: 20px; margin-bottom: 20px; }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 12px; color: var(--text-gray); font-size: 0.85rem; border-bottom: 1px solid var(--border-color); }
        td { padding: 16px 12px; border-bottom: 1px solid var(--border-color); font-size: 0.95rem; }
        tr:last-child td { border-bottom: none; }
        
        /* Badges */
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .bg-green { background: rgba(16, 185, 129, 0.2); color: #34d399; }
        .bg-yellow { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }
        .bg-red { background: rgba(239, 68, 68, 0.2); color: #f87171; }
        .bg-blue { background: rgba(59, 130, 246, 0.2); color: #60a5fa; } /* Novo para professores */

        /* Buttons */
        .btn-primary {
            background-color: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 8px;
            font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.2s;
        }
        .btn-primary:hover { filter: brightness(1.1); }
        
        /* Inputs (Estilo Dark) */
        .form-input {
            width: 100%; background-color: #111827; border: 1px solid var(--border-color);
            color: white; padding: 10px; border-radius: 6px; margin-top: 5px; margin-bottom: 15px;
        }
        .form-input:focus { border-color: var(--primary); }
        label { color: var(--text-gray); font-size: 0.9rem; }

        /* Modal */
        .modal {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.7); backdrop-filter: blur(5px);
            display: none; justify-content: center; align-items: center; z-index: 100;
        }
        .modal-content {
            background-color: var(--card-bg); width: 500px; padding: 25px;
            border-radius: 12px; border: 1px solid var(--border-color);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
        }

        /* Views Logic */
        .view-section { display: none; animation: fadeIn 0.3s ease; }
        .view-section.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Treino Tabs (Novo) */
        .workout-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--border-color);
        }
        .workout-tab {
            background: transparent;
            border: none;
            padding: 10px 15px;
            color: var(--text-gray);
            font-weight: 600;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
        }
        .workout-tab:hover {
            color: var(--text-white);
        }
        .workout-tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }
        .workout-sheet-tab {
            display: none;
        }
        .workout-sheet-tab.active {
            display: block;
        }

        /* Treino Builder */
        .builder-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 20px; }
        
        /* Accordion para Banco de Exercícios */
        .accordion-header {
            background-color: var(--card-bg);
            padding: 15px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            margin-bottom: 5px;
            border: 1px solid var(--border-color);
            transition: background-color 0.2s;
        }
        .accordion-header:hover {
            background-color: #2a3547; /* Um pouco mais claro */
        }
        .accordion-content {
            padding: 0 15px;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out, padding 0.3s ease-out;
        }
        .accordion-content.active {
            max-height: 500px; /* Valor alto para simular auto */
            padding: 15px;
        }
        .exercise-list-group {
            margin-bottom: 15px;
        }
        .exercise-item { 
            padding: 10px; background: #111827; margin-bottom: 8px; border-radius: 6px; 
            display: flex; justify-content: space-between; align-items: center; cursor: pointer; border: 1px solid transparent;
        }
        .exercise-item:hover { border-color: var(--primary); }
        .workout-sheet { min-height: 400px; border: 2px dashed var(--border-color); border-radius: 12px; padding: 20px; display: flex; flex-direction: column; gap: 10px; }

    </style>
</head>
<body class="bg-gray-900 text-white">

    <div id="toast" style="position: fixed; top: 20px; right: 20px; background: var(--card-bg); border-left: 4px solid #10b981; padding: 15px 20px; border-radius: 6px; display: none; box-shadow: 0 5px 15px rgba(0,0,0,0.3); z-index: 200;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i data-lucide="check-circle" style="color: #10b981;"></i>
            <div>
                <h4 style="font-weight: bold; font-size: 0.9rem;">Sucesso!</h4>
                <p style="font-size: 0.8rem; color: var(--text-gray);">Ação realizada com êxito.</p>
            </div>
        </div>
    </div>

    <aside class="sidebar">
        <div class="sidebar-header">
            <h1 class="text-2xl font-bold italic tracking-tighter" style="font-size: 1.5rem; font-style: italic; font-weight: 800;">
                Tech<span style="color: var(--primary);">Fit</span> <span style="font-size: 0.8rem; color: var(--text-gray); font-style: normal; font-weight: 400;">Admin</span>
            </h1>
        </div>

        <nav class="sidebar-nav">
            <p class="nav-title">Gestão Principal</p>
            
            <button onclick="admin.switchTab('alunos')" id="btn-alunos" class="nav-item active">
                <i data-lucide="users" style="width: 20px; margin-right: 10px;"></i> Alunos
            </button>
            
            <!-- NOVO: Botão de Professores -->
            <button onclick="admin.switchTab('professores')" id="btn-professores" class="nav-item">
                <i data-lucide="graduation-cap" style="width: 20px; margin-right: 10px;"></i> Professores
            </button>
            
            <button onclick="admin.switchTab('produtos')" id="btn-produtos" class="nav-item">
                <i data-lucide="shopping-bag" style="width: 20px; margin-right: 10px;"></i> Produtos
            </button>
            
            <button onclick="admin.switchTab('treinos')" id="btn-treinos" class="nav-item">
                <i data-lucide="dumbbell" style="width: 20px; margin-right: 10px;"></i> Gerar Treino
            </button>
        </nav>
        
        <div class="sidebar-footer">
            <div class="user-info-container">
                <div class="user-avatar">AD</div>
                <div class="user-info">
                    <p style="font-size: 0.9rem; font-weight: 600;">Administrador</p>
                    <p style="font-size: 0.75rem; color: var(--text-gray);">Gerente Geral</p>
                </div>
            </div>
            <!-- NOVO: Botão de Sair -->
            <button onclick="admin.logout()" class="btn-logout">
                <i data-lucide="log-out" style="width: 20px;"></i> Sair
            </button>
        </div>
    </aside>

    <main class="main-content">

        <!-- Seção de Alunos (Existente) -->
        <div id="view-alunos" class="view-section active">
            <header class="section-header">
                <div>
                    <h2 style="font-size: 1.8rem; font-weight: 700;">Controle de Alunos</h2>
                    <p style="color: var(--text-gray);">Gerencie cadastros e mensalidades</p>
                </div>
                <button onclick="admin.toggleModal('modal-cadastro-aluno')" class="btn-primary">
                    <i data-lucide="user-plus" style="width: 18px;"></i> Novo Aluno
                </button>
            </header>

            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Plano</th>
                            <th>Status</th>
                            <th style="text-align: right;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alunos as $aluno): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 32px; height: 32px; background: #374151; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 0.8rem; font-weight: bold;">
                                        <?php echo substr($aluno['nome'], 0, 1); ?>
                                    </div>
                                    <?php echo $aluno['nome']; ?>
                                </div>
                            </td>
                            <td><?php echo $aluno['email']; ?></td>
                            <td><?php echo $aluno['plano']; ?></td>
                            <td>
                                <?php 
                                    $badgeClass = '';
                                    if ($aluno['status'] == 'Ativo') $badgeClass = 'bg-green';
                                    else if ($aluno['status'] == 'Pendente') $badgeClass = 'bg-yellow';
                                    else $badgeClass = 'bg-red';
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>"><?php echo $aluno['status']; ?></span>
                            </td>
                            <td style="text-align: right;">
                                <button style="background: none; border: none; color: var(--text-gray); cursor: pointer; margin-right: 10px;"><i data-lucide="edit-2" style="width: 16px;"></i></button>
                                <button style="background: none; border: none; color: #f87171; cursor: pointer;"><i data-lucide="trash-2" style="width: 16px;"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- NOVO: Seção de Professores -->
        <div id="view-professores" class="view-section">
            <header class="section-header">
                <div>
                    <h2 style="font-size: 1.8rem; font-weight: 700;">Controle de Professores</h2>
                    <p style="color: var(--text-gray);">Gerencie a equipe de instrutores e suas especialidades</p>
                </div>
                <button onclick="admin.toggleModal('modal-cadastro-professor')" class="btn-primary">
                    <i data-lucide="user-plus" style="width: 18px;"></i> Novo Professor
                </button>
            </header>

            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Especialidade</th>
                            <th>Status</th>
                            <th style="text-align: right;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($professores as $professor): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 32px; height: 32px; background: #374151; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 0.8rem; font-weight: bold;">
                                        <?php echo substr($professor['nome'], 0, 1); ?>
                                    </div>
                                    <?php echo $professor['nome']; ?>
                                </div>
                            </td>
                            <td><?php echo $professor['email']; ?></td>
                            <td><?php echo $professor['especialidade']; ?></td>
                            <td>
                                <?php 
                                    $badgeClass = '';
                                    if ($professor['status'] == 'Ativo') $badgeClass = 'bg-green';
                                    else if ($professor['status'] == 'Férias') $badgeClass = 'bg-blue';
                                    else $badgeClass = 'bg-red';
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>"><?php echo $professor['status']; ?></span>
                            </td>
                            <td style="text-align: right;">
                                <button style="background: none; border: none; color: var(--text-gray); cursor: pointer; margin-right: 10px;"><i data-lucide="edit-2" style="width: 16px;"></i></button>
                                <button style="background: none; border: none; color: #f87171; cursor: pointer;"><i data-lucide="trash-2" style="width: 16px;"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="view-produtos" class="view-section">
            <header class="section-header">
                <div>
                    <h2 style="font-size: 1.8rem; font-weight: 700;">Controle de Produtos</h2>
                    <p style="color: var(--text-gray);">Estoque da Loja e Bar</p>
                </div>
                <button onclick="admin.showToast('Funcionalidade de adição...')" class="btn-primary" style="background-color: #374151;">
                    <i data-lucide="package-plus" style="width: 18px;"></i> Adicionar Item
                </button>
            </header>

            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Categoria</th>
                            <th>Estoque</th>
                            <th>Preço Unit.</th>
                            <th>Situação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtos as $prod): ?>
                        <tr>
                            <td style="font-weight: 600;"><?php echo $prod['nome']; ?></td>
                            <td><?php echo $prod['cat']; ?></td>
                            <td style="font-weight: bold;"><?php echo $prod['qtd']; ?></td>
                            <td>R$ <?php echo number_format($prod['preco'], 2, ',', '.'); ?></td>
                            <td>
                                <?php if($prod['qtd'] < 5): ?>
                                    <span style="color: #f87171; font-size: 0.8rem; display: flex; align-items: center; gap: 5px;">
                                        <i data-lucide="alert-triangle" style="width: 14px;"></i> Baixo Estoque
                                    </span>
                                <?php else: ?>
                                    <span style="color: #34d399; font-size: 0.8rem;">Normal</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="view-treinos" class="view-section">
            <header class="section-header">
                <div>
                    <h2 style="font-size: 1.8rem; font-weight: 700;">Gerador de Treinos</h2>
                    <p style="color: var(--text-gray);">Monte a ficha e salve no perfil do aluno</p>
                </div>
                <button onclick="admin.saveWorkout()" class="btn-primary">
                    <i data-lucide="save" style="width: 18px;"></i> Salvar Treinos (A, B, C)
                </button>
            </header>
            
            <div class="card" style="margin-bottom: 20px;">
                <label>Selecione o Aluno:</label>
                <select class="form-input" style="width: 300px; display: block;">
                    <?php foreach($alunos as $aluno): ?>
                        <option value="<?php echo $aluno['id']; ?>"><?php echo $aluno['nome']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="builder-grid">
                <div id="exercise-bank-container">
                    <h4 style="margin-bottom: 15px; font-weight: 600; color: var(--primary);">Banco de Exercícios (Treino <span id="current-treino-label">A</span>)</h4>
                    
                    <!-- O conteúdo do banco de exercícios será renderizado aqui pelo JS -->
                    <div id="exercise-accordion">
                        <!-- Conteúdo dinâmico -->
                    </div>
                </div>

                <div>
                    <!-- Abas de Seleção de Treino (A, B, C) -->
                    <div class="workout-tabs">
                        <button onclick="admin.switchWorkoutTab('A', this)" id="tab-A" class="workout-tab active">Treino A</button>
                        <button onclick="admin.switchWorkoutTab('B', this)" id="tab-B" class="workout-tab">Treino B</button>
                        <button onclick="admin.switchWorkoutTab('C', this)" id="tab-C" class="workout-tab">Treino C</button>
                    </div>

                    <!-- Containers dos Treinos -->
                    <div id="workout-container-A" class="workout-sheet workout-sheet-tab active">
                        <p style="text-align: center; color: var(--text-gray); margin-top: 50px;" id="empty-msg-A">
                            Clique nos exercícios à esquerda para adicionar ao Treino A.
                        </p>
                    </div>
                    <div id="workout-container-B" class="workout-sheet workout-sheet-tab">
                        <p style="text-align: center; color: var(--text-gray); margin-top: 50px;" id="empty-msg-B">
                            Clique nos exercícios à esquerda para adicionar ao Treino B.
                        </p>
                    </div>
                    <div id="workout-container-C" class="workout-sheet workout-sheet-tab">
                        <p style="text-align: center; color: var(--text-gray); margin-top: 50px;" id="empty-msg-C">
                            Clique nos exercícios à esquerda para adicionar ao Treino C.
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- Modal de Cadastro de Aluno (Nome alterado para ser específico) -->
    <div id="modal-cadastro-aluno" class="modal">
        <div class="modal-content">
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <h3 style="font-size: 1.2rem; font-weight: 700;">Cadastrar Novo Aluno</h3>
                <button onclick="admin.toggleModal('modal-cadastro-aluno')" style="background:none; border:none; color:white; cursor:pointer;"><i data-lucide="x"></i></button>
            </div>
            
            <form onsubmit="event.preventDefault(); admin.toggleModal('modal-cadastro-aluno'); admin.showToast('Aluno cadastrado com sucesso!');">
                <label>Nome Completo</label>
                <input type="text" class="form-input" required placeholder="Ex: João da Silva">
                
                <label>Email</label>
                <input type="email" class="form-input" required placeholder="email@exemplo.com">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label>Plano</label>
                        <select class="form-input">
                            <option>Mensal</option>
                            <option>Trimestral</option>
                            <option>Anual</option>
                        </select>
                    </div>
                    <div>
                        <label>Valor (R$)</label>
                        <input type="number" class="form-input" value="89.90">
                    </div>
                </div>

                <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="admin.toggleModal('modal-cadastro-aluno')" style="background: transparent; border: 1px solid var(--border-color); color: white; padding: 10px 20px; border-radius: 8px; cursor: pointer;">Cancelar</button>
                    <button type="submit" class="btn-primary">Confirmar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- NOVO: Modal de Cadastro de Professor -->
    <div id="modal-cadastro-professor" class="modal">
        <div class="modal-content">
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <h3 style="font-size: 1.2rem; font-weight: 700;">Cadastrar Novo Professor</h3>
                <button onclick="admin.toggleModal('modal-cadastro-professor')" style="background:none; border:none; color:white; cursor:pointer;"><i data-lucide="x"></i></button>
            </div>
            
            <form onsubmit="event.preventDefault(); admin.toggleModal('modal-cadastro-professor'); admin.showToast('Professor cadastrado com sucesso!');">
                <label>Nome Completo</label>
                <input type="text" class="form-input" required placeholder="Ex: Maria da Silva">
                
                <label>Email</label>
                <input type="email" class="form-input" required placeholder="maria@techfit.com">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label>Especialidade</label>
                        <select class="form-input">
                            <option>Musculação</option>
                            <option>Funcional</option>
                            <option>Natação</option>
                            <option>Pilates</option>
                        </select>
                    </div>
                    <div>
                        <label>Salário Base (R$)</label>
                        <input type="number" class="form-input" value="2500.00">
                    </div>
                </div>

                <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="admin.toggleModal('modal-cadastro-professor')" style="background: transparent; border: 1px solid var(--border-color); color: white; padding: 10px 20px; border-radius: 8px; cursor: pointer;">Cancelar</button>
                    <button type="submit" class="btn-primary">Confirmar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Inicializa ícones Lucide
        // lucide.createIcons(); // Movido para admin.init()

        const admin = {
            // Troca de Abas
            switchTab: (tabId) => {
                // Remove active de todos
                document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
                document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
                
                // Adiciona active no selecionado
                document.getElementById('view-' + tabId).classList.add('active');
                document.getElementById('btn-' + tabId).classList.add('active');
            },

            // Modal Toggle
            toggleModal: (modalId) => {
                const modal = document.getElementById(modalId);
                modal.style.display = (modal.style.display === 'flex') ? 'none' : 'flex';
            },

            // Sistema de Toast simples
            showToast: (msg) => {
                const toast = document.getElementById('toast');
                toast.querySelector('p').innerText = msg;
                toast.style.display = 'flex';
                toast.style.animation = 'fadeIn 0.3s ease';
                setTimeout(() => { toast.style.display = 'none'; }, 3000);
            },

            // NOVO: Função de Logout
            logout: () => {
                // Redireciona para a página principal (assumindo que é a raiz do site)
                window.location.href = '/';
            },

            // Lógica do Gerador de Treino
            currentWorkoutTab: 'A', // Controla qual aba está ativa
            
            // Mock Data de Exercícios (para uso no JS)
            treinoExercicios: <?php echo json_encode($treino_exercicios); ?>,

            // Função para alternar o accordion
            toggleAccordion: (headerElement) => {
                const content = headerElement.nextElementSibling;
                const icon = headerElement.querySelector('i');

                if (content.classList.contains('active')) {
                    content.classList.remove('active');
                    icon.setAttribute('data-lucide', 'chevron-down');
                } else {
                    // Fecha todos os outros
                    document.querySelectorAll('.accordion-content.active').forEach(c => {
                        c.classList.remove('active');
                        c.previousElementSibling.querySelector('i').setAttribute('data-lucide', 'chevron-down');
                    });
                    
                    content.classList.add('active');
                    icon.setAttribute('data-lucide', 'chevron-up');
                }
                lucide.createIcons();
            },

            // Função para renderizar o banco de exercícios
            renderExerciseBank: (treinoId) => {
                const bankContainer = document.getElementById('exercise-accordion');
                const label = document.getElementById('current-treino-label');
                const exercicios = admin.treinoExercicios[treinoId];
                let html = '';

                label.innerText = treinoId;

                for (const grupo in exercicios) {
                    const lista = exercicios[grupo];
                    
                    // Header do Accordion
                    html += `<div class="accordion-header" onclick="admin.toggleAccordion(this)">
                                <span>${grupo}</span>
                                <i data-lucide="chevron-down" style="width: 18px;"></i>
                            </div>
                            <div class="accordion-content">
                                <div class="exercise-list-group">`;
                    
                    // Lista de Exercícios
                    lista.forEach(ex => {
                        html += `<div class="exercise-item" onclick="admin.addExercise('${ex}')">
                                    <span>${ex}</span>
                                    <i data-lucide="plus-circle" style="width: 16px; color: var(--text-gray);"></i>
                                </div>`;
                    });

                    html += `       </div>
                            </div>`;
                }

                bankContainer.innerHTML = html;
                lucide.createIcons();
            },

            saveWorkout: () => {
                // Lógica de salvar: Aqui você pegaria o conteúdo de workout-container-A, B e C
                // e enviaria para o backend (simulado por um toast)
                admin.showToast('Treinos A, B e C salvos para o aluno!');
            },

            addExercise: (name) => {
                const container = document.getElementById('workout-container-' + admin.currentWorkoutTab);
                const emptyMsg = document.getElementById('empty-msg-' + admin.currentWorkoutTab);
                if(emptyMsg) emptyMsg.style.display = 'none';

                const div = document.createElement('div');
                div.classList.add('workout-item-builder'); // Nova classe para estilização
                div.style.cssText = 'background: #1f2937; padding: 15px; border-radius: 8px; border-left: 4px solid var(--primary); display: flex; justify-content: space-between; align-items: center; animation: fadeIn 0.2s; margin-bottom: 10px;';
                
                div.innerHTML = `
                    <div style="flex: 1;">
                        <span style="font-weight: bold; display: block; margin-bottom: 5px;">${name}</span>
                        <div style="display: flex; gap: 15px; align-items: center;">
                            <div style="display: flex; flex-direction: column;">
                                <label style="font-size: 0.7rem; color: var(--text-gray);">Séries/Reps</label>
                                <input type="text" value="3x12" style="background: #111827; border: 1px solid #374151; color: #9ca3af; padding: 4px 8px; border-radius: 4px; width: 80px; font-size: 0.8rem;">
                            </div>
                            <div style="display: flex; flex-direction: column;">
                                <label style="font-size: 0.7rem; color: var(--text-gray);">Carga (kg)</label>
                                <input type="text" placeholder="kg" style="background: #111827; border: 1px solid #374151; color: #9ca3af; padding: 4px 8px; border-radius: 4px; width: 60px; font-size: 0.8rem;">
                            </div>
                            <div style="display: flex; flex-direction: column; flex: 1;">
                                <label style="font-size: 0.7rem; color: var(--text-gray);">Observação</label>
                                <input type="text" placeholder="Ex: Foco na excêntrica" style="background: #111827; border: 1px solid #374151; color: #9ca3af; padding: 4px 8px; border-radius: 4px; width: 100%; font-size: 0.8rem;">
                            </div>
                        </div>
                    </div>
                    <button class="remove-btn" style="background: none; border: none; color: #f87171; cursor: pointer; margin-left: 15px;"><i data-lucide="trash-2" style="width: 18px;"></i></button>
                `;

                // Botão de remover
                div.querySelector('.remove-btn').onclick = function() {
                    div.remove();
                };

                container.appendChild(div);
                lucide.createIcons(); // Recarrega ícone do novo elemento
            },

            // Inicializa o banco de exercícios ao carregar a página
            init: () => {
                lucide.createIcons();
                admin.renderExerciseBank(admin.currentWorkoutTab);
            },

            switchWorkoutTab: (tabId, element) => {
                admin.currentWorkoutTab = tabId;
                // Remove active de todos os containers e tabs
                document.querySelectorAll('.workout-sheet-tab').forEach(el => el.classList.remove('active'));
                document.querySelectorAll('.workout-tab').forEach(el => el.classList.remove('active'));
                
                // Adiciona active no selecionado
                document.getElementById('workout-container-' + tabId).classList.add('active');
                element.classList.add('active');

                // Renderiza o banco de exercícios para o treino selecionado
                admin.renderExerciseBank(tabId);
            },

            saveWorkout: () => {
                // Lógica de salvar: Aqui você pegaria o conteúdo de workout-container-A, B e C
                // e enviaria para o backend (simulado por um toast)
                admin.showToast('Treinos A, B e C salvos para o aluno!');
            },

            addExercise: (name) => {
                const container = document.getElementById('workout-container-' + admin.currentWorkoutTab);
                const emptyMsg = document.getElementById('empty-msg-' + admin.currentWorkoutTab);
                if(emptyMsg) emptyMsg.style.display = 'none';

                const div = document.createElement('div');
                div.classList.add('workout-item-builder'); // Nova classe para estilização
                div.style.cssText = 'background: #1f2937; padding: 15px; border-radius: 8px; border-left: 4px solid var(--primary); display: flex; justify-content: space-between; align-items: center; animation: fadeIn 0.2s; margin-bottom: 10px;';
                
                div.innerHTML = `
                    <div style="flex: 1;">
                        <span style="font-weight: bold; display: block; margin-bottom: 5px;">${name}</span>
                        <div style="display: flex; gap: 15px; align-items: center;">
                            <div style="display: flex; flex-direction: column;">
                                <label style="font-size: 0.7rem; color: var(--text-gray);">Séries/Reps</label>
                                <input type="text" value="3x12" style="background: #111827; border: 1px solid #374151; color: #9ca3af; padding: 4px 8px; border-radius: 4px; width: 80px; font-size: 0.8rem;">
                            </div>
                            <div style="display: flex; flex-direction: column;">
                                <label style="font-size: 0.7rem; color: var(--text-gray);">Carga (kg)</label>
                                <input type="text" placeholder="kg" style="background: #111827; border: 1px solid #374151; color: #9ca3af; padding: 4px 8px; border-radius: 4px; width: 60px; font-size: 0.8rem;">
                            </div>
                            <div style="display: flex; flex-direction: column; flex: 1;">
                                <label style="font-size: 0.7rem; color: var(--text-gray);">Observação</label>
                                <input type="text" placeholder="Ex: Foco na excêntrica" style="background: #111827; border: 1px solid #374151; color: #9ca3af; padding: 4px 8px; border-radius: 4px; width: 100%; font-size: 0.8rem;">
                            </div>
                        </div>
                    </div>
                    <button class="remove-btn" style="background: none; border: none; color: #f87171; cursor: pointer; margin-left: 15px;"><i data-lucide="trash-2" style="width: 18px;"></i></button>
                `;

                // Botão de remover
                div.querySelector('.remove-btn').onclick = function() {
                    div.remove();
                };

                container.appendChild(div);
                lucide.createIcons(); // Recarrega ícone do novo elemento
            }
        };

        // Chama a função de inicialização
        admin.init();
    </script>
</body>
</html>
