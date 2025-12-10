<?php
session_start();
require_once __DIR__ . '/../Model/RecepcionistaDAO.php';

// --- VERIFICA SE JÁ ESTÁ LOGADO COMO RECEPCIONISTA ---
if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'recepcionista') {
    $dao = new RecepcionistaDAO();
    $recepcionista = $dao->buscarPorId($_SESSION['usuario_id']);
    
    if (!$recepcionista) {
        session_destroy();
        header('Location: index.php');
        exit;
    }
    
    // MOSTRAR DASHBOARD DO RECEPCIONISTA
    showDashboard($recepcionista);
    exit;
}

// --- LOGOUT ---
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// --- PROCESSAR LOGIN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    if (empty($email) || empty($senha)) {
        $erro = "Preencha todos os campos";
    } else {
        $dao = new RecepcionistaDAO();
        $recepcionista = $dao->validarLogin($email, $senha);
        
        if ($recepcionista) {
            // Verifica status
            if (isset($recepcionista['status']) && $recepcionista['status'] !== 'Ativo') {
                $msgErro = "Acesso negado. Sua conta está " . $recepcionista['status'] . ".";
                header("Location: recepcionista.php?login_erro=1&msg=" . urlencode($msgErro));
                exit;
            }
            
            // Salva dados temporários para verificação
            $_SESSION['recepcionista_pre_login_id'] = $recepcionista['id'];
            $_SESSION['recepcionista_pre_nome'] = $recepcionista['nome'];
            header('Location: recepcionista_verificacao.php');
            exit;
        } else {
            $erro = "Credenciais inválidas";
        }
    }
}

// --- MOSTRAR FORMULÁRIO DE LOGIN ---
showLoginForm($erro ?? null);
exit;

// ============================================
// FUNÇÕES AUXILIARES
// ============================================

function showDashboard($recepcionista) {
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Recepção - TechFit</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://unpkg.com/lucide@latest"></script>
        
        <style>
            body {
                font-family: 'Inter', sans-serif;
                background-color: #0B0F19;
                color: #f3f4f6;
                min-height: 100vh;
            }
            
            .glass {
                background: rgba(255, 255, 255, 0.05);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.1);
            }
            
            .stat-card {
                transition: all 0.3s ease;
            }
            
            .stat-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 25px rgba(251, 146, 60, 0.2);
            }
            
            .action-card {
                transition: all 0.3s ease;
                border: 1px solid rgba(251, 146, 60, 0.1);
            }
            
            .action-card:hover {
                border-color: rgba(251, 146, 60, 0.4);
                background: rgba(251, 146, 60, 0.05);
                transform: translateY(-3px);
            }
        </style>
        
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            amber: {
                                50: '#fffbeb',
                                100: '#fef3c7',
                                200: '#fde68a',
                                300: '#fcd34d',
                                400: '#fbbf24',
                                500: '#f59e0b',
                                600: '#d97706',
                                700: '#b45309',
                                800: '#92400e',
                                900: '#78350f',
                            },
                            gray: {
                                900: '#0B0F19',
                                800: '#151b2b',
                                700: '#374151',
                            }
                        }
                    }
                }
            }
        </script>
    </head>
    <body class="min-h-screen">
        <!-- Navbar -->
        <nav class="bg-gray-900/80 backdrop-blur-md border-b border-amber-700/30 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center gap-3">
                        <div class="bg-gradient-to-br from-amber-600 to-orange-600 p-2 rounded-lg">
                            <i data-lucide="user-circle" class="h-6 w-6 text-white"></i>
                        </div>
                        <div>
                            <span class="font-bold text-xl text-white">Recepção</span>
                            <span class="text-amber-400 font-medium ml-2"><?php echo htmlspecialchars($recepcionista['nome']); ?></span>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-amber-300 bg-amber-900/30 px-3 py-1 rounded-full flex items-center gap-2">
                            <i data-lucide="clock" class="w-3 h-3"></i>
                            Turno: <?php echo htmlspecialchars($recepcionista['turno'] ?? 'Diurno'); ?>
                        </span>
                        
                        <div class="hidden md:flex items-center gap-2 bg-gray-800/50 px-3 py-1 rounded-lg">
                            <i data-lucide="calendar" class="w-4 h-4 text-amber-400"></i>
                            <span class="text-sm text-gray-300"><?php echo date('d/m/Y'); ?></span>
                        </div>
                        
                        <a href="?logout=true" 
                           class="text-sm text-red-300 hover:text-white hover:bg-red-500/10 px-3 py-2 rounded-lg flex items-center gap-1 transition-colors">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            <span class="hidden sm:inline">Sair</span>
                        </a>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Dashboard Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold mb-2 text-white">Painel da Recepção</h1>
                <p class="text-gray-400">Bem-vindo(a) ao sistema de gerenciamento da academia TechFit</p>
            </div>
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="stat-card glass rounded-xl p-6 border border-amber-700/30">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-lg text-white">Check-ins Hoje</h3>
                        <div class="bg-amber-500/20 p-2 rounded-lg">
                            <i data-lucide="users" class="w-6 h-6 text-amber-500"></i>
                        </div>
                    </div>
                    <div class="text-4xl font-bold text-amber-400 mb-2">142</div>
                    <div class="flex items-center text-sm text-amber-300/70">
                        <i data-lucide="trending-up" class="w-4 h-4 mr-1"></i>
                        <span>+12% em relação a ontem</span>
                    </div>
                </div>
                
                <div class="stat-card glass rounded-xl p-6 border border-blue-700/30">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-lg text-white">Matrículas Pendentes</h3>
                        <div class="bg-blue-500/20 p-2 rounded-lg">
                            <i data-lucide="clipboard-list" class="w-6 h-6 text-blue-500"></i>
                        </div>
                    </div>
                    <div class="text-4xl font-bold text-blue-400 mb-2">8</div>
                    <div class="text-sm text-blue-300/70">Aguardando análise e confirmação</div>
                </div>
                
                <div class="stat-card glass rounded-xl p-6 border border-green-700/30">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-lg text-white">Agendamentos</h3>
                        <div class="bg-green-500/20 p-2 rounded-lg">
                            <i data-lucide="calendar" class="w-6 h-6 text-green-500"></i>
                        </div>
                    </div>
                    <div class="text-4xl font-bold text-green-400 mb-2">19</div>
                    <div class="text-sm text-green-300/70">Para hoje - <?php echo date('d/m'); ?></div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                    <i data-lucide="zap" class="w-5 h-5 text-amber-500"></i>
                    Ações Rápidas
                </h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="#" class="action-card glass rounded-lg p-5 text-center transition-all group">
                        <div class="bg-amber-500/10 p-3 rounded-full inline-flex mb-3 group-hover:bg-amber-500/20 transition-colors">
                            <i data-lucide="user-plus" class="w-6 h-6 text-amber-500"></i>
                        </div>
                        <span class="font-medium text-white">Nova Matrícula</span>
                        <p class="text-xs text-gray-400 mt-1">Cadastrar novo aluno</p>
                    </a>
                    
                    <a href="#" class="action-card glass rounded-lg p-5 text-center transition-all group">
                        <div class="bg-blue-500/10 p-3 rounded-full inline-flex mb-3 group-hover:bg-blue-500/20 transition-colors">
                            <i data-lucide="calendar-clock" class="w-6 h-6 text-blue-500"></i>
                        </div>
                        <span class="font-medium text-white">Agendar Aula</span>
                        <p class="text-xs text-gray-400 mt-1">Marcar horários</p>
                    </a>
                    
                    <a href="#" class="action-card glass rounded-lg p-5 text-center transition-all group">
                        <div class="bg-green-500/10 p-3 rounded-full inline-flex mb-3 group-hover:bg-green-500/20 transition-colors">
                            <i data-lucide="credit-card" class="w-6 h-6 text-green-500"></i>
                        </div>
                        <span class="font-medium text-white">Pagamentos</span>
                        <p class="text-xs text-gray-400 mt-1">Gerenciar mensalidades</p>
                    </a>
                    
                    <a href="#" class="action-card glass rounded-lg p-5 text-center transition-all group">
                        <div class="bg-purple-500/10 p-3 rounded-full inline-flex mb-3 group-hover:bg-purple-500/20 transition-colors">
                            <i data-lucide="file-text" class="w-6 h-6 text-purple-500"></i>
                        </div>
                        <span class="font-medium text-white">Relatórios</span>
                        <p class="text-xs text-gray-400 mt-1">Gerar relatórios</p>
                    </a>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="glass rounded-xl p-6 border border-gray-700/50">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <i data-lucide="activity" class="w-5 h-5 text-amber-500"></i>
                        Atividade Recente
                    </h2>
                    <button class="text-sm text-amber-400 hover:text-amber-300 flex items-center gap-1">
                        <i data-lucide="refresh-ccw" class="w-4 h-4"></i>
                        Atualizar
                    </button>
                </div>
                
                <div class="space-y-3">
                    <div class="flex items-center gap-3 p-3 bg-gray-800/30 rounded-lg hover:bg-gray-800/50 transition-colors">
                        <div class="bg-green-500/20 p-2 rounded-full">
                            <i data-lucide="check-circle" class="w-4 h-4 text-green-500"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-white">Maria Silva fez check-in</p>
                            <p class="text-sm text-gray-400">Há 5 minutos • Plano: Pro</p>
                        </div>
                        <span class="text-xs text-green-400 bg-green-500/10 px-2 py-1 rounded">Check-in</span>
                    </div>
                    
                    <div class="flex items-center gap-3 p-3 bg-gray-800/30 rounded-lg hover:bg-gray-800/50 transition-colors">
                        <div class="bg-blue-500/20 p-2 rounded-full">
                            <i data-lucide="dollar-sign" class="w-4 h-4 text-blue-500"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-white">João Santos renovou plano VIP</p>
                            <p class="text-sm text-gray-400">Há 15 minutos • Valor: R$ 399,00</p>
                        </div>
                        <span class="text-xs text-blue-400 bg-blue-500/10 px-2 py-1 rounded">Pagamento</span>
                    </div>
                    
                    <div class="flex items-center gap-3 p-3 bg-gray-800/30 rounded-lg hover:bg-gray-800/50 transition-colors">
                        <div class="bg-amber-500/20 p-2 rounded-full">
                            <i data-lucide="user-plus" class="w-4 h-4 text-amber-500"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-white">Nova matrícula de Ana Oliveira</p>
                            <p class="text-sm text-gray-400">Há 30 minutos • Plano: Start</p>
                        </div>
                        <span class="text-xs text-amber-400 bg-amber-500/10 px-2 py-1 rounded">Matrícula</span>
                    </div>
                    
                    <div class="flex items-center gap-3 p-3 bg-gray-800/30 rounded-lg hover:bg-gray-800/50 transition-colors">
                        <div class="bg-purple-500/20 p-2 rounded-full">
                            <i data-lucide="calendar" class="w-4 h-4 text-purple-500"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-white">Pedro Costa agendou aula de Cross Tech</p>
                            <p class="text-sm text-gray-400">Há 1 hora • 14:00 - Sala 2</p>
                        </div>
                        <span class="text-xs text-purple-400 bg-purple-500/10 px-2 py-1 rounded">Agendamento</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="mt-12 pt-8 border-t border-gray-700/30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center text-sm text-gray-500">
                    <p>Sistema de Recepção TechFit • <?php echo date('Y'); ?> • Versão 1.0</p>
                    <p class="mt-1">Para suporte técnico, contate: suporte@techfit.com</p>
                </div>
            </div>
        </footer>
        
        <script>
            lucide.createIcons();
            
            // Atualizar hora atual
            function updateTime() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('pt-BR', { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
                const timeElement = document.querySelector('.time-display');
                if (timeElement) {
                    timeElement.textContent = timeString;
                }
            }
            
            // Inicializar e atualizar a cada minuto
            updateTime();
            setInterval(updateTime, 60000);
            
            // Toast notification
            function showToast(message, type = 'info') {
                const toast = document.createElement('div');
                const colors = {
                    success: 'bg-green-500/20 border-green-500 text-green-300',
                    error: 'bg-red-500/20 border-red-500 text-red-300',
                    info: 'bg-blue-500/20 border-blue-500 text-blue-300',
                    warning: 'bg-amber-500/20 border-amber-500 text-amber-300'
                };
                
                toast.className = `${colors[type]} fixed bottom-4 right-4 border px-4 py-3 rounded-lg shadow-xl flex items-center gap-2 z-50 animate-slideIn`;
                toast.innerHTML = `
                    <i data-lucide="${type === 'success' ? 'check-circle' : type === 'error' ? 'alert-circle' : 'info'}" class="w-5 h-5"></i>
                    <span>${message}</span>
                `;
                
                document.body.appendChild(toast);
                lucide.createIcons();
                
                setTimeout(() => {
                    toast.classList.add('animate-slideOut');
                    setTimeout(() => toast.remove(), 300);
                }, 4000);
            }
            
            // Adicionar animação CSS
            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOut {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
                .animate-slideIn { animation: slideIn 0.3s ease-out; }
                .animate-slideOut { animation: slideOut 0.3s ease-in forwards; }
            `;
            document.head.appendChild(style);
            
            // Exemplo de uso: showToast('Operação realizada com sucesso!', 'success');
        </script>
    </body>
    </html>
    <?php
}

function showLoginForm($erro = null) {
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login Recepcionista - TechFit</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://unpkg.com/lucide@latest"></script>
        
        <style>
            body {
                background: linear-gradient(135deg, #0B0F19 0%, #151b2b 100%);
                font-family: 'Inter', sans-serif;
            }
            
            .login-container {
                background: rgba(255, 255, 255, 0.05);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.1);
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            }
            
            .input-field {
                background: rgba(255, 255, 255, 0.05);
                border: 1px solid rgba(255, 255, 255, 0.1);
                transition: all 0.3s ease;
            }
            
            .input-field:focus {
                border-color: rgba(251, 146, 60, 0.5);
                box-shadow: 0 0 0 3px rgba(251, 146, 60, 0.1);
            }
        </style>
        
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            amber: {
                                600: '#d97706',
                                700: '#b45309',
                            }
                        }
                    }
                }
            }
        </script>
    </head>
    <body class="min-h-screen flex items-center justify-center p-4">
        <div class="login-container rounded-2xl p-8 w-full max-w-md">
            <div class="text-center mb-8">
                <div class="flex justify-center mb-6">
                    <div class="bg-gradient-to-br from-amber-600 to-orange-600 p-4 rounded-full">
                        <i data-lucide="user-circle" class="w-16 h-16 text-white"></i>
                    </div>
                </div>
                <h2 class="text-3xl font-bold text-white mb-2">Recepção TechFit</h2>
                <p class="text-gray-400">Acesso exclusivo para recepcionistas</p>
            </div>
            
            <?php if (isset($_GET['login_erro'])): ?>
                <div class="bg-red-500/20 border border-red-500/50 text-red-200 p-4 rounded-lg mb-6 text-sm animate-pulse">
                    <div class="flex items-center gap-2">
                        <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
                        <span><?php echo isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : 'Credenciais inválidas'; ?></span>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">E-mail Institucional</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <i data-lucide="mail" class="w-5 h-5 text-gray-500"></i>
                        </div>
                        <input type="email" name="email" placeholder="seu@techfit.com" 
                               class="input-field w-full pl-10 p-3 rounded-lg text-white focus:outline-none"
                               required autofocus>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Senha</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <i data-lucide="lock" class="w-5 h-5 text-gray-500"></i>
                        </div>
                        <input type="password" name="senha" placeholder="********" 
                               class="input-field w-full pl-10 p-3 rounded-lg text-white focus:outline-none"
                               required>
                        <button type="button" onclick="togglePassword()" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-white cursor-pointer">
                            <i id="eyeIcon" data-lucide="eye" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white font-bold py-3 rounded-lg transition-all duration-300 flex items-center justify-center gap-2 shadow-lg hover:shadow-amber-500/20">
                    <i data-lucide="log-in" class="w-5 h-5"></i>
                    Entrar na Recepção
                </button>
                
                <?php if ($erro): ?>
                    <div class="text-red-500 text-sm text-center p-3 bg-red-500/10 rounded-lg animate-pulse">
                        <?php echo htmlspecialchars($erro); ?>
                    </div>
                <?php endif; ?>
            </form>
            
            <div class="mt-8 pt-6 border-t border-gray-700/50">
                <p class="text-sm text-gray-500 text-center">
                    <a href="../index.php" class="text-amber-500 hover:text-amber-400 transition-colors inline-flex items-center gap-1">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        Voltar para página inicial
                    </a>
                </p>
            </div>
            
            <div class="mt-6 text-center">
                <p class="text-xs text-gray-600">
                    Sistema seguro • Verificação em 2 etapas • 
                    <span class="text-amber-500"><?php echo date('Y'); ?></span>
                </p>
            </div>
        </div>
        
        <script>
            lucide.createIcons();
            
            function togglePassword() {
                const passwordInput = document.querySelector('input[name="senha"]');
                const eyeIcon = document.getElementById('eyeIcon');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    eyeIcon.setAttribute('data-lucide', 'eye-off');
                } else {
                    passwordInput.type = 'password';
                    eyeIcon.setAttribute('data-lucide', 'eye');
                }
                lucide.createIcons();
            }
            
            // Limpar parâmetros da URL após 3 segundos
            if (window.location.search.includes('login_erro')) {
                setTimeout(() => {
                    window.history.replaceState({}, document.title, window.location.pathname);
                }, 3000);
            }
            
            // Auto-focus no primeiro campo
            document.querySelector('input[name="email"]')?.focus();
        </script>
    </body>
    </html>
    <?php
}
?>