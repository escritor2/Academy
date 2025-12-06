<?php
session_start();

// --- LÓGICA DE LOGOUT ---
if (isset($_GET['sair'])) {
    session_destroy(); // Destrói a sessão
    header('Location: index.php'); // Manda para a home
    exit;
}

// O "Segurança": Se não tiver ID na sessão, chuta para o index
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?login_erro=1&msg=Faça login para acessar');
    exit;
}
// Pega os dados para usar no HTML
$nomeAluno = $_SESSION['usuario_nome'];
$planoAluno = $_SESSION['usuario_plano'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icons/halter.png">
    <title>TechFit - Painel do Aluno Premium</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Configuração da Paleta -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        tech: {
                            900: '#0B0F19', // Fundo Principal
                            800: '#151b2b', // Cards/Sidebar
                            700: '#2d3748', // Bordas
                            primary: '#ea580c', // Laranja TechFit
                            primaryHover: '#c2410c',
                            text: '#f3f4f6', 
                            muted: '#9ca3af',
                            success: '#10b981',
                            error: '#ef4444'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.4s ease-out forwards',
                        'pulse-glow': 'pulseGlow 2s infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'scale(0.98)' },
                            '100%': { opacity: '1', transform: 'scale(1)' },
                        },
                        pulseGlow: {
                            '0%, 100%': { boxShadow: '0 0 5px rgba(234, 88, 12, 0.2)' },
                            '50%': { boxShadow: '0 0 20px rgba(234, 88, 12, 0.6)' },
                        }
                    }
                }
            }
        }
    </script>

    <!-- Ícones Lucide -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Fonte Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0B0F19;
            color: #f3f4f6;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0B0F19; }
        ::-webkit-scrollbar-thumb { background: #2d3748; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #ea580c; }

        /* Glassmorphism Cards */
        .glass-card {
            background: rgba(21, 27, 43, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }

        /* Nav Items */
        .nav-item { position: relative; overflow: hidden; }
        .nav-item.active {
            background: linear-gradient(90deg, rgba(234, 88, 12, 0.15), transparent);
            border-left: 3px solid #ea580c;
            color: #ea580c;
        }

        /* Grid de Frequência */
        .frequency-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 6px;
        }
        .freq-day {
            aspect-ratio: 1;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            background-color: #1f2937;
            color: #4b5563;
            transition: all 0.3s;
            position: relative;
        }
        .freq-day.present {
            background-color: #ea580c;
            color: white;
            box-shadow: 0 0 10px rgba(234, 88, 12, 0.3);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .freq-day.future { opacity: 0.2; pointer-events: none; }
        .freq-day.today {
            border: 2px solid white;
            box-shadow: 0 0 15px rgba(255,255,255,0.5);
            font-weight: bold;
            z-index: 10;
        }

        /* Timeline History */
        .history-timeline {
            position: relative;
            padding-left: 20px;
        }
        .history-timeline::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, #ea580c, rgba(234, 88, 12, 0.1));
        }

        /* Sidebar Transition */
        aside#sidebar {
            transition: width 0.3s ease, transform 0.3s ease;
        }
        aside#sidebar.collapsed {
            width: 0;
            overflow: hidden;
            border: none;
        }

        /* Animação de Digitação */
        .typing-container { display: inline-block; vertical-align: bottom; }
        .typing-effect {
            display: inline-block;
            overflow: hidden;
            white-space: nowrap;
            width: 0;
            background: linear-gradient(to right, #ea580c, #fed7aa);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: typing-loop 6s cubic-bezier(0.4, 0, 0.2, 1) infinite;
            border-right: none;
        }
        @keyframes typing-loop {
            0%, 10% { width: 0; }
            40%, 70% { width: 100%; }
            90%, 100% { width: 0; }
        }

        /* Modal/Cart Backdrop */
        .modal-backdrop {
            background: rgba(11, 15, 25, 0.85);
            backdrop-filter: blur(8px);
        }
        
        /* Scrollbar Hide for horizontal scroll */
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="h-screen flex overflow-hidden">
<?php 
    // Inicia a sessão para garantir que você pode usar $_SESSION mais tarde
    // Embora não seja estritamente necessário para este bloco de URL, é boa prática.
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
    <div class="fixed top-4 left-1/2 -translate-x-1/2 p-3 rounded-lg <?= $color ?> text-white text-sm z-50 shadow-md">
        <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

    <!-- SIDEBAR -->
    <aside id="sidebar" class="w-64 bg-tech-800 border-r border-tech-700 flex flex-col justify-between z-30 hidden md:flex">
        <div>
            <!-- Logo -->
            <div class="h-20 flex items-center justify-center border-b border-tech-700 relative">
                <div class="flex items-center gap-2">
                    <i data-lucide="dumbbell" class="text-tech-primary w-6 h-6"></i>
                    <span class="text-2xl font-bold tracking-tighter text-white">Tech<span class="text-tech-primary">Fit</span></span>
                </div>
            </div>

            <!-- Navegação -->
            <nav class="mt-6 px-4 space-y-2">
                <p class="px-4 text-xs font-bold text-tech-muted uppercase tracking-wider mb-2">Principal</p>
                
                <button onclick="switchView('home')" id="nav-home" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 rounded-r-lg transition-all active">
                    <i data-lucide="home" class="w-5 h-5"></i> Home
                </button>

                <button onclick="switchView('frequency')" id="nav-frequency" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 rounded-r-lg transition-all">
                    <i data-lucide="bar-chart-2" class="w-5 h-5"></i> Frequência
                </button>

                <button onclick="switchView('workouts')" id="nav-workouts" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 rounded-r-lg transition-all">
                    <i data-lucide="dumbbell" class="w-5 h-5"></i> Meus Treinos
                </button>

                <button onclick="switchView('diet')" id="nav-diet" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 rounded-r-lg transition-all">
                    <i data-lucide="apple" class="w-5 h-5"></i> Nutrição
                </button>
            </nav>
        </div>

        <!-- Footer Sidebar -->
        <div class="p-4 border-t border-tech-700">
            <!-- Botão Acesso Rápido -->
            <button onclick="openQRModal()" class="w-full bg-tech-primary hover:bg-tech-primaryHover text-white font-bold py-3 rounded-lg flex items-center justify-center gap-2 mb-4 shadow-lg shadow-orange-900/20 transition-all hover:scale-105 animate-pulse-glow" id="btn-quick-access">
                <i data-lucide="qr-code" class="w-5 h-5"></i> Acessar Academia
            </button>
<div class="border-t border-tech-700 pt-4 mt-auto">
    <a href="?sair=true" class="flex items-center gap-3 p-3 rounded-lg text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-all group">
        <i data-lucide="log-out" class="w-5 h-5 group-hover:-translate-x-1 transition-transform"></i>
        <span class="font-medium">Sair da Conta</span>
    </a>
</div>
            <div class="flex items-center gap-3 p-2 hover:bg-white/5 rounded-lg cursor-pointer transition-colors">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-tech-700 to-tech-900 flex items-center justify-center font-bold text-white border border-tech-600"><?php echo htmlspecialchars(substr($nomeAluno,0,2)); ?></div>
                <div class="text-left overflow-hidden">
                    <p class="text-sm font-semibold text-white truncate"><?php echo htmlspecialchars($nomeAluno); ?></p>
                    <p class="text-xs text-tech-primary"><?php echo htmlspecialchars($planoAluno); ?></p>
                </div>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden bg-tech-900 relative">
        
        <!-- Header Principal (Desktop & Mobile) -->
        <header class="h-16 bg-tech-800/80 backdrop-blur-md border-b border-tech-700 flex items-center justify-between px-4 md:px-6 z-20 shrink-0">
            <!-- Lado Esquerdo: Toggle Menu & Logo Mobile -->
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="p-2 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                
                <!-- Logo visível apenas no mobile ou quando sidebar colapsada -->
                <div class="md:hidden flex items-center gap-2" id="mobile-logo">
                    <i data-lucide="dumbbell" class="text-tech-primary w-5 h-5"></i>
                    <span class="font-bold text-lg">TechFit</span>
                </div>
            </div>

            <!-- Lado Direito: Carrinho & Ações -->
            <div class="flex items-center gap-4">
                <!-- Botão Carrinho -->
                <button onclick="toggleCart()" class="relative p-2 text-gray-300 hover:text-white hover:bg-white/5 rounded-lg transition-colors group">
                    <i data-lucide="shopping-bag" class="w-6 h-6 group-hover:text-tech-primary transition-colors"></i>
                    <span id="cart-badge" class="absolute top-1 right-1 w-4 h-4 bg-tech-primary text-white text-[10px] font-bold rounded-full flex items-center justify-center hidden shadow-sm">0</span>
                </button>

                <button onclick="openQRModal()" class="md:hidden p-2 bg-tech-primary text-white rounded-lg shadow-lg shadow-orange-500/20">
                    <i data-lucide="qr-code" class="w-5 h-5"></i>
                </button>
                <div class="hidden md:flex items-center gap-3">
                    <span class="text-sm text-tech-muted">Olá, <span class="text-white font-bold"><?php echo htmlspecialchars($nomeAluno); ?></span></span>
                    <div class="w-8 h-8 rounded-full bg-tech-700 border border-tech-600"></div>
                </div>
            </div>
        </header>

        <!-- Area Scrollavel -->
        <div class="flex-1 overflow-y-auto p-4 md:p-8 scroll-smooth" id="content-area">

            <!-- ================= VIEW: HOME ================= -->
            <div id="view-home" class="space-y-8 animate-fade-in">
                <!-- Header com Frase Animada -->
                <div class="glass-card rounded-2xl p-8 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-96 h-96 bg-tech-primary/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>
                    <div class="relative z-10">
                        <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">
                            Bora treinar hoje? <br>
                            <span class="typing-container"><span class="typing-effect">O futuro começa agora.</span></span>
                        </h1>
                        <p class="text-tech-muted mt-2">Você está há <span class="text-tech-primary font-bold">3 dias</span> sem faltar. Continue assim!</p>
                    </div>
                </div>

                <!-- Grid de Destaques Rápidos -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Card Acesso -->
                    <div class="glass-card rounded-xl p-6 border-l-4 border-tech-primary flex flex-col justify-between hover:bg-white/5 transition-colors cursor-pointer" onclick="openQRModal()">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-tech-primary/10 rounded-lg text-tech-primary"><i data-lucide="qr-code" class="w-6 h-6"></i></div>
                            <span class="text-xs font-bold bg-green-500/20 text-green-500 px-2 py-1 rounded">ATIVO</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg" id="home-access-title">Acesso Rápido</h3>
                            <p class="text-xs text-tech-muted" id="home-access-desc">Liberar catraca</p>
                        </div>
                    </div>

                    <!-- Card Desafio -->
                    <div class="glass-card rounded-xl p-6 border-l-4 border-blue-500 flex flex-col justify-between hover:bg-white/5 transition-colors">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-blue-500/10 rounded-lg text-blue-500"><i data-lucide="trophy" class="w-6 h-6"></i></div>
                            <span class="text-xs font-bold bg-blue-500/20 text-blue-500 px-2 py-1 rounded">SEMANAL</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">Desafio Cardio</h3>
                            <p class="text-xs text-tech-muted">Complete 20km de bike até domingo.</p>
                            <div class="w-full bg-tech-700 h-1.5 rounded-full mt-3 overflow-hidden">
                                <div class="bg-blue-500 h-full rounded-full" style="width: 65%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Dica -->
                    <div class="glass-card rounded-xl p-6 border-l-4 border-green-500 flex flex-col justify-between hover:bg-white/5 transition-colors">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-green-500/10 rounded-lg text-green-500"><i data-lucide="apple" class="w-6 h-6"></i></div>
                            <span class="text-xs font-bold bg-green-500/20 text-green-500 px-2 py-1 rounded">DICA</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">Pré-Treino Ideal</h3>
                            <p class="text-xs text-tech-muted">Carboidratos complexos 1h antes dão mais energia.</p>
                        </div>
                    </div>
                </div>

                <!-- CARROSSEL DE AULAS -->
                <div>
                    <div class="flex justify-between items-end mb-4">
                        <h3 class="text-xl font-bold flex items-center gap-2"><i data-lucide="calendar-days" class="w-5 h-5 text-tech-primary"></i> Aulas de Hoje</h3>
                        <a href="#" class="text-tech-primary text-sm hover:underline">Ver grade</a>
                    </div>
                    <div class="flex gap-4 overflow-x-auto pb-4 scrollbar-hide">
                        <!-- Card Aula 1 -->
                        <div class="min-w-[260px] glass-card p-4 rounded-xl border-t border-tech-700 hover:border-tech-primary transition-all cursor-pointer group">
                            <div class="h-32 rounded-lg bg-cover bg-center mb-3 relative overflow-hidden" style="background-image: url('https://images.unsplash.com/photo-1518611012118-696072aa579a?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&q=80');">
                                <div class="absolute top-2 right-2 bg-red-600 text-white text-xs font-bold px-2 py-1 rounded">AO VIVO</div>
                                <div class="absolute inset-0 bg-black/30 group-hover:bg-transparent transition-all"></div>
                            </div>
                            <h4 class="font-bold text-lg">Yoga Flow</h4>
                            <p class="text-xs text-tech-muted mb-3">18:00 • Studio 2 • Prof. Ana</p>
                            <button class="w-full py-2 rounded-lg bg-tech-800 text-tech-primary border border-tech-primary/30 hover:bg-tech-primary hover:text-white transition-colors text-sm font-bold">Reservar</button>
                        </div>
                        
                        <!-- Card Aula 2 -->
                        <div class="min-w-[260px] glass-card p-4 rounded-xl border-t border-tech-700 hover:border-tech-primary transition-all cursor-pointer group">
                            <div class="h-32 rounded-lg bg-cover bg-center mb-3 relative overflow-hidden" style="background-image: url('https://images.unsplash.com/photo-1534258936925-c48947387e3b?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&q=80');">
                                <div class="absolute inset-0 bg-black/30 group-hover:bg-transparent transition-all"></div>
                            </div>
                            <h4 class="font-bold text-lg">Cross Tech</h4>
                            <p class="text-xs text-tech-muted mb-3">19:30 • Arena • Prof. Pedro</p>
                            <button class="w-full py-2 rounded-lg bg-tech-800 text-tech-primary border border-tech-primary/30 hover:bg-tech-primary hover:text-white transition-colors text-sm font-bold">Reservar</button>
                        </div>

                         <!-- Card Aula 3 -->
                         <div class="min-w-[260px] glass-card p-4 rounded-xl border-t border-tech-700 hover:border-tech-primary transition-all cursor-pointer group">
                            <div class="h-32 rounded-lg bg-cover bg-center mb-3 relative overflow-hidden" style="background-image: url('https://images.unsplash.com/photo-1599058945522-28d584b6f0ff?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&q=80');">
                                <div class="absolute inset-0 bg-black/30 group-hover:bg-transparent transition-all"></div>
                            </div>
                            <h4 class="font-bold text-lg">Spinning</h4>
                            <p class="text-xs text-tech-muted mb-3">20:00 • Sala Bike • Prof. Marcos</p>
                            <button class="w-full py-2 rounded-lg bg-tech-800 text-tech-primary border border-tech-primary/30 hover:bg-tech-primary hover:text-white transition-colors text-sm font-bold">Reservar</button>
                        </div>
                    </div>
                </div>

                <!-- Vitrine de Produtos -->
                <div>
                    <div class="flex justify-between items-end mb-4">
                        <h3 class="text-xl font-bold flex items-center gap-2"><i data-lucide="shopping-bag" class="w-5 h-5 text-tech-primary"></i> Destaques da Loja</h3>
                        <a href="#" class="text-tech-primary text-sm hover:underline">Ver loja completa</a>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Prod 1 -->
                        <div class="glass-card p-4 rounded-xl flex gap-4 hover:bg-white/5 cursor-pointer transition-colors group relative overflow-hidden">
                            <div class="absolute top-2 right-2 bg-tech-primary text-white text-[10px] font-bold px-2 py-0.5 rounded">-15%</div>
                            <div class="w-20 h-20 bg-white rounded-lg flex items-center justify-center p-2 shrink-0">
                                <img src="https://images.unsplash.com/photo-1593095948071-474c5cc2989d?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&q=60" class="h-full object-contain group-hover:scale-110 transition-transform">
                            </div>
                            <div class="flex flex-col justify-center w-full">
                                <h4 class="font-bold text-sm leading-tight mb-1">Whey Tech Isolate</h4>
                                <p class="text-xs text-tech-muted mb-2">Chocolate • 900g</p>
                                <div class="flex items-center justify-between">
                                    <p class="text-tech-primary font-bold text-sm">R$ 189,90</p>
                                    <button class="p-1.5 rounded bg-tech-700 hover:bg-tech-primary text-white transition-colors" onclick="addToCart('Whey Tech Isolate', 189.90, 'https://images.unsplash.com/photo-1593095948071-474c5cc2989d?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&q=60')"><i data-lucide="plus" class="w-4 h-4"></i></button>
                                </div>
                            </div>
                        </div>

                        <!-- Prod 2 -->
                        <div class="glass-card p-4 rounded-xl flex gap-4 hover:bg-white/5 cursor-pointer transition-colors group">
                            <div class="w-20 h-20 bg-white rounded-lg flex items-center justify-center p-2 shrink-0">
                                <img src="https://images.unsplash.com/photo-1526506118085-60ce8714f8c5?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&q=60" class="h-full object-contain group-hover:scale-110 transition-transform">
                            </div>
                            <div class="flex flex-col justify-center w-full">
                                <h4 class="font-bold text-sm leading-tight mb-1">Creatina Power</h4>
                                <p class="text-xs text-tech-muted mb-2">Pura • 300g</p>
                                <div class="flex items-center justify-between">
                                    <p class="text-tech-primary font-bold text-sm">R$ 89,90</p>
                                    <button class="p-1.5 rounded bg-tech-700 hover:bg-tech-primary text-white transition-colors" onclick="addToCart('Creatina Power', 89.90, 'https://images.unsplash.com/photo-1526506118085-60ce8714f8c5?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&q=60')"><i data-lucide="plus" class="w-4 h-4"></i></button>
                                </div>
                            </div>
                        </div>

                        <!-- Prod 3 -->
                        <div class="glass-card p-4 rounded-xl flex gap-4 hover:bg-white/5 cursor-pointer transition-colors group">
                            <div class="w-20 h-20 bg-white rounded-lg flex items-center justify-center p-2 shrink-0">
                                <img src="https://images.unsplash.com/photo-1591196720526-7f415354e601?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&q=60" class="h-full object-contain group-hover:scale-110 transition-transform">
                            </div>
                            <div class="flex flex-col justify-center w-full">
                                <h4 class="font-bold text-sm leading-tight mb-1">Tech Shaker</h4>
                                <p class="text-xs text-tech-muted mb-2">700ml • Preto</p>
                                <div class="flex items-center justify-between">
                                    <p class="text-tech-primary font-bold text-sm">R$ 45,00</p>
                                    <button class="p-1.5 rounded bg-tech-700 hover:bg-tech-primary text-white transition-colors" onclick="addToCart('Tech Shaker', 45.00, 'https://images.unsplash.com/photo-1591196720526-7f415354e601?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&q=60')"><i data-lucide="plus" class="w-4 h-4"></i></button>
                                </div>
                            </div>
                        </div>

                        <!-- Prod 4 (Promo) -->
                        <div class="glass-card p-4 rounded-xl flex gap-4 hover:bg-white/5 cursor-pointer transition-colors group bg-gradient-to-br from-tech-800 to-tech-700 border border-tech-primary/30">
                            <div class="w-20 h-20 bg-white rounded-lg flex items-center justify-center p-2 shrink-0">
                                <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&q=60" class="h-full object-contain group-hover:scale-110 transition-transform">
                            </div>
                            <div class="flex flex-col justify-center w-full">
                                <h4 class="font-bold text-sm leading-tight mb-1">Runner X</h4>
                                <p class="text-xs text-tech-muted mb-2">Pro Edition</p>
                                <div class="flex items-center justify-between">
                                    <p class="text-tech-primary font-bold text-sm">R$ 499,90</p>
                                    <button class="p-1.5 rounded bg-tech-700 hover:bg-tech-primary text-white transition-colors" onclick="addToCart('Runner X', 499.90, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&q=60')"><i data-lucide="plus" class="w-4 h-4"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= VIEW: FREQUENCY ================= -->
            <div id="view-frequency" class="hidden space-y-8 animate-slide-up">
                <!-- Conteúdo da frequência (Mantido) -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h2 class="text-2xl font-bold">Registro de Frequência</h2>
                        <p class="text-tech-muted text-sm">Controle seus dias de treino.</p>
                    </div>
                    <div class="flex gap-4">
                        <div class="glass-card px-4 py-2 rounded-lg text-center border border-tech-700">
                            <p class="text-xs text-tech-muted uppercase">Treinos no Mês</p>
                            <p class="text-xl font-bold text-white" id="month-count">--</p>
                        </div>
                        <div class="glass-card px-4 py-2 rounded-lg text-center border border-tech-primary/30 bg-tech-primary/10">
                            <p class="text-xs text-tech-muted uppercase">Sequência</p>
                            <p class="text-xl font-bold text-tech-primary" id="streak-count">4 🔥</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Coluna 1: Heatmap (Visual) -->
                    <div class="lg:col-span-1">
                        <div class="glass-card p-6 rounded-2xl h-full">
                            <!-- Navegação de Mês -->
                            <div class="flex items-center justify-between mb-6">
                                <button onclick="changeMonth(-1)" class="p-2 rounded-lg hover:bg-white/10 text-tech-muted hover:text-white transition-colors">
                                    <i data-lucide="chevron-left" class="w-5 h-5"></i>
                                </button>
                                <h3 class="font-bold text-lg flex items-center gap-2" id="current-month-display">
                                    <i data-lucide="calendar" class="w-4 h-4 text-tech-primary"></i> 
                                    <span>Carregando...</span>
                                </h3>
                                <button onclick="changeMonth(1)" class="p-2 rounded-lg hover:bg-white/10 text-tech-muted hover:text-white transition-colors">
                                    <i data-lucide="chevron-right" class="w-5 h-5"></i>
                                </button>
                            </div>

                            <div class="frequency-grid" id="heatmap-grid">
                                <!-- JS vai preencher isso -->
                            </div>
                            
                            <div class="flex justify-between mt-6 text-xs text-tech-muted">
                                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-tech-700"></span> Ausente</span>
                                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full border border-white bg-transparent"></span> Hoje</span>
                                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-tech-primary"></span> Presente</span>
                            </div>
                        </div>
                    </div>

                    <!-- Coluna 2: Histórico Detalhado (Timeline) -->
                    <div class="lg:col-span-2">
                        <div class="glass-card p-6 rounded-2xl min-h-[400px]">
                            <h3 class="font-bold mb-6 flex items-center gap-2"><i data-lucide="clock" class="w-4 h-4 text-tech-primary"></i> Histórico Recente</h3>
                            <div class="history-timeline space-y-6" id="access-history"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= VIEW: WORKOUTS & DIET ================= -->
            <div id="view-workouts" class="hidden animate-slide-up">
                <h2 class="text-2xl font-bold mb-6">Meus Treinos</h2>
                <div class="glass-card p-6 rounded-2xl text-center py-12">
                    <i data-lucide="dumbbell" class="w-12 h-12 text-tech-muted mx-auto mb-3"></i>
                    <h3 class="font-bold">Treino A - Peito e Tríceps</h3>
                    <p class="text-tech-muted text-sm mb-4">Seu treino de hoje está configurado.</p>
                    <button class="px-6 py-2 bg-tech-primary text-white rounded-lg">Iniciar Treino</button>
                </div>
            </div>

             <div id="view-diet" class="hidden animate-slide-up">
                <h2 class="text-2xl font-bold mb-6">Nutrição</h2>
                <div class="glass-card p-6 rounded-2xl text-center py-12">
                    <i data-lucide="apple" class="w-12 h-12 text-tech-muted mx-auto mb-3"></i>
                    <h3 class="font-bold">Plano Alimentar</h3>
                    <p class="text-tech-muted text-sm mb-4">Metas de macros do dia.</p>
                    <button class="px-6 py-2 bg-tech-800 border border-tech-700 hover:bg-tech-700 rounded-lg">Ver Detalhes</button>
                </div>
            </div>

        </div>
    </main>

    <!-- ================= SIDEBAR CART (CARRINHO ATUALIZADO) ================= -->
    <div id="cart-sidebar" class="fixed inset-y-0 right-0 w-80 bg-tech-900 border-l border-tech-700 shadow-2xl transform translate-x-full transition-transform duration-300 z-50 flex flex-col">
        <div class="p-6 border-b border-tech-700 flex justify-between items-center">
            <h3 class="text-xl font-bold flex items-center gap-2"><i data-lucide="shopping-bag" class="text-tech-primary"></i> Carrinho</h3>
            <button onclick="toggleCart()" class="text-gray-400 hover:text-white"><i data-lucide="x"></i></button>
        </div>
        
        <div class="flex-1 overflow-y-auto p-6 space-y-4" id="cart-items-container">
            <!-- Itens serão injetados via JS -->
        </div>

        <div class="p-6 border-t border-tech-700 bg-tech-800">
            <div class="flex justify-between mb-4 text-sm">
                <span class="text-gray-400">Subtotal</span>
                <span class="font-bold text-white" id="cart-total">R$ 0,00</span>
            </div>
            <button onclick="checkout()" class="w-full py-3 bg-tech-primary hover:bg-tech-primaryHover text-white font-bold rounded-lg transition-colors flex justify-center gap-2">
                <i data-lucide="credit-card" class="w-5 h-5"></i> Finalizar Compra
            </button>
        </div>
    </div>
    
    <!-- Backdrop do Carrinho -->
    <div id="cart-backdrop" onclick="toggleCart()" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 hidden opacity-0 transition-opacity duration-300"></div>

    <!-- ================= MODAL QR CODE ================= -->
    <div id="qr-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center modal-backdrop transition-opacity duration-300 opacity-0 pointer-events-none">
        <div class="glass-card p-8 rounded-2xl w-full max-w-sm text-center transform scale-95 transition-transform duration-300 border-t-4 border-tech-primary relative shadow-2xl" id="qr-modal-content">
            <button onclick="closeQRModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white"><i data-lucide="x" class="w-6 h-6"></i></button>

            <!-- CONTEÚDO PADRÃO (ACESSÍVEL) -->
            <div id="qr-active-content">
                <h3 class="text-2xl font-bold text-white mb-1">Acesso TechFit</h3>
                <p class="text-tech-muted text-sm mb-6" id="qr-status-text">Aproxime este código da catraca para entrar.</p>
                <div class="bg-white p-4 rounded-xl inline-block mb-6 relative group">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode($nomeAluno . '-TechFit-12345'); ?>" alt="QR Code" class="w-48 h-48 opacity-90">
                    <div class="absolute top-0 left-0 w-full h-1 bg-tech-primary opacity-50 animate-[scan_2s_ease-in-out_infinite]"></div>
                </div>
                <div id="action-status" class="mb-6 p-2 rounded bg-tech-800 text-xs text-tech-primary border border-tech-primary/20">
                    <span class="animate-pulse">●</span> Aguardando leitura...
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <button onclick="processAccess()" id="btn-confirm-access" class="py-3 bg-tech-primary hover:bg-tech-primaryHover text-white font-bold rounded-lg text-sm flex items-center justify-center gap-2 transition-colors">
                        <i data-lucide="log-in"></i> Confirmar
                    </button>
                    <button onclick="closeQRModal()" class="py-3 bg-tech-800 hover:bg-tech-700 text-white font-bold rounded-lg text-sm transition-colors">Cancelar</button>
                </div>
            </div>

            <!-- CONTEÚDO BLOQUEADO (TREINO JÁ FEITO) -->
            <div id="qr-blocked-content" class="hidden">
                <div class="flex flex-col items-center py-6">
                    <div class="w-20 h-20 bg-green-500/20 rounded-full flex items-center justify-center mb-6">
                        <i data-lucide="check-circle" class="w-10 h-10 text-green-500"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2">Treino Concluído!</h3>
                    <p class="text-tech-muted text-sm mb-8 px-4">Você já registrou seu treino de hoje. Descanse e volte amanhã com tudo!</p>
                    <button onclick="closeQRModal()" class="w-full py-3 bg-tech-800 hover:bg-tech-700 text-white font-bold rounded-lg text-sm transition-colors">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed top-5 right-5 bg-tech-800 border-l-4 border-tech-success text-white px-6 py-4 rounded shadow-2xl transform translate-x-full transition-transform duration-300 z-50 flex items-center gap-3">
        <div class="bg-tech-success/20 p-2 rounded-full"><i id="toast-icon" data-lucide="check" class="w-5 h-5 text-tech-success"></i></div>
        <div>
            <h4 class="font-bold text-sm" id="toast-title">Sucesso!</h4>
            <p class="text-xs text-tech-muted" id="toast-msg">Operação realizada.</p>
        </div>
    </div>

    <!-- STYLE PARA O SCANNER -->
    <style>
        @keyframes scan {
            0%, 100% { top: 0%; opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { top: 100%; opacity: 0; }
        }
    </style>

    <!-- LOGICA JS -->
    <script>
        lucide.createIcons();

        // --- SISTEMA DE CARRINHO DE COMPRAS ---
        const CART_STORAGE_KEY = 'techfit_cart_v1';
        let cart = [];

        function loadCart() {
            const saved = localStorage.getItem(CART_STORAGE_KEY);
            if (saved) cart = JSON.parse(saved);
            updateCartBadge();
        }

        function saveCart() {
            localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart));
            updateCartBadge();
            renderCart();
        }

        function addToCart(name, price, image) {
            const existing = cart.find(item => item.name === name);
            if (existing) {
                existing.quantity++;
            } else {
                cart.push({ name, price, image, quantity: 1 });
            }
            saveCart();
            showToast('Adicionado!', `${name} foi para o carrinho.`, 'success');
            
            const cartBtn = document.querySelector('button[onclick="toggleCart()"] i');
            if(cartBtn) {
                cartBtn.classList.add('text-tech-primary', 'scale-125');
                setTimeout(() => cartBtn.classList.remove('text-tech-primary', 'scale-125'), 200);
            }
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            saveCart();
        }

        function updateCartBadge() {
            const badge = document.getElementById('cart-badge');
            const totalItems = cart.reduce((acc, item) => acc + item.quantity, 0);
            
            if (totalItems > 0) {
                badge.innerText = totalItems;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }

        function renderCart() {
            const container = document.getElementById('cart-items-container');
            const totalEl = document.getElementById('cart-total');
            
            let htmlContent = '';
            let total = 0;

            if (cart.length === 0) {
                htmlContent = `
                    <div class="text-center text-tech-muted py-10" id="empty-cart-msg">
                        <i data-lucide="shopping-cart" class="w-12 h-12 mx-auto mb-3 opacity-20"></i>
                        <p>Seu carrinho está vazio.</p>
                    </div>
                `;
            } else {
                cart.forEach((item, index) => {
                    total += item.price * item.quantity;
                    htmlContent += `
                        <div class="flex gap-3 bg-white/5 p-3 rounded-lg border border-tech-700 animate-fade-in relative group">
                            <div class="w-16 h-16 bg-white rounded p-1 flex items-center justify-center shrink-0">
                                <img src="${item.image}" class="h-full object-contain">
                            </div>
                            <div class="flex-1 flex flex-col justify-between">
                                <div>
                                    <h4 class="font-bold text-sm text-white line-clamp-1">${item.name}</h4>
                                    <p class="text-xs text-tech-muted">Qtd: ${item.quantity}</p>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-tech-primary font-bold text-sm">R$ ${(item.price * item.quantity).toFixed(2).replace('.', ',')}</span>
                                    <button onclick="removeFromCart(${index})" class="text-red-400 hover:text-red-300 text-xs p-1 rounded hover:bg-white/10 transition-colors" title="Remover Item">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }
            
            container.innerHTML = htmlContent;
            totalEl.innerText = `R$ ${total.toFixed(2).replace('.', ',')}`;
            lucide.createIcons();
        }

        function toggleCart() {
            const sidebar = document.getElementById('cart-sidebar');
            const backdrop = document.getElementById('cart-backdrop');
            
            if (sidebar.classList.contains('translate-x-full')) {
                sidebar.classList.remove('translate-x-full');
                backdrop.classList.remove('hidden');
                setTimeout(() => backdrop.classList.remove('opacity-0'), 10);
                renderCart();
            } else {
                sidebar.classList.add('translate-x-full');
                backdrop.classList.add('opacity-0');
                setTimeout(() => backdrop.classList.add('hidden'), 300);
            }
        }

        function checkout() {
            if(cart.length === 0) return;
            showToast('Compra Realizada!', 'Seus produtos foram reservados.', 'success');
            cart = [];
            saveCart();
            toggleCart();
        }

        // --- SISTEMA DE SIDEBAR (TOGGLE) ---
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
            
            if (sidebar.classList.contains('collapsed')) {
                sidebar.classList.remove('w-64');
            } else {
                sidebar.classList.add('w-64');
            }
        }

        // --- GERENCIAMENTO DE ESTADO (FREQUÊNCIA) ---
        const STORAGE_KEY = 'techfit_state_v1';

        function loadState() {
            const saved = localStorage.getItem(STORAGE_KEY);
            const todayStr = new Date().toDateString();

            if (saved) {
                const parsed = JSON.parse(saved);
                if (parsed.lastActiveDate !== todayStr) {
                    return {
                        isCheckedIn: false,
                        lastCheckInTime: null,
                        dailyCompleted: false,
                        lastActiveDate: todayStr
                    };
                }
                if (parsed.lastCheckInTime) {
                    parsed.lastCheckInTime = new Date(parsed.lastCheckInTime);
                }
                return parsed;
            }
            return {
                isCheckedIn: false,
                lastCheckInTime: null,
                dailyCompleted: false,
                lastActiveDate: todayStr
            };
        }

        function saveState() {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(userState));
        }

        let userState = loadState();
        let currentViewDate = new Date();
        let historyData = [
            { date: '02 Set', weekday: 'Sexta', checkIn: '18:15', checkOut: '19:30', duration: '1h 15m', status: 'completed' },
            { date: '01 Set', weekday: 'Quinta', checkIn: '19:00', checkOut: '20:00', duration: '1h 00m', status: 'completed' },
            { date: '29 Ago', weekday: 'Segunda', checkIn: '07:30', checkOut: '08:45', duration: '1h 15m', status: 'completed' }
        ];

        // --- NAVEGAÇÃO DE PÁGINA ---
        function switchView(viewName) {
            document.querySelectorAll('[id^="view-"]').forEach(el => el.classList.add('hidden'));
            const target = document.getElementById(`view-${viewName}`);
            if(target) {
                target.classList.remove('hidden');
                target.classList.remove('animate-fade-in', 'animate-slide-up');
                void target.offsetWidth; 
                target.classList.add('animate-fade-in');
            }

            document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
            const btn = document.getElementById(`nav-${viewName}`);
            if(btn) btn.classList.add('active');

            if(viewName === 'frequency') {
                updateMonthDisplay();
                renderHeatmap();
            }
        }

        // --- LÓGICA DE CALENDÁRIO ---
        function changeMonth(delta) {
            currentViewDate.setMonth(currentViewDate.getMonth() + delta);
            updateMonthDisplay();
            renderHeatmap();
        }

        function updateMonthDisplay() {
            const options = { year: 'numeric', month: 'long' };
            let text = currentViewDate.toLocaleDateString('pt-BR', options);
            text = text.charAt(0).toUpperCase() + text.slice(1);
            document.getElementById('current-month-display').innerHTML = `<i data-lucide="calendar" class="w-4 h-4 text-tech-primary"></i> ${text}`;
            lucide.createIcons();
        }

        function renderHeatmap() {
            const grid = document.getElementById('heatmap-grid');
            grid.innerHTML = '';
            
            const year = currentViewDate.getFullYear();
            const month = currentViewDate.getMonth();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const today = new Date();
            const isCurrentMonth = today.getMonth() === month && today.getFullYear() === year;

            let presentCount = 0;

            for(let i=1; i<=daysInMonth; i++) {
                const day = document.createElement('div');
                day.classList.add('freq-day');
                day.innerText = i;
                
                if (isCurrentMonth && i === today.getDate()) day.classList.add('today');

                if (isCurrentMonth && i === today.getDate() && userState.dailyCompleted) {
                    day.classList.add('present');
                    presentCount++;
                } else if (today > new Date(year, month, i)) {
                    if ((i + month) % 3 !== 0 && (i % 5 !== 0)) { 
                        day.classList.add('present');
                        presentCount++;
                    }
                } else if (!isCurrentMonth && today < new Date(year, month, i)) {
                    day.classList.add('future');
                }
                grid.appendChild(day);
            }
            document.getElementById('month-count').innerText = presentCount;
        }

        // --- LÓGICA DE ACESSO QR ---
        function openQRModal() {
            const modal = document.getElementById('qr-modal');
            const modalContent = modal.querySelector('#qr-modal-content');
            
            const contentActive = document.getElementById('qr-active-content');
            const contentBlocked = document.getElementById('qr-blocked-content');
            
            const btnConfirm = document.getElementById('btn-confirm-access');
            const statusText = document.getElementById('qr-status-text');
            const actionStatus = document.getElementById('action-status');

            if (userState.dailyCompleted) {
                contentActive.classList.add('hidden');
                contentBlocked.classList.remove('hidden');
            } else {
                contentBlocked.classList.add('hidden');
                contentActive.classList.remove('hidden');

                if (userState.isCheckedIn) {
                    statusText.innerText = "Registre sua SAÍDA para contabilizar o treino.";
                    btnConfirm.innerHTML = '<i data-lucide="log-out"></i> Registrar Saída';
                    btnConfirm.classList.replace('bg-tech-primary', 'bg-red-600');
                    btnConfirm.classList.replace('hover:bg-tech-primaryHover', 'hover:bg-red-500');
                    actionStatus.innerHTML = '<span class="text-red-400">●</span> Saindo...';
                } else {
                    statusText.innerText = "Aproxime o código para registrar sua ENTRADA.";
                    btnConfirm.innerHTML = '<i data-lucide="log-in"></i> Registrar Entrada';
                    btnConfirm.classList.replace('bg-red-600', 'bg-tech-primary');
                    btnConfirm.classList.replace('hover:bg-red-500', 'hover:bg-tech-primaryHover');
                    actionStatus.innerHTML = '<span class="text-tech-primary">●</span> Entrando...';
                }
            }
            lucide.createIcons();
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0', 'pointer-events-none');
                modalContent.classList.remove('scale-95');
                modalContent.classList.add('scale-100');
            }, 10);
        }

        function closeQRModal() {
            const modal = document.getElementById('qr-modal');
            const modalContent = modal.querySelector('#qr-modal-content');
            modal.classList.add('opacity-0', 'pointer-events-none');
            modalContent.classList.remove('scale-100');
            modalContent.classList.add('scale-95');
            setTimeout(() => modal.classList.add('hidden'), 300);
        }

        function processAccess() {
            const now = new Date();
            const timeString = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
            
            if (!userState.isCheckedIn) {
                userState.isCheckedIn = true;
                userState.lastCheckInTime = now;
                userState.lastActiveDate = now.toDateString();
                
                historyData.unshift({
                    date: 'Hoje',
                    weekday: getWeekday(now.getDay()),
                    checkIn: timeString,
                    checkOut: '--:--',
                    duration: 'Em andamento',
                    status: 'active'
                });
                
                saveState();
                showToast('Bem-vindo!', 'Entrada registrada com sucesso.', 'success');
                updateHomeButtonState(true);
            } else {
                userState.isCheckedIn = false;
                userState.dailyCompleted = true;
                
                if (historyData.length > 0 && historyData[0].status === 'active') {
                    historyData[0].checkOut = timeString;
                    if (userState.lastCheckInTime) {
                        const checkInDate = new Date(userState.lastCheckInTime);
                        const diffMs = now - checkInDate;
                        const diffMins = Math.round(((diffMs % 86400000) % 3600000) / 60000);
                        const diffHrs = Math.floor((diffMs % 86400000) / 3600000);
                        historyData[0].duration = `${diffHrs}h ${diffMins}m`;
                    } else {
                        historyData[0].duration = "Concluído";
                    }
                    historyData[0].status = 'completed';
                }
                
                saveState();
                showToast('Até logo!', 'Treino finalizado com sucesso.', 'success');
                updateHomeButtonState(false);
            }
            renderHistory();
            closeQRModal();
        }

        function updateHomeButtonState(isInside) {
            const btnHome = document.getElementById('home-access-btn');
            const titleHome = document.getElementById('home-access-title');
            const descHome = document.getElementById('home-access-desc');
            const sidebarBtn = document.getElementById('btn-quick-access');

            if (userState.dailyCompleted && !isInside) {
                if(btnHome) {
                    btnHome.innerHTML = '<i data-lucide="check-circle"></i> Treino Concluído';
                    btnHome.classList.replace('text-red-600', 'text-green-600'); 
                    btnHome.classList.replace('text-tech-900', 'text-green-600');
                }
                if(sidebarBtn) {
                    sidebarBtn.innerHTML = '<i data-lucide="check-circle" class="w-5 h-5"></i> Treino Concluído';
                    sidebarBtn.classList.remove('bg-red-600', 'hover:bg-red-500', 'bg-tech-primary', 'hover:bg-tech-primaryHover');
                    sidebarBtn.classList.add('bg-green-600', 'hover:bg-green-500');
                }
                return;
            }

            if (isInside) {
                if(btnHome) {
                    btnHome.innerHTML = '<i data-lucide="log-out"></i> Sair da Academia';
                    btnHome.classList.replace('text-tech-900', 'text-red-600');
                    titleHome.innerHTML = '<i data-lucide="clock" class="text-tech-primary"></i> Treino em Andamento';
                    descHome.innerText = "Não esqueça de registrar sua saída.";
                }
                if(sidebarBtn) {
                    sidebarBtn.innerHTML = '<i data-lucide="log-out" class="w-5 h-5"></i> Sair da Academia';
                    sidebarBtn.classList.replace('bg-tech-primary', 'bg-red-600');
                    sidebarBtn.classList.replace('hover:bg-tech-primaryHover', 'hover:bg-red-500');
                }
            } else {
                if(btnHome) {
                    btnHome.innerHTML = '<i data-lucide="qr-code"></i> Abrir Meu Passe';
                    btnHome.classList.replace('text-red-600', 'text-tech-900');
                    titleHome.innerHTML = '<i data-lucide="zap" class="text-tech-primary"></i> Acesso Rápido';
                    descHome.innerText = "Utilize seu QR Code digital para liberar a catraca.";
                }
                if(sidebarBtn) {
                    sidebarBtn.innerHTML = '<i data-lucide="qr-code" class="w-5 h-5"></i> Acessar Academia';
                    sidebarBtn.classList.replace('bg-red-600', 'bg-tech-primary');
                    sidebarBtn.classList.replace('hover:bg-red-500', 'hover:bg-tech-primaryHover');
                }
            }
            lucide.createIcons();
        }

        function getWeekday(dayIndex) {
            const days = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
            return days[dayIndex];
        }

        function renderHistory() {
            const container = document.getElementById('access-history');
            container.innerHTML = '';
            historyData.forEach(item => {
                const dotColor = item.status === 'active' ? 'bg-green-500 animate-pulse' : 'bg-tech-primary';
                const opacityClass = item.status === 'active' ? 'opacity-100' : 'opacity-80';
                
                const dateParts = item.date.split(' ');
                const dayDisplay = dateParts[0];
                const monthDisplay = dateParts.length > 1 ? dateParts[1] : '';

                const html = `
                    <div class="relative pl-8 ${opacityClass}">
                        <div class="absolute left-[-5px] top-1/2 -translate-y-1/2 w-3 h-3 rounded-full ${dotColor} border-2 border-tech-900 z-10"></div>
                        <div class="glass-card p-4 rounded-xl flex items-center justify-between group hover:bg-white/5 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="text-center w-14 border-r border-tech-700 pr-4">
                                    <span class="block text-xl font-bold text-white leading-none">${dayDisplay}</span>
                                    <span class="block text-xs text-tech-muted uppercase">${monthDisplay}</span>
                                </div>
                                <div>
                                    <p class="font-bold text-white text-sm">${item.weekday}</p>
                                    <p class="text-xs ${item.status === 'active' ? 'text-green-400' : 'text-tech-muted'}">
                                        ${item.status === 'active' ? 'Treinando agora...' : 'Treino concluído'}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-6 text-right">
                                <div class="hidden md:block">
                                    <div class="flex items-center gap-2 text-xs text-gray-400"><i data-lucide="log-in" class="w-3 h-3 text-tech-primary"></i> ${item.checkIn}</div>
                                    <div class="flex items-center gap-2 text-xs text-gray-400 mt-1"><i data-lucide="log-out" class="w-3 h-3 text-red-400"></i> ${item.checkOut}</div>
                                </div>
                                <div class="bg-tech-800 px-3 py-1 rounded-lg border border-tech-700"><span class="text-sm font-mono font-bold text-white">${item.duration}</span></div>
                            </div>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', html);
            });
            lucide.createIcons();
        }

        function showToast(title, msg, type = 'success') {
            const toast = document.getElementById('toast');
            const icon = document.getElementById('toast-icon');
            document.getElementById('toast-title').innerText = title;
            document.getElementById('toast-msg').innerText = msg;
            if(type === 'success') {
                icon.className = "w-5 h-5 text-green-500";
                toast.querySelector('.rounded-full').classList.replace('bg-red-500/20', 'bg-tech-success/20');
            }
            toast.classList.remove('translate-x-full');
            setTimeout(() => toast.classList.add('translate-x-full'), 4000);
        }

        // Init App
        loadCart();
        updateHomeButtonState(userState.isCheckedIn);
        renderHistory();
        updateMonthDisplay(); 
    </script>
    <!-- Dados de treinos vindos do PHP (disponibiliza `treinosVindosDoPHP` para o JS) -->
    <?php
        // Permite que um controller PHP defina $treinos antes de incluir este template.
        if (!isset($treinos) || !is_array($treinos)) {
            $treinos = [
                'A' => [
                    ['name' => 'Supino Reto', 'equip' => 'Barra', 'sets' => 4, 'reps' => '8-12', 'weight' => 30, 'done' => false],
                    ['name' => 'Supino Inclinado', 'equip' => 'Halter', 'sets' => 3, 'reps' => '10-12', 'weight' => 22, 'done' => false],
                    ['name' => 'Tríceps Corda', 'equip' => 'Polia', 'sets' => 4, 'reps' => '15', 'weight' => 25, 'done' => false]
                ],
                'B' => [
                    ['name' => 'Puxada Alta', 'equip' => 'Máquina', 'sets' => 4, 'reps' => '10', 'weight' => 50, 'done' => false],
                    ['name' => 'Remada Curvada', 'equip' => 'Barra', 'sets' => 4, 'reps' => '8-10', 'weight' => 40, 'done' => false],
                    ['name' => 'Rosca Direta', 'equip' => 'Barra W', 'sets' => 3, 'reps' => '12', 'weight' => 15, 'done' => false]
                ],
                'C' => [
                    ['name' => 'Agachamento Livre', 'equip' => 'Barra', 'sets' => 5, 'reps' => '8', 'weight' => 80, 'done' => false],
                    ['name' => 'Leg Press 45', 'equip' => 'Máquina', 'sets' => 4, 'reps' => '12', 'weight' => 200, 'done' => false],
                    ['name' => 'Elevação Lateral', 'equip' => 'Halter', 'sets' => 4, 'reps' => '15', 'weight' => 10, 'done' => false]
                ]
            ];
        }
    ?>

    <script>
        // Exporta para JS de forma segura
        const treinosVindosDoPHP = <?php echo json_encode($treinos, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>;
    </script>

    <!-- Carrega o script principal da página (implementação em view/src/paginacliente.js) -->
    <script src="src/paginacliente.js"></script>

</body>
</html>