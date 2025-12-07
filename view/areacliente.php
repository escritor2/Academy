<?php
session_start();

// --- IMPORTAÇÕES ---
require_once __DIR__ . '/../Model/AlunoDAO.php';
require_once __DIR__ . '/../Model/AdminDAO.php';
require_once __DIR__ . '/../Controller/AlunoController.php'; 

$cadastroMessage = null;
$cadastroColor = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- LÓGICA DE LOGIN ---
    if (isset($_POST['form_type']) && $_POST['form_type'] === 'login') {
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        
        // 1. Aluno
        $alunoDao = new AlunoDAO();
        $aluno = $alunoDao->buscarPorEmail($email);
        if ($aluno && password_verify($senha, $aluno['senha'])) {
            // --- NOVO BLOQUEIO DE STATUS ---
            if (isset($aluno['status']) && $aluno['status'] !== 'Ativo') {
                $msgErro = "Acesso negado. Sua conta está " . $aluno['status'] . ".";
                header("Location: " . basename($_SERVER['PHP_SELF']) . "?login_erro=1&msg=" . urlencode($msgErro));
                exit;
            }
            // -------------------------------

            $_SESSION['usuario_id'] = $aluno['id'];
            $_SESSION['usuario_nome'] = $aluno['nome'];
            $_SESSION['usuario_plano'] = $aluno['plano'];
            header('Location: paginacliente.php'); 
            exit;
        }

        // 2. Admin
        $adminDao = new AdminDAO();
        $admin = $adminDao->buscarPorEmail($email);
        if ($admin && password_verify($senha, $admin['senha'])) {
            $_SESSION['admin_pre_login_id'] = $admin['id'];
            $_SESSION['admin_pre_nome'] = $admin['nome'];
            header('Location: admin_verificacao.php'); 
            exit;
        }

        header("Location: areacliente.php?login_erro=1&msg=" . urlencode("Credenciais incorretas"));
        exit;
    }

    // --- LÓGICA DE CADASTRO (COM VALIDAÇÃO FORTE) ---
    else {
        try {
            $controller = new AlunoController();
            
            // Recebe e LIMPA os dados
            // trim() remove espaços do começo/fim, mas mantem "Nome Sobrenome"
            $nome = trim($_POST['nome']); 
            $cpf = $_POST['cpf'];
            $telefone = $_POST['telefone'];
            $senha = $_POST['senha'];
            
            // --- VALIDAÇÃO DE SEGURANÇA (PHP - O Muro Final) ---
            
            // 1. CPF Incompleto? (Tem que ter 14 chars: 111.222.333-44)
            if (strlen($cpf) < 14) {
                throw new Exception("CPF incompleto. Digite os 11 números.");
            }

            // 2. Telefone Incompleto? (Mínimo 14: (11) 9999-9999)
            if (strlen($telefone) < 14) {
                throw new Exception("Telefone inválido. Digite DDD + Número.");
            }

            // 3. Senha Fraca? (Mínimo 8)
            if (strlen($senha) < 8) {
                throw new Exception("A senha deve ter pelo menos 8 caracteres.");
            }

            // 4. Nome Vazio?
            if (empty($nome)) {
                throw new Exception("O nome não pode ser vazio.");
            }

            // Se passou tudo, pega o resto e salva
            $data_nascimento = $_POST['data_nascimento'];
            $email = $_POST['email'];
            $genero = $_POST['genero'];
            $objetivo = $_POST['goal'] ?? 'Não informado'; 
            $plano = $_POST['plan'] ?? 'Start';

            $controller->cadastrar($nome, $data_nascimento, $email, $telefone, $cpf, $genero, $senha, $objetivo, $plano);

            header("Location: areacliente.php?cadastro=sucesso");
            exit;

        } catch (Exception $e) {
            $msg = urlencode($e->getMessage());
            header("Location: areacliente.php?cadastro=erro&msg=$msg");
            exit;
        }
    }
}

// Mensagens visuais
if (isset($_GET['cadastro'])) {
    $type = $_GET['cadastro'];
    $cadastroMessage = ($type == 'sucesso') 
        ? 'Cadastro realizado com sucesso! Faça login abaixo.' 
        : ($_GET['msg'] ?? 'Erro desconhecido.');
    $cadastroColor = ($type == 'sucesso') ? 'bg-green-500' : 'bg-red-500';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icons/halter.png">
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
    <?php if (!empty($cadastroMessage)): ?>
        <div class="fixed top-4 left-1/2 -translate-x-1/2 p-3 rounded-lg <?= $cadastroColor ?> text-white text-sm z-50 shadow-md">
            <?= htmlspecialchars($cadastroMessage) ?>
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

            <form id="enrollmentForm" method="POST" action="" class="space-y-8">
                <div class="space-y-4 animate-slide-up" style="animation-delay: 0.1s;">
                    <div class="flex items-center gap-2 text-tech-primary mb-2">
                        <i data-lucide="user" class="w-5 h-5"></i>
                        <h3 class="font-bold uppercase tracking-wider text-sm">Dados Pessoais</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Nome Completo</label>
                            <input type="text" id="name" name="nome" required class="w-full p-3 rounded-lg tech-input" placeholder="Seu nome">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Data de Nascimento</label>
                            <input type="date" id="birthdate" name="data_nascimento" required class="w-full p-3 rounded-lg tech-input">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">E-mail</label>
                            <input type="email" id="email" name="email" required class="w-full p-3 rounded-lg tech-input" placeholder="seu@email.com">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Telefone / WhatsApp</label>
                            <input type="tel" id="phone" name="telefone" required class="w-full p-3 rounded-lg tech-input" placeholder="(00) 00000-0000">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">CPF</label>
                            <input type="text" id="cpf" name="cpf" required class="w-full p-3 rounded-lg tech-input" placeholder="000.000.000-00">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Gênero</label>
                            <select id="gender" name="genero" required class="w-full p-3 rounded-lg tech-input appearance-none">
                                <option value="" disabled selected>Selecione</option>
                                <option value="masculino">Masculino</option>
                                <option value="feminino">Feminino</option>
                                <option value="outro">Outro</option>
                                <option value="prefiro_nao_dizer">Prefiro não dizer</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Senha</label>
                        
                        <div class="relative">
                            <input type="password" name="senha" id="senhaInput" required 
                                class="w-full bg-tech-900 border border-tech-700 rounded-lg pl-4 pr-12 py-3 text-white focus:outline-none focus:border-tech-primary focus:ring-1 focus:ring-tech-primary transition-all placeholder-gray-600"
                                placeholder="********">
                            
                            <button type="button" onclick="togglePassword()" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-white focus:outline-none cursor-pointer p-1 z-10">
                                <i id="eyeOpen" data-lucide="eye" class="w-5 h-5"></i>
                                <i id="eyeClosed" data-lucide="eye-off" class="w-5 h-5 hidden"></i>
                            </button>
                        </div>

                        <p id="msgSenha" class="text-xs mt-1 hidden"></p>
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
                            <input type="radio" name="plan" value="Pro" class="plan-radio sr-only">
                            <div class="plan-card p-4 rounded-xl border border-tech-700 bg-tech-800 h-full transition-all group-hover:border-gray-500">
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
                <p class="text-tech-muted">Já tem uma conta? <a href="#" onclick="toggleLoginModal(event)" class="text-white font-bold hover:text-tech-primary transition-colors">Fazer Login</a></p>
            </div>
        </div>
    </div>

    <!-- Modal de Login -->
    <div id="loginModal" class="fixed inset-0 z-50 hidden" aria-labelledby="login-modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-70 backdrop-blur-sm" onclick="toggleLoginModal()"></div>

        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-tech-900 border border-tech-700 rounded-2xl shadow-2xl">
                <div class="flex items-center justify-between px-5 py-4 border-b border-tech-700 bg-tech-800/50 rounded-t-2xl">
                    <h3 id="login-modal-title" class="text-lg font-bold text-white">Entrar</h3>
                    <button type="button" class="text-tech-muted hover:text-white" onclick="toggleLoginModal()">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="px-6 py-6">
                    <form action="" method="POST" class="space-y-4">
    <input type="hidden" name="form_type" value="login">

    <div>
        <label for="login-email" class="block text-sm text-tech-muted">E-mail</label>
        <div class="mt-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <i data-lucide="mail" class="w-4 h-4 text-tech-muted"></i>
            </div>
            <input type="email" name="email" id="login-email" required class="block w-full rounded-md border-0 bg-tech-800 py-2.5 pl-10 text-white ring-1 ring-inset ring-tech-700 placeholder:text-gray-500 focus:ring-2 focus:ring-tech-primary sm:text-sm" placeholder="seu@email.com">
        </div>
    </div>

    <div>
        <label for="login-password" class="block text-sm text-tech-muted">Senha</label>
        <div class="mt-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <i data-lucide="lock" class="w-4 h-4 text-tech-muted"></i>
            </div>
            <input type="password" name="senha" id="login-password" required class="block w-full rounded-md border-0 bg-tech-800 py-2.5 pl-10 text-white ring-1 ring-inset ring-tech-700 placeholder:text-gray-500 focus:ring-2 focus:ring-tech-primary sm:text-sm" placeholder="********">
        </div>
    </div>

    <div class="flex justify-end">
        <a href="recuperar_senha.php" class="text-xs text-tech-primary hover:text-orange-400 transition-colors">Esqueceu a senha?</a>
    </div>

    <div>
        <button type="submit" class="w-full inline-flex justify-center rounded-md bg-tech-primary px-4 py-2 text-white font-semibold hover:bg-tech-primaryHover">Entrar</button>
    </div>
</form>

                    <!-- 'Cadastre-se' link removed from modal per request -->
                </div>
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

            // (eye toggle removed - using new togglePassword() function + lucide icons)

            // Permitir desmarcar radio buttons de plano
            const planRadios = document.querySelectorAll('input[name="plan"]');
            let lastChecked = null;

            planRadios.forEach(radio => {
                radio.addEventListener('click', function(e) {
                    if (this.checked && lastChecked === this.value) {
                        // Se clicou no mesmo que estava selecionado, desmarcar
                        this.checked = false;
                        lastChecked = null;
                    } else {
                        // Caso contrário, marcar normalmente
                        lastChecked = this.value;
                    }
                });
            });
        });

        // Toggle Login Modal
        function toggleLoginModal(e) {
            if (e && e.preventDefault) {
                e.preventDefault();
            }
            const modal = document.getElementById('loginModal');
            if (!modal) return;
            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } else {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('login_erro')) {
                const loginModal = document.getElementById('loginModal');
                if (loginModal) {
                    loginModal.classList.remove('hidden');
                }

                const msg = urlParams.get('msg');
                if (msg) {
                    const formLogin = document.querySelector('#loginModal form');
                    if (formLogin) {
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'bg-red-500/20 border border-red-500 text-red-200 p-3 rounded mb-4 text-sm text-center';
                        errorDiv.textContent = msg;
                        formLogin.insertBefore(errorDiv, formLogin.firstChild);
                    }
                }

                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
    </script>
<script>
// --- FUNÇÃO DO OLHO (GLOBAL) ---
function togglePassword() {
    const senhaInput = document.getElementById('senhaInput');
    const eyeOpen = document.getElementById('eyeOpen');
    const eyeClosed = document.getElementById('eyeClosed');
    if (!senhaInput) return;

    if (senhaInput.type === 'password') {
        senhaInput.type = 'text';
        eyeOpen.classList.add('hidden');
        eyeClosed.classList.remove('hidden');
    } else {
        senhaInput.type = 'password';
        eyeOpen.classList.remove('hidden');
        eyeClosed.classList.add('hidden');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    
    // ============================================================
    // 1. SISTEMA DE LIMPEZA (O FAXINEIRO)
    // ============================================================
    function agendarDesaparecimento(elemento, tempo = 4000) {
        if (!elemento) return;
        
        setTimeout(() => {
            // Adiciona classe de transição para sumir suavemente
            elemento.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            elemento.style.opacity = '0';
            elemento.style.transform = 'translateX(100%)'; // Joga pra direita
            
            // Remove do HTML depois da animação
            setTimeout(() => {
                if(elemento.parentNode) elemento.parentNode.removeChild(elemento);
            }, 500);
        }, tempo);
    }

    // A. Limpar TOASTS criados via JS (Função Helper)
    function exibirToast(mensagem, tipo = 'erro') {
        const toast = document.createElement('div');
        let cores = tipo === 'sucesso' 
            ? 'bg-tech-800 border-l-4 border-green-500 text-white' 
            : 'bg-tech-800 border-l-4 border-red-500 text-white';
        
        let icone = tipo === 'sucesso' 
            ? '<i data-lucide="check-circle" class="w-6 h-6 text-green-500"></i>' 
            : '<i data-lucide="alert-circle" class="w-6 h-6 text-red-500"></i>';

        toast.className = `${cores} fixed top-5 right-5 z-50 px-6 py-4 rounded-lg shadow-2xl flex items-center gap-4 min-w-[300px] transform transition-all duration-500 translate-x-full`;
        toast.innerHTML = `
            <div class="${tipo==='sucesso'?'bg-green-500/20':'bg-red-500/20'} p-2 rounded-full">${icone}</div>
            <div>
                <h4 class="font-bold text-sm">${tipo==='sucesso'?'Sucesso':'Atenção'}</h4>
                <p class="text-xs text-gray-400">${mensagem}</p>
            </div>
        `;
        
        document.body.appendChild(toast);
        if (typeof lucide !== 'undefined') lucide.createIcons();
        
        // Entrada
        requestAnimationFrame(() => toast.classList.remove('translate-x-full'));
        
        // Agenda a Saída usando o Faxineiro
        agendarDesaparecimento(toast);
    }

    // B. Limpar MENSAGENS DO PHP (Login/Cadastro) que já estão na tela
    // Procura divs que contenham classes de cor de fundo (bg-red, bg-green)
    const mensagensPHP = document.querySelectorAll('div[class*="bg-red-500"], div[class*="bg-green-500"]');
    
    mensagensPHP.forEach(msg => {
        // Verifica se é uma mensagem de texto (para não apagar botões ou inputs)
        if (msg.tagName === 'DIV' && !msg.querySelector('input')) {
            agendarDesaparecimento(msg, 5000); // Dá 5 segundos para ler as mensagens do servidor
        }
    });

    // Se houve mensagem de URL (?cadastro=sucesso), limpa a URL também
    if (window.location.search.includes('cadastro=') || window.location.search.includes('login_erro=')) {
        setTimeout(() => {
            window.history.replaceState({}, document.title, window.location.pathname);
        }, 5000);
    }

    // ============================================================
    // 2. VALIDAÇÕES E MÁSCARAS
    // ============================================================
    const cpfInput = document.querySelector('input[name="cpf"]');
    const telInput = document.querySelector('input[name="telefone"]');

    if (cpfInput) {
        cpfInput.setAttribute('maxlength', '14'); 
        cpfInput.addEventListener('input', function(e) {
            let v = e.target.value.replace(/\D/g, "");
            if (v.length > 11) v = v.slice(0, 11);
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
            e.target.value = v;
        });
        cpfInput.addEventListener('blur', function() {
            if(this.value.length > 0 && this.value.length < 14) {
                this.classList.add('border-red-500', 'animate-pulse');
                exibirToast("CPF incompleto.");
            } else {
                this.classList.remove('border-red-500', 'animate-pulse');
            }
        });
    }

    if (telInput) {
        telInput.setAttribute('maxlength', '15');
        telInput.addEventListener('input', function(e) {
            let v = e.target.value.replace(/\D/g, "");
            if (v.length > 11) v = v.slice(0, 11);
            v = v.replace(/^(\d{2})(\d)/g, "($1) $2");
            v = v.replace(/(\d)(\d{4})$/, "$1-$2");
            e.target.value = v;
        });
        telInput.addEventListener('blur', function() {
            if(this.value.length > 0 && this.value.length < 14) {
                this.classList.add('border-red-500', 'animate-pulse');
                exibirToast("Telefone incompleto.");
            } else {
                this.classList.remove('border-red-500', 'animate-pulse');
            }
        });
    }

    const dataInput = document.querySelector('input[name="data_nascimento"]');
    if (dataInput) {
        const hoje = new Date();
        const maxDate = new Date(hoje.getFullYear() - 18, hoje.getMonth(), hoje.getDate()).toISOString().split('T')[0];
        dataInput.setAttribute("max", maxDate);
        dataInput.setAttribute("min", "1900-01-01");
    }

    // ============================================================
    // 3. BLOQUEIO DE ENVIO (FORMULÁRIO)
    // ============================================================
    const formCadastro = document.querySelector('form[action=""]'); 
    const senhaInput = document.getElementById('senhaInput');
    const msgSenha = document.getElementById('msgSenha');
    const emailInput = document.querySelector('input[name="email"]');

    if (formCadastro) {
        if (senhaInput && msgSenha) {
            senhaInput.addEventListener('input', function() {
                const senha = this.value;
                const forte = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/.test(senha);
                
                if (senha.length === 0) {
                    msgSenha.classList.add('hidden');
                    senhaInput.classList.remove('border-red-500', 'border-green-500');
                    return;
                }
                msgSenha.classList.remove('hidden');
                if (!forte) {
                    msgSenha.textContent = "Fraca: Mínimo 8 chars, letras, números e símbolo.";
                    msgSenha.className = 'text-xs mt-1 text-red-400';
                    senhaInput.classList.add('border-red-500');
                } else {
                    msgSenha.textContent = "Senha Forte! ✅";
                    msgSenha.className = 'text-xs mt-1 text-green-500 font-bold';
                    senhaInput.classList.remove('border-red-500');
                    senhaInput.classList.add('border-green-500');
                }
            });
        }

        formCadastro.addEventListener('submit', function(e) {
            // Ignora se for login
            if (document.querySelector('input[name="form_type"][value="login"]')) return;

            let temErro = false;

            // Validações Finais
            if (cpfInput && cpfInput.value.length !== 14) {
                exibirToast("O CPF deve ter 11 números.");
                cpfInput.classList.add('border-red-500', 'animate-pulse');
                temErro = true;
            }

            if (telInput && telInput.value.length < 14) {
                exibirToast("Telefone inválido.");
                telInput.classList.add('border-red-500', 'animate-pulse');
                temErro = true;
            }

            if (senhaInput) {
                const forte = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/.test(senhaInput.value);
                if (!forte) {
                    exibirToast("Senha fraca! Reforce a segurança.");
                    senhaInput.focus();
                    temErro = true;
                }
            }

            if (emailInput && (!emailInput.value.includes('@') || !emailInput.value.includes('.'))) {
                exibirToast("E-mail inválido.");
                emailInput.focus();
                temErro = true;
            }

            if (temErro) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });
    }
});
</script>
</body>
</html>