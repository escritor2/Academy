<?php
session_start();
// Ajuste o caminho se necessário
require_once __DIR__ . '/../Model/AlunoDAO.php';

$msg = '';
$tipoMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $cpf = $_POST['cpf'];
    $data_nascimento = $_POST['data_nascimento'];
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    // Validação PHP (Backup)
    if ($nova_senha !== $confirmar_senha) {
        $msg = "As senhas não coincidem!";
        $tipoMsg = "erro";
    } 
    else {
        $dao = new AlunoDAO();
        $aluno = $dao->validarRecuperacao($email, $cpf, $data_nascimento);

        if ($aluno) {
            $dao->atualizarSenha($aluno['id'], $nova_senha);
            $msg = "Senha redefinida com sucesso! Redirecionando...";
            $tipoMsg = "sucesso";
            header("refresh:3;url=index.php");
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
    <title>Recuperar Senha - TechFit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
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
    </style>
</head>
<body class="bg-tech-900 text-white min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-tech-800 rounded-2xl shadow-2xl border border-tech-700 p-8 relative overflow-hidden">
        
        <div class="absolute top-0 right-0 w-32 h-32 bg-tech-primary/10 rounded-full blur-3xl -mr-10 -mt-10"></div>

        <div class="text-center mb-8 relative z-10">
            <div class="w-16 h-16 bg-tech-primary/20 rounded-full flex items-center justify-center mx-auto mb-4 text-tech-primary animate-pulse">
                <i data-lucide="key-round" class="w-8 h-8"></i>
            </div>
            <h1 class="text-2xl font-bold">Redefinir Senha</h1>
            <p class="text-gray-400 text-sm mt-2">Confirme seus dados para criar uma nova senha.</p>
        </div>

        <?php if ($msg): ?>
            <div class="mensagem-php p-4 rounded-lg mb-6 text-sm text-center font-bold animate-bounce <?php echo $tipoMsg == 'sucesso' ? 'bg-green-500/20 text-green-400 border border-green-500/50' : 'bg-red-500/20 text-red-400 border border-red-500/50'; ?>">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5 relative z-10" autocomplete="off">
            
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1 tracking-wider">E-mail Cadastrado</label>
                <div class="relative">
                    <input type="email" name="email" id="emailInput" required 
                        class="w-full bg-tech-900 border border-tech-700 rounded-lg pl-10 pr-4 py-3 focus:border-tech-primary focus:ring-1 focus:ring-tech-primary outline-none transition-all placeholder-gray-600" 
                        placeholder="seu@email.com">
                    <i data-lucide="mail" class="absolute left-3 top-3.5 w-5 h-5 text-gray-500"></i>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 tracking-wider">CPF</label>
                    <div class="relative">
                        <input type="text" name="cpf" required id="cpfInput"
                            class="w-full bg-tech-900 border border-tech-700 rounded-lg pl-3 pr-3 py-3 focus:border-tech-primary focus:ring-1 focus:ring-tech-primary outline-none transition-all text-center placeholder-gray-600" 
                            placeholder="000.000.000-00">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 tracking-wider">Nascimento</label>
                    <div class="relative">
                        <input type="date" name="data_nascimento" id="dataInput" required 
                            class="w-full bg-tech-900 border border-tech-700 rounded-lg pl-3 pr-3 py-3 focus:border-tech-primary focus:ring-1 focus:ring-tech-primary outline-none transition-all text-center text-gray-300">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1 tracking-wider">Nova Senha</label>
                <div class="relative">
                    <input type="password" name="nova_senha" id="novaSenha" required 
                        class="w-full bg-tech-900 border border-tech-700 rounded-lg pl-10 pr-12 py-3 focus:border-tech-primary focus:ring-1 focus:ring-tech-primary outline-none transition-all placeholder-gray-600" 
                        placeholder="Mínimo 8 caracteres">
                    <i data-lucide="lock" class="absolute left-3 top-3.5 w-5 h-5 text-gray-500"></i>
                    
                    <button type="button" onclick="togglePass('novaSenha', 'eye1', 'eyeOff1')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white transition-colors focus:outline-none">
                        <i id="eye1" data-lucide="eye" class="w-5 h-5"></i>
                        <i id="eyeOff1" data-lucide="eye-off" class="w-5 h-5 hidden"></i>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1 tracking-wider">Confirmar Nova Senha</label>
                <div class="relative">
                    <input type="password" name="confirmar_senha" id="confirmarSenha" required 
                        class="w-full bg-tech-900 border border-tech-700 rounded-lg pl-10 pr-12 py-3 focus:border-tech-primary focus:ring-1 focus:ring-tech-primary outline-none transition-all placeholder-gray-600" 
                        placeholder="Digite novamente">
                    <i data-lucide="lock-check" class="absolute left-3 top-3.5 w-5 h-5 text-gray-500"></i>

                    <button type="button" onclick="togglePass('confirmarSenha', 'eye2', 'eyeOff2')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white transition-colors focus:outline-none">
                        <i id="eye2" data-lucide="eye" class="w-5 h-5"></i>
                        <i id="eyeOff2" data-lucide="eye-off" class="w-5 h-5 hidden"></i>
                    </button>
                </div>
                <p id="msgMatch" class="text-xs mt-1 hidden text-red-400 font-bold">As senhas não coincidem!</p>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-tech-primary to-orange-600 hover:to-orange-500 text-white font-bold py-3 rounded-lg transition-all shadow-lg shadow-orange-900/20 mt-4 transform hover:scale-[1.02]">
                SALVAR NOVA SENHA
            </button>
        </form>

        <div class="mt-6 text-center border-t border-tech-700 pt-4">
            <a href="index.php" class="text-xs text-gray-400 hover:text-white transition-colors flex items-center justify-center gap-1">
                <i data-lucide="arrow-left" class="w-3 h-3"></i> Voltar para Início
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();

            // 1. TOAST (O substituto do Alert)
            function exibirToast(mensagem) {
                const toast = document.createElement('div');
                toast.className = `fixed top-5 right-5 z-50 px-6 py-4 rounded-lg shadow-2xl flex items-center gap-4 min-w-[300px] transform transition-all duration-500 translate-x-full bg-tech-800 border-l-4 border-red-500 text-white`;
                toast.innerHTML = `<div class="bg-red-500/20 p-2 rounded-full"><i data-lucide="alert-circle" class="w-6 h-6 text-red-500"></i></div><div><h4 class="font-bold text-sm">Atenção</h4><p class="text-xs text-gray-400">${mensagem}</p></div>`;
                document.body.appendChild(toast);
                lucide.createIcons();
                requestAnimationFrame(() => toast.classList.remove('translate-x-full'));
                setTimeout(() => { toast.classList.add('translate-x-full', 'opacity-0'); setTimeout(() => toast.remove(), 500); }, 4000);
            }

            // 2. FAXINEIRO (Limpa msg do PHP)
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
            cpfInput.addEventListener('input', function(e) {
                let v = e.target.value.replace(/\D/g, "");
                if (v.length > 11) v = v.slice(0, 11);
                v = v.replace(/(\d{3})(\d)/, "$1.$2");
                v = v.replace(/(\d{3})(\d)/, "$1.$2");
                v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
                e.target.value = v;
            });
            // Valida ao sair
            cpfInput.addEventListener('blur', function() {
                if(this.value.length > 0 && this.value.length < 14) {
                    this.classList.add('border-red-500');
                    exibirToast("CPF incompleto.");
                } else {
                    this.classList.remove('border-red-500');
                }
            });

            // 4. DATA (Validação Visual)
            const dataInput = document.getElementById('dataInput');
            const hoje = new Date();
            const maxDate = hoje.toISOString().split('T')[0];
            dataInput.setAttribute("max", maxDate);
            dataInput.setAttribute("min", "1900-01-01");

            dataInput.addEventListener('change', function() {
                const dataSelecionada = new Date(this.value);
                const ano = dataSelecionada.getFullYear();
                
                if (ano < 1900) {
                    exibirToast("Ano muito antigo. Verifique a data.");
                    this.value = "";
                }
                if (dataSelecionada > new Date()) {
                    exibirToast("Data futura inválida.");
                    this.value = "";
                }
            });

            // 5. SENHAS E ENVIO (Bloqueio Total)
            const form = document.querySelector('form');
            const novaSenha = document.getElementById('novaSenha');
            const confirmarSenha = document.getElementById('confirmarSenha');
            const msgMatch = document.getElementById('msgMatch');
            const emailInput = document.getElementById('emailInput');

            // Feedback Visual Senhas
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

                // Senhas diferentes
                if (novaSenha.value !== confirmarSenha.value) {
                    exibirToast("As senhas não coincidem!");
                    temErro = true;
                }

                // Senha fraca
                if (novaSenha.value.length < 8) {
                    exibirToast("Senha muito curta (Mín. 8 caracteres).");
                    novaSenha.classList.add('border-red-500');
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

        // Alternar Senha (Olho)
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
    </script>
</body>
</html>