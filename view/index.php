<!DOCTYPE html>
<html lang="pt-br" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFit - A Evolução do Seu Treino</title>
    
    <!-- Importando Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Configurando a Paleta de Cores -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        tech: {
                            900: '#111827', 
                            800: '#1f2937', 
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
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'gradient-x': 'gradient-x 3s ease infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        },
                        'gradient-x': {
                            '0%, 100%': {
                                'background-size': '200% 200%',
                                'background-position': 'left center'
                            },
                            '50%': {
                                'background-size': '200% 200%',
                                'background-position': 'right center'
                            },
                        }
                    }
                }
            }
        }
    </script>

    <!-- Ícones Lucide -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Fonte Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* Estilos Globais */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #111827;
            color: white;
            overflow-x: hidden;
        }

        /* Hero Background com Parallax Suave */
        .hero-bg {
            background-image: linear-gradient(to right, rgba(17, 24, 39, 0.95), rgba(17, 24, 39, 0.6)), url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        /* Animação de Scroll (Reveal) */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s cubic-bezier(0.5, 0, 0, 1);
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* Stagger Delay para elementos filhos */
        .reveal-delay-100 { transition-delay: 100ms; }
        .reveal-delay-200 { transition-delay: 200ms; }
        .reveal-delay-300 { transition-delay: 300ms; }

        /* Botões com Glow Intenso */
        .btn-glow {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .btn-glow:hover {
            box-shadow: 0 0 20px rgba(234, 88, 12, 0.6);
            transform: translateY(-2px);
        }
        .btn-glow::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }
        .btn-glow:hover::after {
            left: 100%;
        }

        /* Card Hover Effect Premium */
        .card-premium {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(55, 65, 81, 0.5);
        }
        .card-premium:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px -5px rgba(0,0,0,0.4), 0 0 15px rgba(234, 88, 12, 0.3);
            border-color: #ea580c;
        }
        .card-premium:hover img {
            transform: scale(1.1);
        }

        /* CORREÇÃO DA ANIMAÇÃO DE DIGITAÇÃO 
           - Ciclo infinito
           - Sem barra laranja
        */
        .typing-container {
            display: inline-flex;
            justify-content: center; /* Centraliza se necessário */
            width: fit-content;
        }

        .typing-effect {
            white-space: nowrap;
            overflow: hidden;
            display: inline-block;
            max-width: 0; /* Começa fechado */
            /* Animação ciclica infinita: 6 segundos total */
            animation: typing-cycle 6s steps(40, end) infinite, gradient-x 3s ease infinite;
            
            /* Mantendo o gradiente no texto */
            border-right: none !important; /* Garante que não tenha barra */
        }

        @keyframes typing-cycle {
            0% { max-width: 0; }
            30% { max-width: 100%; } /* Digita até 30% do tempo */
            70% { max-width: 100%; } /* Fica parado lendo até 70% */
            90% { max-width: 0; }    /* Apaga rápido */
            100% { max-width: 0; }   /* Pausa antes de recomeçar */
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 10px; }
        ::-webkit-scrollbar-track { background: #111827; }
        ::-webkit-scrollbar-thumb { background: #374151; border-radius: 5px; }
        ::-webkit-scrollbar-thumb:hover { background: #ea580c; }
    </style>
</head>
<body class="antialiased selection:bg-tech-primary selection:text-white">

    <!-- Navbar Glassmorphism -->
    <nav class="fixed w-full z-50 transition-all duration-300 bg-transparent" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <div class="flex items-center gap-2 group cursor-pointer">
                    <i data-lucide="dumbbell" class="h-8 w-8 text-tech-primary transition-transform group-hover:rotate-45 duration-500"></i>
                    <span class="font-bold text-2xl tracking-tighter text-white">TECH<span class="text-tech-primary">FIT</span></span>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-8">
                        <a href="#home" class="relative group px-3 py-2 text-sm font-medium hover:text-white transition-colors">
                            Início
                            <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-tech-primary transition-all group-hover:w-full"></span>
                        </a>
                        <a href="#classes" class="relative group px-3 py-2 text-sm font-medium hover:text-white transition-colors">
                            Aulas
                            <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-tech-primary transition-all group-hover:w-full"></span>
                        </a>
                        <a href="#products" class="relative group px-3 py-2 text-sm font-medium hover:text-white transition-colors">
                            Loja
                            <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-tech-primary transition-all group-hover:w-full"></span>
                        </a>
                        <a href="#plans" class="relative group px-3 py-2 text-sm font-medium hover:text-white transition-colors">
                            Planos
                            <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-tech-primary transition-all group-hover:w-full"></span>
                        </a>
                        <a href="areacliente.php" class="bg-tech-primary hover:bg-tech-primaryHover text-white px-6 py-2 rounded-full font-bold transition-all btn-glow ml-4">
                            Área do Aluno
                        </a>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <div class="-mr-2 flex md:hidden">
                    <button onclick="toggleMobileMenu()" class="text-gray-400 hover:text-white p-2">
                        <i data-lucide="menu" class="h-8 w-8"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div class="md:hidden hidden bg-tech-900/95 backdrop-blur-xl border-t border-tech-700 absolute w-full" id="mobile-menu">
            <div class="px-4 pt-2 pb-6 space-y-2">
                <a href="#home" class="block px-3 py-3 rounded-md text-base font-medium hover:bg-tech-800 hover:text-tech-primary transition-colors">Início</a>
                <a href="#classes" class="block px-3 py-3 rounded-md text-base font-medium hover:bg-tech-800 hover:text-tech-primary transition-colors">Aulas</a>
                <a href="#products" class="block px-3 py-3 rounded-md text-base font-medium hover:bg-tech-800 hover:text-tech-primary transition-colors">Loja</a>
                <a href="#plans" class="block px-3 py-3 rounded-md text-base font-medium hover:bg-tech-800 hover:text-tech-primary transition-colors">Planos</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section com Efeitos -->
    <section id="home" class="relative h-screen flex items-center justify-center hero-bg overflow-hidden">
        <!-- Partículas de fundo (Simples CSS) -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-tech-primary/10 rounded-full blur-3xl animate-pulse-slow"></div>
            <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl animate-pulse-slow" style="animation-delay: 1s;"></div>
        </div>

        <div class="text-center px-4 max-w-5xl mx-auto relative z-10">
            <div class="reveal inline-flex items-center gap-2 px-4 py-2 rounded-full bg-tech-800/80 backdrop-blur-sm border border-tech-700 mb-8 animate-float">
                <span class="w-2 h-2 rounded-full bg-tech-primary animate-ping"></span>
                <span class="text-sm font-medium text-gray-300">A academia do futuro chegou</span>
            </div>
            
            <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight mb-6 leading-tight reveal reveal-delay-100">
                TRANSFORME SEU CORPO <br>
                <!-- Texto Gradiente Animado + Typing Effect -->
                <span class="typing-container">
                    <span class="typing-effect text-transparent bg-clip-text bg-gradient-to-r from-tech-primary via-orange-400 to-tech-primary pb-2">
                        DOMINE SUA MENTE
                    </span>
                </span>
            </h1>
            
            <p class="text-xl text-gray-300 mb-10 max-w-2xl mx-auto reveal reveal-delay-200">
                Tecnologia de ponta, IA integrada e uma comunidade que te impulsiona além dos limites.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center reveal reveal-delay-300">
                <a href="#plans" class="bg-tech-primary hover:bg-tech-primaryHover text-white px-10 py-4 rounded-lg font-bold text-lg transition-all btn-glow transform hover:-translate-y-1">
                    Começar Agora
                </a>
                <a href="#classes" class="group bg-white/5 border border-white/20 hover:border-white hover:bg-white/10 text-white px-10 py-4 rounded-lg font-bold text-lg transition-all backdrop-blur-sm flex items-center justify-center gap-2">
                    Conhecer Modalidades 
                    <i data-lucide="arrow-down" class="w-5 h-5 group-hover:translate-y-1 transition-transform"></i>
                </a>
            </div>
            
            <!-- Stats Flutuantes -->
            <div class="mt-20 grid grid-cols-2 md:grid-cols-4 gap-8 border-t border-gray-700/30 pt-10 reveal reveal-delay-300 bg-tech-900/30 backdrop-blur-sm rounded-xl p-6">
                <div class="group hover:-translate-y-2 transition-transform duration-300">
                    <h3 class="text-4xl font-bold text-white group-hover:text-tech-primary transition-colors">1.5k+</h3>
                    <p class="text-tech-muted text-sm uppercase tracking-wider font-semibold">Alunos</p>
                </div>
                <div class="group hover:-translate-y-2 transition-transform duration-300 delay-75">
                    <h3 class="text-4xl font-bold text-white group-hover:text-tech-primary transition-colors">50+</h3>
                    <p class="text-tech-muted text-sm uppercase tracking-wider font-semibold">Treinadores</p>
                </div>
                <div class="group hover:-translate-y-2 transition-transform duration-300 delay-100">
                    <h3 class="text-4xl font-bold text-white group-hover:text-tech-primary transition-colors">24h</h3>
                    <p class="text-tech-muted text-sm uppercase tracking-wider font-semibold">Aberto</p>
                </div>
                <div class="group hover:-translate-y-2 transition-transform duration-300 delay-150">
                    <h3 class="text-4xl font-bold text-white group-hover:text-tech-primary transition-colors">4.9</h3>
                    <p class="text-tech-muted text-sm uppercase tracking-wider font-semibold">Estrelas</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Modalidades/Aulas -->
    <section id="classes" class="py-24 bg-tech-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 reveal">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">Nossas Modalidades</h2>
                <div class="w-24 h-1.5 bg-tech-primary mx-auto rounded-full"></div>
                <p class="mt-4 text-tech-muted text-lg">Tecnologia aplicada ao movimento.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Card 1 -->
                <div class="bg-tech-800 rounded-2xl overflow-hidden card-premium reveal group">
                    <div class="h-56 overflow-hidden relative">
                        <div class="absolute inset-0 bg-gradient-to-t from-tech-900 to-transparent z-10 opacity-60"></div>
                        <img src="https://images.unsplash.com/photo-1517836357463-d25dfeac3438?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="Musculação" class="w-full h-full object-cover transition-transform duration-700">
                        <div class="absolute top-4 right-4 z-20 bg-tech-primary/90 backdrop-blur text-white p-2 rounded-lg">
                            <i data-lucide="activity" class="w-6 h-6"></i>
                        </div>
                    </div>
                    <div class="p-8">
                        <h3 class="text-2xl font-bold mb-3 group-hover:text-tech-primary transition-colors">Musculação IA</h3>
                        <p class="text-tech-muted mb-6">Sensores biométricos que ajustam a carga ideal para o seu dia.</p>
                        <a href="#" class="inline-flex items-center text-tech-primary font-bold hover:tracking-wide transition-all">
                            EXPLORAR <i data-lucide="chevron-right" class="ml-2 w-4 h-4"></i>
                        </a>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="bg-tech-800 rounded-2xl overflow-hidden card-premium reveal reveal-delay-100 group">
                    <div class="h-56 overflow-hidden relative">
                        <div class="absolute inset-0 bg-gradient-to-t from-tech-900 to-transparent z-10 opacity-60"></div>
                        <img src="https://images.unsplash.com/photo-1534258936925-c48947387e3b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="Cross Training" class="w-full h-full object-cover transition-transform duration-700">
                         <div class="absolute top-4 right-4 z-20 bg-tech-primary/90 backdrop-blur text-white p-2 rounded-lg">
                            <i data-lucide="flame" class="w-6 h-6"></i>
                        </div>
                    </div>
                    <div class="p-8">
                        <h3 class="text-2xl font-bold mb-3 group-hover:text-tech-primary transition-colors">Cross Tech</h3>
                        <p class="text-tech-muted mb-6">Monitore seus batimentos e calorias em tempo real nos telões.</p>
                        <a href="#" class="inline-flex items-center text-tech-primary font-bold hover:tracking-wide transition-all">
                            EXPLORAR <i data-lucide="chevron-right" class="ml-2 w-4 h-4"></i>
                        </a>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="bg-tech-800 rounded-2xl overflow-hidden card-premium reveal reveal-delay-200 group">
                    <div class="h-56 overflow-hidden relative">
                        <div class="absolute inset-0 bg-gradient-to-t from-tech-900 to-transparent z-10 opacity-60"></div>
                        <img src="https://images.unsplash.com/photo-1518611012118-696072aa579a?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="Yoga" class="w-full h-full object-cover transition-transform duration-700">
                         <div class="absolute top-4 right-4 z-20 bg-tech-primary/90 backdrop-blur text-white p-2 rounded-lg">
                            <i data-lucide="sun" class="w-6 h-6"></i>
                        </div>
                    </div>
                    <div class="p-8">
                        <h3 class="text-2xl font-bold mb-3 group-hover:text-tech-primary transition-colors">Bio Yoga</h3>
                        <p class="text-tech-muted mb-6">Ambiente climatizado e sonoro controlado por IA para foco total.</p>
                        <a href="#" class="inline-flex items-center text-tech-primary font-bold hover:tracking-wide transition-all">
                            EXPLORAR <i data-lucide="chevron-right" class="ml-2 w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Loja / Produtos -->
    <section id="products" class="py-24 bg-tech-800 border-y border-tech-700 relative overflow-hidden">
        <!-- Detalhe de fundo -->
        <div class="absolute -right-20 top-20 w-96 h-96 bg-tech-primary/5 rounded-full blur-3xl"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex flex-col md:flex-row justify-between items-end mb-12 reveal">
                <div>
                    <h2 class="text-3xl md:text-4xl font-bold mb-2">TechFit Store</h2>
                    <p class="text-tech-muted">Suplementos e equipamentos oficiais.</p>
                </div>
                <a href="#" class="hidden md:flex items-center gap-2 text-tech-primary hover:text-white transition-colors group">
                    Ver todos os produtos <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Produto 1 -->
                <div class="bg-tech-900 rounded-xl p-4 border border-tech-700 card-premium reveal">
                    <div class="bg-white rounded-lg h-48 flex items-center justify-center mb-4 relative overflow-hidden group">
                        <span class="absolute top-2 left-2 bg-tech-900 text-white text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider z-10">Promo</span>
                        <img src="https://images.unsplash.com/photo-1593095948071-474c5cc2989d?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" alt="Whey Protein" class="h-32 object-contain group-hover:scale-110 transition-transform duration-500">
                    </div>
                    <h4 class="font-bold text-lg mb-1">Whey Tech Isolate</h4>
                    <p class="text-tech-muted text-xs mb-3">Proteína pura para recuperação.</p>
                    <div class="flex justify-between items-center mt-2">
                        <span class="text-xl font-bold text-tech-primary">R$ 189,90</span>
                        <button class="bg-tech-700 hover:bg-tech-primary hover:scale-110 text-white p-2 rounded-lg transition-all" onclick="addToCart()">
                            <i data-lucide="plus" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                <!-- Produto 2 -->
                <div class="bg-tech-900 rounded-xl p-4 border border-tech-700 card-premium reveal reveal-delay-100">
                    <div class="bg-gray-200 rounded-lg h-48 flex items-center justify-center mb-4 overflow-hidden group">
                        <img src="https://images.unsplash.com/photo-1526506118085-60ce8714f8c5?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" alt="Creatina" class="h-32 object-contain group-hover:scale-110 transition-transform duration-500">
                    </div>
                    <h4 class="font-bold text-lg mb-1">Creatina Power</h4>
                    <p class="text-tech-muted text-xs mb-3">Força explosiva.</p>
                    <div class="flex justify-between items-center mt-2">
                        <span class="text-xl font-bold text-tech-primary">R$ 89,90</span>
                        <button class="bg-tech-700 hover:bg-tech-primary hover:scale-110 text-white p-2 rounded-lg transition-all" onclick="addToCart()">
                            <i data-lucide="plus" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                 <!-- Produto 3 -->
                 <div class="bg-tech-900 rounded-xl p-4 border border-tech-700 card-premium reveal reveal-delay-200">
                    <div class="bg-white rounded-lg h-48 flex items-center justify-center mb-4 overflow-hidden group">
                        <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" alt="Tênis" class="h-32 object-contain group-hover:scale-110 transition-transform duration-500">
                    </div>
                    <h4 class="font-bold text-lg mb-1">Tech Runner X</h4>
                    <p class="text-tech-muted text-xs mb-3">Tênis para alta performance.</p>
                    <div class="flex justify-between items-center mt-2">
                        <span class="text-xl font-bold text-tech-primary">R$ 499,90</span>
                        <button class="bg-tech-700 hover:bg-tech-primary hover:scale-110 text-white p-2 rounded-lg transition-all" onclick="addToCart()">
                            <i data-lucide="plus" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                 <!-- Produto 4 -->
                 <div class="bg-tech-900 rounded-xl p-4 border border-tech-700 card-premium reveal reveal-delay-300">
                    <div class="bg-gray-100 rounded-lg h-48 flex items-center justify-center mb-4 overflow-hidden group">
                        <img src="https://images.unsplash.com/photo-1591196720526-7f415354e601?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" alt="Shaker" class="h-32 object-contain group-hover:scale-110 transition-transform duration-500">
                    </div>
                    <h4 class="font-bold text-lg mb-1">Smart Shaker</h4>
                    <p class="text-tech-muted text-xs mb-3">Coqueteleira com compartimentos.</p>
                    <div class="flex justify-between items-center mt-2">
                        <span class="text-xl font-bold text-tech-primary">R$ 45,00</span>
                        <button class="bg-tech-700 hover:bg-tech-primary hover:scale-110 text-white p-2 rounded-lg transition-all" onclick="addToCart()">
                            <i data-lucide="plus" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="mt-12 text-center md:hidden">
                 <a href="#" class="inline-flex items-center gap-2 text-tech-primary hover:text-white transition-colors">
                    Ver todos os produtos <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Planos de Preço -->
    <section id="plans" class="py-24 bg-tech-900 relative">
        <!-- Elemento decorativo de fundo -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full max-w-5xl max-h-[600px] bg-tech-primary/5 blur-[120px] rounded-full pointer-events-none animate-pulse-slow"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center mb-16 reveal">
                <h2 class="text-4xl font-bold mb-4">Escolha sua Evolução</h2>
                <div class="w-24 h-1.5 bg-tech-primary mx-auto rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-center">
                <!-- Basic -->
                <div class="bg-tech-800/50 backdrop-blur rounded-2xl p-8 border border-tech-700 reveal card-premium hover:border-tech-primary/50">
                    <h3 class="text-2xl font-bold text-white mb-2">Start</h3>
                    <div class="flex items-baseline mb-6">
                        <span class="text-4xl font-bold">R$ 89</span>
                        <span class="text-tech-muted">/mês</span>
                    </div>
                    <ul class="space-y-4 mb-8 text-tech-muted text-sm">
                        <li class="flex items-center gap-3"><i data-lucide="check" class="w-5 h-5 text-green-500"></i> Acesso à musculação</li>
                        <li class="flex items-center gap-3"><i data-lucide="check" class="w-5 h-5 text-green-500"></i> App de treino básico</li>
                        <li class="flex items-center gap-3"><i data-lucide="check" class="w-5 h-5 text-green-500"></i> Sem fidelidade</li>
                        <li class="flex items-center gap-3 text-gray-600"><i data-lucide="x" class="w-5 h-5"></i> Aulas coletivas</li>
                    </ul>
                    <a href="#" class="block text-center w-full py-3 border border-tech-700 hover:border-tech-primary text-gray-300 hover:text-white rounded-lg font-bold transition-all hover:bg-tech-800">Começar Básico</a>
                </div>

                <!-- Pro (Destaque) -->
                <div class="bg-tech-800 rounded-2xl p-8 border-2 border-tech-primary relative transform md:-translate-y-6 shadow-[0_0_40px_rgba(234,88,12,0.15)] reveal reveal-delay-100 hover:shadow-[0_0_60px_rgba(234,88,12,0.3)] transition-all duration-500 z-10">
                    <div class="absolute -top-4 left-0 right-0 flex justify-center">
                        <span class="bg-gradient-to-r from-orange-600 to-tech-primary text-white text-xs font-bold px-4 py-1 rounded-full uppercase tracking-widest shadow-lg">Mais Escolhido</span>
                    </div>
                    <h3 class="text-3xl font-bold text-white mb-2">Pro Tech</h3>
                    <div class="flex items-baseline mb-6">
                        <span class="text-5xl font-bold text-tech-primary">R$ 149</span>
                        <span class="text-tech-muted">/mês</span>
                    </div>
                    <ul class="space-y-4 mb-8 text-white text-sm font-medium">
                        <li class="flex items-center gap-3"><div class="p-1 bg-tech-primary rounded-full"><i data-lucide="check" class="w-3 h-3 text-white"></i></div> Acesso total (Musculação + Aulas)</li>
                        <li class="flex items-center gap-3"><div class="p-1 bg-tech-primary rounded-full"><i data-lucide="check" class="w-3 h-3 text-white"></i></div> App Premium (IA Trainer)</li>
                        <li class="flex items-center gap-3"><div class="p-1 bg-tech-primary rounded-full"><i data-lucide="check" class="w-3 h-3 text-white"></i></div> Smartwatch integration</li>
                        <li class="flex items-center gap-3"><div class="p-1 bg-tech-primary rounded-full"><i data-lucide="check" class="w-3 h-3 text-white"></i></div> Levar 1 amigo por mês</li>
                        <li class="flex items-center gap-3"><div class="p-1 bg-tech-primary rounded-full"><i data-lucide="check" class="w-3 h-3 text-white"></i></div> Acesso Multi-unidades</li>
                    </ul>
                    <a href="#" class="block text-center w-full py-4 bg-gradient-to-r from-tech-primary to-orange-600 hover:to-orange-500 text-white rounded-lg font-bold transition-all shadow-lg hover:shadow-orange-500/50 transform hover:-translate-y-1">Quero Ser Pro</a>
                </div>

                <!-- Vip -->
                <div class="bg-tech-800/50 backdrop-blur rounded-2xl p-8 border border-tech-700 reveal reveal-delay-200 card-premium hover:border-tech-primary/50">
                    <h3 class="text-2xl font-bold text-white mb-2">VIP</h3>
                    <div class="flex items-baseline mb-6">
                        <span class="text-4xl font-bold">R$ 399</span>
                        <span class="text-tech-muted">/mês</span>
                    </div>
                    <ul class="space-y-4 mb-8 text-tech-muted text-sm">
                        <li class="flex items-center gap-3"><i data-lucide="check" class="w-5 h-5 text-green-500"></i> Tudo do plano Pro</li>
                        <li class="flex items-center gap-3"><i data-lucide="check" class="w-5 h-5 text-green-500"></i> Personal Trainer (2x/sem)</li>
                        <li class="flex items-center gap-3"><i data-lucide="check" class="w-5 h-5 text-green-500"></i> Nutricionista Online</li>
                        <li class="flex items-center gap-3"><i data-lucide="check" class="w-5 h-5 text-green-500"></i> Kit TechFit Exclusivo</li>
                    </ul>
                    <a href="#" class="block text-center w-full py-3 border border-tech-700 hover:border-tech-primary text-gray-300 hover:text-white rounded-lg font-bold transition-all hover:bg-tech-800">Ser VIP</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-black text-gray-400 py-16 border-t border-tech-700 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-tech-primary to-transparent opacity-50"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-12">
            <div>
                <div class="flex items-center gap-2 mb-6">
                    <i data-lucide="dumbbell" class="h-8 w-8 text-tech-primary"></i>
                    <span class="font-bold text-2xl text-white">TECH<span class="text-tech-primary">FIT</span></span>
                </div>
                <p class="text-sm leading-relaxed mb-6">
                    A primeira academia 100% digital e integrada do Brasil. Sua performance é nossa ciência.
                </p>
                <div class="flex gap-4">
                    <a href="#" class="text-gray-400 hover:text-tech-primary transition-colors"><i data-lucide="instagram" class="w-5 h-5"></i></a>
                    <a href="#" class="text-gray-400 hover:text-tech-primary transition-colors"><i data-lucide="facebook" class="w-5 h-5"></i></a>
                    <a href="#" class="text-gray-400 hover:text-tech-primary transition-colors"><i data-lucide="twitter" class="w-5 h-5"></i></a>
                </div>
            </div>
            
            <div>
                <h4 class="text-white font-bold mb-6 text-lg">Links Rápidos</h4>
                <ul class="space-y-3 text-sm">
                    <li><a href="#" class="hover:text-tech-primary transition-colors flex items-center gap-2"><i data-lucide="chevron-right" class="w-3 h-3"></i> Sobre Nós</a></li>
                    <li><a href="#" class="hover:text-tech-primary transition-colors flex items-center gap-2"><i data-lucide="chevron-right" class="w-3 h-3"></i> Modalidades</a></li>
                    <li><a href="#" class="hover:text-tech-primary transition-colors flex items-center gap-2"><i data-lucide="chevron-right" class="w-3 h-3"></i> Horários</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-white font-bold mb-6 text-lg">Suporte</h4>
                <ul class="space-y-3 text-sm">
                    <li><a href="#" class="hover:text-tech-primary transition-colors flex items-center gap-2"><i data-lucide="chevron-right" class="w-3 h-3"></i> FAQ</a></li>
                    <li><a href="#" class="hover:text-tech-primary transition-colors flex items-center gap-2"><i data-lucide="chevron-right" class="w-3 h-3"></i> Central de Ajuda</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-white font-bold mb-6 text-lg">Newsletter</h4>
                <p class="text-sm mb-4">Receba dicas de treino e promoções exclusivas.</p>
                <div class="flex flex-col gap-3">
                    <input type="email" placeholder="Seu e-mail principal" class="bg-tech-800 border border-tech-700 text-white px-4 py-3 rounded-lg focus:outline-none focus:border-tech-primary focus:ring-1 focus:ring-tech-primary transition-all w-full">
                    <button class="bg-tech-primary hover:bg-tech-primaryHover px-4 py-3 rounded-lg text-white font-bold transition-all btn-glow w-full">
                        Inscrever-se
                    </button>
                </div>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-16 pt-8 border-t border-tech-700/50 text-center text-sm flex flex-col md:flex-row justify-between items-center gap-4">
            <p>&copy; 2023 TechFit. Todos os direitos reservados.</p>
            <p class="text-gray-600 flex items-center gap-1">Feito com <i data-lucide="zap" class="w-3 h-3 text-yellow-500"></i> para alta performance</p>
        </div>
    </footer>

    <!-- Notificação Toast -->
    <div id="toast" class="fixed bottom-8 right-8 bg-tech-800 border-l-4 border-green-500 text-white px-6 py-4 rounded-r-lg shadow-2xl transform translate-y-32 transition-transform duration-500 flex items-center gap-4 z-50">
        <div class="bg-green-500/20 p-2 rounded-full">
            <i data-lucide="check" class="w-5 h-5 text-green-500"></i>
        </div>
        <div>
            <h4 class="font-bold text-sm">Sucesso!</h4>
            <p class="text-xs text-gray-400">Produto adicionado ao carrinho.</p>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Inicializar ícones Lucide
        lucide.createIcons();

        // Menu Mobile Toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                setTimeout(() => {
                    menu.style.opacity = '1';
                    menu.style.transform = 'translateY(0)';
                }, 10);
            } else {
                menu.classList.add('hidden');
            }
        }

        // Carrinho Toast Notification
        function addToCart() {
            const toast = document.getElementById('toast');
            toast.classList.remove('translate-y-32');
            
            setTimeout(() => {
                toast.classList.add('translate-y-32');
            }, 3000);
        }

        // Scroll Animations e Typing Effect
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.15
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.reveal').forEach((el) => {
            observer.observe(el);
        });

        // Navbar Transição ao Rolar
        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 20) {
                navbar.classList.add('bg-tech-900/90', 'backdrop-blur-md', 'shadow-xl', 'border-b', 'border-tech-700/50');
                navbar.classList.remove('bg-transparent');
            } else {
                navbar.classList.remove('bg-tech-900/90', 'backdrop-blur-md', 'shadow-xl', 'border-b', 'border-tech-700/50');
                navbar.classList.add('bg-transparent');
            }
        });
    </script>
</body>
</html>