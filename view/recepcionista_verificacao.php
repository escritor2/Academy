<?php
session_start();

// Verificar se houve tentativa de login como recepcionista
if (!isset($_SESSION['recepcionista_pre_login_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../Model/RecepcionistaDAO.php';

$recepcionistaDao = new RecepcionistaDAO();
$recepcionista = $recepcionistaDao->buscarPorId($_SESSION['recepcionista_pre_login_id']);

if (!$recepcionista) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$error = '';
$success = false;

// Processar verificação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cpf_digitado = $_POST['cpf'] ?? '';
    $nome_digitado = trim($_POST['nome'] ?? '');
    $data_nascimento_digitada = $_POST['data_nascimento'] ?? '';
    
    // Limpar CPF para comparação
    $cpf_digitado_limpo = preg_replace('/[^0-9]/', '', $cpf_digitado);
    $cpf_bd_limpo = preg_replace('/[^0-9]/', '', $recepcionista['cpf']);
    
    // Comparar dados
    if (
        $cpf_digitado_limpo === $cpf_bd_limpo &&
        strtolower($nome_digitado) === strtolower($recepcionista['nome']) &&
        $data_nascimento_digitada === $recepcionista['data_nascimento']
    ) {
        // Dados corretos - criar sessão completa
        $_SESSION['usuario_id'] = $recepcionista['id'];
        $_SESSION['usuario_nome'] = $recepcionista['nome'];
        $_SESSION['tipo'] = 'recepcionista';
        $_SESSION['recepcionista_email'] = $recepcionista['email'];
        $_SESSION['recepcionista_turno'] = $recepcionista['turno'] ?? 'Diurno';
        $_SESSION['recepcionista_status'] = $recepcionista['status'];
        $_SESSION['recepcionista_foto'] = $recepcionista['foto_perfil'] ?? null;
        
        // Limpar dados temporários
        unset($_SESSION['recepcionista_pre_login_id']);
        unset($_SESSION['recepcionista_pre_nome']);
        
        $success = true;
        
        // REDIRECIONAR PARA recepcionista.php EM VEZ DE recepcionista_dashboard.php
        header("Refresh: 2; url=recepcionista.php");
    } else {
        $error = "Dados de verificação incorretos. Tente novamente.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação de Segurança - Recepcionista</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0B0F19 0%, #151b2b 100%);
            min-height: 100vh;
            color: #f3f4f6;
        }
        
        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="flex items-center justify-center gap-2 mb-4">
                <i data-lucide="shield" class="h-10 w-10 text-amber-500"></i>
                <span class="font-black text-2xl tracking-tighter text-white">TECH<span class="text-amber-500">FIT</span></span>
            </div>
            <h1 class="text-2xl font-bold mb-2">Verificação de Segurança</h1>
            <p class="text-gray-400">Para garantir a segurança, confirme seus dados pessoais</p>
        </div>
        
        <?php if ($success): ?>
            <div class="glass rounded-xl p-6 mb-4 text-center border border-green-500/30">
                <div class="bg-green-500/20 p-3 rounded-full inline-flex mb-4">
                    <i data-lucide="check-circle" class="w-8 h-8 text-green-500"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Verificação Bem-sucedida!</h3>
                <p class="text-gray-400 mb-4">Redirecionando para o painel do recepcionista...</p>
                <div class="flex justify-center">
                    <div class="loader border-2 border-gray-700 border-t-amber-500 rounded-full w-8 h-8 animate-spin"></div>
                </div>
            </div>
        <?php else: ?>
            <div class="glass rounded-xl p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="bg-amber-500/20 p-2 rounded-lg">
                        <i data-lucide="user-check" class="w-6 h-6 text-amber-500"></i>
                    </div>
                    <div>
                        <h3 class="font-bold">Verificação de Identidade</h3>
                        <p class="text-sm text-gray-400">Olá, <?php echo htmlspecialchars($recepcionista['nome']); ?></p>
                    </div>
                </div>
                
                <?php if ($error): ?>
                    <div class="bg-red-500/20 border border-red-500/50 text-red-200 p-3 rounded-lg mb-4 text-sm">
                        <div class="flex items-center gap-2">
                            <i data-lucide="alert-circle" class="w-4 h-4"></i>
                            <span><?php echo $error; ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Nome Completo</label>
                        <input type="text" name="nome" required 
                               class="w-full p-3 rounded-lg bg-black/30 border border-gray-700 text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none"
                               placeholder="Digite seu nome completo">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">CPF</label>
                        <input type="text" name="cpf" required 
                               class="w-full p-3 rounded-lg bg-black/30 border border-gray-700 text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none"
                               placeholder="000.000.000-00"
                               oninput="formatCPF(this)">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Data de Nascimento</label>
                        <input type="date" name="data_nascimento" required 
                               class="w-full p-3 rounded-lg bg-black/30 border border-gray-700 text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none">
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800 text-white font-bold py-3 rounded-lg transition-all flex items-center justify-center gap-2">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                        Verificar e Acessar
                    </button>
                </form>
                
                <div class="mt-6 pt-6 border-t border-gray-700/50">
                    <p class="text-xs text-gray-500 text-center">
                        Esta verificação adicional protege sua conta contra acessos não autorizados.
                    </p>
                </div>
            </div>
            
            <div class="text-center mt-6">
                <a href="index.php" class="text-sm text-gray-400 hover:text-amber-500 transition-colors inline-flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Voltar para o início
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        lucide.createIcons();
        
        function formatCPF(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            value = value.replace(/(\d{3})(\d)/, "$1.$2");
            value = value.replace(/(\d{3})(\d)/, "$1.$2");
            value = value.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
            input.value = value;
        }
    </script>
</body>
</html>