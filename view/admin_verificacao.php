<?php
session_start();
require_once __DIR__ . '/../Model/AdminDAO.php';

// Segurança: Se não tiver passado pelo login inicial, chuta fora
if (!isset($_SESSION['admin_pre_login_id'])) {
    header('Location: index.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cpf = $_POST['cpf_secreto'];
    $pin = $_POST['pin_secreto'];
    $palavra = $_POST['palavra_chave'];
    
    $idAdmin = $_SESSION['admin_pre_login_id'];
    $dao = new AdminDAO();
    
    if ($dao->verificarCredenciaisCompletas($idAdmin, $cpf, $pin, $palavra)) {
        // SUCESSO
        $_SESSION['admin_logado'] = true;
        $_SESSION['admin_nome'] = $_SESSION['admin_pre_nome'];
        unset($_SESSION['admin_pre_login_id']);
        unset($_SESSION['admin_pre_nome']);
        
        header('Location: adm.php');
        exit;
    } else {
        // FALHA: EXPULSA
        unset($_SESSION['admin_pre_login_id']);
        unset($_SESSION['admin_pre_nome']);
        $msg = urlencode("ALERTA: Credenciais de segurança incorretas. Acesso negado.");
        header("Location: index.php?login_erro=1&msg=$msg");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icons/halter.png">
    <title>Protocolo de Segurança Nível 5</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* SOLUÇÃO DEFINITIVA: Transforma o input em text e usa CSS para esconder o texto */
        .password-wrapper {
            position: relative;
        }
        
        .password-input {
            /* Força a remoção completa dos estilos nativos */
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            font-family: monospace;
            letter-spacing: 4px;
        }
        
        /* Remove qualquer ícone nativo do navegador */
        input::-webkit-credentials-auto-fill-button,
        input::-webkit-caps-lock-indicator,
        input::-webkit-credentials-auto-fill-button:hover,
        input::-webkit-credentials-auto-fill-button:active {
            visibility: hidden;
            display: none !important;
            pointer-events: none;
            position: absolute;
            right: -9999px;
        }
        
        /* Para Edge/IE */
        input::-ms-reveal,
        input::-ms-clear {
            display: none !important;
        }
        
        /* Para Firefox */
        input[type="password"] {
            font-family: monospace;
        }
        
        /* Garante que nosso botão fique acima de tudo */
        .toggle-password-btn {
            z-index: 10;
            cursor: pointer;
        }
        
        /* Container para o ícone com barra */
        .eye-icon-container {
            position: relative;
            display: inline-block;
            width: 20px;
            height: 20px;
        }
        
        /* Barra que corta o olho */
        .eye-strike {
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: currentColor;
            transform: translateY(-50%) rotate(45deg);
            transform-origin: center;
            transition: all 0.3s ease;
            opacity: 0;
        }
        
        /* Quando a senha está visível (olho com barra) */
        .eye-strike.visible {
            opacity: 1;
            transform: translateY(-50%) rotate(45deg) scaleX(1);
        }
        
        /* Quando a senha está oculta (olho sem barra) */
        .eye-strike.hidden {
            opacity: 0;
            transform: translateY(-50%) rotate(45deg) scaleX(0);
        }
        
        /* Animação suave para o ícone */
        .eye-icon {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Efeito de hover */
        .toggle-password-btn:hover .eye-icon {
            transform: scale(1.1);
        }
        
        /* Efeito de clique */
        .toggle-password-btn:active .eye-icon {
            transform: scale(0.95);
        }
    </style>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { tech: { 900: '#0f172a', 800: '#1e293b', 700: '#334155', red: '#ef4444' } } } }
        }
    </script>
</head>
<body class="bg-tech-900 text-white min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-lg bg-tech-800 rounded-2xl shadow-[0_0_50px_rgba(239,68,68,0.2)] border border-red-900/50 overflow-hidden relative">
        
        <div class="bg-red-900/30 border-b border-red-500/30 p-4 text-center">
            <div class="flex items-center justify-center gap-2 text-red-500 font-bold tracking-widest uppercase text-xs">
                <i data-lucide="siren" class="w-4 h-4 animate-pulse"></i>
                Área de Segurança Máxima
                <i data-lucide="siren" class="w-4 h-4 animate-pulse"></i>
            </div>
        </div>

        <div class="p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-white mb-2">Confirmação de Identidade</h1>
                <p class="text-gray-400 text-sm">Gerente: <span class="text-red-400 font-mono"><?= htmlspecialchars($_SESSION['admin_pre_nome']) ?></span></p>
                <p class="text-gray-500 text-xs mt-1">Insira as credenciais mestras para liberar o painel.</p>
            </div>

            <form method="POST" class="space-y-5" autocomplete="off">
                
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 tracking-wider">1. CPF do Titular</label>
                    <div class="relative">
                        <input type="text" name="cpf_secreto" id="cpfInput" required 
                            autocomplete="off"
                            class="w-full bg-tech-900 border border-tech-700 rounded-lg pl-10 pr-4 py-3 focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all font-mono"
                            placeholder="000.000.000-00">
                        <i data-lucide="user-check" class="absolute left-3 top-3.5 w-5 h-5 text-gray-600"></i>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1 tracking-wider">2. PIN (6 Dígitos)</label>
                        <div class="relative password-wrapper">
                            <input type="password" name="pin_secreto" id="pinInput" required maxlength="6"
                                autocomplete="new-password"
                                class="w-full bg-tech-900 border border-tech-700 rounded-lg pl-10 pr-12 py-3 focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all password-input font-mono tracking-widest"
                                placeholder="******">
                            <i data-lucide="hash" class="absolute left-3 top-3.5 w-5 h-5 text-gray-600"></i>
                            <button type="button" id="togglePin" class="absolute right-3 top-3.5 w-5 h-5 text-white hover:text-gray-300 transition-colors toggle-password-btn flex items-center justify-center">
                                <div class="eye-icon-container">
                                    <i data-lucide="eye" class="w-5 h-5 eye-icon" id="pinEyeIcon"></i>
                                    <div class="eye-strike hidden" id="pinEyeStrike"></div>
                                </div>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1 tracking-wider">3. Palavra-Chave</label>
                        <div class="relative password-wrapper">
                            <input type="password" name="palavra_chave" id="keyInput" required 
                                autocomplete="new-password"
                                class="w-full bg-tech-900 border border-tech-700 rounded-lg pl-10 pr-12 py-3 focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all password-input font-mono"
                                placeholder="Acesso Mestre">
                            <i data-lucide="key" class="absolute left-3 top-3.5 w-5 h-5 text-gray-600"></i>
                            <button type="button" id="toggleKey" class="absolute right-3 top-3.5 w-5 h-5 text-white hover:text-gray-300 transition-colors toggle-password-btn flex items-center justify-center">
                                <div class="eye-icon-container">
                                    <i data-lucide="eye" class="w-5 h-5 eye-icon" id="keyEyeIcon"></i>
                                    <div class="eye-strike hidden" id="keyEyeStrike"></div>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-gradient-to-r from-red-700 to-red-600 hover:from-red-600 hover:to-red-500 text-white font-bold py-4 rounded-lg transition-all shadow-lg shadow-red-900/30 flex items-center justify-center gap-2 mt-4 group">
                    <i data-lucide="unlock" class="w-5 h-5 group-hover:rotate-12 transition-transform"></i>
                    DESBLOQUEAR SISTEMA
                </button>
            </form>
        </div>
        
        <div class="bg-tech-900/50 p-4 text-center border-t border-tech-700">
            <a href="index.php?sair=true" class="text-xs text-gray-500 hover:text-white transition-colors">Abortar Operação</a>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();

        // 1. TOGGLE VISIBILIDADE PIN - COM BARRA CORTANDO
        const pinInput = document.getElementById('pinInput');
        const togglePinBtn = document.getElementById('togglePin');
        const pinEyeIcon = document.getElementById('pinEyeIcon');
        const pinEyeStrike = document.getElementById('pinEyeStrike');
        
        togglePinBtn.addEventListener('click', function() {
            const type = pinInput.getAttribute('type') === 'password' ? 'text' : 'password';
            pinInput.setAttribute('type', type);
            
            // Anima a barra cortando
            if (type === 'text') {
                // Senha visível - mostra barra cortando
                pinEyeIcon.setAttribute('data-lucide', 'eye');
                pinEyeStrike.classList.remove('hidden');
                void pinEyeStrike.offsetWidth; // Força reflow
                pinEyeStrike.classList.add('visible');
            } else {
                // Senha oculta - esconde barra cortando
                pinEyeIcon.setAttribute('data-lucide', 'eye');
                pinEyeStrike.classList.remove('visible');
                void pinEyeStrike.offsetWidth; // Força reflow
                pinEyeStrike.classList.add('hidden');
            }
            
            // Atualiza ícone
            lucide.createIcons();
            
            // Foca no input após toggle
            setTimeout(() => pinInput.focus(), 100);
        });

        // 2. TOGGLE VISIBILIDADE PALAVRA-CHAVE - COM BARRA CORTANDO
        const keyInput = document.getElementById('keyInput');
        const toggleKeyBtn = document.getElementById('toggleKey');
        const keyEyeIcon = document.getElementById('keyEyeIcon');
        const keyEyeStrike = document.getElementById('keyEyeStrike');
        
        toggleKeyBtn.addEventListener('click', function() {
            const type = keyInput.getAttribute('type') === 'password' ? 'text' : 'password';
            keyInput.setAttribute('type', type);
            
            // Anima a barra cortando
            if (type === 'text') {
                // Senha visível - mostra barra cortando
                keyEyeIcon.setAttribute('data-lucide', 'eye');
                keyEyeStrike.classList.remove('hidden');
                void keyEyeStrike.offsetWidth; // Força reflow
                keyEyeStrike.classList.add('visible');
            } else {
                // Senha oculta - esconde barra cortando
                keyEyeIcon.setAttribute('data-lucide', 'eye');
                keyEyeStrike.classList.remove('visible');
                void keyEyeStrike.offsetWidth; // Força reflow
                keyEyeStrike.classList.add('hidden');
            }
            
            // Atualiza ícone
            lucide.createIcons();
            
            // Foca no input após toggle
            setTimeout(() => keyInput.focus(), 100);
        });

        // 3. EFEITO DE CLIQUE NOS BOTÕES
        [togglePinBtn, toggleKeyBtn].forEach(btn => {
            btn.addEventListener('mousedown', function() {
                const icon = this.querySelector('.eye-icon');
                icon.style.transform = 'scale(0.95)';
            });
            
            btn.addEventListener('mouseup', function() {
                const icon = this.querySelector('.eye-icon');
                icon.style.transform = 'scale(1)';
            });
            
            btn.addEventListener('mouseleave', function() {
                const icon = this.querySelector('.eye-icon');
                icon.style.transform = 'scale(1)';
            });
        });

        // 4. TOAST (Notificações)
        function exibirToast(mensagem) {
            const toast = document.createElement('div');
            toast.className = `fixed top-5 right-5 z-50 px-6 py-4 rounded-lg shadow-2xl flex items-center gap-4 min-w-[300px] transform transition-all duration-500 translate-x-full bg-tech-800 border-l-4 border-red-500 text-white`;
            
            toast.innerHTML = `
                <div class="bg-red-500/20 p-2 rounded-full"><i data-lucide="alert-circle" class="w-6 h-6 text-red-500"></i></div>
                <div><h4 class="font-bold text-sm">Acesso Negado</h4><p class="text-xs text-gray-400">${mensagem}</p></div>
            `;
            
            document.body.appendChild(toast);
            lucide.createIcons();
            requestAnimationFrame(() => toast.classList.remove('translate-x-full'));
            setTimeout(() => { toast.classList.add('translate-x-full', 'opacity-0'); setTimeout(() => toast.remove(), 500); }, 4000);
        }

        // 5. MÁSCARA CPF
        const cpfInput = document.getElementById('cpfInput');
        cpfInput.setAttribute('maxlength', '14');
        cpfInput.addEventListener('input', function(e) {
            let v = e.target.value.replace(/\D/g, "");
            if (v.length > 11) v = v.slice(0, 11);
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
            e.target.value = v;
        });

        // 6. MÁSCARA PIN (SÓ NÚMEROS)
        pinInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, "");
        });

        // 7. VALIDAÇÃO DE ENVIO
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            let temErro = false;

            // Valida CPF
            if (cpfInput.value.length !== 14) {
                exibirToast("O CPF deve estar completo.");
                cpfInput.classList.add('border-red-500', 'animate-pulse');
                temErro = true;
            } else {
                cpfInput.classList.remove('border-red-500', 'animate-pulse');
            }

            // Valida PIN (Deve ter 6 dígitos)
            if (pinInput.value.length !== 6) {
                exibirToast("O PIN deve conter 6 dígitos numéricos.");
                pinInput.classList.add('border-red-500', 'animate-pulse');
                temErro = true;
            } else {
                pinInput.classList.remove('border-red-500', 'animate-pulse');
            }

            // Valida Palavra-Chave (Vazia?)
            if (keyInput.value.trim() === '') {
                exibirToast("Digite a Palavra-Chave Mestra.");
                keyInput.classList.add('border-red-500', 'animate-pulse');
                temErro = true;
            } else {
                keyInput.classList.remove('border-red-500', 'animate-pulse');
            }

            if (temErro) {
                e.preventDefault();
            }
        });
        
        // 8. SOLUÇÃO ALTERNATIVA: Impede que o navegador adicione seus próprios ícones
        setTimeout(() => {
            lucide.createIcons();
            
            // Adiciona um ouvinte para quando o input ganha foco
            [pinInput, keyInput].forEach(input => {
                input.addEventListener('focus', () => {
                    lucide.createIcons();
                });
            });
        }, 100);
    });
    </script>
</body>
</html>