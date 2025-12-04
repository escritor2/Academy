<!DOCTYPE html>
<html lang="pt-br" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icons/halter.png">
    <title>TechFit - Área do Professor</title>
    
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
    <nav class="fixed w-full z-50 transition-all duration-300 bg-tech-900/90 backdrop-blur-md shadow-xl border-b border-tech-700/50" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <div class="flex items-center gap-2 group cursor-pointer">
                    <i data-lucide="dumbbell" class="h-8 w-8 text-tech-primary transition-transform group-hover:rotate-45 duration-500"></i>
                    <span class="font-bold text-2xl tracking-tighter text-white">TECH<span class="text-tech-primary">FIT</span></span>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-8">
                        <a href="index.html" class="relative group px-3 py-2 text-sm font-medium hover:text-white transition-colors">
                            Site Principal
                            <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-tech-primary transition-all group-hover:w-full"></span>
                        </a>
                        <a href="#" class="bg-tech-primary hover:bg-tech-primaryHover text-white px-6 py-2 rounded-full font-bold transition-all btn-glow ml-4">
                            Sair
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
                <a href="index.html" class="block px-3 py-3 rounded-md text-base font-medium hover:bg-tech-800 hover:text-tech-primary transition-colors">Site Principal</a>
                <a href="#" class="block px-3 py-3 rounded-md text-base font-medium hover:bg-tech-800 hover:text-tech-primary transition-colors">Sair</a>
            </div>
        </div>
    </nav>

    <!-- Main Content - Dashboard Professor -->
    <main class="pt-24 pb-16 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <header class="mb-12 reveal">
                <h1 class="text-4xl font-extrabold text-white tracking-tight sm:text-5xl">
                    <span class="text-tech-primary">Área</span> do Professor
                </h1>
                <p class="mt-3 text-xl text-tech-muted">Gerencie seus alunos, treinos e horários.</p>
            </header>

            <!-- Cards de Estatísticas -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                <div class="bg-tech-800 p-6 rounded-xl shadow-lg border border-tech-700 reveal reveal-delay-100">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-tech-muted">Alunos Ativos</p>
                        <i data-lucide="users" class="w-6 h-6 text-tech-primary"></i>
                    </div>
                    <p class="mt-1 text-3xl font-bold text-white">42</p>
                </div>
                <div class="bg-tech-800 p-6 rounded-xl shadow-lg border border-tech-700 reveal reveal-delay-200">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-tech-muted">Treinos Criados</p>
                        <i data-lucide="clipboard-list" class="w-6 h-6 text-tech-primary"></i>
                    </div>
                    <p class="mt-1 text-3xl font-bold text-white">187</p>
                </div>
                <div class="bg-tech-800 p-6 rounded-xl shadow-lg border border-tech-700 reveal reveal-delay-300">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-tech-muted">Próxima Aula</p>
                        <i data-lucide="calendar" class="w-6 h-6 text-tech-primary"></i>
                    </div>
                    <p class="mt-1 text-3xl font-bold text-white">14:00</p>
                </div>
            </div>

            <!-- Seção de Alunos Recentes -->
            <section class="bg-tech-800 p-8 rounded-xl shadow-lg border border-tech-700 reveal mb-12">
                <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-2">
                    <i data-lucide="user-check" class="w-6 h-6 text-tech-primary"></i> Meus Alunos
                </h2>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-tech-700">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-tech-muted uppercase tracking-wider">Nome</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-tech-muted uppercase tracking-wider">Plano</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-tech-muted uppercase tracking-wider">Último Treino</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-tech-muted uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-tech-700">
                            <tr class="hover:bg-tech-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">Ana Silva</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-400">VIP</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-tech-muted">Hoje, 10:00</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="#" class="text-tech-primary hover:text-tech-primaryHover">Ver Perfil</a>
                                </td>
                            </tr>
                            <tr class="hover:bg-tech-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">Bruno Costa</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-400">Pro</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-tech-muted">Ontem, 18:30</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="#" class="text-tech-primary hover:text-tech-primaryHover">Ver Perfil</a>
                                </td>
                            </tr>
                            <tr class="hover:bg-tech-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">Carla Mendes</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-400">Basic</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-tech-muted">2 dias atrás</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="#" class="text-tech-primary hover:text-tech-primaryHover">Ver Perfil</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Seção de Ações Rápidas -->
            <section class="reveal">
                <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-2">
                    <i data-lucide="zap" class="w-6 h-6 text-tech-primary"></i> Ações Rápidas
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <a href="#" class="bg-tech-800 p-6 rounded-xl shadow-lg border border-tech-700 hover:border-tech-primary transition-all group">
                        <i data-lucide="plus-circle" class="w-8 h-8 text-tech-primary mb-3 group-hover:scale-110 transition-transform"></i>
                        <h3 class="text-lg font-semibold text-white">Criar Novo Treino</h3>
                        <p class="text-sm text-tech-muted mt-1">Monte uma ficha personalizada.</p>
                    </a>
                    <a href="#" class="bg-tech-800 p-6 rounded-xl shadow-lg border border-tech-700 hover:border-tech-primary transition-all group">
                        <i data-lucide="message-square" class="w-8 h-8 text-tech-primary mb-3 group-hover:scale-110 transition-transform"></i>
                        <h3 class="text-lg font-semibold text-white">Enviar Mensagem</h3>
                        <p class="text-sm text-tech-muted mt-1">Comunique-se com seus alunos.</p>
                    </a>
                    <a href="#" class="bg-tech-800 p-6 rounded-xl shadow-lg border border-tech-700 hover:border-tech-primary transition-all group">
                        <i data-lucide="clock" class="w-8 h-8 text-tech-primary mb-3 group-hover:scale-110 transition-transform"></i>
                        <h3 class="text-lg font-semibold text-white">Ver Horários</h3>
                        <p class="text-sm text-tech-muted mt-1">Consulte sua agenda de aulas.</p>
                    </a>
                    <a href="#" class="bg-tech-800 p-6 rounded-xl shadow-lg border border-tech-700 hover:border-tech-primary transition-all group">
                        <i data-lucide="bar-chart-3" class="w-8 h-8 text-tech-primary mb-3 group-hover:scale-110 transition-transform"></i>
                        <h3 class="text-lg font-semibold text-white">Relatórios</h3>
                        <p class="text-sm text-tech-muted mt-1">Acompanhe a evolução dos alunos.</p>
                    </a>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer (Simplificado) -->
    <footer class="bg-black text-gray-400 py-8 border-t border-tech-700 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm">
            <p>&copy; 2023 TechFit. Área Restrita do Professor.</p>
        </div>
    </footer>

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

        // Scroll Animations
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.15
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                    // Não desobservar para que a animação possa ocorrer em cada carregamento de página
                    // observer.unobserve(entry.target); 
                }
            });
        }, observerOptions);

        document.querySelectorAll('.reveal').forEach((el) => {
            observer.observe(el);
        });
    </script>
</body>
</html>
