<!DOCTYPE html>
<html lang="pt-br" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icons/halter.png">
    <title>TechFit - Nossa História & Tecnologia</title>

    <!-- Importando Tailwind CSS (Mesma versão do seu index) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Configurando a Paleta de Cores EXATA do seu projeto -->
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
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
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
        /* Estilos Globais idênticos ao index.php */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #111827;
            color: white;
            overflow-x: hidden;
        }

        /* Hero Background Específico desta página */
        .story-bg {
            background-image: linear-gradient(to bottom, rgba(17, 24, 39, 0.8), rgba(17, 24, 39, 1)), url('https://images.unsplash.com/photo-1571902943202-507ec2618e8f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        /* Efeitos de Glow */
        .glow-text {
            text-shadow: 0 0 20px rgba(234, 88, 12, 0.5);
        }

        .video-container {
            position: relative;
            overflow: hidden;
            border-radius: 1rem;
            box-shadow: 0 0 30px rgba(234, 88, 12, 0.2);
            transition: all 0.3s ease;
        }
        
        .video-container:hover {
            box-shadow: 0 0 50px rgba(234, 88, 12, 0.4);
            transform: scale(1.01);
        }

        /* Timeline Connector */
        .timeline-line {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, transparent, #ea580c, transparent);
        }

        /* Card Premium Style (reutilizado) */
        .card-story {
            background: rgba(31, 41, 55, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(55, 65, 81, 0.5);
            transition: all 0.4s ease;
        }
        .card-story:hover {
            border-color: #ea580c;
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="antialiased selection:bg-tech-primary selection:text-white">

    <!-- Navbar Simplificada (Mantendo consistência visual) -->
    <nav class="fixed w-full z-50 bg-tech-900/90 backdrop-blur-md border-b border-tech-700/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <a href="index.php" class="flex items-center gap-2 group cursor-pointer hover:opacity-80 transition-opacity">
                    <i data-lucide="arrow-left" class="h-6 w-6 text-tech-muted group-hover:text-white transition-colors"></i>
                    <span class="font-bold text-2xl tracking-tighter text-white">TECH<span class="text-tech-primary">FIT</span></span>
                </a>
                
                <div class="hidden md:block">
                    <span class="text-tech-muted text-sm font-medium">Sobre Nós & Tecnologia</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Header Section -->
    <section class="story-bg pt-40 pb-24 relative">
        <div class="max-w-4xl mx-auto px-4 text-center relative z-10">
            <span class="inline-block py-1 px-3 rounded-full bg-tech-primary/10 border border-tech-primary/30 text-tech-primary text-sm font-bold tracking-wider mb-6 animate-pulse">
                NOSSA ORIGEM
            </span>
            <h1 class="text-5xl md:text-6xl font-extrabold mb-6 leading-tight">
                Onde o Suor Encontra <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-tech-primary to-orange-400 glow-text">o Algoritmo</span>
            </h1>
            <p class="text-xl text-gray-300 leading-relaxed max-w-2xl mx-auto">
                Não somos apenas uma academia. Somos um laboratório de performance humana impulsionado por dados. Descubra como transformamos bytes em bíceps.
            </p>
        </div>
        
        <!-- Decoração de fundo -->
        <div class="absolute bottom-0 left-0 w-full h-24 bg-gradient-to-t from-tech-900 to-transparent"></div>
    </section>

    <!-- Seção do Vídeo Institucional -->
    <section class="py-16 bg-tech-900 relative">
        <div class="max-w-5xl mx-auto px-4">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold mb-2">A Experiência TechFit</h2>
                <p class="text-tech-muted">Veja como a tecnologia se integra ao seu treino diário.</p>
            </div>

            <!-- Container do Vídeo -->
            <!-- A classe 'video-container' aplica os cantos arredondados e o efeito de sombra/glow laranja -->
            <div class="video-container aspect-video bg-tech-800 border border-tech-700 overflow-hidden">
                <!-- A propriedade onended="this.load()" faz o vídeo recarregar (e mostrar o poster) ao terminar. -->
                <video controls class="w-full h-full object-cover bg-black" poster="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" onended="this.load()">
                    <source src="Video/saiba mais.mp4" type="video/mp4">
                    Seu navegador não suporta a tag de vídeo.
                </video>
            </div>
        </div>
    </section>

    <!-- Nossa História (Timeline) -->
    <section class="py-24 bg-tech-900 relative overflow-hidden">
        <!-- Linha de fundo sutil -->
        <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(#374151 1px, transparent 1px); background-size: 30px 30px;"></div>

        <div class="max-w-4xl mx-auto px-4 relative z-10">
            <h2 class="text-4xl font-bold mb-16 text-center">A Evolução</h2>

            <div class="relative space-y-12">
                <!-- Linha central -->
                <div class="absolute left-4 md:left-1/2 top-0 bottom-0 w-0.5 bg-gradient-to-b from-tech-primary via-tech-700 to-transparent md:-translate-x-1/2"></div>

                <!-- Item 1 -->
                <div class="relative flex flex-col md:flex-row items-center justify-between group">
                    <div class="md:w-5/12 mb-4 md:mb-0 order-2 md:order-1">
                        <div class="card-story p-6 rounded-xl text-right md:text-right text-left">
                            <span class="text-tech-primary font-bold text-lg">2018</span>
                            <h3 class="text-xl font-bold text-white mb-2">O Início na Garagem</h3>
                            <p class="text-tech-muted text-sm">Tudo começou com um sensor Arduino e um banco de supino. Queríamos provar que dados poderiam prevenir lesões.</p>
                        </div>
                    </div>
                    <div class="absolute left-4 md:left-1/2 w-8 h-8 bg-tech-900 border-4 border-tech-primary rounded-full transform -translate-x-1/2 flex items-center justify-center z-10 shadow-[0_0_15px_rgba(234,88,12,0.5)] order-1 md:order-2">
                        <div class="w-2 h-2 bg-white rounded-full"></div>
                    </div>
                    <div class="md:w-5/12 order-3"></div>
                </div>

                <!-- Item 2 -->
                <div class="relative flex flex-col md:flex-row items-center justify-between group">
                    <div class="md:w-5/12 order-3 md:order-1"></div>
                    <div class="absolute left-4 md:left-1/2 w-8 h-8 bg-tech-900 border-4 border-tech-700 group-hover:border-tech-primary transition-colors rounded-full transform -translate-x-1/2 z-10 order-1 md:order-2"></div>
                    <div class="md:w-5/12 mb-4 md:mb-0 order-2 md:order-3">
                        <div class="card-story p-6 rounded-xl">
                            <span class="text-tech-primary font-bold text-lg">2021</span>
                            <h3 class="text-xl font-bold text-white mb-2">A Integração IA</h3>
                            <p class="text-tech-muted text-sm">Lançamos nosso primeiro algoritmo proprietário. O sistema passou a ajustar as cargas das máquinas automaticamente baseada na fadiga do aluno.</p>
                        </div>
                    </div>
                </div>

                <!-- Item 3 -->
                <div class="relative flex flex-col md:flex-row items-center justify-between group">
                    <div class="md:w-5/12 mb-4 md:mb-0 order-2 md:order-1">
                        <div class="card-story p-6 rounded-xl text-right md:text-right text-left">
                            <span class="text-tech-primary font-bold text-lg">Hoje</span>
                            <h3 class="text-xl font-bold text-white mb-2">O Ecossistema TechFit</h3>
                            <p class="text-tech-muted text-sm">Não é só um site, é o seu painel de controle biológico. De relógios inteligentes a máquinas conectadas, tudo converge aqui.</p>
                        </div>
                    </div>
                    <div class="absolute left-4 md:left-1/2 w-8 h-8 bg-tech-900 border-4 border-tech-primary rounded-full transform -translate-x-1/2 flex items-center justify-center z-10 shadow-[0_0_15px_rgba(234,88,12,0.5)] order-1 md:order-2">
                        <i data-lucide="zap" class="w-4 h-4 text-tech-primary fill-current"></i>
                    </div>
                    <div class="md:w-5/12 order-3"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sobre o Site (Tech Stack) -->
    <section class="py-24 bg-tech-800 border-t border-tech-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-16 items-center">
                
                <!-- Texto explicativo -->
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <i data-lucide="code" class="text-tech-primary w-6 h-6"></i>
                        <span class="text-sm font-bold uppercase tracking-widest text-tech-muted">Por trás do código</span>
                    </div>
                    <h2 class="text-4xl font-bold mb-6">Um Site Vivo,<br> Assim Como Você.</h2>
                    <p class="text-gray-300 mb-6 leading-relaxed">
                        Este site não é apenas uma vitrine. Ele foi construído com a mesma filosofia de performance que aplicamos nos treinos.
                    </p>
                    
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <div class="bg-tech-900 p-2 rounded-lg border border-tech-700">
                                <i data-lucide="shield-check" class="w-5 h-5 text-green-500"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-white">Segurança de Dados</h4>
                                <p class="text-sm text-tech-muted">Seus dados biométricos são criptografados de ponta a ponta.</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="bg-tech-900 p-2 rounded-lg border border-tech-700">
                                <i data-lucide="smartphone" class="w-5 h-5 text-blue-500"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-white">Design Adaptativo</h4>
                                <p class="text-sm text-tech-muted">Interface fluida que funciona do smartwatch ao desktop 4K.</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="bg-tech-900 p-2 rounded-lg border border-tech-700">
                                <i data-lucide="zap" class="w-5 h-5 text-yellow-500"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-white">Performance Extrema</h4>
                                <p class="text-sm text-tech-muted">Carregamento instantâneo para não atrasar seu treino.</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Visual Tech (Mockup Abstrato) -->
                <div class="relative">
                    <div class="absolute inset-0 bg-tech-primary/20 blur-3xl rounded-full"></div>
                    <div class="relative bg-tech-900 border border-tech-700 rounded-2xl p-8 shadow-2xl rotate-3 hover:rotate-0 transition-transform duration-500">
                        <!-- Header do "Code Editor" -->
                        <div class="flex items-center gap-2 mb-4 border-b border-tech-700 pb-4">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="ml-auto text-xs text-tech-muted font-mono">system_core.js</span>
                        </div>
                        <!-- Conteúdo Falso de Código -->
                        <div class="font-mono text-sm space-y-2">
                            <p class="text-purple-400">class <span class="text-yellow-400">TechFitUser</span> {</p>
                            <p class="pl-4 text-gray-400">constructor(id, goals) {</p>
                            <p class="pl-8 text-blue-400">this<span class="text-white">.id = id;</span></p>
                            <p class="pl-8 text-blue-400">this<span class="text-white">.power = </span><span class="text-green-400">"UNLIMITED"</span><span class="text-white">;</span></p>
                            <p class="pl-4 text-gray-400">}</p>
                            <p class="pl-4 text-gray-400">evolve() {</p>
                            <p class="pl-8 text-gray-500">// Otimizando performance...</p>
                            <p class="pl-8 text-tech-primary">return "Meta Atingida";</p>
                            <p class="pl-4 text-gray-400">}</p>
                            <p class="text-purple-400">}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Final -->
    <section class="py-20 bg-gradient-to-r from-tech-primary to-orange-700 text-center">
        <div class="max-w-3xl mx-auto px-4">
            <h2 class="text-3xl font-bold text-white mb-6">Pronto para fazer parte da história?</h2>
            <p class="text-white/90 mb-8 text-lg">Junte-se a milhares de membros que já transformaram suas vidas com nossa tecnologia.</p>
            <a href="index.php" class="inline-flex items-center gap-2 bg-white text-tech-primary px-8 py-4 rounded-full font-bold hover:bg-gray-100 transition-colors shadow-lg">
                <i data-lucide="arrow-left"></i> Voltar para Início
            </a>
        </div>
    </section>

    <!-- Footer Igual ao Index -->
    <footer class="bg-black text-gray-400 py-12 border-t border-tech-700">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <div class="flex justify-center items-center gap-2 mb-4">
                <i data-lucide="dumbbell" class="h-6 w-6 text-tech-primary"></i>
                <span class="font-bold text-xl text-white">TECH<span class="text-tech-primary">FIT</span></span>
            </div>
            <p class="text-sm">&copy; 2023 TechFit. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>