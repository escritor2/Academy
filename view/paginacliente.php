<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFit - Painel Completo</title>
    <link rel="stylesheet" href="src/paginacliente.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-900 text-white overflow-hidden h-screen flex">
    <!-- Elementos Ocultos para o Picture-in-Picture (PiP) -->
    <canvas id="pip-canvas" width="400" height="300" style="display: none;"></canvas>
    <video id="pip-video" muted autoplay playsinline loop style="position: absolute; top: 0; left: 0; width: 1px; height: 1px; opacity: 0.01; pointer-events: none;"></video>

    <!-- Notificação Flutuante (Toast) -->
    <div id="toast-container" class="toast-container">
        <i data-lucide="check-circle" class="w-6 h-6 mr-3"></i>
        <div>
            <h4 class="font-bold">Sucesso!</h4>
            <p class="text-sm text-green-100" id="toast-message">Ação realizada.</p>
        </div>
    </div>

    <!-- BARRA LATERAL (Sidebar) -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h1 class="text-2xl font-bold italic tracking-tighter">Tech<span class="text-orange-500">Fit</span></h1>
        </div>

        <nav class="sidebar-nav">
            <p class="nav-title">Menu Principal</p>
            
            <button onclick="app.nav.switchTab('treinos')" id="btn-treinos" class="nav-item active">
                <i data-lucide="dumbbell" class="w-5 h-5 mr-3"></i> Meus Treinos
            </button>
            
            <button onclick="app.nav.switchTab('agenda')" id="btn-agenda" class="nav-item">
                <i data-lucide="calendar" class="w-5 h-5 mr-3"></i> Agenda
            </button>
            
            <button onclick="app.nav.switchTab('dieta')" id="btn-dieta" class="nav-item">
                <i data-lucide="utensils" class="w-5 h-5 mr-3"></i> Dieta
            </button>
            
            <button onclick="app.nav.switchTab('perfil')" id="btn-perfil" class="nav-item">
                <i data-lucide="user" class="w-5 h-5 mr-3"></i> Perfil
            </button>
        </nav>
        
        <div class="sidebar-footer">
            <div class="user-avatar">CS</div>
            <div class="user-info">
                <p class="text-sm font-medium">Carlos Silva</p>
                <p class="text-xs text-gray-400">Aluno Premium</p>
            </div>
        </div>
    </aside>

    <!-- CONTEÚDO PRINCIPAL -->
    <main class="main-content">
        <!-- ================= SECÇÃO: TREINOS ================= -->
        <div id="view-treinos" class="view-section active">
            <header class="section-header">
                <div>
                    <h2 class="text-3xl font-bold">Meus Treinos</h2>
                    <p class="text-gray-400">Gerencie sua ficha e tempo de execução</p>
                </div>
                
                <!-- CRONÔMETRO GLOBAL -->
                <div class="global-timer">
                    <div class="timer-info">
                        <span class="timer-label">Tempo Total</span>
                        <div id="global-timer" class="timer-display">00:00:00</div>
                    </div>
                    
                    <div class="timer-controls">
                        <button onclick="app.workouts.toggleGlobalTimer()" id="btn-global-play" class="timer-btn play-btn">
                            <i data-lucide="play" class="w-6 h-6 fill-current"></i>
                        </button>
                        
                        <button onclick="app.workouts.finishGlobalTimer()" id="btn-global-stop" class="timer-btn stop-btn" title="Finalizar Treino">
                            <i data-lucide="square" class="w-5 h-5 fill-current"></i>
                        </button>

                         <button onclick="app.workouts.togglePiP()" class="timer-btn pip-btn" title="Modo Mini Player (Fundo)">
                            <i data-lucide="picture-in-picture-2" class="w-5 h-5 fill-current"></i>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Abas de Seleção de Treino (A, B, C) -->
            <div class="workout-tabs">
                <button onclick="app.workouts.renderList('A', this)" class="workout-tab active">Treino A (Peito)</button>
                <button onclick="app.workouts.renderList('B', this)" class="workout-tab">Treino B (Costas)</button>
                <button onclick="app.workouts.renderList('C', this)" class="workout-tab">Treino C (Pernas)</button>
            </div>

            <!-- Botões de Organização -->
            <div class="organization-controls">
                <button onclick="app.workouts.openManualOrganization()" class="org-btn manual-btn">
                    <i data-lucide="edit-3" class="w-4 h-4 mr-2"></i> Organizar Manualmente
                </button>
                <button onclick="app.workouts.openAutoOrganization()" class="org-btn auto-btn">
                    <i data-lucide="zap" class="w-4 h-4 mr-2"></i> Organização Automática
                </button>
            </div>

            <!-- Lista de Exercícios (Preenchida via JS) -->
            <div class="exercise-list" id="exercise-list-container">
                <!-- O JavaScript vai injetar os exercícios aqui -->
            </div>
        </div>

        <!-- ================= SECÇÃO: AGENDA ================= -->
        <div id="view-agenda" class="view-section">
            <header class="section-header">
                <h2 class="text-3xl font-bold">Agenda de Frequência</h2>
                <div class="month-controls">
                    <button onclick="app.agenda.changeMonth(-1)" class="month-btn">
                        <i data-lucide="chevron-left" class="w-5 h-5"></i>
                    </button>
                    <span id="current-month-display" class="month-display">Carregando...</span>
                    <button onclick="app.agenda.changeMonth(1)" class="month-btn">
                        <i data-lucide="chevron-right" class="w-5 h-5"></i>
                    </button>
                </div>
            </header>

            <div class="agenda-grid">
                <!-- Estatísticas -->
                <div class="agenda-stats">
                    <div class="stat-card">
                        <div class="stat-icon trophy">
                            <i data-lucide="trophy" class="w-8 h-8"></i>
                        </div>
                        <h3 class="stat-value" id="completed-workouts">0</h3>
                        <p class="stat-label">Treinos concluídos</p>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon scheduled">
                            <i data-lucide="calendar" class="w-8 h-8"></i>
                        </div>
                        <h3 class="stat-value" id="scheduled-workouts">0</h3>
                        <p class="stat-label">Treinos programados</p>
                    </div>
                    
                    <!-- Legenda -->
                    <div class="legend-card">
                        <h4 class="font-bold mb-4">Legenda</h4>
                        <div class="legend-items">
                            <div class="legend-item">
                                <div class="legend-color today"></div>
                                <span>Hoje</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color selected"></div>
                                <span>Selecionado</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color workout-scheduled"></div>
                                <span>Treino Programado</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color workout-completed"></div>
                                <span>Treino Concluído</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendário Interativo Real -->
                <div class="calendar-container">
                    <div class="calendar-header">
                        <div>DOM</div><div>SEG</div><div>TER</div><div>QUA</div><div>QUI</div><div>SEX</div><div>SÁB</div>
                    </div>
                    <div id="calendar-grid" class="calendar-grid">
                        <!-- Os dias serão gerados aqui pelo Javascript -->
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= SECÇÃO: DIETA ================= -->
        <div id="view-dieta" class="view-section">
            <h2 class="section-title">Dieta & Macros</h2>
            
            <!-- Barras de Macros Dinâmicas -->
            <div class="macros-grid">
                <!-- Proteína -->
                <div class="macro-card">
                    <div class="macro-header">
                        <span class="macro-label">Proteínas</span>
                        <span class="macro-value"><span id="val-prot">0</span>g <span class="macro-goal">/ 180g</span></span>
                    </div>
                    <div class="macro-bar">
                        <div id="bar-prot" class="macro-progress protein"></div>
                    </div>
                </div>

                <!-- Carboidratos -->
                <div class="macro-card">
                    <div class="macro-header">
                        <span class="macro-label">Carboidratos</span>
                        <span class="macro-value"><span id="val-carb">0</span>g <span class="macro-goal">/ 250g</span></span>
                    </div>
                    <div class="macro-bar">
                        <div id="bar-carb" class="macro-progress carb"></div>
                    </div>
                </div>

                <!-- Gorduras -->
                <div class="macro-card">
                    <div class="macro-header">
                        <span class="macro-label">Gorduras</span>
                        <span class="macro-value"><span id="val-fat">0</span>g <span class="macro-goal">/ 70g</span></span>
                    </div>
                    <div class="macro-bar">
                        <div id="bar-fat" class="macro-progress fat"></div>
                    </div>
                </div>
            </div>

            <div class="diet-content">
                <!-- Lista de Refeições -->
                <div class="meals-section">
                    <h3 class="meals-title">Refeições de Hoje (Clique para marcar)</h3>
                    
                    <!-- Café -->
                    <div class="meal-item" data-prot="30" data-carb="45" data-fat="10" onclick="app.diet.toggleMeal(this)">
                        <div class="meal-icon coffee">
                            <i data-lucide="coffee" class="w-5 h-5"></i>
                        </div>
                        <div class="meal-info">
                            <h4 class="meal-name">Café da Manhã</h4>
                            <p class="meal-description">3 Ovos, 2 Pães, 1 Banana</p>
                        </div>
                        <div class="meal-calories">
                            <span class="calories-value">450</span>
                            <span class="calories-label">Kcal</span>
                        </div>
                        <div class="check-icon">
                            <i data-lucide="circle" class="w-6 h-6"></i>
                        </div>
                    </div>

                    <!-- Almoço -->
                    <div class="meal-item" data-prot="50" data-carb="60" data-fat="15" onclick="app.diet.toggleMeal(this)">
                        <div class="meal-icon lunch">
                            <i data-lucide="sun" class="w-5 h-5"></i>
                        </div>
                        <div class="meal-info">
                            <h4 class="meal-name">Almoço</h4>
                            <p class="meal-description">Arroz, Feijão, Frango, Salada</p>
                        </div>
                        <div class="meal-calories">
                            <span class="calories-value">750</span>
                            <span class="calories-label">Kcal</span>
                        </div>
                        <div class="check-icon">
                            <i data-lucide="circle" class="w-6 h-6"></i>
                        </div>
                    </div>
                </div>

                <!-- Hidratação -->
                <div class="hydration-section">
                    <h3 class="hydration-title">Hidratação</h3>
                    <div class="hydration-display">
                        <div id="water-level" class="water-level"></div>
                        <div class="hydration-info">
                            <span class="hydration-value" id="water-val">1.5L</span>
                            <span class="hydration-goal">/ 3.0L</span>
                        </div>
                    </div>
                    <div class="hydration-controls">
                        <button onclick="app.diet.updateWater(-250)" class="hydration-btn minus">
                            <i data-lucide="minus" class="w-4 h-4"></i>
                        </button>
                        <button onclick="app.diet.updateWater(250)" class="hydration-btn add">
                            <i data-lucide="glass-water" class="w-4 h-4 mr-2"></i> +250ml
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= SECÇÃO: PERFIL ================= -->
        <div id="view-perfil" class="view-section">
            <h2 class="section-title">Configurações</h2>
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">CS</div>
                    <div class="profile-info">
                        <h3 class="profile-name">Carlos Silva</h3>
                        <p class="profile-since">Membro desde Jan 2024</p>
                    </div>
                </div>
                <form class="profile-form" onsubmit="app.profile.save(event)">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Nome</label>
                            <input type="text" value="Carlos Silva" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" value="carlos@email.com" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Peso (kg)</label>
                            <input type="number" value="78.5" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Altura (m)</label>
                            <input type="number" value="1.75" class="form-input">
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="save-btn">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal de Organização de Treinos -->
        <div id="workout-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="modal-title">Organizar Treino</h3>
                    <button onclick="app.workouts.closeModal()" class="modal-close">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <div class="modal-body" id="modal-body">
                    <!-- Conteúdo do modal será injetado aqui -->
                </div>
                <div class="modal-footer">
                    <button onclick="app.workouts.closeModal()" class="modal-btn cancel">Cancelar</button>
                    <button onclick="app.workouts.saveWorkout()" class="modal-btn save">Salvar Treino</button>
                </div>
            </div>
        </div>

    </main>
<script src="src/paginacliente.js"></script>
</body>
</html>