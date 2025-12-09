<?php
session_start();
require_once __DIR__ . '/../Model/ProfessorDAO.php';
require_once __DIR__ . '/../Model/RecepcionistaDAO.php';

$msg = '';
$tipoMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $tipo = $_POST['tipo'] ?? 'professor';
    
    if ($tipo === 'professor') {
        $dao = new ProfessorDAO();
        $usuario = $dao->validarLogin($email, $senha);
        
        if ($usuario) {
            $_SESSION['tipo'] = 'professor';
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            
            header('Location: professor.php');
            exit;
        } else {
            $msg = "Email ou senha incorretos!";
            $tipoMsg = 'erro';
        }
    } elseif ($tipo === 'recepcionista') {
        $dao = new RecepcionistaDAO();
        $usuario = $dao->validarLogin($email, $senha);
        
        if ($usuario) {
            $_SESSION['tipo'] = 'recepcionista';
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            
            header('Location: recepcionista.php');
            exit;
        } else {
            $msg = "Email ou senha incorretos!";
            $tipoMsg = 'erro';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - Equipe TechFit</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="bg-[#1e293b] w-full max-w-md rounded-2xl border border-white/10 shadow-2xl p-8">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl mb-4">
                <i data-lucide="dumbbell" class="w-8 h-8 text-white"></i>
            </div>
            <h2 class="text-2xl font-bold text-white">Login da Equipe</h2>
            <p class="text-gray-400 mt-2">Acesse sua conta</p>
        </div>
        
        <?php if ($msg): ?>
        <div class="mb-6 p-4 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 flex items-center gap-3">
            <i data-lucide="alert-circle" class="w-5 h-5"></i>
            <span><?= $msg ?></span>
        </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Tipo de Conta</label>
                <div class="flex gap-2">
                    <button type="button" id="btnProfessor" class="flex-1 py-3 rounded-lg bg-orange-600 text-white font-medium transition-all">
                        Professor
                    </button>
                    <button type="button" id="btnRecepcionista" class="flex-1 py-3 rounded-lg bg-[#0f172a] text-gray-400 font-medium hover:text-white transition-all">
                        Recepcionista
                    </button>
                </div>
                <input type="hidden" name="tipo" id="inputTipo" value="professor">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                <input type="email" name="email" required class="w-full bg-[#0f172a] border border-white/10 rounded-lg p-3 text-white focus:border-orange-500 outline-none">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Senha</label>
                <input type="password" name="senha" required class="w-full bg-[#0f172a] border border-white/10 rounded-lg p-3 text-white focus:border-orange-500 outline-none">
            </div>
            
            <button type="submit" class="w-full bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white font-bold py-3 rounded-lg transition-all shadow-lg">
                Entrar
            </button>
            
            <div class="text-center">
                <a href="../index.php" class="text-gray-400 hover:text-white text-sm transition-colors">
                    ← Voltar para o site
                </a>
            </div>
        </form>
    </div>
    
    <script>
        lucide.createIcons();
        
        const btnProfessor = document.getElementById('btnProfessor');
        const btnRecepcionista = document.getElementById('btnRecepcionista');
        const inputTipo = document.getElementById('inputTipo');
        
        btnProfessor.addEventListener('click', () => {
            inputTipo.value = 'professor';
            btnProfessor.classList.remove('bg-[#0f172a]', 'text-gray-400');
            btnProfessor.classList.add('bg-orange-600', 'text-white');
            btnRecepcionista.classList.remove('bg-orange-600', 'text-white');
            btnRecepcionista.classList.add('bg-[#0f172a]', 'text-gray-400');
        });
        
        btnRecepcionista.addEventListener('click', () => {
            inputTipo.value = 'recepcionista';
            btnRecepcionista.classList.remove('bg-[#0f172a]', 'text-gray-400');
            btnRecepcionista.classList.add('bg-orange-600', 'text-white');
            btnProfessor.classList.remove('bg-orange-600', 'text-white');
            btnProfessor.classList.add('bg-[#0f172a]', 'text-gray-400');
        });
    </script>
</body>
</html>