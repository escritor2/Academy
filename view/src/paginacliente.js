// Inicializa ícones do Lucide
lucide.createIcons();

const app = {
    // ESTADO DA APLICAÇÃO (Dados globais)
    state: {
        currentWorkoutType: 'A', // Estado do treino atual
        waterCurrent: 1500, // em ml
        waterGoal: 3000,
        macros: {
            prot: { current: 0, goal: 180 },
            carb: { current: 0, goal: 250 },
            fat:  { current: 0, goal: 70 }
        },
        // Variáveis do Cronômetro Global
        globalTimer: {
            seconds: 0,
            interval: null,
            isRunning: false,
            isPiPActive: false
        },
        // Variáveis da Agenda
        calendar: {
            currentDate: new Date()
        },
        // Variáveis para organização de treinos
        workoutOrganization: {
            currentMode: null, // 'manual' ou 'auto'
            selectedWorkout: null
        }
    },

    // DADOS DOS TREINOS
    data: {
        workouts: {
            'A': [
                { name: 'Supino Reto', equip: 'Barra', sets: 4, reps: '8-12', weight: 30, done: false },
                { name: 'Supino Inclinado', equip: 'Halter', sets: 3, reps: '10-12', weight: 22, done: false },
                { name: 'Tríceps Corda', equip: 'Polia', sets: 4, reps: '15', weight: 25, done: false }
            ],
            'B': [
                { name: 'Puxada Alta', equip: 'Máquina', sets: 4, reps: '10', weight: 50, done: false },
                { name: 'Remada Curvada', equip: 'Barra', sets: 4, reps: '8-10', weight: 40, done: false },
                { name: 'Rosca Direta', equip: 'Barra W', sets: 3, reps: '12', weight: 15, done: false }
            ],
            'C': [
                { name: 'Agachamento Livre', equip: 'Barra', sets: 5, reps: '8', weight: 80, done: false },
                { name: 'Leg Press 45', equip: 'Máquina', sets: 4, reps: '12', weight: 200, done: false },
                { name: 'Elevação Lateral', equip: 'Halter', sets: 4, reps: '15', weight: 10, done: false }
            ]
        },
        // Dados para organização automática
        workoutTemplates: {
            'Iniciante': {
                'A': ['Supino Reto', 'Crucifixo', 'Tríceps Testa'],
                'B': ['Puxada Alta', 'Remada Curvada', 'Rosca Direta'],
                'C': ['Agachamento Livre', 'Cadeira Extensora', 'Mesa Flexora']
            },
            'Intermediário': {
                'A': ['Supino Reto', 'Supino Inclinado', 'Crucifixo', 'Tríceps Corda', 'Tríceps Testa'],
                'B': ['Puxada Alta', 'Remada Curvada', 'Pulldown', 'Rosca Direta', 'Rosca Martelo'],
                'C': ['Agachamento Livre', 'Leg Press', 'Cadeira Extensora', 'Mesa Flexora', 'Panturrilha']
            },
            'Avançado': {
                'A': ['Supino Reto', 'Supino Inclinado', 'Supino Declinado', 'Crucifixo', 'Cross Over', 'Tríceps Corda', 'Tríceps Testa', 'Francês'],
                'B': ['Puxada Alta', 'Remada Curvada', 'Pulldown', 'Remada Cavalinho', 'Rosca Direta', 'Rosca Martelo', 'Rosca Concentrada', 'Rosca Scott'],
                'C': ['Agachamento Livre', 'Leg Press', 'Agachamento Hack', 'Cadeira Extensora', 'Mesa Flexora', 'Stiff', 'Panturrilha Sentado', 'Panturrilha em Pé']
            }
        },
        // Exercícios disponíveis para organização manual
        availableExercises: [
            { name: 'Supino Reto', equip: 'Barra', muscle: 'Peito' },
            { name: 'Supino Inclinado', equip: 'Halter', muscle: 'Peito' },
            { name: 'Supino Declinado', equip: 'Barra', muscle: 'Peito' },
            { name: 'Crucifixo', equip: 'Halter', muscle: 'Peito' },
            { name: 'Cross Over', equip: 'Polia', muscle: 'Peito' },
            { name: 'Puxada Alta', equip: 'Máquina', muscle: 'Costas' },
            { name: 'Remada Curvada', equip: 'Barra', muscle: 'Costas' },
            { name: 'Pulldown', equip: 'Polia', muscle: 'Costas' },
            { name: 'Remada Cavalinho', equip: 'Máquina', muscle: 'Costas' },
            { name: 'Agachamento Livre', equip: 'Barra', muscle: 'Pernas' },
            { name: 'Leg Press', equip: 'Máquina', muscle: 'Pernas' },
            { name: 'Agachamento Hack', equip: 'Máquina', muscle: 'Pernas' },
            { name: 'Cadeira Extensora', equip: 'Máquina', muscle: 'Pernas' },
            { name: 'Mesa Flexora', equip: 'Máquina', muscle: 'Pernas' },
            { name: 'Stiff', equip: 'Barra', muscle: 'Pernas' },
            { name: 'Panturrilha Sentado', equip: 'Máquina', muscle: 'Pernas' },
            { name: 'Panturrilha em Pé', equip: 'Máquina', muscle: 'Pernas' },
            { name: 'Rosca Direta', equip: 'Barra', muscle: 'Braços' },
            { name: 'Rosca Martelo', equip: 'Halter', muscle: 'Braços' },
            { name: 'Rosca Concentrada', equip: 'Halter', muscle: 'Braços' },
            { name: 'Rosca Scott', equip: 'Máquina', muscle: 'Braços' },
            { name: 'Tríceps Corda', equip: 'Polia', muscle: 'Braços' },
            { name: 'Tríceps Testa', equip: 'Barra', muscle: 'Braços' },
            { name: 'Francês', equip: 'Halter', muscle: 'Braços' },
            { name: 'Elevação Lateral', equip: 'Halter', muscle: 'Ombros' },
            { name: 'Desenvolvimento', equip: 'Barra', muscle: 'Ombros' },
            { name: 'Elevação Frontal', equip: 'Halter', muscle: 'Ombros' }
        ]
    },

    // NAVEGAÇÃO
    nav: {
        switchTab: (tabId) => {
            document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.nav-item').forEach(el => {
                el.classList.remove('active');
            });
            
            document.getElementById('view-' + tabId).classList.add('active');
            document.getElementById('btn-' + tabId).classList.add('active');
            
            // Atualizar banner quando voltar para a aba de treinos
            if (tabId === 'treinos') {
                app.workouts.updateTodayWorkoutBanner();
            }
        }
    },

    // LÓGICA DE TREINOS
    workouts: {
        renderList: (type, btnElement) => {
            // Verificar se o treino está bloqueado
            const isLocked = app.agenda.checkWorkoutLock(type);
            const todayWorkout = app.agenda.getTodayScheduledWorkout();
            
            if (isLocked) {
                let message = '';
                if (todayWorkout && todayWorkout.completed) {
                    message = 'Treino do dia já concluído! 🎉';
                } else if (todayWorkout && todayWorkout.type !== type) {
                    message = `Hoje é dia do ${app.agenda.getWorkoutName(todayWorkout.type)}`;
                } else if (todayWorkout && todayWorkout.type === 'descanso') {
                    message = 'Hoje é dia de descanso! 💤';
                }
                
                app.utils.showToast(message);
                return;
            }

            app.state.currentWorkoutType = type; // Atualiza estado global

            if(btnElement) {
                document.querySelectorAll('.workout-tab').forEach(t => {
                    t.classList.remove('active');
                });
                btnElement.classList.add('active');
            }

            const listContainer = document.getElementById('exercise-list-container');
            listContainer.innerHTML = ''; 

            const exercises = app.data.workouts[type];

            exercises.forEach((ex, index) => {
                const html = `
                    <div class="exercise-card">
                        <div class="exercise-icon">
                            <i data-lucide="dumbbell" class="w-8 h-8"></i>
                        </div>
                        <div class="exercise-details">
                            <h4 class="exercise-name">${ex.name}</h4>
                            <p class="exercise-equip">${ex.equip}</p>
                            <div class="exercise-stats">
                                <span class="stat-badge">Sets: ${ex.sets}</span>
                                <span class="stat-badge">Reps: ${ex.reps}</span>
                            </div>
                        </div>
                        <div class="exercise-controls">
                            <div class="weight-input">
                                <label class="weight-label">Carga (kg)</label>
                                <input type="number" value="${ex.weight}" class="weight-field" onchange="app.utils.showToast('Carga atualizada')">
                            </div>
                            <button onclick="app.workouts.startTimer(this)" class="timer-btn" title="Cronômetro 60s">
                                <i data-lucide="timer" class="w-6 h-6"></i>
                            </button>
                            <button onclick="app.workouts.toggleCheck(this, '${type}', ${index})" class="check-btn ${ex.done ? 'checked' : ''}" title="Concluir">
                                <i data-lucide="check" class="w-6 h-6"></i>
                            </button>
                        </div>
                    </div>
                `;
                listContainer.innerHTML += html;
            });
            lucide.createIcons();
        },

        // MODIFICADO: toggleCheck agora atualiza o estado e verifica conclusão automática
        toggleCheck: (btn, workoutType, exerciseIndex) => {
            const card = btn.closest('.exercise-card');
            const exercise = app.data.workouts[workoutType][exerciseIndex];
            
            if (btn.classList.contains('checked')) {
                btn.classList.remove('checked');
                card.classList.remove('border-green-500', 'bg-gray-800/80');
                card.classList.add('border-gray-700');
                exercise.done = false;
            } else {
                btn.classList.add('checked');
                card.classList.remove('border-gray-700');
                card.classList.add('border-green-500', 'bg-gray-800/80');
                exercise.done = true;
                app.utils.showToast("Exercício concluído!");
                
                // Verificar se todos os exercícios foram concluídos
                app.agenda.checkAutoCompleteWorkout();
            }
        },

        startTimer: (btn) => {
            if(btn.classList.contains('active')) return;
            let timeLeft = 60;
            const originalContent = btn.innerHTML;
            btn.classList.add('active');
            btn.innerHTML = `<span class="font-bold text-sm">${timeLeft}s</span>`;
            const interval = setInterval(() => {
                timeLeft--;
                btn.innerHTML = `<span class="font-bold text-sm">${timeLeft}s</span>`;
                if (timeLeft <= 0) {
                    clearInterval(interval);
                    btn.classList.remove('active');
                    btn.innerHTML = originalContent;
                    lucide.createIcons();
                    app.utils.showToast("Descanso finalizado!");
                }
            }, 1000);
        },

        // --- CRONÔMETRO GLOBAL ---
        toggleGlobalTimer: () => {
            const btn = document.getElementById('btn-global-play');
            const timerDisplay = document.getElementById('global-timer');
            const state = app.state.globalTimer;

            if (state.isRunning) {
                // PAUSAR
                clearInterval(state.interval);
                state.isRunning = false;
                
                btn.classList.remove('bg-green-600', 'hover:bg-green-700');
                btn.classList.add('bg-yellow-600', 'hover:bg-yellow-700');
                btn.innerHTML = '<i data-lucide="play" class="w-6 h-6 fill-current"></i>'; 
                timerDisplay.classList.remove('global-timer-active');
                
                app.utils.showToast("Treino Pausado");
            } else {
                // INICIAR
                state.isRunning = true;
                
                btn.classList.remove('bg-yellow-600', 'hover:bg-yellow-700');
                btn.classList.add('bg-green-600', 'hover:bg-green-700');
                btn.innerHTML = '<i data-lucide="pause" class="w-6 h-6 fill-current"></i>'; 
                timerDisplay.classList.add('global-timer-active');
                
                app.utils.showToast("Treino Iniciado!");

                state.interval = setInterval(() => {
                    state.seconds++;
                    const hrs = Math.floor(state.seconds / 3600);
                    const mins = Math.floor((state.seconds % 3600) / 60);
                    const secs = state.seconds % 60;
                    
                    const formatted = 
                        (hrs > 0 ? (hrs < 10 ? "0" + hrs : hrs) + ":" : "") + 
                        (mins < 10 ? "0" + mins : mins) + ":" + 
                        (secs < 10 ? "0" + secs : secs);
                    
                    timerDisplay.innerText = formatted;

                }, 1000);
            }
            lucide.createIcons();
        },

        finishGlobalTimer: () => {
            const state = app.state.globalTimer;
            if(state.seconds === 0) return;

            clearInterval(state.interval);
            state.isRunning = false;
            
            const finalTime = document.getElementById('global-timer').innerText;
            state.seconds = 0;
            
            document.getElementById('global-timer').innerText = "00:00:00";
            document.getElementById('global-timer').classList.remove('global-timer-active');
            
            const btn = document.getElementById('btn-global-play');
            btn.classList.remove('bg-yellow-600', 'hover:bg-yellow-700');
            btn.classList.add('bg-green-600', 'hover:bg-green-700');
            btn.innerHTML = '<i data-lucide="play" class="w-6 h-6 fill-current"></i>';
            
            lucide.createIcons();
            app.utils.showToast(`Treino finalizado! Tempo: ${finalTime}`);
        },

        // --- Picture-in-Picture (PiP) "Mini Site" ---
        renderToCanvas: () => {
            const canvas = document.getElementById('pip-canvas');
            const ctx = canvas.getContext('2d');
            const state = app.state.globalTimer;
            const workoutType = app.state.currentWorkoutType;
            
            // 1. Fundo Geral
            ctx.fillStyle = '#111827'; 
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            // 2. Cabeçalho (Laranja)
            ctx.fillStyle = '#ea580c';
            ctx.fillRect(0, 0, canvas.width, 50);
            
            // Logo
            ctx.font = 'bold 24px Inter, sans-serif';
            ctx.fillStyle = '#ffffff';
            ctx.textAlign = 'left';
            ctx.fillText('TechFit', 20, 35);

            // Status Badge
            ctx.font = '14px Inter, sans-serif';
            ctx.fillStyle = state.isRunning ? '#dcfce7' : '#fee2e2';
            ctx.fillRect(300, 15, 80, 24);
            ctx.fillStyle = state.isRunning ? '#166534' : '#991b1b';
            ctx.textAlign = 'center';
            ctx.fillText(state.isRunning ? 'ON' : 'PAUSE', 340, 32);

            // 3. Info Principal
            ctx.fillStyle = '#9ca3af';
            ctx.font = '16px Inter, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(`Treino Atual: Ficha ${workoutType}`, canvas.width / 2, 100);

            const timerText = document.getElementById('global-timer').innerText;
            ctx.font = 'bold 60px monospace';
            ctx.fillStyle = state.isRunning ? '#4ade80' : '#ffffff'; 
            ctx.shadowColor = state.isRunning ? "rgba(74, 222, 128, 0.5)" : "transparent";
            ctx.shadowBlur = 15;
            ctx.fillText(timerText, canvas.width / 2, 180);
            ctx.shadowBlur = 0;

            // 4. Barra de Progresso
            ctx.fillStyle = '#374151';
            ctx.fillRect(50, 240, 300, 10);
            
            ctx.fillStyle = '#f97316';
            let progress = (state.seconds % 60) / 60 * 300; 
            if(!state.isRunning && state.seconds > 0) progress = 300; 
            if(state.seconds === 0) progress = 0;
            
            ctx.fillRect(50, 240, progress, 10);
            
            ctx.font = '12px Inter, sans-serif';
            ctx.fillStyle = '#6b7280';
            ctx.fillText('Progresso da Série', canvas.width / 2, 270);
        },

        togglePiP: async () => {
            const video = document.getElementById('pip-video');
            const canvas = document.getElementById('pip-canvas');
            
            // Função de Loop Corrigida
            const startLoop = () => {
                function loop() {
                    app.workouts.renderToCanvas();
                    // Continua se o PiP estiver ativo OU se o vídeo não estiver pausado
                    if (document.pictureInPictureElement || !video.paused) {
                        requestAnimationFrame(loop);
                    }
                }
                loop();
            };

            if (document.pictureInPictureElement) {
                await document.exitPictureInPicture();
            } else {
                try {
                    // Tenta inicializar o stream do Canvas
                    if (!video.srcObject) {
                        // Nota: captureStream() pode falhar em alguns navegadores se o canvas estiver "sujo" ou vazio
                        // Desenhamos uma vez antes para garantir
                        app.workouts.renderToCanvas();
                        const stream = canvas.captureStream(30); // 30 FPS
                        video.srcObject = stream;
                    }
                    
                    // Importante: await play() antes de requestPictureInPicture()
                    await video.play();
                    await video.requestPictureInPicture();
                    
                    // Inicia o loop visual
                    startLoop();
                    
                } catch (err) {
                    console.error("Erro PiP:", err);
                    let msg = "Erro ao ativar Mini Player.";
                    if(err.name === 'NotAllowedError') msg = "Permissão para PiP negada.";
                    if(err.name === 'InvalidStateError') msg = "Vídeo ainda não carregado.";
                    app.utils.showToast(msg);
                }
            }
        },

        // --- ORGANIZAÇÃO DE TREINOS ---
        openManualOrganization: () => {
            app.state.workoutOrganization.currentMode = 'manual';
            document.getElementById('modal-title').innerText = 'Organizar Treino Manualmente';
            
            const modalBody = document.getElementById('modal-body');
            modalBody.innerHTML = `
                <div class="workout-organization">
                    <div class="organization-type">
                        <h4 class="type-title">Selecione os Exercícios</h4>
                        <p class="type-description">Arraste e solte para reorganizar a ordem dos exercícios</p>
                        
                        <div class="exercise-list-edit" id="manual-exercise-list">
                            ${app.data.workouts[app.state.currentWorkoutType].map((ex, index) => `
                                <div class="exercise-item-edit" data-index="${index}">
                                    <div class="exercise-drag">
                                        <i data-lucide="grip-vertical" class="w-4 h-4"></i>
                                    </div>
                                    <div class="exercise-details-edit">
                                        <div class="exercise-name">${ex.name}</div>
                                        <div class="exercise-equip">${ex.equip}</div>
                                    </div>
                                    <div class="exercise-actions">
                                        <button class="exercise-action" onclick="app.workouts.removeExercise(this)" title="Remover">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                        
                        <div class="add-exercise-section">
                            <h4 class="type-title">Adicionar Novo Exercício</h4>
                            <div class="exercise-options">
                                <select id="exercise-select" class="form-input">
                                    <option value="">Selecione um exercício</option>
                                    ${app.data.availableExercises.map(ex => 
                                        `<option value="${ex.name}">${ex.name} (${ex.equip}) - ${ex.muscle}</option>`
                                    ).join('')}
                                </select>
                                <button onclick="app.workouts.addExercise()" class="modal-btn save" style="margin-top: 10px;">
                                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Adicionar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('workout-modal').classList.add('active');
            lucide.createIcons();
            app.workouts.initDragAndDrop();
        },

        openAutoOrganization: () => {
            app.state.workoutOrganization.currentMode = 'auto';
            document.getElementById('modal-title').innerText = 'Organização Automática de Treino';
            
            const modalBody = document.getElementById('modal-body');
            modalBody.innerHTML = `
                <div class="workout-organization">
                    <div class="organization-type">
                        <h4 class="type-title">Selecione um Modelo de Treino</h4>
                        <p class="type-description">O sistema irá gerar automaticamente um treino baseado no seu nível de experiência</p>
                        
                        <div class="workout-options">
                            <div class="workout-option" onclick="app.workouts.selectWorkoutTemplate('Iniciante', this)">
                                <div class="workout-option-icon">💪</div>
                                <div class="workout-option-name">Iniciante</div>
                                <div class="workout-option-desc">3 exercícios por grupo</div>
                            </div>
                            <div class="workout-option" onclick="app.workouts.selectWorkoutTemplate('Intermediário', this)">
                                <div class="workout-option-icon">🔥</div>
                                <div class="workout-option-name">Intermediário</div>
                                <div class="workout-option-desc">5 exercícios por grupo</div>
                            </div>
                            <div class="workout-option" onclick="app.workouts.selectWorkoutTemplate('Avançado', this)">
                                <div class="workout-option-icon">🚀</div>
                                <div class="workout-option-name">Avançado</div>
                                <div class="workout-option-desc">8 exercícios por grupo</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('workout-modal').classList.add('active');
        },

        selectWorkoutTemplate: (template, element) => {
            document.querySelectorAll('.workout-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            element.classList.add('selected');
            app.state.workoutOrganization.selectedWorkout = template;
        },

        initDragAndDrop: () => {
            const container = document.getElementById('manual-exercise-list');
            let draggedItem = null;
            
            container.querySelectorAll('.exercise-item-edit').forEach(item => {
                item.setAttribute('draggable', 'true');
                
                item.addEventListener('dragstart', function() {
                    draggedItem = this;
                    setTimeout(() => this.style.opacity = '0.4', 0);
                });
                
                item.addEventListener('dragend', function() {
                    setTimeout(() => this.style.opacity = '1', 0);
                    draggedItem = null;
                });
                
                item.addEventListener('dragover', function(e) {
                    e.preventDefault();
                });
                
                item.addEventListener('dragenter', function(e) {
                    e.preventDefault();
                    this.style.backgroundColor = '#374151';
                });
                
                item.addEventListener('dragleave', function() {
                    this.style.backgroundColor = '';
                });
                
                item.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.style.backgroundColor = '';
                    if (draggedItem !== this) {
                        container.insertBefore(draggedItem, this);
                    }
                });
            });
        },

        removeExercise: (btn) => {
            const exerciseItem = btn.closest('.exercise-item-edit');
            exerciseItem.remove();
        },

        addExercise: () => {
            const select = document.getElementById('exercise-select');
            const selectedExercise = select.value;
            
            if (!selectedExercise) {
                app.utils.showToast('Selecione um exercício para adicionar');
                return;
            }
            
            const exerciseData = app.data.availableExercises.find(ex => ex.name === selectedExercise);
            if (!exerciseData) return;
            
            const exerciseList = document.getElementById('manual-exercise-list');
            const newExercise = document.createElement('div');
            newExercise.className = 'exercise-item-edit';
            newExercise.innerHTML = `
                <div class="exercise-drag">
                    <i data-lucide="grip-vertical" class="w-4 h-4"></i>
                </div>
                <div class="exercise-details-edit">
                    <div class="exercise-name">${exerciseData.name}</div>
                    <div class="exercise-equip">${exerciseData.equip}</div>
                </div>
                <div class="exercise-actions">
                    <button class="exercise-action" onclick="app.workouts.removeExercise(this)" title="Remover">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            `;
            
            exerciseList.appendChild(newExercise);
            lucide.createIcons();
            select.value = '';
            app.workouts.initDragAndDrop();
        },

        saveWorkout: () => {
            const mode = app.state.workoutOrganization.currentMode;
            const workoutType = app.state.currentWorkoutType;
            
            if (mode === 'manual') {
                // Salvar organização manual
                const exerciseList = document.getElementById('manual-exercise-list');
                const exercises = Array.from(exerciseList.querySelectorAll('.exercise-item-edit'));
                
                const updatedWorkout = exercises.map(item => {
                    const name = item.querySelector('.exercise-name').textContent;
                    const equip = item.querySelector('.exercise-equip').textContent;
                    
                    // Encontrar exercício existente ou criar um novo
                    const existingExercise = app.data.workouts[workoutType].find(ex => ex.name === name);
                    if (existingExercise) {
                        return {...existingExercise};
                    } else {
                        return {
                            name: name,
                            equip: equip,
                            sets: 3,
                            reps: '10-12',
                            weight: 20,
                            done: false
                        };
                    }
                });
                
                app.data.workouts[workoutType] = updatedWorkout;
                app.utils.showToast('Treino organizado com sucesso!');
                
            } else if (mode === 'auto' && app.state.workoutOrganization.selectedWorkout) {
                // Salvar organização automática
                const template = app.state.workoutOrganization.selectedWorkout;
                const exerciseNames = app.data.workoutTemplates[template][workoutType];
                
                if (exerciseNames) {
                    const updatedWorkout = exerciseNames.map(name => {
                        const exerciseData = app.data.availableExercises.find(ex => ex.name === name);
                        if (exerciseData) {
                            return {
                                name: exerciseData.name,
                                equip: exerciseData.equip,
                                sets: template === 'Iniciante' ? 3 : template === 'Intermediário' ? 4 : 5,
                                reps: template === 'Iniciante' ? '10-12' : template === 'Intermediário' ? '8-12' : '6-10',
                                weight: 20,
                                done: false
                            };
                        }
                        return null;
                    }).filter(ex => ex !== null);
                    
                    app.data.workouts[workoutType] = updatedWorkout;
                    app.utils.showToast(`Treino ${template} aplicado com sucesso!`);
                }
            } else {
                app.utils.showToast('Selecione um modelo de treino primeiro');
                return;
            }
            
            // Fechar modal e atualizar a lista
            app.workouts.closeModal();
            app.workouts.renderList(workoutType);
        },

        closeModal: () => {
            document.getElementById('workout-modal').classList.remove('active');
            app.state.workoutOrganization.currentMode = null;
            app.state.workoutOrganization.selectedWorkout = null;
        },

        // Atualizar banner do treino do dia
        updateTodayWorkoutBanner: () => {
            const banner = document.getElementById('today-workout-banner');
            const todayWorkout = app.agenda.getTodayScheduledWorkout();
            
            if (!todayWorkout) {
                banner.style.display = 'none';
                return;
            }
            
            banner.style.display = 'flex';
            document.getElementById('today-workout-title').textContent = app.agenda.getWorkoutName(todayWorkout.type);
            
            if (todayWorkout.completed) {
                document.getElementById('today-workout-desc').textContent = 'Concluído com sucesso!';
                document.getElementById('today-workout-status').textContent = 'Concluído 🎉';
                document.getElementById('today-workout-status').classList.add('completed');
            } else {
                document.getElementById('today-workout-desc').textContent = 'Programado para hoje';
                document.getElementById('today-workout-status').textContent = 'Em andamento';
                document.getElementById('today-workout-status').classList.remove('completed');
            }
            
            // Atualizar estado das abas
            document.querySelectorAll('.workout-tab').forEach(tab => {
                const tabType = tab.id.replace('tab-', '');
                const isLocked = app.agenda.checkWorkoutLock(tabType);
                
                if (isLocked) {
                    tab.classList.add('locked');
                } else {
                    tab.classList.remove('locked');
                }
            });
            
            lucide.createIcons();
        }
    },

    // LÓGICA DA AGENDA (COM CALENDÁRIO REAL E PROGRAMAÇÃO DE TREINOS)
    agenda: {
        months: [
            'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
            'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
        ],

        // Dados dos treinos programados
        workoutSchedule: {},
        selectedWorkout: null,

        selectDay: (el, day, month, year) => {
            document.querySelectorAll('.calendar-day').forEach(d => d.classList.remove('selected'));
            el.classList.add('selected');
            
            // Mostrar modal para programar treino no dia selecionado
            app.agenda.showWorkoutScheduler(day, month, year);
        },

        changeMonth: (delta) => {
            const cal = app.state.calendar;
            // Atualiza mês
            cal.currentDate.setMonth(cal.currentDate.getMonth() + delta);
            app.agenda.renderCalendar();
        },

        // Mostrar modal para programar treino
        showWorkoutScheduler: (day, month, year) => {
            const dateKey = `${year}-${month}-${day}`;
            const scheduledWorkout = app.agenda.workoutSchedule[dateKey];
            
            document.getElementById('modal-title').innerText = `Programar Treino - ${day}/${month + 1}/${year}`;
            
            const modalBody = document.getElementById('modal-body');
            modalBody.innerHTML = `
                <div class="workout-scheduler">
                    <div class="schedule-options">
                        <h4 class="type-title">Selecione o Treino</h4>
                        <div class="workout-options">
                            <div class="workout-option ${scheduledWorkout && scheduledWorkout.type === 'A' ? 'selected' : ''}" onclick="app.agenda.selectScheduledWorkout('A', this)">
                                <div class="workout-option-icon">💪</div>
                                <div class="workout-option-name">Treino A</div>
                                <div class="workout-option-desc">Peito/Tríceps</div>
                            </div>
                            <div class="workout-option ${scheduledWorkout && scheduledWorkout.type === 'B' ? 'selected' : ''}" onclick="app.agenda.selectScheduledWorkout('B', this)">
                                <div class="workout-option-icon">🏋️</div>
                                <div class="workout-option-name">Treino B</div>
                                <div class="workout-option-desc">Costas/Bíceps</div>
                            </div>
                            <div class="workout-option ${scheduledWorkout && scheduledWorkout.type === 'C' ? 'selected' : ''}" onclick="app.agenda.selectScheduledWorkout('C', this)">
                                <div class="workout-option-icon">🦵</div>
                                <div class="workout-option-name">Treino C</div>
                                <div class="workout-option-desc">Pernas/Ombros</div>
                            </div>
                            <div class="workout-option ${scheduledWorkout && scheduledWorkout.type === 'descanso' ? 'selected' : ''}" onclick="app.agenda.selectScheduledWorkout('descanso', this)">
                                <div class="workout-option-icon">😴</div>
                                <div class="workout-option-name">Descanso</div>
                                <div class="workout-option-desc">Dia de recuperação</div>
                            </div>
                        </div>
                    </div>

                    <!-- Programação Automática da Semana -->
                    <div class="auto-schedule-section">
                        <h4 class="type-title">Programação Automática da Semana</h4>
                        <p class="type-description">Programe automaticamente toda a semana com uma rotina ABC</p>
                        <div class="auto-schedule-options">
                            <button onclick="app.agenda.autoScheduleWeek('ABC')" class="modal-btn auto-schedule-btn">
                                <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> Programar Semana ABC
                            </button>
                            <button onclick="app.agenda.autoScheduleWeek('AB')" class="modal-btn auto-schedule-btn">
                                <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> Programar Semana AB
                            </button>
                        </div>
                    </div>
                    
                    <div class="schedule-actions">
                        <button onclick="app.agenda.saveScheduledWorkout('${dateKey}')" class="modal-btn save">
                            <i data-lucide="save" class="w-4 h-4 mr-2"></i> Salvar Programação
                        </button>
                        ${scheduledWorkout ? `
                        <button onclick="app.agenda.markWorkoutCompleted('${dateKey}')" class="modal-btn complete">
                            <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Marcar como Concluído
                        </button>
                        <button onclick="app.agenda.clearScheduledWorkout('${dateKey}')" class="modal-btn cancel">
                            <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i> Remover Treino
                        </button>
                        ` : ''}
                    </div>
                    
                    ${scheduledWorkout ? `
                    <div class="current-schedule">
                        <h4 class="type-title">Treino Programado</h4>
                        <div class="schedule-info">
                            <div class="schedule-status ${app.agenda.workoutSchedule[dateKey].completed ? 'completed' : 'scheduled'}">
                                <i data-lucide="${app.agenda.workoutSchedule[dateKey].completed ? 'check-circle' : 'clock'}" class="w-4 h-4 mr-2"></i>
                                ${app.agenda.workoutSchedule[dateKey].completed ? 'Concluído' : 'Programado'} - 
                                ${app.agenda.getWorkoutName(scheduledWorkout.type)}
                            </div>
                            ${app.agenda.workoutSchedule[dateKey].completed ? `
                            <div class="completion-time">
                                <small>Concluído em: ${new Date(app.agenda.workoutSchedule[dateKey].completedAt).toLocaleString()}</small>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;
            
            document.getElementById('workout-modal').classList.add('active');
            lucide.createIcons();
        },

        // Programação automática da semana
        autoScheduleWeek: (pattern) => {
            const today = new Date();
            const currentYear = today.getFullYear();
            const currentMonth = today.getMonth();
            const currentDay = today.getDate();
            
            const patterns = {
                'ABC': ['A', 'B', 'C', 'descanso', 'A', 'B', 'C'],
                'AB': ['A', 'B', 'descanso', 'A', 'B', 'descanso', 'A']
            };
            
            const weekPattern = patterns[pattern];
            if (!weekPattern) return;
            
            // Programar os próximos 7 dias
            for (let i = 0; i < 7; i++) {
                const scheduleDate = new Date(currentYear, currentMonth, currentDay + i);
                const dateKey = `${scheduleDate.getFullYear()}-${scheduleDate.getMonth()}-${scheduleDate.getDate()}`;
                const workoutType = weekPattern[i % weekPattern.length];
                
                app.agenda.workoutSchedule[dateKey] = {
                    type: workoutType,
                    scheduled: true,
                    completed: false,
                    scheduledAt: new Date()
                };
            }
            
            app.agenda.closeModal();
            app.agenda.renderCalendar();
            app.utils.showToast(`Semana ${pattern} programada automaticamente!`);
        },

        // Selecionar treino para programação
        selectScheduledWorkout: (workoutType, element) => {
            document.querySelectorAll('.workout-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            element.classList.add('selected');
            app.agenda.selectedWorkout = workoutType;
        },

        // Salvar treino programado
        saveScheduledWorkout: (dateKey) => {
            if (!app.agenda.selectedWorkout) {
                app.utils.showToast('Selecione um treino primeiro');
                return;
            }

            app.agenda.workoutSchedule[dateKey] = {
                type: app.agenda.selectedWorkout,
                scheduled: true,
                completed: false,
                scheduledAt: new Date()
            };

            app.agenda.closeModal();
            app.agenda.renderCalendar();
            app.utils.showToast('Treino programado com sucesso!');
        },

        // Marcar treino como concluído
        markWorkoutCompleted: (dateKey) => {
            if (app.agenda.workoutSchedule[dateKey]) {
                app.agenda.workoutSchedule[dateKey].completed = true;
                app.agenda.workoutSchedule[dateKey].completedAt = new Date();
                
                app.agenda.closeModal();
                app.agenda.renderCalendar();
                app.utils.showToast('Treino marcado como concluído! 🎉');
            }
        },

        // Marcar treino automaticamente quando todos os exercícios são concluídos
        checkAutoCompleteWorkout: () => {
            const today = new Date();
            const dateKey = `${today.getFullYear()}-${today.getMonth()}-${today.getDate()}`;
            const scheduledWorkout = app.agenda.workoutSchedule[dateKey];
            
            if (!scheduledWorkout || scheduledWorkout.completed) return;
            
            // Verificar se todos os exercícios do treino atual estão concluídos
            const currentWorkout = app.data.workouts[app.state.currentWorkoutType];
            const allExercisesCompleted = currentWorkout.every(exercise => exercise.done);
            
            if (allExercisesCompleted) {
                app.agenda.workoutSchedule[dateKey].completed = true;
                app.agenda.workoutSchedule[dateKey].completedAt = new Date();
                app.agenda.renderCalendar();
                app.workouts.updateTodayWorkoutBanner();
                app.utils.showToast('Parabéns! Treino do dia concluído! 🎉');
            }
        },

        // Limpar treino programado
        clearScheduledWorkout: (dateKey) => {
            delete app.agenda.workoutSchedule[dateKey];
            app.agenda.closeModal();
            app.agenda.renderCalendar();
            app.utils.showToast('Treino removido da programação');
        },

        // Fechar modal
        closeModal: () => {
            document.getElementById('workout-modal').classList.remove('active');
            app.agenda.selectedWorkout = null;
        },

        // Obter nome do treino
        getWorkoutName: (workoutType) => {
            const names = {
                'A': 'Treino A (Peito/Tríceps)',
                'B': 'Treino B (Costas/Bíceps)',
                'C': 'Treino C (Pernas/Ombros)',
                'descanso': 'Dia de Descanso'
            };
            return names[workoutType] || 'Treino';
        },

        // Obter cor do treino
        getWorkoutColor: (workoutType) => {
            const colors = {
                'A': '#ef4444', // Vermelho
                'B': '#3b82f6', // Azul
                'C': '#10b981', // Verde
                'descanso': '#6b7280' // Cinza
            };
            return colors[workoutType] || '#6b7280';
        },

        // Verificar se o treino do dia está bloqueado
        checkWorkoutLock: (workoutType) => {
            const today = new Date();
            const dateKey = `${today.getFullYear()}-${today.getMonth()}-${today.getDate()}`;
            const scheduledWorkout = app.agenda.workoutSchedule[dateKey];
            
            // Se não há treino programado para hoje, permite qualquer treino
            if (!scheduledWorkout) return false;
            
            // Se o treino já foi concluído, bloqueia todos os treinos
            if (scheduledWorkout.completed) {
                return true;
            }
            
            // Se há treino programado e não é o mesmo que o usuário está tentando acessar, bloqueia
            if (scheduledWorkout.type !== workoutType && scheduledWorkout.type !== 'descanso') {
                return true;
            }
            
            return false;
        },

        // Obter treino programado para hoje
        getTodayScheduledWorkout: () => {
            const today = new Date();
            const dateKey = `${today.getFullYear()}-${today.getMonth()}-${today.getDate()}`;
            return app.agenda.workoutSchedule[dateKey];
        },

        // Atualizar estatísticas
        updateWorkoutStats: () => {
            const scheduledWorkouts = Object.values(app.agenda.workoutSchedule).filter(workout => workout.scheduled && !workout.completed).length;
            const completedWorkouts = Object.values(app.agenda.workoutSchedule).filter(workout => workout.completed).length;
            
            document.getElementById('scheduled-workouts').textContent = scheduledWorkouts;
            document.getElementById('completed-workouts').textContent = completedWorkouts;
        },

        renderCalendar: () => {
            const cal = app.state.calendar;
            const year = cal.currentDate.getFullYear();
            const month = cal.currentDate.getMonth();
            const today = new Date(); // Data real de hoje

            // Atualiza Título
            const monthName = app.agenda.months[month];
            document.getElementById('current-month-display').innerText = `${monthName} ${year}`;

            // Lógica de dias
            const firstDayIndex = new Date(year, month, 1).getDay(); // Dia da semana do dia 1 (0=Dom)
            const lastDay = new Date(year, month + 1, 0).getDate(); // Total dias no mês

            const grid = document.getElementById('calendar-grid');
            grid.innerHTML = '';

            // Dias vazios antes do dia 1
            for (let i = 0; i < firstDayIndex; i++) {
                const emptyDiv = document.createElement('div');
                emptyDiv.className = 'calendar-day empty';
                grid.appendChild(emptyDiv);
            }

            // Dias reais do mês
            for (let i = 1; i <= lastDay; i++) {
                const dayDiv = document.createElement('div');
                const dateKey = `${year}-${month}-${i}`;
                const scheduledWorkout = app.agenda.workoutSchedule[dateKey];
                
                // Estilo base
                let className = 'calendar-day';
                
                // Verifica se é "Hoje"
                const isToday = (i === today.getDate() && month === today.getMonth() && year === today.getFullYear());
                if (isToday) {
                    className += ' today';
                }

                // Verifica se tem treino programado
                if (scheduledWorkout) {
                    className += ' has-workout';
                    if (scheduledWorkout.completed) {
                        className += ' workout-completed';
                    } else {
                        className += ' workout-scheduled';
                    }
                }

                dayDiv.className = className;
                dayDiv.onclick = function() { app.agenda.selectDay(this, i, month, year); };

                // HTML interno do dia
                let htmlContent = `<span class="day-number">${i}</span>`;
                
                // Labels para dias especiais
                if (isToday) {
                    htmlContent += `<div class="day-label today-label"><i data-lucide="clock" class="w-3 h-3 mr-1"></i> Hoje</div>`;
                }
                
                // Indicador de treino programado
                if (scheduledWorkout) {
                    const workoutColor = app.agenda.getWorkoutColor(scheduledWorkout.type);
                    const workoutName = app.agenda.getWorkoutName(scheduledWorkout.type);
                    const icon = scheduledWorkout.completed ? 'check-circle' : 'dumbbell';
                    const statusClass = scheduledWorkout.completed ? 'workout-completed-label' : 'workout-scheduled-label';
                    
                    htmlContent += `
                        <div class="day-label ${statusClass}" style="border-left-color: ${workoutColor}">
                            <i data-lucide="${icon}" class="w-3 h-3 mr-1"></i> 
                            ${scheduledWorkout.completed ? 'Concluído' : workoutName.split(' ')[0]}
                        </div>
                    `;
                }

                dayDiv.innerHTML = htmlContent;
                grid.appendChild(dayDiv);
            }
            
            // Atualizar estatísticas
            app.agenda.updateWorkoutStats();
            
            lucide.createIcons();
        }
    },

    // LÓGICA DA DIETA
    diet: {
        updateWater: (amount) => {
            let newAmount = app.state.waterCurrent + amount;
            if(newAmount < 0) newAmount = 0;
            app.state.waterCurrent = newAmount;
            
            const percent = Math.min((newAmount / app.state.waterGoal) * 100, 100);
            document.getElementById('water-val').innerText = (newAmount / 1000).toFixed(1) + 'L';
            document.getElementById('water-level').style.height = percent + '%';
        },

        toggleMeal: (el) => {
            const isChecked = el.classList.contains('checked');
            const p = parseInt(el.getAttribute('data-prot'));
            const c = parseInt(el.getAttribute('data-carb'));
            const f = parseInt(el.getAttribute('data-fat'));

            if (!isChecked) {
                // Marcar
                el.classList.add('checked');
                app.state.macros.prot.current += p;
                app.state.macros.carb.current += c;
                app.state.macros.fat.current += f;
            } else {
                // Desmarcar
                el.classList.remove('checked');
                app.state.macros.prot.current -= p;
                app.state.macros.carb.current -= c;
                app.state.macros.fat.current -= f;
            }
            lucide.createIcons();
            app.diet.updateMacroBars();
        },

        updateMacroBars: () => {
            const m = app.state.macros;
            
            const pPct = Math.min((m.prot.current / m.prot.goal) * 100, 100);
            document.getElementById('val-prot').innerText = m.prot.current;
            document.getElementById('bar-prot').style.width = pPct + '%';

            const cPct = Math.min((m.carb.current / m.carb.goal) * 100, 100);
            document.getElementById('val-carb').innerText = m.carb.current;
            document.getElementById('bar-carb').style.width = cPct + '%';

            const fPct = Math.min((m.fat.current / m.fat.goal) * 100, 100);
            document.getElementById('val-fat').innerText = m.fat.current;
            document.getElementById('bar-fat').style.width = fPct + '%';
        }
    },

    // LÓGICA DO PERFIL
    profile: {
        save: (e) => {
            e.preventDefault();
            // Simula salvamento
            const btn = e.target.querySelector('button');
            const originalText = btn.innerText;
            btn.innerText = "Salvando...";
            
            setTimeout(() => {
                btn.innerText = originalText;
                app.utils.showToast("Perfil atualizado com sucesso!");
            }, 800);
        }
    },

    // UTILITÁRIOS
    utils: {
        showToast: (msg) => {
            const toast = document.getElementById('toast-container');
            document.getElementById('toast-message').innerText = msg;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3000);
        }
    }
};

// INICIALIZAÇÃO
app.workouts.renderList('A');
app.diet.updateMacroBars(); // Inicia com zeros
app.agenda.renderCalendar(); // Gera o calendário real
app.workouts.updateTodayWorkoutBanner(); // Atualiza banner do treino do dia