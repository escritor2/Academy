<?php
session_start();
require_once __DIR__ . '/../Model/AdminDAO.php';

// Segurança básica
if (!isset($_SESSION['admin_pre_login_id'])) {
    header('Location: index.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recebe as 3 chaves do formulário
    $cpf = $_POST['cpf_secreto'];
    $pin = $_POST['pin_secreto'];
    $palavra = $_POST['palavra_chave'];
    
    $idAdmin = $_SESSION['admin_pre_login_id'];

    $dao = new AdminDAO();
    
    // Verifica se TUDO bate
    if ($dao->verificarCredenciaisCompletas($idAdmin, $cpf, $pin, $palavra)) {
        
        // SUCESSO!
        $_SESSION['admin_logado'] = true;
        $_SESSION['admin_nome'] = $_SESSION['admin_pre_nome'];
        unset($_SESSION['admin_pre_login_id']);
        
        header('Location: adm.php');
        exit;
    } else {
        $erro = 'Falha na autenticação. Protocolo de segurança ativado.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Protocolo de Segurança Nível 5</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
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
                <p class="text-gray-400 text-sm">Gerente: <span class="text-red-400 font-mono"><?= $_SESSION['admin_pre_nome'] ?></span></p>
                <p class="text-gray-500 text-xs mt-1">Insira as credenciais mestras para liberar o painel.</p>
            </div>

            <?php if ($erro): ?>
                <div class="bg-red-500/10 border border-red-500/50 text-red-400 p-3 rounded-lg text-sm text-center mb-6 flex items-center justify-center gap-2 animate-bounce">
                    <i data-lucide="lock" class="w-4 h-4"></i> <?= $erro ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 tracking-wider">1. CPF do Titular</label>
                    <div class="relative">
                        <input type="text" name="cpf_secreto" required 
                            class="w-full bg-tech-900 border border-tech-700 rounded-lg pl-10 pr-4 py-3 focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all font-mono"
                            placeholder="000.000.000-00">
                        <i data-lucide="user-check" class="absolute left-3 top-3.5 w-5 h-5 text-gray-600"></i>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1 tracking-wider">2. PIN (6 Dígitos)</label>
                        <div class="relative">
                            <input type="password" name="pin_secreto" required maxlength="6"
                                class="w-full bg-tech-900 border border-tech-700 rounded-lg pl-10 pr-4 py-3 focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all font-mono tracking-widest"
                                placeholder="******">
                            <i data-lucide="hash" class="absolute left-3 top-3.5 w-5 h-5 text-gray-600"></i>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1 tracking-wider">3. Palavra-Chave</label>
                        <div class="relative">
                            <input type="text" name="palavra_chave" required 
                                class="w-full bg-tech-900 border border-tech-700 rounded-lg pl-10 pr-4 py-3 focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all font-mono"
                                placeholder="Ex: Master">
                            <i data-lucide="key" class="absolute left-3 top-3.5 w-5 h-5 text-gray-600"></i>
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

    <script>lucide.createIcons();</script>
</body>
</html>