<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFit - Área do Aluno - Matrícula</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        tech: {
                            900: '#0B0F19', 
                            800: '#151b2b', 
                            700: '#374151', 
                            primary: '#ea580c', 
                            primaryHover: '#c2410c',
                            text: '#f3f4f6', 
                            muted: '#9ca3af' 
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out forwards',
                        'slide-up': 'slideUp 0.5s ease-out forwards',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>

    <script src="https://unpkg.com/lucide@latest"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0B0F19;
            color: #f3f4f6;
            overflow-x: hidden;
        }

        /* Inputs Customizados */
        .tech-input {
            background-color: #151b2b;
            border: 1px solid #374151;
            color: white;
            transition: all 0.3s ease;
        }
        .tech-input:focus {
            border-color: #ea580c;
            box-shadow: 0 0 0 2px rgba(234, 88, 12, 0.2);
            outline: none;
        }
        
        /* --- CORREÇÃO 1: Remover o olho nativo do navegador (Edge/IE) --- */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none;
        }

        /* Calendário Branco */
        ::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        ::-webkit-calendar-picker-indicator:hover {
            opacity: 1;
        }

        /* Select Plan Card */
        .plan-radio:checked + .plan-card {
            border-color: #ea580c;
            background-color: rgba(234, 88, 12, 0.1);
            box-shadow: 0 0 20px rgba(234, 88, 12, 0.2);
        }
        .plan-radio:checked + .plan-card .check-icon {
            opacity: 1;
            transform: scale(1);
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0B0F19; }
        ::-webkit-scrollbar-thumb { background: #374151; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #ea580c; }

        /* Loader */
        .loader {
            border: 3px solid rgba(255,255,255,0.1);
            border-left-color: #ffffff;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* Animação do Olho */
        .eye-transition {
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        }
        .eye-visible {
            opacity: 1;
            transform: rotate(0deg) scale(1);
        }
        .eye-hidden {
            opacity: 0;
            transform: rotate(180deg) scale(0);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col md:flex-row">

    <div class="hidden md:flex md:w-1/2 lg:w-2/5 bg-tech-800 relative overflow-hidden flex-col justify-between p-12">
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1549476464-37392f717541?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" alt="Gym Background" class="w-full h-full object-cover opacity-40">
            <div class="absolute inset-0 bg-gradient-to-t from-tech-900 via-tech-900/60 to-transparent"></div>
        </div>

        <div class="relative z-10">
            <div class="flex items-center gap-2 mb-8">
                <i data-lucide="dumbbell" class="h-8 w-8 text-tech-primary"></i>
                <span class="font-black text-2xl tracking-tighter text-white">TECH<span class="text-tech-primary">FIT</span></span>
            </div>
        </div>

        <div class="relative z-10 space-y-6">
            <h1 class="text-4xl lg:text-5xl font-bold leading-tight">
                Sua jornada para o <span class="text-tech-primary">futuro</span> começa aqui.
            </h1>
            <p class="text-tech-muted text-lg">
                Junte-se a mais de 1.500 membros que transformaram seus corpos e mentes com nossa tecnologia.
            </p>
            
            <div class="flex gap-4 pt-4">
                <div class="bg-white/5 backdrop-blur-md p-4 rounded-xl border border-white/10">
                    <i data-lucide="zap" class="w-6 h-6 text-tech-primary mb-2"></i>
                    <div class="font-bold">Treino IA</div>
                    <div class="text-xs text-tech-muted">Personalizado</div>
                </div>
                <div class="bg-white/5 backdrop-blur-md p-4 rounded-xl border border-white/10">
                    <i data-lucide="users" class="w-6 h-6 text-tech-primary mb-2"></i>
                    <div class="font-bold">Comunidade</div>
                    <div class="text-xs text-tech-muted">Ativa 24h</div>
                </div>
                <div class="bg-white/5 backdrop-blur-md p-4 rounded-xl border border-white/10">
                    <i data-lucide="bar-chart" class="w-6 h-6 text-tech-primary mb-2"></i>
                    <div class="font-bold">Evolução</div>
                    <div class="text-xs text-tech-muted">Em tempo real</div>
                </div>
            </div>
        </div>

        <div class="relative z-10 text-sm text-tech-muted mt-8">
            &copy; 2023 TechFit Gym Inc.
        </div>
    </div>

    <div class="w-full md:w-1/2 lg:w-3/5 bg-tech-900 overflow-y-auto h-screen relative">
        <div class="absolute top-0 right-0 w-96 h-96 bg-tech-primary/5 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="max-w-2xl mx-auto p-6 md:p-12 lg:p-16 animate-fade-in">
            <div class="md:hidden flex items-center gap-2 mb-8 justify-center">
                <i data-lucide="dumbbell" class="h-8 w-8 text-tech-primary"></i>
                <span class="font-black text-2xl tracking-tighter text-white">TECH<span class="text-tech-primary">FIT</span></span>
            </div>

            <div class="mb-10">
                <h2 class="text-3xl font-bold mb-2">Matrícula Online</h2>
                <p class="text-tech-muted">Preencha seus dados para criar sua conta de aluno.</p>
            </div>

            <form id="enrollmentForm" class="space-y-8">
                
                <div class="space-y-4 animate-slide-up" style="animation-delay: 0.1s;">
                    <div class="flex items-center gap-2 text-tech-primary mb-2">
                        <i data-lucide="user" class="w-5 h-5"></i>
                        <h3 class="font-bold uppercase tracking-wider text-sm">Dados Pessoais</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Nome Completo</label>
                            <input type="text" id="name" required class="w-full p-3 rounded-lg tech-input" placeholder="Seu nome">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Data de Nascimento</label>
                            <input type="date" id="birthdate" required class="w-full p-3 rounded-lg tech-input">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">E-mail</label>
                            <input type="email" id="email" required class="w-full p-3 rounded-lg tech-input" placeholder="seu@email.com">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Telefone / WhatsApp</label>
                            <input type="tel" id="phone" required class="w-full p-3 rounded-lg tech-input" placeholder="(00) 00000-0000">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">CPF</label>
                            <input type="text" id="cpf" required class="w-full p-3 rounded-lg tech-input" placeholder="000.000.000-00">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Gênero</label>
                            <select id="gender" required class="w-full p-3 rounded-lg tech-input appearance-none">
                                <option value="" disabled selected>Selecione</option>
                                <option value="masculino">Masculino</option>
                                <option value="feminino">Feminino</option>
                                <option value="outro">Outro</option>
                                <option value="prefiro_nao_dizer">Prefiro não dizer</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Senha de Acesso</label>
                        <div class="relative z-10">
                            <input type="password" id="password" required class="w-full p-3 pr-12 rounded-lg tech-input" placeholder="Crie uma senha segura">
                            
                            <button type="button" id="togglePasswordBtn" class="absolute right-3 top-1/2 transform -translate-y-1/2 focus:outline-none p-1 group z-20">
                                <div class="relative w-6 h-6">
                                    
                                    <div id="eyeOpen" class="absolute inset-0 eye-transition eye-visible">
                                        <i data-lucide="eye" class="w-6 h-6 text-white group-hover:text-tech-primary transition-colors"></i>
                                    </div>
                                    
                                    <div id="eyeClosed" class="absolute inset-0 eye-transition eye-hidden">
                                        <i data-lucide="eye-off" class="w-6 h-6 text-white group-hover:text-tech-primary transition-colors"></i>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>

                </div>

                <div class="space-y-4 animate-slide-up" style="animation-delay: 0.2s;">
                    <div class="flex items-center gap-2 text-tech-primary mb-2 pt-4 border-t border-tech-800">
                        <i data-lucide="target" class="w-5 h-5"></i>
                        <h3 class="font-bold uppercase tracking-wider text-sm">Objetivo Principal</h3>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="goal" value="hipertrofia" class="peer sr-only" checked>
                            <div class="p-3 rounded-lg border border-tech-700 bg-tech-800 text-center peer-checked:border-tech-primary peer-checked:bg-tech-primary/10 transition-all hover:border-gray-500">
                                <span class="text-sm font-medium">Hipertrofia</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="goal" value="emagrecimento" class="peer sr-only">
                            <div class="p-3 rounded-lg border border-tech-700 bg-tech-800 text-center peer-checked:border-tech-primary peer-checked:bg-tech-primary/10 transition-all hover:border-gray-500">
                                <span class="text-sm font-medium">Emagrecer</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="goal" value="resistencia" class="peer sr-only">
                            <div class="p-3 rounded-lg border border-tech-700 bg-tech-800 text-center peer-checked:border-tech-primary peer-checked:bg-tech-primary/10 transition-all hover:border-gray-500">
                                <span class="text-sm font-medium">Resistência</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="goal" value="saude" class="peer sr-only">
                            <div class="p-3 rounded-lg border border-tech-700 bg-tech-800 text-center peer-checked:border-tech-primary peer-checked:bg-tech-primary/10 transition-all hover:border-gray-500">
                                <span class="text-sm font-medium">Saúde</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="space-y-4 animate-slide-up" style="animation-delay: 0.3s;">
                    <div class="flex items-center gap-2 text-tech-primary mb-2 pt-4 border-t border-tech-800">
                        <i data-lucide="credit-card" class="w-5 h-5"></i>
                        <h3 class="font-bold uppercase tracking-wider text-sm">Escolha seu Plano</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="plan" value="Start" class="plan-radio sr-only">
                            <div class="plan-card p-4 rounded-xl border border-tech-700 bg-tech-800 h-full transition-all group-hover:border-gray-500">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-bold text-lg">Start</h4>
                                    <div class="check-icon opacity-0 transform scale-50 transition-all duration-300 bg-tech-primary rounded-full p-1 text-white">
                                        <i data-lucide="check" class="w-3 h-3"></i>
                                    </div>
                                </div>
                                <div class="text-2xl font-bold mb-2">R$ 89<span class="text-sm font-normal text-tech-muted">/mês</span></div>
                                <ul class="text-xs text-tech-muted space-y-1">
                                    <li>• Acesso Musculação</li>
                                    <li>• Sem fidelidade</li>
                                </ul>
                            </div>
                        </label>

                        <label class="relative cursor-pointer group">
                            <input type="radio" name="plan" value="Pro" class="plan-radio sr-only" checked>
                            <div class="plan-card p-4 rounded-xl border border-tech-primary bg-tech-800 h-full transition-all relative overflow-hidden">
                                <div class="absolute top-0 right-0 bg-tech-primary text-white text-[10px] font-bold px-2 py-1 rounded-bl-lg">POPULAR</div>
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-bold text-lg text-white">Pro</h4>
                                    <div class="check-icon opacity-0 transform scale-50 transition-all duration-300 bg-tech-primary rounded-full p-1 text-white">
                                        <i data-lucide="check" class="w-3 h-3"></i>
                                    </div>
                                </div>
                                <div class="text-2xl font-bold mb-2 text-tech-primary">R$ 149<span class="text-sm font-normal text-tech-muted">/mês</span></div>
                                <ul class="text-xs text-gray-300 space-y-1">
                                    <li>• Tudo do Start</li>
                                    <li>• IA Trainer</li>
                                    <li>• Aulas Coletivas</li>
                                </ul>
                            </div>
                        </label>

                        <label class="relative cursor-pointer group">
                            <input type="radio" name="plan" value="VIP" class="plan-radio sr-only">
                            <div class="plan-card p-4 rounded-xl border border-tech-700 bg-tech-800 h-full transition-all group-hover:border-gray-500">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-bold text-lg">VIP</h4>
                                    <div class="check-icon opacity-0 transform scale-50 transition-all duration-300 bg-tech-primary rounded-full p-1 text-white">
                                        <i data-lucide="check" class="w-3 h-3"></i>
                                    </div>
                                </div>
                                <div class="text-2xl font-bold mb-2">R$ 399<span class="text-sm font-normal text-tech-muted">/mês</span></div>
                                <ul class="text-xs text-tech-muted space-y-1">
                                    <li>• Tudo do Pro</li>
                                    <li>• Personal + Nutri</li>
                                </ul>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="pt-6 animate-slide-up" style="animation-delay: 0.4s;">
                    <button type="submit" id="submitBtn" class="w-full bg-gradient-to-r from-tech-primary to-orange-600 hover:to-orange-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-orange-900/30 transform transition-all hover:-translate-y-1 active:translate-y-0 flex items-center justify-center gap-2">
                        <span>Finalizar Matrícula</span>
                        <i data-lucide="arrow-right" class="w-5 h-5"></i>
                    </button>
                    <p class="text-center text-xs text-tech-muted mt-4">
                        Ao clicar em finalizar, você concorda com nossos <a href="#" class="text-tech-primary hover:underline">Termos de Uso</a>.
                    </p>
                </div>

            </form>
            
            <div class="mt-8 text-center border-t border-tech-800 pt-8">
                <p class="text-tech-muted">Já tem uma conta? <a href="#" class="text-white font-bold hover:text-tech-primary transition-colors">Fazer Login</a></p>
            </div>
        </div>
    </div>

    <div id="toast" class="fixed top-5 right-5 z-50 transform translate-x-full transition-transform duration-300">
        <div class="bg-tech-800 border-l-4 border-green-500 text-white px-6 py-4 rounded-lg shadow-2xl flex items-center gap-4 min-w-[300px]">
            <div class="bg-green-500/20 p-2 rounded-full">
                <i data-lucide="check-circle" class="w-6 h-6 text-green-500"></i>
            </div>
            <div>
                <h4 class="font-bold text-sm">Matrícula Realizada!</h4>
                <p class="text-xs text-gray-400">Bem-vindo à TechFit.</p>
            </div>
        </div>
    </div>
    
    <div id="errorToast" class="fixed top-5 right-5 z-50 transform translate-x-full transition-transform duration-300">
        <div class="bg-tech-800 border-l-4 border-red-500 text-white px-6 py-4 rounded-lg shadow-2xl flex items-center gap-4 min-w-[300px]">
            <div class="bg-red-500/20 p-2 rounded-full">
                <i data-lucide="alert-circle" class="w-6 h-6 text-red-500"></i>
            </div>
            <div>
                <h4 class="font-bold text-sm">Erro</h4>
                <p class="text-xs text-gray-400" id="errorMessage">Algo deu errado.</p>
            </div>
        </div>
    </div>

    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-app.js";
        import { getFirestore, collection, addDoc, serverTimestamp } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";
        import { getAuth, signInAnonymously } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-auth.js";

        const firebaseConfig = JSON.parse(__firebase_config);
        const appId = typeof __app_id !== 'undefined' ? __app_id : 'default-app-id';

        const app = initializeApp(firebaseConfig);
        const db = getFirestore(app);
        const auth = getAuth(app);

        async function initAuth() {
            try {
                if (typeof __initial_auth_token !== 'undefined' && __initial_auth_token) {
                     await signInWithCustomToken(auth, __initial_auth_token);
                } else {
                     await signInAnonymously(auth);
                }
                console.log("Autenticado no Firebase");
            } catch (error) {
                console.error("Erro na autenticação:", error);
            }
        }
        initAuth();

        // Inicializar ícones Lucide
        lucide.createIcons();

        // Lógica do Toggle de Senha
        const togglePasswordBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('password');
        const eyeOpen = document.getElementById('eyeOpen');
        const eyeClosed = document.getElementById('eyeClosed');

        togglePasswordBtn.addEventListener('click', () => {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            
            // Troca o tipo do input
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');

            // Troca as classes de animação (Morph effect)
            if (isPassword) {
                // Mostrar senha (vira texto)
                eyeOpen.classList.remove('eye-visible');
                eyeOpen.classList.add('eye-hidden');
                
                eyeClosed.classList.remove('eye-hidden');
                eyeClosed.classList.add('eye-visible');
            } else {
                // Ocultar senha (vira bolinha)
                eyeOpen.classList.remove('eye-hidden');
                eyeOpen.classList.add('eye-visible');
                
                eyeClosed.classList.remove('eye-visible');
                eyeClosed.classList.add('eye-hidden');
            }
        });

        // Manipulação do Formulário
        const form = document.getElementById('enrollmentForm');
        const submitBtn = document.getElementById('submitBtn');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const originalBtnContent = submitBtn.innerHTML;
            submitBtn.innerHTML = '<div class="loader"></div> Processando...';
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-70', 'cursor-not-allowed');

            try {
                const formData = {
                    name: document.getElementById('name').value,
                    email: document.getElementById('email').value,
                    birthdate: document.getElementById('birthdate').value,
                    phone: document.getElementById('phone').value,
                    cpf: document.getElementById('cpf').value,
                    gender: document.getElementById('gender').value,
                    goal: document.querySelector('input[name="goal"]:checked').value,
                    plan: document.querySelector('input[name="plan"]:checked').value,
                    password: '***',
                    createdAt: serverTimestamp(),
                    status: 'ativo'
                };

                const user = auth.currentUser;
                if (!user) {
                    throw new Error("Usuário não autenticado. Tente recarregar a página.");
                }

                await addDoc(collection(db, 'artifacts', appId, 'public', 'data', 'enrollments'), formData);

                showToast('toast');
                form.reset();
                
                // Resetar botão do olho para estado inicial
                if(passwordInput.getAttribute('type') === 'text') {
                    togglePasswordBtn.click();
                }

                setTimeout(() => {
                    submitBtn.innerHTML = originalBtnContent;
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-70', 'cursor-not-allowed');
                }, 2000);

            } catch (error) {
                console.error("Erro ao salvar:", error);
                document.getElementById('errorMessage').innerText = "Erro ao salvar matrícula. Tente novamente.";
                showToast('errorToast');
                
                submitBtn.innerHTML = originalBtnContent;
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-70', 'cursor-not-allowed');
            }
        });

        function showToast(id) {
            const toast = document.getElementById(id);
            toast.classList.remove('translate-x-full');
            setTimeout(() => {
                toast.classList.add('translate-x-full');
            }, 4000);
        }
    </script>
</body>
</html>