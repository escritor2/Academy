<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFit - Área do Aluno - Matrícula</title>
    <link rel="icon" href="/Academy/view/icons/favicon.ico" type="image/x-icon">
    
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
        
        /* Remove ícones nativos de senha */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear { display: none !important; }
        input[type="password"]::-webkit-password-toggle-visibility { display: none !important; -webkit-appearance: none !important; }

        /* Calendário Branco */
        ::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        ::-webkit-calendar-picker-indicator:hover { opacity: 1; }

        /* Estilos do Cartão de Plano */
        .plan-radio:checked + .plan-card {
            border-color: #ea580c;
            background-color: rgba(234, 88, 12, 0.1);
            box-shadow: 0 0 20px rgba(234, 88, 12, 0.2);
        }
        .plan-radio:checked + .plan-card .check-icon { opacity: 1; transform: scale(1); }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0B0F19; }
        ::-webkit-scrollbar-thumb { background: #374151; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #ea580c; }

        .loader {
            border: 3px solid rgba(255,255,255,0.1);
            border-left-color: #ffffff;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* --- ANIMAÇÃO DO OLHO --- */
        .eye-container {
            position: relative;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .eye-svg {
            position: absolute;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            pointer-events: none;
        }
        .eye-show.active { opacity: 1; transform: rotate(0deg) scale(1); }
        .eye-show.inactive { opacity: 0; transform: rotate(90deg) scale(0.5); }
        .eye-hide.active { opacity: 1; transform: rotate(0deg) scale(1); }
        .eye-hide.inactive { opacity: 0; transform: rotate(-90deg) scale(0.5); }
    </style>
</head>
<body class="min-h-screen flex flex-col md:flex-row">
    <?php 
    // Inicia a sessão para exibir mensagens de cadastro enviadas pelo controller
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_GET['cadastro'])):
        $type = $_GET['cadastro'];
        $message = ($type == 'sucesso') 
            ? 'Cadastro realizado com sucesso! Faça login abaixo.' 
            : ($_GET['msg'] ?? 'Ocorreu um erro ao cadastrar.');
        $color = ($type == 'sucesso') ? 'bg-green-500' : 'bg-red-500';
    ?>
    <div class="fixed top-0 left-1/2 -translate-x-1/2 mt-4 p-4 rounded-lg <?= $color ?> text-white text-sm z-50 shadow-lg animate-fade-in">
        <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

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
            
            <div class="mb-6">
                <a href="index.php" class="inline-flex items-center gap-2 text-tech-muted hover:text-tech-primary transition-colors group">
                    <i data-lucide="arrow-left" class="w-5 h-5 transition-transform group-hover:-translate-x-1"></i>
                    <span>Voltar ao início</span>
                </a>
            </div>
            <div class="md:hidden flex items-center gap-2 mb-8 justify-center">
                <i data-lucide="dumbbell" class="h-8 w-8 text-tech-primary"></i>
                <span class="font-black text-2xl tracking-tighter text-white">TECH<span class="text-tech-primary">FIT</span></span>
            </div>

            <div class="mb-10">
                <h2 class="text-3xl font-bold mb-2">Matrícula Online</h2>
                <p class="text-tech-muted">Preencha seus dados para criar sua conta de aluno.</p>
            </div>

            <form id="enrollmentForm" method="POST" action="controllers/CadastroController.php" class="space-y-8">
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
                        <div class="relative">
                            <input type="password" id="password" required class="w-full p-3 pr-12 rounded-lg tech-input" placeholder="Crie uma senha segura">
                            
                            <button type="button" id="togglePasswordBtn" class="absolute right-3 top-1/2 transform -translate-y-1/2 focus:outline-none p-2 rounded-full hover:bg-white/5 transition-colors z-20 cursor-pointer">
                                <div class="eye-container">
                                    <svg id="iconShow" class="eye-svg eye-show active" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <svg id="iconHide" class="eye-svg eye-hide inactive" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                                        <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                                        <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7c.44 0 .87-.03 1.28-.09"></path>
                                        <line x1="2" x2="22" y1="2" y2="22"></line>
                                    </svg>
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
                            <div class="p-3 rounded-lg border border-tech-700 bg-tech-800 text-center peer-checked:border-tech-primary peer-checked:bg-tech-primary/10 transition-all hover:border-gray-500"><span class="text-sm font-medium">Hipertrofia</span></div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="goal" value="emagrecimento" class="peer sr-only">
                            <div class="p-3 rounded-lg border border-tech-700 bg-tech-800 text-center peer-checked:border-tech-primary peer-checked:bg-tech-primary/10 transition-all hover:border-gray-500"><span class="text-sm font-medium">Emagrecer</span></div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="goal" value="resistencia" class="peer sr-only">
                            <div class="p-3 rounded-lg border border-tech-700 bg-tech-800 text-center peer-checked:border-tech-primary peer-checked:bg-tech-primary/10 transition-all hover:border-gray-500"><span class="text-sm font-medium">Resistência</span></div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="goal" value="saude" class="peer sr-only">
                            <div class="p-3 rounded-lg border border-tech-700 bg-tech-800 text-center peer-checked:border-tech-primary peer-checked:bg-tech-primary/10 transition-all hover:border-gray-500"><span class="text-sm font-medium">Saúde</span></div>
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
                                <div class="flex justify-between items-start mb-2"><h4 class="font-bold text-lg">Start</h4><div class="check-icon opacity-0 transform scale-50 transition-all duration-300 bg-tech-primary rounded-full p-1 text-white"><i data-lucide="check" class="w-3 h-3"></i></div></div>
                                <div class="text-2xl font-bold mb-2">R$ 89<span class="text-sm font-normal text-tech-muted">/mês</span></div>
                                <ul class="text-xs text-tech-muted space-y-1"><li>• Acesso Musculação</li><li>• Sem fidelidade</li></ul>
                            </div>
                        </label>
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="plan" value="Pro" class="plan-radio sr-only" checked>
                            <div class="plan-card p-4 rounded-xl border border-tech-primary bg-tech-800 h-full transition-all relative overflow-hidden">
                                <div class="absolute top-0 right-0 bg-tech-primary text-white text-[10px] font-bold px-2 py-1 rounded-bl-lg">POPULAR</div>
                                <div class="flex justify-between items-start mb-2"><h4 class="font-bold text-lg text-white">Pro</h4><div class="check-icon opacity-0 transform scale-50 transition-all duration-300 bg-tech-primary rounded-full p-1 text-white"><i data-lucide="check" class="w-3 h-3"></i></div></div>
                                <div class="text-2xl font-bold mb-2 text-tech-primary">R$ 149<span class="text-sm font-normal text-tech-muted">/mês</span></div>
                                <ul class="text-xs text-gray-300 space-y-1"><li>• Tudo do Start</li><li>• IA Trainer</li><li>• Aulas Coletivas</li></ul>
                            </div>
                        </label>
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="plan" value="VIP" class="plan-radio sr-only">
                            <div class="plan-card p-4 rounded-xl border border-tech-700 bg-tech-800 h-full transition-all group-hover:border-gray-500">
                                <div class="flex justify-between items-start mb-2"><h4 class="font-bold text-lg">VIP</h4><div class="check-icon opacity-0 transform scale-50 transition-all duration-300 bg-tech-primary rounded-full p-1 text-white"><i data-lucide="check" class="w-3 h-3"></i></div></div>
                                <div class="text-2xl font-bold mb-2">R$ 399<span class="text-sm font-normal text-tech-muted">/mês</span></div>
                                <ul class="text-xs text-tech-muted space-y-1"><li>• Tudo do Pro</li><li>• Personal + Nutri</li></ul>
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
            <div class="bg-green-500/20 p-2 rounded-full"><i data-lucide="check-circle" class="w-6 h-6 text-green-500"></i></div>
            <div><h4 class="font-bold text-sm">Matrícula Realizada!</h4><p class="text-xs text-gray-400">Bem-vindo à TechFit.</p></div>
        </div>
    </div>
    <div id="errorToast" class="fixed top-5 right-5 z-50 transform translate-x-full transition-transform duration-300">
        <div class="bg-tech-800 border-l-4 border-red-500 text-white px-6 py-4 rounded-lg shadow-2xl flex items-center gap-4 min-w-[300px]">
            <div class="bg-red-500/20 p-2 rounded-full"><i data-lucide="alert-circle" class="w-6 h-6 text-red-500"></i></div>
            <div><h4 class="font-bold text-sm">Erro</h4><p class="text-xs text-gray-400" id="errorMessage">Algo deu errado.</p></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();

            const toggleBtn = document.getElementById('togglePasswordBtn');
            const passInput = document.getElementById('password');
            const iconShow = document.getElementById('iconShow');
            const iconHide = document.getElementById('iconHide');

            if (toggleBtn && passInput) {
                toggleBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const isPassword = passInput.getAttribute('type') === 'password';
                    passInput.setAttribute('type', isPassword ? 'text' : 'password');
                    if (isPassword) {
                        iconShow.classList.remove('active'); iconShow.classList.add('inactive');
                        iconHide.classList.remove('inactive'); iconHide.classList.add('active');
                    } else {
                        iconShow.classList.remove('inactive'); iconShow.classList.add('active');
                        iconHide.classList.remove('active'); iconHide.classList.add('inactive');
                    }
                });
            }
        });
    </script>

    <!-- Firebase enrollment script removed.
         Submissão do formulário agora é tratada pelo Controller PHP em `controllers/CadastroController.php`.
         Se precisar reaplicar lógica cliente, restaurar o bloco original ou adaptar para envio AJAX ao endpoint PHP.
    -->
</body>
</html>