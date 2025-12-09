<?php
session_start();
require_once __DIR__ . '/../Model/AlunoDAO.php';

$msg = '';
$tipoMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $cpf = $_POST['cpf'];
    $data_nascimento = $_POST['data_nascimento'];
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    // 1. Validação de Senha Forte (Regex)
    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/', $nova_senha)) {
        $msg = "A senha deve ter 8 caracteres, letras, números e símbolos (@#$%).";
        $tipoMsg = "erro";
    }
    // 2. Validação de Igualdade
    elseif ($nova_senha !== $confirmar_senha) {
        $msg = "As senhas não coincidem!";
        $tipoMsg = "erro";
    } 
    else {
        $dao = new AlunoDAO();
        
        // Busca o aluno e a senha atual
        $aluno = $dao->validarRecuperacao($email, $cpf, $data_nascimento);

        if ($aluno) {
            // --- NOVA VERIFICAÇÃO DE SEGURANÇA ---
            // Verifica se a nova senha é igual à antiga
            if (password_verify($nova_senha, $aluno['senha'])) {
                $msg = "A nova senha não pode ser igual à senha atual!";
                $tipoMsg = "erro";
            } 
            else {
                // Tudo certo: Atualiza
                $dao->atualizarSenha($aluno['id'], $nova_senha);
                $msg = "Senha redefinida com sucesso! Redirecionando...";
                $tipoMsg = "sucesso";
                header("refresh:3;url=index.php");
            }
        } else {
            $msg = "Dados incorretos. Verifique E-mail, CPF e Data.";
            $tipoMsg = "erro";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icons/halter.png">
    <title>Recuperar Senha - TechFit</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { tech: { 900: '#111827', 800: '#1f2937', 700: '#374151', primary: '#ea580c' } } } }
        }
    </script>
    <style>
        /* Calendário Branco */
        ::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer; opacity: 0.7; transition: 0.2s;
        }
        ::-webkit-calendar-picker-indicator:hover { opacity: 1; }

        /* Remove olho nativo */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear { display: none; }
        input[type="password"]::-webkit-password-toggle-visibility { display: none !important; }
    </style>
</head>
<body class="bg-tech-900 text-white min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-3xl bg-tech-800 rounded-2xl shadow-2xl border border-tech-700 p-8 md:p-10 relative overflow-hidden">
        
        <div class="absolute top-0 right-0 w-64 h-64 bg-tech-primary/5 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-blue-500/5 rounded-full blur-3xl -ml-20 -mb-20 pointer-events-none"></div>

        <div class="text-center mb-10 relative z-10 border-b border-tech-700 pb-6">
            <div class="flex items-center justify-center gap-3 mb-2">
                <div class="w-10 h-10 bg-tech-primary/20 rounded-lg flex items-center justify-center text-tech-primary">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                </div>
                <h1 class="text-2xl font-bold tracking-tight">Redefinição de Senha</h1>
            </div>
            <p class="text-gray-400 text-sm">Preencha os dados de segurança para criar uma nova credencial.</p>
        </div>

        <?php if ($msg): ?>
            <div class="mensagem-php p-4 rounded-lg mb-8 text-sm text-center font-bold animate-bounce <?php echo $tipoMsg == 'sucesso' ? 'bg-green-500/20 text-green-400 border border-green-500/50' : 'bg-red-500/20 text-red-400 border border-red-500/50'; ?>">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6 relative z-10" autocomplete="off">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div class="md:col-span-2 group">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 tracking-wider group-focus-within:text-tech-primary transition-colors">E-mail Cadastrado</label>
                    <div class="relative">
                        <input type="email" name="email" id="emailInput" required 
                            class="w-full bg-tech-900 border border-tech-700 rounded-lg pl-10 pr-4 py-3 focus:border-tech-primary focus:ring-1 focus:ring-tech-primary outline-none transition-all placeholder-gray-600 text-white" 
                            placeholder="seu@email.com">
                        <i data-lucide="mail" class="absolute left-3 top-3.5 w-5 h-5 text-gray-500 group-focus-within:text-tech-primary transition-colors duration-300"></i>
                    </div>
                </div>

                <div class="group">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 tracking-wider group-focus-within:text-tech-primary transition-colors">CPF</label>
                    <div class="relative">
                        <input type="text" name="cpf" required id="cpfInput"
                            class="w-full bg-tech-900 border border-tech-700 rounded-lg pl-10 pr-3 py-3 focus:border-tech-primary focus:ring-1 focus:ring-tech-primary outline-none transition-all placeholder-gray-600" 
                            placeholder="000.000.000-00">
                        <i data-lucide="user" class="absolute left-3 top-3.5 w-5 h-5 text-gray-500 group-focus-within:text-tech-primary transition-colors duration-300"></i>
                    </div>
                </div>

                <div class="group">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 tracking-wider group-focus-within:text-tech-primary transition-colors">Nascimento</label>
                    <div class="relative">
                        <input type="date" name="data_nascimento" id="dataInput" required 
                            class="w-full bg-tech-900 border border-tech-700 rounded-lg pl-3 pr-3 py-3 focus:border-tech-primary focus:ring-1 focus:ring-tech-primary outline-none transition-all text-center text-gray-300 group-focus-within:text-white">
                    </div>
                </div>

                <div class="md:col-span-2 border-t border-tech-700 my-2 pt-4">
                    <p class="text-xs text-center text-gray-500 font-semibold uppercase tracking-widest">Defina a Nova Senha</p>
                </div>

                <div class="group relative">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 tracking-wider group-focus-within:text-tech-primary transition-colors">Nova Senha</label>
                    <div class="relative">
                        <input type="password" name="nova_senha" id="novaSenha" required 
                            class="w-full bg-tech-900 border border-tech-700 rounded-lg pl-10 pr-12 py-3 focus:border-tech-primary focus:ring-1 focus:ring-tech-primary focus:shadow-[0_0_15px_rgba(234,88,12,0.1)] outline-none transition-all placeholder-gray-600" 
                            placeholder="Mínimo 8 caracteres">
                        
                        <i data-lucide="lock" class="absolute left-3 top-3.5 w-5 h-5 text-gray-500 group-focus-within:text-tech-primary group-focus-within:scale-110 transition-all duration-300"></i>
                        
                        <button type="button" onclick="togglePass('novaSenha', 'eye1', 'eyeOff1')" class="absolute right-3 top-1/2 -translate-y-1/2 text-white hover:text-tech-primary transition-colors focus:outline-none p-1 cursor-pointer z-10">
                            <i id="eye1" data-lucide="eye" class="w-5 h-5"></i>
                            <i id="eyeOff1" data-lucide="eye-off" class="w-5 h-5 hidden"></i>
                        </button>
                    </div>
                    <p id="msgForca" class="text-xs mt-1 hidden"></p>
                </div>

                <div class="group relative">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 tracking-wider group-focus-within:text-tech-primary transition-colors">Confirmar Senha</label>
                    <div class="relative">
                        <input type="password" name="confirmar_senha" id="confirmarSenha" required 
                            class="w-full bg-tech-900 border border-tech-700 rounded-lg pl-10 pr-12 py-3 focus:border-tech-primary focus:ring-1 focus:ring-tech-primary focus:shadow-[0_0_15px_rgba(234,88,12,0.1)] outline-none transition-all placeholder-gray-600" 
                            placeholder="Digite novamente">
                        
                        <i data-lucide="shield-check" class="absolute left-3 top-3.5 w-5 h-5 text-gray-500 group-focus-within:text-tech-primary group-focus-within:scale-110 transition-all duration-300"></i>

                        <button type="button" onclick="togglePass('confirmarSenha', 'eye2', 'eyeOff2')" class="absolute right-3 top-1/2 -translate-y-1/2 text-white hover:text-tech-primary transition-colors focus:outline-none p-1 cursor-pointer z-10">
                            <i id="eye2" data-lucide="eye" class="w-5 h-5"></i>
                            <i id="eyeOff2" data-lucide="eye-off" class="w-5 h-5 hidden"></i>
                        </button>
                    </div>
                    <p id="msgMatch" class="text-xs mt-2 hidden text-red-400 font-bold text-right absolute right-0 bottom-[-20px]">As senhas não coincidem!</p>
                </div>

            </div>

            <div class="pt-8">
                <button type="submit" class="w-full bg-gradient-to-r from-tech-primary to-orange-600 hover:to-orange-500 text-white font-bold py-4 rounded-xl transition-all shadow-lg shadow-orange-900/20 transform hover:scale-[1.01] flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    ATUALIZAR CREDENCIAIS
                </button>
            </div>
        </form>

        <div class="mt-8 text-center">
            <a href="index.php" class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-white transition-colors py-2 px-4 rounded-lg hover:bg-white/5">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Voltar para Início
            </a>
        </div>
    </div>

    <script>
        // Função do Olho (Global)
        function togglePass(inputId, eyeId, eyeOffId) {
            const input = document.getElementById(inputId);
            const eye = document.getElementById(eyeId);
            const eyeOff = document.getElementById(eyeOffId);

            if (input.type === 'password') {
                input.type = 'text';
                eye.classList.add('hidden');
                eyeOff.classList.remove('hidden');
            } else {
                input.type = 'password';
                eye.classList.remove('hidden');
                eyeOff.classList.add('hidden');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();

            // 1. TOAST
            function exibirToast(mensagem, tipo = 'erro') {
                const toast = document.createElement('div');
                let cores = tipo === 'sucesso' 
                    ? 'bg-tech-800 border-l-4 border-green-500 text-white' 
                    : 'bg-tech-800 border-l-4 border-red-500 text-white';
                
                toast.className = `${cores} fixed top-5 right-5 z-50 px-6 py-4 rounded-lg shadow-2xl flex items-center gap-4 min-w-[300px] transform transition-all duration-500 translate-x-full`;
                toast.innerHTML = `<div class="${tipo==='sucesso'?'bg-green-500/20':'bg-red-500/20'} p-2 rounded-full"><i data-lucide="${tipo==='sucesso'?'check-circle':'alert-circle'}" class="w-6 h-6 ${tipo==='sucesso'?'text-green-500':'text-red-500'}"></i></div><div><h4 class="font-bold text-sm">${tipo==='sucesso'?'Sucesso':'Atenção'}</h4><p class="text-xs text-gray-400">${mensagem}</p></div>`;
                
                document.body.appendChild(toast);
                lucide.createIcons();
                requestAnimationFrame(() => toast.classList.remove('translate-x-full'));
                setTimeout(() => { toast.classList.add('translate-x-full', 'opacity-0'); setTimeout(() => toast.remove(), 500); }, 4000);
            }

            // 2. FAXINEIRO DE MENSAGENS PHP
            const msgsPHP = document.querySelectorAll('.mensagem-php');
            if(msgsPHP.length > 0) {
                setTimeout(() => {
                    msgsPHP.forEach(el => {
                        el.style.transition = 'opacity 1s ease';
                        el.style.opacity = '0';
                        setTimeout(() => el.remove(), 1000);
                    });
                }, 4000);
            }

            // 3. MÁSCARA CPF
            const cpfInput = document.getElementById('cpfInput');
            cpfInput.setAttribute('maxlength', '14');
            cpfInput.addEventListener('input', e => {
                let v = e.target.value.replace(/\D/g, "");
                if (v.length > 11) v = v.slice(0, 11);
                v = v.replace(/(\d{3})(\d)/, "$1.$2");
                v = v.replace(/(\d{3})(\d)/, "$1.$2");
                v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
                e.target.value = v;
            });
            cpfInput.addEventListener('blur', function() {
                if(this.value.length > 0 && this.value.length < 14) {
                    this.classList.add('border-red-500');
                    exibirToast("CPF incompleto.");
                } else {
                    this.classList.remove('border-red-500');
                }
            });

            // 4. DATA (Validação no envio)
            const dataInput = document.getElementById('dataInput');
            const hoje = new Date();
            dataInput.setAttribute("max", new Date().toISOString().split('T')[0]);
            dataInput.setAttribute("min", "1900-01-01");

            // 5. SENHAS E ENVIO
            const form = document.querySelector('form');
            const novaSenha = document.getElementById('novaSenha');
            const confirmarSenha = document.getElementById('confirmarSenha');
            const msgMatch = document.getElementById('msgMatch');
            const msgForca = document.getElementById('msgForca');
            const emailInput = document.getElementById('emailInput');

            // Feedback Força da Senha (NOVO)
            novaSenha.addEventListener('input', function() {
                const s = this.value;
                if (s.length === 0) {
                    msgForca.classList.add('hidden');
                    return;
                }
                const forte = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/.test(s);
                
                msgForca.classList.remove('hidden');
                if(!forte) {
                    msgForca.textContent = "Fraca: Letras, números e símbolo (@#) e 8 chars.";
                    msgForca.className = "text-xs mt-1 text-red-400";
                    this.classList.add('border-red-500');
                } else {
                    msgForca.textContent = "Senha Forte! ✅";
                    msgForca.className = "text-xs mt-1 text-green-500 font-bold";
                    this.classList.remove('border-red-500');
                    this.classList.add('border-green-500');
                }
            });

            // Feedback Senhas Iguais
            confirmarSenha.addEventListener('input', function() {
                if (this.value !== novaSenha.value) {
                    msgMatch.classList.remove('hidden');
                    this.classList.add('border-red-500');
                } else {
                    msgMatch.classList.add('hidden');
                    this.classList.remove('border-red-500');
                }
            });

            form.addEventListener('submit', function(e) {
                let temErro = false;

                // CPF
                if (cpfInput.value.length !== 14) {
                    exibirToast("O CPF deve ter 11 números.");
                    cpfInput.classList.add('border-red-500');
                    temErro = true;
                }

                // Data (Validação no envio)
                if (dataInput.value) {
                    const d = new Date(dataInput.value);
                    if (d.getFullYear() < 1900) { exibirToast("Ano inválido."); temErro = true; }
                    else if (d > new Date()) { exibirToast("Data futura inválida."); temErro = true; }
                }

                // Senha Fraca (Regex)
                const s = novaSenha.value;
                const forte = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/.test(s);
                if (!forte) {
                    exibirToast("A senha nova é muito fraca!");
                    novaSenha.classList.add('border-red-500');
                    temErro = true;
                }

                // Senhas Diferentes
                if (s !== confirmarSenha.value) {
                    exibirToast("As senhas não coincidem!");
                    temErro = true;
                }

                // Email
                if (!emailInput.value.includes('@') || !emailInput.value.includes('.')) {
                    exibirToast("E-mail inválido.");
                    temErro = true;
                }

                if (temErro) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>