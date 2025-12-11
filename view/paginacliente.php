<?php
session_start();
require_once __DIR__ . '/../Model/TreinoDAO.php';
require_once __DIR__ . '/../Model/AlunoDAO.php';
require_once __DIR__ . '/../Model/ProdutoDAO.php';
require_once __DIR__ . '/../Model/VendaDAO.php';

// SEGURANÇA
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?login_erro=1&msg=Faça login para acessar');
    exit;
}
if (isset($_GET['sair'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$idAluno = $_SESSION['usuario_id'];
$daoAluno = new AlunoDAO();
$treinoDao = new TreinoDAO();
$produtoDao = new ProdutoDAO();
$vendaDao = new VendaDAO();

// --- CARRINHO ---
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// --- PROCESSAMENTO: EDITAR PERFIL ---
$msgCliente = '';
$tipoMsg = '';

// Processar POSTs
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] === 'editar_perfil') {
        // Remove espaços extras do nome
        $nome = trim($_POST['nome']);
        $novaSenha = !empty($_POST['nova_senha']) ? $_POST['nova_senha'] : null;
        
        if (empty($nome)) {
            $msgCliente = "O nome não pode estar vazio!";
            $tipoMsg = 'erro';
        } elseif ($novaSenha && !preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/', $novaSenha)) {
            $msgCliente = "A senha deve ser forte (Letras, números e símbolo)!";
            $tipoMsg = 'erro';
        } else {
            if ($daoAluno->atualizarPerfilAluno($idAluno, $nome, $_POST['email'], $_POST['telefone'], $novaSenha)) {
                $_SESSION['usuario_nome'] = $nome;
                header("Location: paginacliente.php?tab=perfil&msg=perfil_ok");
                exit;
            } else {
                $msgCliente = "Erro ao atualizar. Tente novamente.";
                $tipoMsg = 'erro';
            }
        }
    }
    
    // ADICIONAR AO CARRINHO
    if ($_POST['acao'] === 'adicionar_carrinho') {
        $produto_id = $_POST['produto_id'];
        $quantidade = $_POST['quantidade'] ?? 1;
        
        // Verifica estoque
        $produto = $produtoDao->buscarPorId($produto_id);
        if ($produto && $produto['estoque'] >= $quantidade) {
            if (isset($_SESSION['carrinho'][$produto_id])) {
                $_SESSION['carrinho'][$produto_id] += $quantidade;
            } else {
                $_SESSION['carrinho'][$produto_id] = $quantidade;
            }
            $_SESSION['msg_carrinho'] = "Produto adicionado ao carrinho!";
            header("Location: paginacliente.php?tab=loja");
            exit;
        } else {
            $msgCliente = "Estoque insuficiente!";
            $tipoMsg = 'erro';
        }
    }
    
    // REMOVER DO CARRINHO
    if ($_POST['acao'] === 'remover_carrinho') {
        $produto_id = $_POST['produto_id'];
        if (isset($_SESSION['carrinho'][$produto_id])) {
            unset($_SESSION['carrinho'][$produto_id]);
            $_SESSION['msg_carrinho'] = "Produto removido do carrinho!";
        }
        header("Location: paginacliente.php?tab=loja&sub=carrinho");
        exit;
    }
    
    // ATUALIZAR QUANTIDADE
    if ($_POST['acao'] === 'atualizar_carrinho') {
        $produto_id = $_POST['produto_id'];
        $quantidade = $_POST['quantidade'];
        
        if ($quantidade > 0) {
            $_SESSION['carrinho'][$produto_id] = $quantidade;
        } else {
            unset($_SESSION['carrinho'][$produto_id]);
        }
        header("Location: paginacliente.php?tab=loja&sub=carrinho");
        exit;
    }
    
    // FINALIZAR COMPRA
    if ($_POST['acao'] === 'finalizar_compra') {
        $forma_pagamento = $_POST['forma_pagamento'];
        $total = 0;
        $erros = [];
        
        foreach ($_SESSION['carrinho'] as $produto_id => $quantidade) {
            $produto = $produtoDao->buscarPorId($produto_id);
            if (!$produto) {
                $erros[] = "Produto não encontrado!";
                continue;
            }
            
            if ($produto['estoque'] < $quantidade) {
                $erros[] = "Estoque insuficiente para {$produto['nome']}";
                continue;
            }
            
            // Registrar venda
            if ($vendaDao->registrarVenda($idAluno, $produto_id, $quantidade, $produto['preco'], $forma_pagamento)) {
                // Atualizar estoque
                $novoEstoque = $produto['estoque'] - $quantidade;
                $produtoDao->atualizarEstoque($produto_id, $novoEstoque);
                $total += $produto['preco'] * $quantidade;
            }
        }
        
        if (empty($erros)) {
            $_SESSION['carrinho'] = [];
            $_SESSION['msg_compra'] = "Compra realizada com sucesso! Total: R$ " . number_format($total, 2, ',', '.');
            header("Location: paginacliente.php?tab=loja&msg=compra_ok");
            exit;
        } else {
            $msgCliente = implode("<br>", $erros);
            $tipoMsg = 'erro';
        }
    }
}

// --- PROCESSAMENTO: FREQUÊNCIA (ENTRADA/SAÍDA) ---
if (isset($_POST['acao_frequencia'])) {
    if ($_POST['acao_frequencia'] == 'entrada') {
        $daoAluno->registrarEntrada($idAluno);
        header("Location: paginacliente.php?msg=entrada_ok&open_qr=true");
        exit;
    }
    if ($_POST['acao_frequencia'] == 'saida') { 
        $daoAluno->registrarSaida($idAluno);
        header("Location: paginacliente.php?msg=saida_ok&open_qr=true");
        exit;
    }
}

// --- DADOS ---
$dadosAluno = $daoAluno->buscarPorId($idAluno);
$meusTreinos = $treinoDao->buscarPorAluno($idAluno);
$nomeCompleto = $dadosAluno['nome'];
$primeiroNome = explode(' ', $nomeCompleto)[0];
$planoAluno = $dadosAluno['plano'];

// NOVO: Histórico de compras do aluno
$historicoCompras = $vendaDao->getVendasPorAluno($idAluno);

// Gerar iniciais do nome para avatar
$nomes = explode(' ', $nomeCompleto);
if (count($nomes) > 0) {
    $iniciais = '';
    $iniciais .= strtoupper(substr($nomes[0], 0, 1));
    if (count($nomes) > 1) {
        $iniciais .= strtoupper(substr($nomes[1], 0, 1));
    }
} else {
    $iniciais = 'U';
}

// --- DADOS DA LOJA ---
$listaProdutos = $produtoDao->listar();
$termoBuscaProd = $_GET['busca_produto'] ?? '';
$categoriaFiltro = $_GET['categoria'] ?? '';

// Filtrar produtos
if ($termoBuscaProd || $categoriaFiltro) {
    $listaProdutos = array_filter($listaProdutos, function($p) use ($termoBuscaProd, $categoriaFiltro) {
        $matchNome = !$termoBuscaProd || stripos($p['nome'], $termoBuscaProd) !== false || stripos($p['categoria'], $termoBuscaProd) !== false;
        $matchCategoria = !$categoriaFiltro || $p['categoria'] === $categoriaFiltro;
        return $matchNome && $matchCategoria;
    });
}

// Calcular total do carrinho
$totalCarrinho = 0;
$itensCarrinho = [];
foreach ($_SESSION['carrinho'] as $produto_id => $quantidade) {
    $produto = $produtoDao->buscarPorId($produto_id);
    if ($produto) {
        $itensCarrinho[] = [
            'id' => $produto_id,
            'nome' => $produto['nome'],
            'preco' => $produto['preco'],
            'quantidade' => $quantidade,
            'subtotal' => $produto['preco'] * $quantidade,
            'estoque' => $produto['estoque']
        ];
        $totalCarrinho += $produto['preco'] * $quantidade;
    }
}

// STATUS FREQUÊNCIA
$statusFrequencia = $daoAluno->getStatusFrequenciaHoje($idAluno); 
$treinoBloqueado = ($statusFrequencia !== 'treinando'); // Bloqueia se não estiver treinando

// Verificar se pode ver treinos (só se registrou entrada)
$podeVerTreinos = ($statusFrequencia === 'treinando' || $statusFrequencia === 'finalizado');

$tempoHoje = ($statusFrequencia === 'finalizado') ? $daoAluno->getTempoTreinoHoje($idAluno) : '';

// Obter hora de entrada se estiver treinando
$horaEntrada = '';
if ($statusFrequencia === 'treinando') {
    $horaEntrada = $daoAluno->getHoraEntradaHoje($idAluno);
}

// CALENDÁRIO MELHORADO
$mesAtual = date('n');
$anoAtual = date('Y');
$diasTreinados = $daoAluno->getFrequenciaMes($idAluno, $mesAtual, $anoAtual);
$totalDiasMes = date('t');
$diaHoje = date('j');
$nomeMes = [
    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 
    5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 
    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
];

// Estatísticas para dashboard
$presencasMes = count($diasTreinados);
$diasUteis = date('t');
$porcentagemPresenca = $diasUteis > 0 ? round(($presencasMes / $diasUteis) * 100) : 0;

// Mensagens motivadoras (para alternar)
$mensagensMotivacionais = [
    "A disciplina supera o talento quando o talento não é disciplinado.",
    "O progresso acontece fora da zona de conforto.",
    "Cada treino é um investimento no seu futuro.",
    "A força não vem da capacidade física, mas de uma vontade indomável.",
    "O único treino ruim é aquele que você não fez.",
    "Seu corpo pode aguentar quase tudo. É sua mente que você precisa convencer.",
    "Não espere por inspiração. Crie disciplina e a inspiração virá.",
    "Peque leve no fast food e pesado nos pesos.",
    "O cansaço de hoje é a força de amanhã.",
    "Treine como se sua vida dependesse disso, porque ela depende."
];

// Selecionar uma mensagem aleatória
$mensagemMotivacional = $mensagensMotivacionais[array_rand($mensagensMotivacionais)];

// MENSAGENS
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'perfil_ok') { $msgCliente = "Perfil atualizado com sucesso!"; $tipoMsg = 'sucesso'; }
    if ($_GET['msg'] == 'foto_ok') { $msgCliente = "Foto de perfil atualizada com sucesso!"; $tipoMsg = 'sucesso'; }
    if ($_GET['msg'] == 'foto_removida') { $msgCliente = "Foto de perfil removida!"; $tipoMsg = 'sucesso'; }
    if ($_GET['msg'] == 'entrada_ok') { $msgCliente = "Bem-vindo! Treino iniciado."; $tipoMsg = 'sucesso'; }
    if ($_GET['msg'] == 'saida_ok') { $msgCliente = "Treino finalizado! Bom descanso."; $tipoMsg = 'sucesso'; }
    if ($_GET['msg'] == 'compra_ok') { $msgCliente = $_SESSION['msg_compra'] ?? "Compra realizada com sucesso!"; $tipoMsg = 'sucesso'; }
}

if (isset($_SESSION['msg_carrinho'])) {
    $msgCliente = $_SESSION['msg_carrinho'];
    $tipoMsg = 'sucesso';
    unset($_SESSION['msg_carrinho']);
}

$abrirQR = isset($_GET['open_qr']);
$subTab = $_GET['sub'] ?? 'produtos';
?>
<!DOCTYPE html>
<html lang="pt-br" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icons/halter.png">
    <title>Área do Aluno - TechFit</title>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <script>
        tailwind.config = {
            theme: { 
                extend: { 
                    colors: { 
                        tech: { 
                            900: '#0B0F19', 
                            800: '#151b2b', 
                            700: '#2d3748', 
                            primary: '#ea580c' 
                        } 
                    }, 
                    boxShadow: { 
                        'glow': '0 0 15px rgba(249, 115, 22, 0.3)' 
                    },
                    animation: {
                        'pulse-glow': 'pulse-glow 2s infinite',
                        'fade-in': 'fadeIn 0.5s ease-out',
                        'slide-up': 'slideUp 0.3s ease-out',
                        'bounce-subtle': 'bounce-subtle 1s infinite',
                        'fade-in-out': 'fadeInOut 8s ease-in-out infinite',
                        'pulse-slow': 'pulse 3s infinite'
                    },
                    keyframes: {
                        'pulse-glow': {
                            '0%, 100%': { opacity: 1 },
                            '50%': { opacity: 0.7 }
                        },
                        'fadeIn': {
                            '0%': { opacity: 0, transform: 'translateY(10px)' },
                            '100%': { opacity: 1, transform: 'translateY(0)' }
                        },
                        'slideUp': {
                            '0%': { opacity: 0, transform: 'translateY(20px)' },
                            '100%': { opacity: 1, transform: 'translateY(0)' }
                        },
                        'bounce-subtle': {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-5px)' }
                        },
                        'fadeInOut': {
                            '0%, 100%': { opacity: 0 },
                            '20%, 80%': { opacity: 1 }
                        }
                    }
                } 
            }
        }
    </script>
    <style>
        * {
            box-sizing: border-box;
        }
        
        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        
        .no-scrollbar::-webkit-scrollbar { 
            display: none; 
        }
        
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        .fade-in { 
            animation: fadeIn 0.3s ease-out; 
        }
        
        @keyframes fadeIn { 
            from { 
                opacity: 0; 
                transform: translateY(10px); 
            } 
            to { 
                opacity: 1; 
                transform: translateY(0); 
            } 
        }
        
        .check-custom:checked + div { 
            background-color: #10b981; 
            border-color: #10b981; 
        }
        
        .check-custom:checked + div svg { 
            display: block; 
        }
        
        input, select, textarea { 
            background-color: #0f172a !important; 
            color: white !important; 
            border-color: #334155 !important; 
        }
        
        input:focus, select:focus, textarea:focus { 
            border-color: #ea580c !important; 
            box-shadow: 0 0 0 1px #ea580c !important; 
            outline: none;
        }
        
        /* Calendário melhorado */
        .day-presente { 
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(16, 185, 129, 0.25) 100%); 
            border: 2px solid #10b981;
            color: #10b981;
            font-weight: 700;
            box-shadow: 0 2px 10px rgba(16, 185, 129, 0.2);
        }
        
        .day-falta { 
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid #ef4444;
            color: #ef4444;
            opacity: 0.8;
        }
        
        .day-hoje { 
            background: linear-gradient(135deg, rgba(234, 88, 12, 0.2) 0%, rgba(234, 88, 12, 0.3) 100%);
            border: 2px solid #ea580c;
            color: #ea580c;
            font-weight: 700;
            box-shadow: 0 0 15px rgba(234, 88, 12, 0.4);
            animation: pulse-glow 2s infinite;
        }
        
        .day-futuro { 
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #94a3b8;
            opacity: 0.5;
        }
        
        /* Layout fixo */
        .main-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .content-container {
            flex: 1;
            overflow: hidden;
            display: flex;
            position: relative;
        }
        
        .page-content {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            background: linear-gradient(180deg, #0b1120 0%, #0f172a 100%);
        }
        
        /* Sidebar styles corrigida */
        .sidebar {
            position: relative;
            z-index: 40;
        }
        
        .toggle-sidebar-btn {
            position: absolute;
            top: 1.5rem;
            right: -0.75rem;
            z-index: 50;
            background-color: #ea580c;
            color: white;
            padding: 0.375rem;
            border-radius: 9999px;
            box-shadow: 0 0 15px rgba(249, 115, 22, 0.3);
            border: 2px solid #1e293b;
            transition: all 0.3s;
        }
        
        .toggle-sidebar-btn:hover {
            transform: scale(1.1);
            background-color: #c2410c;
        }
        
        .logo-text { 
            transition: opacity 0.3s; 
        }
        
        .sidebar-collapsed .logo-text { 
            opacity: 0; 
            width: 0; 
            overflow: hidden; 
        }
        
        .sidebar-collapsed .logo-container { 
            justify-content: center; 
            padding-left: 0.75rem;
        }
        
        .sidebar-collapsed .nav-text { 
            display: none; 
        }
        
        .sidebar-collapsed .nav-item { 
            justify-content: center; 
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        
        /* Sidebar com divisões */
        .sidebar-section {
            margin-bottom: 1.5rem;
        }
        
        .sidebar-section:last-child {
            margin-bottom: 0;
        }
        
        .section-title {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
            color: #6b7280;
            padding: 0 1rem;
            margin-bottom: 0.5rem;
            transition: opacity 0.3s;
        }
        
        .sidebar-collapsed .section-title {
            opacity: 0;
            height: 0;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        
        /* Card styles */
        .card {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.8) 0%, rgba(15, 23, 42, 0.9) 100%);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 1rem;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            border-color: rgba(234, 88, 12, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(234, 88, 12, 0.1);
        }
        
        /* Form styles */
        .form-input {
            background-color: #0f172a;
            border: 1px solid #334155;
            border-radius: 0.75rem;
            padding: 0.875rem;
            color: white;
            width: 100%;
            transition: all 0.2s;
        }
        
        .form-input:focus {
            border-color: #ea580c;
            outline: none;
            box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.2);
        }
        
        /* Button styles */
        .btn-primary {
            background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.2s;
            border: none;
            box-shadow: 0 4px 15px rgba(234, 88, 12, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(234, 88, 12, 0.4);
        }
        
        /* Avatar com iniciais - CORRIGIDO */
        .avatar-iniciais {
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
            overflow: hidden;
            line-height: 1;
        }
        
        /* Responsive fixes */
        @media (max-width: 768px) {
            .page-content {
                padding: 1rem;
            }
            
            .grid-cols-1 {
                grid-template-columns: 1fr !important;
            }
            
            .flex-col-mobile {
                flex-direction: column;
            }
            
            .text-2xl {
                font-size: 1.5rem;
            }
            
            .text-3xl {
                font-size: 1.75rem;
            }
        }
        
        /* Garantir que ícones sejam visíveis */
        [data-lucide] {
            display: inline-block !important;
            vertical-align: middle;
        }
        
        /* Modal overlay */
        .modal-overlay {
            z-index: 9999;
        }
        
        /* Corrigir visual dos checkboxes */
        input[type="checkbox"] {
            appearance: none;
            -webkit-appearance: none;
            width: 1.2rem;
            height: 1.2rem;
            border: 2px solid #4b5563;
            border-radius: 0.4rem;
            background-color: #0f172a;
            cursor: pointer;
            position: relative;
            transition: all 0.2s;
        }
        
        input[type="checkbox"]:checked {
            background-color: #10b981;
            border-color: #10b981;
        }
        
        input[type="checkbox"]:checked::after {
            content: '✓';
            position: absolute;
            color: white;
            font-size: 0.8rem;
            font-weight: bold;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        
        /* Progress bar */
        .progress-bar {
            height: 8px;
            border-radius: 4px;
            background: rgba(255, 255, 255, 0.1);
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            border-radius: 4px;
            background: linear-gradient(90deg, #10b981, #34d399);
            transition: width 0.5s ease;
        }
        
        /* Timer display */
        .timer-display {
            font-family: 'Courier New', monospace;
            font-size: 1.5rem;
            letter-spacing: 2px;
            background: rgba(0, 0, 0, 0.3);
            padding: 0.5rem 1rem;
            border-radius: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* CORREÇÃO DAS MENSAGENS MOTIVACIONAIS */
        .mensagem-container {
            position: relative;
            height: 60px;
            overflow: hidden;
        }
        
        .mensagem-motivacional {
            position: absolute;
            width: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            animation-duration: 40s;
            animation-timing-function: ease-in-out;
            animation-iteration-count: infinite;
        }
        
        .mensagem-motivacional:nth-child(1) {
            animation-name: mensagemFade1;
        }
        
        .mensagem-motivacional:nth-child(2) {
            animation-name: mensagemFade2;
        }
        
        .mensagem-motivacional:nth-child(3) {
            animation-name: mensagemFade3;
        }
        
        .mensagem-motivacional:nth-child(4) {
            animation-name: mensagemFade4;
        }
        
        .mensagem-motivacional:nth-child(5) {
            animation-name: mensagemFade5;
        }
        
        @keyframes mensagemFade1 {
            0%, 20% { opacity: 1; transform: translateY(0); }
            25%, 100% { opacity: 0; transform: translateY(-20px); }
        }
        
        @keyframes mensagemFade2 {
            0%, 20% { opacity: 0; transform: translateY(20px); }
            25%, 45% { opacity: 1; transform: translateY(0); }
            50%, 100% { opacity: 0; transform: translateY(-20px); }
        }
        
        @keyframes mensagemFade3 {
            0%, 45% { opacity: 0; transform: translateY(20px); }
            50%, 70% { opacity: 1; transform: translateY(0); }
            75%, 100% { opacity: 0; transform: translateY(-20px); }
        }
        
        @keyframes mensagemFade4 {
            0%, 70% { opacity: 0; transform: translateY(20px); }
            75%, 95% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-20px); }
        }
        
        @keyframes mensagemFade5 {
            0%, 95% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        
        /* Animação de confetes */
        @keyframes confetti-fall {
            0% { transform: translateY(-100px) rotate(0deg); opacity: 1; }
            100% { transform: translateY(1000px) rotate(720deg); opacity: 0; }
        }
        
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            animation: confetti-fall 3s linear forwards;
        }
        
        /* Estilos para o modal corrigido */
        .modal-fixed {
            position: fixed !important;
            inset: 0 !important;
            z-index: 9999 !important;
            background-color: rgba(0, 0, 0, 0.9) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 1rem !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        .modal-hidden {
            display: none !important;
        }
        
        /* QR Code Container */
        #qrcode {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        #qrcode canvas {
            max-width: 100%;
            max-height: 100%;
        }
        
        /* QR Code Scan Animation */
        .qr-scan-line {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, transparent, #00ff00, transparent);
            animation: qrScan 2s infinite linear;
            border-radius: 3px;
        }
        
        @keyframes qrScan {
            0% { top: 0; }
            50% { top: 100%; }
            100% { top: 0; }
        }
        
        /* Animação suave para o carrinho */
        .quantidade-input {
            transition: all 0.2s ease;
        }
        
        .quantidade-input:focus {
            transform: scale(1.05);
            border-color: #ea580c !important;
            box-shadow: 0 0 0 2px rgba(234, 88, 12, 0.2) !important;
        }
        
        /* Efeito de clique nos botões do carrinho */
        .btn-carrinho:active {
            transform: scale(0.95);
        }
        
        /* Estilos para a tabela de histórico - SEM BARRA DE ROLAGEM */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th, table td {
            padding: 12px 16px;
            text-align: left;
            white-space: nowrap;
        }
        
        @media (max-width: 768px) {
            table {
                display: block;
                overflow-x: visible;
            }
            
            table th, table td {
                padding: 8px 12px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body class="bg-[#0b1120] text-gray-100 font-sans h-full overflow-hidden">
    <div class="main-container">
        <div class="content-container">
            <!-- Sidebar Corrigida com divisões -->
            <aside id="sidebar" class="w-64 bg-[#111827]/95 backdrop-blur-lg border-r border-white/5 flex-col justify-between hidden md:flex transition-all duration-300 flex-shrink-0 sidebar relative">
                <button onclick="toggleSidebar()" class="toggle-sidebar-btn">
                    <i id="toggleIcon" data-lucide="chevron-left" class="w-4 h-4"></i>
                </button>
                <div class="flex-1 overflow-hidden flex flex-col">
                    <div class="h-20 flex items-center px-6 border-b border-white/5 logo-container flex-shrink-0">
                        <div class="bg-gradient-to-br from-orange-500 to-red-600 p-2 rounded-lg shadow-lg animate-pulse-glow">
                            <i data-lucide="dumbbell" class="w-6 h-6 text-white"></i>
                        </div>
                        <span class="text-xl font-bold ml-3 logo-text tracking-wide">TECH<span class="text-tech-primary">FIT</span></span>
                    </div>
                    
                    <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-1">
                        <!-- Seção HOME -->
                        <div class="sidebar-section">
                            <div class="section-title">HOME</div>
                            <button onclick="switchTab('dashboard')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl bg-tech-primary/10 text-tech-primary shadow-sm border border-tech-primary/20">
                                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                                <span class="nav-text">Dashboard</span>
                            </button>
                        </div>
                        
                        <!-- Seção TREINO -->
                        <div class="sidebar-section">
                            <div class="section-title">TREINO</div>
                            <button onclick="switchTab('treinos')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all <?= !$podeVerTreinos ? 'cursor-not-allowed opacity-50' : '' ?>">
                                <i data-lucide="biceps-flexed" class="w-5 h-5"></i>
                                <span class="nav-text">Meus Treinos</span>
                            </button>
                            <button onclick="abrirCarteirinha()" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all">
                                <i data-lucide="qr-code" class="w-5 h-5"></i>
                                <span class="nav-text">Carteirinha</span>
                            </button>
                        </div>
                        
                        <!-- Seção LOJA -->
                        <div class="sidebar-section">
                            <div class="section-title">LOJA</div>
                            <button onclick="switchTab('loja')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all">
                                <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                                <span class="nav-text">Loja</span>
                            </button>
                            <!-- NOVO BOTÃO DE HISTÓRICO DE COMPRAS -->
                            <button onclick="switchTab('compras')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all">
                                <i data-lucide="receipt" class="w-5 h-5"></i>
                                <span class="nav-text">Minhas Compras</span>
                            </button>
                        </div>
                        
                        <!-- Seção ALUNO -->
                        <div class="sidebar-section">
                            <div class="section-title">ALUNO</div>
                            <button onclick="switchTab('perfil')" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all">
                                <i data-lucide="user-cog" class="w-5 h-5"></i>
                                <span class="nav-text">Meu Perfil</span>
                            </button>
                        </div>
                    </nav>
                </div>
                
                <div class="p-4 border-t border-white/5 flex-shrink-0">
                    <a href="?sair=true" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-red-400 hover:bg-red-500/10 rounded-xl transition-colors">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                        <span class="nav-text">Sair</span>
                    </a>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 flex flex-col h-full overflow-hidden relative bg-[#0b1120]">
                <!-- Header CORRIGIDO (avatar alinhado) -->
                <header class="h-20 bg-[#111827]/90 backdrop-blur-xl border-b border-white/5 flex items-center justify-between px-6 md:px-8 z-10 flex-shrink-0">
                    <div>
                        <h2 id="pageTitle" class="text-xl md:text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white via-orange-200 to-gray-400 animate-fade-in">Olá, <?= htmlspecialchars($primeiroNome) ?></h2>
                        <p class="text-xs text-gray-500 mt-0.5">Seu progresso diário</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-bold text-white"><?= htmlspecialchars($nomeCompleto) ?></p>
                            <span class="text-[10px] uppercase font-bold tracking-wider bg-tech-primary/20 text-tech-primary px-2 py-0.5 rounded-full border border-tech-primary/30"><?= htmlspecialchars($planoAluno) ?></span>
                        </div>
                        <div class="avatar-iniciais w-10 h-10 bg-gradient-to-br from-orange-500 to-red-600 border-2 border-white/30 hover:border-tech-primary transition-all duration-300 cursor-pointer flex items-center justify-center text-white font-bold text-sm" onclick="abrirCarteirinha()">
                            <?= htmlspecialchars($iniciais) ?>
                        </div>
                        <div onclick="abrirCarteirinha()" class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-700 to-gray-900 border border-white/10 flex items-center justify-center shadow-lg cursor-pointer hover:border-tech-primary hover:scale-105 transition-all duration-300">
                            <i data-lucide="qr-code" class="w-5 h-5 text-white"></i>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <div class="page-content no-scrollbar">
                    <!-- Dashboard Melhorado -->
                    <div id="tab-dashboard" class="tab-content fade-in">
                        <!-- Mensagem de Boas-Vindas e Motivacional -->
                        <div class="mb-8 animate-slide-up">
                            <div class="bg-gradient-to-r from-tech-primary/10 via-orange-500/5 to-transparent border border-tech-primary/20 rounded-2xl p-6">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div>
                                        <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">
                                            Bem-vindo, <span class="text-tech-primary"><?= htmlspecialchars($primeiroNome) ?></span>!
                                        </h1>
                                        <p class="text-gray-300">Continue evoluindo. Cada treino conta!</p>
                                    </div>
                                    <div class="avatar-iniciais w-16 h-16 bg-gradient-to-br from-orange-500 to-red-600 border-4 border-tech-primary/30 shadow-lg hover:border-tech-primary transition-all duration-300 cursor-pointer flex items-center justify-center text-white font-bold text-2xl" onclick="abrirCarteirinha()">
                                        <?= htmlspecialchars($iniciais) ?>
                                    </div>
                                </div>
                                
                                <!-- Mensagem Motivacional que alterna CORRIGIDA -->
                                <div class="mt-4 pt-4 border-t border-white/10">
                                    <div class="mensagem-container">
                                        <?php for($i = 0; $i < 5; $i++): ?>
                                            <div class="mensagem-motivacional text-center">
                                                <p class="text-gray-300 italic">"<?= htmlspecialchars($mensagensMotivacionais[$i % count($mensagensMotivacionais)]) ?>"</p>
                                            </div>
                                        <?php endfor; ?>
                                        </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cards de Status -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 animate-slide-up">
                            <div onclick="abrirCarteirinha()" class="card cursor-pointer hover:border-tech-primary/50 transition-all group hover:scale-[1.02]">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-gray-400 text-xs font-bold uppercase mb-2">Status Hoje</p>
                                        <?php if($statusFrequencia == 'nao_entrou'): ?>
                                            <h3 class="text-xl md:text-2xl font-bold text-white">Check-in</h3>
                                            <p class="text-xs text-gray-500 mt-1">Clique para entrar</p>
                                        <?php elseif($statusFrequencia == 'treinando'): ?>
                                            <h3 class="text-xl md:text-2xl font-bold text-green-400 animate-pulse">Treinando</h3>
                                            <p class="text-xs text-gray-500 mt-1">
                                                Entrada: <?= date('H:i', strtotime($horaEntrada)) ?>
                                            </p>
                                        <?php else: ?>
                                            <h3 class="text-xl md:text-2xl font-bold text-blue-400">Concluído</h3>
                                            <p class="text-xs text-gray-500 mt-1">Tempo: <?= $tempoHoje ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-tech-primary/20 to-tech-primary/5 flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <i data-lucide="activity" class="w-6 h-6 text-tech-primary"></i>
                                    </div>
                                </div>
                                <?php if($statusFrequencia == 'treinando'): ?>
                                    <div class="mt-4">
                                        <div class="text-center timer-display">
                                            <span id="tempoDecorridoDash">00:00:00</span>
                                        </div>
                                        <p class="text-xs text-gray-500 text-center mt-1">Tempo de treino</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div onclick="switchTab('treinos')" class="card cursor-pointer hover:border-tech-primary/50 transition-all <?= !$podeVerTreinos ? 'opacity-50 cursor-not-allowed' : 'hover:scale-[1.02]' ?>">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-gray-400 text-xs font-bold uppercase mb-2">Treino Atual</p>
                                        <h3 class="text-xl md:text-2xl font-bold <?= $podeVerTreinos ? 'text-white' : 'text-gray-500' ?>"><?= $podeVerTreinos ? 'Ver Ficha' : 'Registre entrada' ?></h3>
                                        <p class="text-xs text-gray-500 mt-1">Acesse sua rotina</p>
                                    </div>
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500/20 to-blue-500/5 flex items-center justify-center">
                                        <i data-lucide="dumbbell" class="w-6 h-6 <?= $podeVerTreinos ? 'text-blue-500' : 'text-gray-500' ?>"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-gray-400 text-xs font-bold uppercase mb-2">Assinatura</p>
                                        <h3 class="text-xl md:text-2xl font-bold text-green-400">Ativa</h3>
                                        <p class="text-xs text-gray-500 mt-1">Plano: <?= htmlspecialchars($planoAluno) ?></p>
                                    </div>
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-500/20 to-green-500/5 flex items-center justify-center">
                                        <i data-lucide="check-circle" class="w-6 h-6 text-green-500"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Estatísticas do Mês -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                            <div class="card animate-slide-up" style="animation-delay: 100ms">
                                <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                                    <i data-lucide="calendar-check" class="w-5 h-5 text-tech-primary"></i> 
                                    Frequência - <?= $nomeMes[$mesAtual] ?> <?= $anoAtual ?>
                                </h3>
                                
                                <!-- Progresso -->
                                <div class="mb-6">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm text-gray-400">Presença no mês</span>
                                        <span class="text-sm font-bold text-white"><?= $presencasMes ?> de <?= $diasUteis ?> dias</span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?= $porcentagemPresenca ?>%"></div>
                                    </div>
                                    <div class="flex justify-between items-center mt-2">
                                        <span class="text-xs text-gray-500"><?= $porcentagemPresenca ?>% de frequência</span>
                                        <?php if($porcentagemPresenca >= 80): ?>
                                            <span class="text-xs text-green-500 font-bold">Excelente! ✅</span>
                                        <?php elseif($porcentagemPresenca >= 60): ?>
                                            <span class="text-xs text-yellow-500 font-bold">Bom 👍</span>
                                        <?php else: ?>
                                            <span class="text-xs text-red-500 font-bold">Precisa melhorar ⚠️</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Calendário Melhorado -->
                                <div>
                                    <div class="grid grid-cols-7 gap-2 text-center mb-3">
                                        <?php 
                                        $diasSemana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
                                        foreach($diasSemana as $dia): 
                                        ?>
                                            <div class="p-2 text-xs font-bold text-gray-500"><?= htmlspecialchars($dia) ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="grid grid-cols-7 gap-2 text-center">
                                        <?php 
                                        // Encontrar primeiro dia do mês
                                        $primeiroDia = date('w', strtotime("$anoAtual-$mesAtual-01"));
                                        
                                        // Dias vazios antes do primeiro dia
                                        for($i = 0; $i < $primeiroDia; $i++): 
                                        ?>
                                            <div class="p-2 rounded-lg border bg-transparent border-transparent"></div>
                                        <?php endfor; ?>
                                        
                                        <?php 
                                        // Dias do mês
                                        for($i = 1; $i <= $totalDiasMes; $i++): 
                                            $classeDia = ''; 
                                            $icone = '';
                                            if (in_array($i, $diasTreinados)) { 
                                                $classeDia = 'day-presente'; 
                                                $icone = '<i data-lucide="check" class="w-3 h-3 mt-1 mx-auto"></i>'; 
                                            } elseif ($i < $diaHoje) { 
                                                $classeDia = 'day-falta'; 
                                                $icone = '<i data-lucide="x" class="w-3 h-3 mt-1 mx-auto"></i>'; 
                                            } elseif ($i == $diaHoje) { 
                                                $classeDia = 'day-hoje'; 
                                                $icone = '<span class="text-xs font-bold mt-1">'.$i.'</span>';
                                            } else { 
                                                $classeDia = 'day-futuro'; 
                                                $icone = '<span class="text-sm mt-1">'.$i.'</span>';
                                            }
                                        ?>
                                            <div class="flex flex-col justify-center items-center h-12 rounded-lg border <?= $classeDia ?> transition-all duration-300 hover:scale-110 cursor-pointer relative group">
                                                <?= $icone ?>
                                                <div class="absolute bottom-full mb-2 hidden group-hover:block bg-gray-900 text-white text-xs px-2 py-1 rounded whitespace-nowrap z-10">
                                                    <?= $i ?>/<?= $mesAtual ?>
                                                    <?php if(in_array($i, $diasTreinados)): ?>
                                                        <br><span class="text-green-400">✔ Treinou</span>
                                                    <?php elseif($i < $diaHoje): ?>
                                                        <br><span class="text-red-400">✘ Faltou</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Próximos Objetivos -->
                            <div class="card animate-slide-up" style="animation-delay: 200ms">
                                <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                                    <i data-lucide="target" class="w-5 h-5 text-purple-500"></i> 
                                    Seus Objetivos
                                </h3>
                                
                                <?php if(!empty($dadosAluno['objetivo'])): ?>
                                    <div class="mb-6 p-4 bg-gradient-to-r from-purple-500/10 to-pink-500/10 rounded-xl border border-purple-500/20">
                                        <h4 class="font-bold text-white mb-2">Objetivo Principal</h4>
                                        <p class="text-gray-300"><?= htmlspecialchars($dadosAluno['objetivo']) ?></p>
                                    </div>
                                <?php else: ?>
                                    <div class="mb-6 p-4 bg-gray-800/50 rounded-xl border border-gray-700 text-center">
                                        <i data-lucide="target" class="w-12 h-12 text-gray-600 mx-auto mb-3"></i>
                                        <p class="text-gray-400">Nenhum objetivo definido</p>
                                        <button onclick="switchTab('perfil')" class="mt-3 px-4 py-2 bg-tech-primary/20 text-tech-primary text-sm rounded-lg hover:bg-tech-primary/30 transition-colors">
                                            Definir objetivo
                                        </button>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Dicas de treino -->
                                <div class="space-y-3">
                                    <h4 class="font-bold text-white text-sm flex items-center gap-2">
                                        <i data-lucide="lightbulb" class="w-4 h-4 text-yellow-500"></i>
                                        Dica do Dia
                                    </h4>
                                    <div class="p-3 bg-gradient-to-r from-yellow-500/5 to-orange-500/5 rounded-lg border border-yellow-500/20">
                                        <p class="text-sm text-gray-300">
                                            <?php
                                            $dicas = [
                                                "Mantenha-se hidratado durante o treino!",
                                                "Alongue-se antes e depois dos exercícios.",
                                                "Foque na execução correta, não no peso.",
                                                "Descanse adequadamente entre as séries.",
                                                "Varie os exercícios para evitar platô."
                                            ];
                                            echo $dicas[array_rand($dicas)];
                                            ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Treinos -->
                    <div id="tab-treinos" class="tab-content hidden fade-in">
                        <?php if(!$podeVerTreinos): ?>
                            <div class="flex flex-col items-center justify-center py-12 text-center animate-fade-in">
                                <div class="w-24 h-24 bg-red-500/10 rounded-full flex items-center justify-center mb-6 shadow-glow border border-red-500/30 animate-pulse">
                                    <i data-lucide="lock" class="w-12 h-12 text-red-500"></i>
                                </div>
                                <h2 class="text-2xl font-bold text-white mb-2">Treino Bloqueado</h2>
                                <p class="text-gray-400 max-w-md mb-4">Você precisa registrar sua entrada na academia para acessar sua ficha de treino.</p>
                                <button onclick="abrirCarteirinha()" class="px-6 py-3 rounded-xl bg-tech-primary hover:bg-orange-600 text-white font-bold shadow-lg transition-all flex items-center justify-center gap-2 hover:scale-105">
                                    <i data-lucide="qr-code" class="w-5 h-5"></i> REGISTRAR ENTRADA
                                </button>
                            </div>
                        <?php elseif($statusFrequencia === 'finalizado'): ?>
                            <div class="flex flex-col items-center justify-center py-12 text-center animate-fade-in">
                                <div class="w-24 h-24 bg-green-500/10 rounded-full flex items-center justify-center mb-6 shadow-glow border border-green-500/30">
                                    <i data-lucide="check-check" class="w-12 h-12 text-green-500"></i>
                                </div>
                                <h2 class="text-2xl font-bold text-white mb-2">Bom Descanso, <?= htmlspecialchars($primeiroNome) ?>!</h2>
                                <p class="text-gray-400 max-w-md mb-4">Treino finalizado. Volte amanhã para mais progresso!</p>
                                <div class="p-6 bg-gradient-to-r from-green-500/10 to-blue-500/10 rounded-xl border border-green-500/30">
                                    <p class="text-sm text-gray-500">Tempo total de hoje:</p>
                                    <p class="text-2xl font-mono text-white font-bold mt-1"><?= $tempoHoje ?></p>
                                    <div class="mt-4 flex justify-center">
                                        <i data-lucide="award" class="w-8 h-8 text-yellow-500"></i>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="mb-6 bg-gradient-to-r from-blue-500/10 to-cyan-500/10 border border-blue-500/30 p-5 rounded-2xl text-blue-400 animate-slide-up">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center">
                                        <i data-lucide="clock" class="w-5 h-5 text-blue-400"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-bold text-lg">Treinando desde <?= date('H:i', strtotime($horaEntrada)) ?></h4>
                                        <p class="text-sm mt-1">Tempo decorrido: 
                                            <span id="tempoTreino" class="font-mono font-bold text-white bg-black/30 px-2 py-1 rounded">00:00:00</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex gap-4 mb-6 border-b border-white/10 pb-1 overflow-x-auto">
                                <button onclick="mudarFicha('A')" id="btn-A" class="pb-3 text-sm font-bold text-tech-primary border-b-2 border-tech-primary transition-all whitespace-nowrap px-4 hover:text-orange-400">Treino A</button>
                                <button onclick="mudarFicha('B')" id="btn-B" class="pb-3 text-sm font-bold text-gray-400 hover:text-white transition-all whitespace-nowrap px-4">Treino B</button>
                                <button onclick="mudarFicha('C')" id="btn-C" class="pb-3 text-sm font-bold text-gray-400 hover:text-white transition-all whitespace-nowrap px-4">Treino C</button>
                            </div>
                            
                            <div id="listaExercicios" class="space-y-3 min-h-[300px] animate-fade-in"></div>
                            
                            <div class="mt-8 border-t border-white/10 pt-6 text-center">
                                <button onclick="abrirModalConclusao()" class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-8 py-4 rounded-xl font-bold shadow-lg flex items-center justify-center gap-2 mx-auto transition-all hover:scale-105">
                                    <i data-lucide="check-circle-2" class="w-6 h-6"></i> CONCLUIR TREINO
                                </button>
                                <p class="text-xs text-gray-500 mt-3">Ao concluir, você registra sua saída automática</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Loja -->
                    <div id="tab-loja" class="tab-content hidden fade-in">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                            <h3 class="text-xl md:text-2xl font-bold text-white">Loja TechFit</h3>
                            <div class="flex items-center gap-3">
                                <span class="bg-tech-primary/20 text-tech-primary px-3 py-1 rounded-full text-xs font-bold">10% OFF</span>
                                <button onclick="switchSubTabLoja('carrinho')" class="relative">
                                    <div class="card p-2 hover:border-tech-primary transition-all">
                                        <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                                        <?php if(count($_SESSION['carrinho']) > 0): ?>
                                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center">
                                                <?= count($_SESSION['carrinho']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </button>
                            </div>
                        </div>
                        
                        <div class="flex gap-4 mb-6 border-b border-white/10 pb-1 overflow-x-auto">
                            <button onclick="switchSubTabLoja('produtos')" id="btn-produtos" class="pb-3 text-sm font-bold <?= $subTab == 'produtos' ? 'text-tech-primary border-b-2 border-tech-primary' : 'text-gray-400 hover:text-white' ?> transition-all whitespace-nowrap px-4">Produtos</button>
                            <button onclick="switchSubTabLoja('carrinho')" id="btn-carrinho" class="pb-3 text-sm font-bold <?= $subTab == 'carrinho' ? 'text-tech-primary border-b-2 border-tech-primary' : 'text-gray-400 hover:text-white' ?> transition-all whitespace-nowrap px-4">Carrinho (<?= count($_SESSION['carrinho']) ?>)</button>
                        </div>
                        
                        <!-- Produtos -->
                        <div id="view-produtos" class="<?= $subTab == 'produtos' ? '' : 'hidden' ?>">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                                <form method="GET" class="relative w-full md:w-96">
                                    <input type="hidden" name="tab" value="loja">
                                    <input type="hidden" name="sub" value="produtos">
                                    <input type="text" name="busca_produto" value="<?= htmlspecialchars($termoBuscaProd) ?>" placeholder="Buscar produto..." class="w-full bg-[#1e293b] border border-white/10 rounded-xl pl-12 pr-4 py-3 focus:border-tech-primary outline-none text-sm text-white">
                                    <button type="submit" class="absolute left-4 top-3 text-gray-500 hover:text-white">
                                        <i data-lucide="search" class="w-5 h-5"></i>
                                    </button>
                                </form>
                                <form method="GET" class="w-full md:w-auto">
                                    <input type="hidden" name="tab" value="loja">
                                    <input type="hidden" name="sub" value="produtos">
                                    <input type="hidden" name="busca_produto" value="<?= htmlspecialchars($termoBuscaProd) ?>">
                                    <select name="categoria" onchange="this.form.submit()" class="w-full bg-[#0f172a] border border-white/10 rounded-lg px-4 py-3 text-sm text-white">
                                        <option value="">Todas Categorias</option>
                                        <option value="Suplemento" <?= $categoriaFiltro == 'Suplemento' ? 'selected' : '' ?>>Suplementos</option>
                                        <option value="Roupa" <?= $categoriaFiltro == 'Roupa' ? 'selected' : '' ?>>Roupas</option>
                                        <option value="Bebida" <?= $categoriaFiltro == 'Bebida' ? 'selected' : '' ?>>Bebidas</option>
                                        <option value="Equipamento" <?= $categoriaFiltro == 'Equipamento' ? 'selected' : '' ?>>Equipamentos</option>
                                        <option value="Alimento" <?= $categoriaFiltro == 'Alimento' ? 'selected' : '' ?>>Alimentos</option>
                                        <option value="Acessório" <?= $categoriaFiltro == 'Acessório' ? 'selected' : '' ?>>Acessórios</option>
                                    </select>
                                </form>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <?php if(empty($listaProdutos)): ?>
                                    <div class="col-span-3 text-center py-12 opacity-50">
                                        <i data-lucide="package" class="w-16 h-16 mx-auto text-gray-500 mb-4"></i>
                                        <p class="text-gray-400">Nenhum produto encontrado</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach($listaProdutos as $prod): 
                                        $icone='shopping-bag'; $cor='text-gray-400'; $bg='bg-white/5';
                                        if($prod['categoria']=='Suplemento'){$icone='zap';$cor='text-purple-400';$bg='bg-purple-500/10';}
                                        if($prod['categoria']=='Roupa'){$icone='shirt';$cor='text-blue-400';$bg='bg-blue-500/10';}
                                        if($prod['categoria']=='Bebida'){$icone='glass-water';$cor='text-cyan-400';$bg='bg-cyan-500/10';}
                                        if($prod['categoria']=='Equipamento'){$icone='dumbbell';$cor='text-orange-400';$bg='bg-orange-500/10';}
                                        if($prod['categoria']=='Alimento'){$icone='apple';$cor='text-green-400';$bg='bg-green-500/10';}
                                        $semEstoque = $prod['estoque'] <= 0;
                                    ?>
                                    <div class="card <?= $semEstoque ? 'opacity-50' : '' ?>">
                                        <div class="h-40 bg-gray-800 rounded-lg flex items-center justify-center relative mb-4">
                                            <i data-lucide="<?= $icone ?>" class="w-12 h-12 <?= $cor ?>"></i>
                                            <?php if($semEstoque): ?>
                                                <div class="absolute inset-0 bg-black/50 rounded-lg flex items-center justify-center">
                                                    <span class="bg-red-500 text-white text-sm font-bold px-3 py-1 rounded">ESGOTADO</span>
                                                </div>
                                            <?php elseif($prod['estoque'] < 5): ?>
                                                <span class="absolute top-2 right-2 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded">Últimas Unidades</span>
                                            <?php endif; ?>
                                        </div>
                                        <h4 class="font-bold text-white text-lg mb-1"><?= htmlspecialchars($prod['nome']) ?></h4>
                                        <p class="text-gray-400 text-sm mb-3"><?= htmlspecialchars($prod['categoria']) ?> • Estoque: <?= $prod['estoque'] ?></p>
                                        <?php if(!empty($prod['descricao'])): ?>
                                            <p class="text-gray-500 text-xs mb-3"><?= htmlspecialchars($prod['descricao']) ?></p>
                                        <?php endif; ?>
                                        <div class="flex justify-between items-center">
                                            <span class="text-lg font-bold text-tech-primary">R$ <?= number_format($prod['preco'], 2, ',', '.') ?></span>
                                            <?php if($semEstoque): ?>
                                                <button disabled class="p-2 bg-gray-700 rounded-lg text-gray-400 cursor-not-allowed">
                                                    <i data-lucide="x" class="w-4 h-4"></i>
                                                </button>
                                            <?php else: ?>
                                                <button onclick="abrirModalAdicionar(<?= $prod['id'] ?>, '<?= htmlspecialchars(addslashes($prod['nome'])) ?>', <?= $prod['estoque'] ?>)" class="p-2 bg-tech-primary rounded-lg text-white hover:bg-orange-600 transition-colors">
                                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Carrinho CORRIGIDO (sem tremedeira) -->
                        <div id="view-carrinho" class="<?= $subTab == 'carrinho' ? '' : 'hidden' ?>">
                            <?php if(empty($itensCarrinho)): ?>
                                <div class="text-center py-12 opacity-50">
                                    <i data-lucide="shopping-cart" class="w-20 h-20 mx-auto text-gray-500 mb-4"></i>
                                    <h3 class="text-xl font-bold text-gray-400 mb-2">Carrinho Vazio</h3>
                                    <p class="text-gray-500 mb-6">Adicione produtos para começar suas compras!</p>
                                    <button onclick="switchSubTabLoja('produtos')" class="bg-tech-primary hover:bg-orange-600 text-white px-6 py-3 rounded-xl font-bold transition-all">
                                        Ver Produtos
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                    <div class="lg:col-span-2">
                                        <div class="card">
                                            <div class="mb-6 pb-4 border-b border-white/5">
                                                <h3 class="text-lg font-bold text-white">Seu Carrinho</h3>
                                                <p class="text-gray-400 text-sm"><?= count($itensCarrinho) ?> ite<?= count($itensCarrinho) > 1 ? 'ns' : 'm' ?></p>
                                            </div>
                                            <div class="space-y-4">
                                                <?php foreach($itensCarrinho as $item): ?>
                                                <div class="flex items-center gap-4 p-4 bg-[#0f172a] rounded-lg border border-white/5">
                                                    <div class="w-12 h-12 bg-white/5 rounded-lg flex items-center justify-center flex-shrink-0">
                                                        <i data-lucide="package" class="w-6 h-6 text-gray-400"></i>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <h4 class="font-bold text-white truncate"><?= htmlspecialchars($item['nome']) ?></h4>
                                                        <p class="text-tech-primary font-bold text-sm">R$ <?= number_format($item['preco'], 2, ',', '.') ?> cada</p>
                                                    </div>
                                                    <div class="flex items-center gap-3">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" onclick="atualizarQuantidadeCarrinho(<?= $item['id'] ?>, -1)" class="w-8 h-8 bg-[#1e293b] border border-white/10 rounded flex items-center justify-center hover:bg-white/5 transition-colors btn-carrinho" <?= $item['quantidade'] <= 1 ? 'disabled' : '' ?>>
                                                                <i data-lucide="minus" class="w-4 h-4"></i>
                                                            </button>
                                                            <input type="number" id="quantidade-<?= $item['id'] ?>" value="<?= $item['quantidade'] ?>" min="1" max="<?= $item['estoque'] ?>" class="w-16 text-center bg-[#1e293b] border border-white/10 rounded py-1 quantidade-input" onchange="atualizarInputCarrinho(<?= $item['id'] ?>, <?= $item['estoque'] ?>)">
                                                            <button type="button" onclick="atualizarQuantidadeCarrinho(<?= $item['id'] ?>, 1)" class="w-8 h-8 bg-[#1e293b] border border-white/10 rounded flex items-center justify-center hover:bg-white/5 transition-colors btn-carrinho" <?= $item['quantidade'] >= $item['estoque'] ? 'disabled' : '' ?>>
                                                                <i data-lucide="plus" class="w-4 h-4"></i>
                                                            </button>
                                                        </div>
                                                        <form method="POST" class="inline">
                                                            <input type="hidden" name="acao" value="remover_carrinho">
                                                            <input type="hidden" name="produto_id" value="<?= $item['id'] ?>">
                                                            <button type="submit" class="p-2 text-red-400 hover:bg-red-500/20 rounded transition-colors btn-carrinho">
                                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="text-gray-400 text-sm">Subtotal</p>
                                                        <p class="text-lg font-bold text-white">R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></p>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="lg:col-span-1">
                                        <div class="card sticky top-24">
                                            <h3 class="text-lg font-bold text-white mb-6">Resumo do Pedido</h3>
                                            <div class="space-y-3 mb-6">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-400">Subtotal</span>
                                                    <span class="text-white">R$ <?= number_format($totalCarrinho, 2, ',', '.') ?></span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-400">Frete</span>
                                                    <span class="text-green-400">Grátis</span>
                                                </div>
                                                <div class="border-t border-white/10 pt-3">
                                                    <div class="flex justify-between">
                                                        <span class="text-white font-bold">Total</span>
                                                        <span class="text-tech-primary text-xl font-bold">R$ <?= number_format($totalCarrinho, 2, ',', '.') ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <button onclick="abrirModalPagamento(<?= $totalCarrinho ?>)" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-xl shadow-lg flex items-center justify-center gap-2 transition-all">
                                                <i data-lucide="credit-card" class="w-5 h-5"></i> FINALIZAR COMPRA
                                            </button>
                                            <button onclick="switchSubTabLoja('produtos')" class="w-full mt-3 border border-white/10 text-gray-300 hover:bg-white/5 py-3 rounded-xl transition-all">
                                                Continuar Comprando
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Histórico de Compras -->
                    <div id="tab-compras" class="tab-content hidden fade-in">
                        <div class="max-w-6xl mx-auto">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                                <div>
                                    <h3 class="text-xl md:text-2xl font-bold text-white">Minhas Compras</h3>
                                    <p class="text-gray-400 text-sm">Histórico completo de suas compras na loja</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="bg-tech-primary/10 text-tech-primary px-3 py-1 rounded-full text-xs font-bold border border-tech-primary/20">
                                        <?= count($historicoCompras) ?> compra<?= count($historicoCompras) != 1 ? 's' : '' ?>
                                    </div>
                                    <button onclick="switchTab('loja')" class="bg-tech-primary hover:bg-orange-600 text-white px-4 py-2 rounded-xl text-sm font-bold transition-all flex items-center gap-2">
                                        <i data-lucide="shopping-bag" class="w-4 h-4"></i> Ir para Loja
                                    </button>
                                </div>
                            </div>
                            
                            <?php if(empty($historicoCompras)): ?>
                                <div class="card text-center py-12 animate-fade-in">
                                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gradient-to-br from-gray-800 to-gray-900 flex items-center justify-center">
                                        <i data-lucide="package" class="w-10 h-10 text-gray-500"></i>
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-400 mb-2">Nenhuma compra realizada</h3>
                                    <p class="text-gray-500 mb-6">Você ainda não fez nenhuma compra em nossa loja.</p>
                                    <button onclick="switchTab('loja')" class="bg-tech-primary hover:bg-orange-600 text-white px-6 py-3 rounded-xl font-bold transition-all inline-flex items-center gap-2">
                                        <i data-lucide="shopping-bag" class="w-4 h-4"></i> Explorar Produtos
                                    </button>
                                </div>
                            <?php else: ?>
                                <!-- Estatísticas rápidas -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 animate-slide-up">
                                    <div class="card">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="text-gray-400 text-xs font-bold uppercase mb-2">Total Gasto</p>
                                                <h3 class="text-xl md:text-2xl font-bold text-white">
                                                    R$ <?php 
                                                    $totalGasto = array_sum(array_column($historicoCompras, 'valor_total'));
                                                    echo number_format($totalGasto, 2, ',', '.');
                                                    ?>
                                                </h3>
                                            </div>
                                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-500/20 to-green-500/5 flex items-center justify-center">
                                                <i data-lucide="dollar-sign" class="w-6 h-6 text-green-500"></i>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="text-gray-400 text-xs font-bold uppercase mb-2">Total de Itens</p>
                                                <h3 class="text-xl md:text-2xl font-bold text-white">
                                                    <?php 
                                                    $totalItens = array_sum(array_column($historicoCompras, 'quantidade'));
                                                    echo $totalItens;
                                                    ?>
                                                </h3>
                                            </div>
                                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500/20 to-blue-500/5 flex items-center justify-center">
                                                <i data-lucide="package" class="w-6 h-6 text-blue-500"></i>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="text-gray-400 text-xs font-bold uppercase mb-2">Primeira Compra</p>
                                                <h3 class="text-xl md:text-2xl font-bold text-white">
                                                    <?php 
                                                    $primeiraCompra = end($historicoCompras);
                                                    if($primeiraCompra) {
                                                        echo date('d/m/Y', strtotime($primeiraCompra['data_venda']));
                                                    } else {
                                                        echo '--/--/----';
                                                    }
                                                    ?>
                                                </h3>
                                            </div>
                                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-500/20 to-purple-500/5 flex items-center justify-center">
                                                <i data-lucide="calendar" class="w-6 h-6 text-purple-500"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Lista de compras -->
                                <div class="card">
                                    <div class="mb-6 pb-4 border-b border-white/5">
                                        <h3 class="text-lg font-bold text-white">Histórico Detalhado</h3>
                                        <p class="text-gray-400 text-sm">Todas as suas transações ordenadas por data</p>
                                    </div>
                                    
                                    <!-- TABELA SEM BARRA DE ROLAGEM HORIZONTAL -->
                                    <div>
                                        <table class="w-full">
                                            <thead>
                                                <tr class="border-b border-white/10">
                                                    <th class="text-left py-3 px-4 text-xs font-bold text-gray-400 uppercase">Data</th>
                                                    <th class="text-left py-3 px-4 text-xs font-bold text-gray-400 uppercase">Produto</th>
                                                    <th class="text-left py-3 px-4 text-xs font-bold text-gray-400 uppercase">Qtd</th>
                                                    <th class="text-left py-3 px-4 text-xs font-bold text-gray-400 uppercase">Valor Unit.</th>
                                                    <th class="text-left py-3 px-4 text-xs font-bold text-gray-400 uppercase">Total</th>
                                                    <th class="text-left py-3 px-4 text-xs font-bold text-gray-400 uppercase">Pagamento</th>
                                                    <th class="text-left py-3 px-4 text-xs font-bold text-gray-400 uppercase">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $contador = 0;
                                                foreach($historicoCompras as $compra): 
                                                    $contador++;
                                                    $dataFormatada = date('d/m/Y H:i', strtotime($compra['data_venda']));
                                                    $corStatus = $compra['status'] === 'Concluída' ? 'text-green-400' : 'text-yellow-400';
                                                    $bgStatus = $compra['status'] === 'Concluída' ? 'bg-green-500/10' : 'bg-yellow-500/10';
                                                    
                                                    // ÍCONE ESPECÍFICO PARA PIX (MODIFICADO)
                                                    if($compra['forma_pagamento'] === 'PIX') {
                                                        $iconePagamento = 'sparkles'; // Ícone mais apropriado para PIX
                                                        $corIconePagamento = 'text-purple-400';
                                                    } else {
                                                        $iconePagamento = 'credit-card';
                                                        $corIconePagamento = 'text-gray-400';
                                                    }
                                                ?>
                                                <tr class="border-b border-white/5 hover:bg-white/2 transition-colors animate-fade-in" style="animation-delay: <?= $contador * 50 ?>ms">
                                                    <td class="py-4 px-4">
                                                        <div class="flex flex-col">
                                                            <span class="text-white font-medium"><?= $dataFormatada ?></span>
                                                            <span class="text-xs text-gray-500">ID: #<?= str_pad($compra['id'], 5, '0', STR_PAD_LEFT) ?></span>
                                                        </div>
                                                    </td>
                                                    <td class="py-4 px-4">
                                                        <div class="flex items-center gap-3">
                                                            <div class="w-10 h-10 bg-white/5 rounded-lg flex items-center justify-center flex-shrink-0">
                                                                <i data-lucide="package" class="w-5 h-5 text-gray-400"></i>
                                                            </div>
                                                            <div>
                                                                <span class="text-white font-medium"><?= htmlspecialchars($compra['produto_nome']) ?></span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="py-4 px-4">
                                                        <span class="text-white font-medium"><?= $compra['quantidade'] ?></span>
                                                    </td>
                                                    <td class="py-4 px-4">
                                                        <span class="text-gray-300">R$ <?= number_format($compra['valor_unitario'], 2, ',', '.') ?></span>
                                                    </td>
                                                    <td class="py-4 px-4">
                                                        <span class="text-tech-primary font-bold">R$ <?= number_format($compra['valor_total'], 2, ',', '.') ?></span>
                                                    </td>
                                                    <td class="py-4 px-4">
                                                        <div class="flex items-center gap-2">
                                                            <div class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center">
                                                                <i data-lucide="<?= $iconePagamento ?>" class="w-4 h-4 <?= $corIconePagamento ?>"></i>
                                                            </div>
                                                            <span class="text-gray-300"><?= htmlspecialchars($compra['forma_pagamento']) ?></span>
                                                        </div>
                                                    </td>
                                                    <td class="py-4 px-4">
                                                        <span class="px-3 py-1 rounded-full text-xs font-bold <?= $bgStatus ?> <?= $corStatus ?> border <?= $compra['status'] === 'Concluída' ? 'border-green-500/30' : 'border-yellow-500/30' ?>">
                                                            <?= htmlspecialchars($compra['status']) ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <!-- Resumo no final -->
                                    <div class="mt-6 pt-6 border-t border-white/10">
                                        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                                            <div class="text-gray-400 text-sm">
                                                Mostrando <?= count($historicoCompras) ?> compra<?= count($historicoCompras) != 1 ? 's' : '' ?> no total
                                            </div>
                                            <div class="text-right">
                                                <p class="text-gray-400 text-sm">Valor total de todas as compras:</p>
                                                <p class="text-2xl font-bold text-tech-primary">R$ <?= number_format($totalGasto, 2, ',', '.') ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Gráfico visual (simples) -->
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                                    <div class="card">
                                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                                            <i data-lucide="trending-up" class="w-5 h-5 text-tech-primary"></i> 
                                            Resumo por Mês
                                        </h3>
                                        
                                        <?php
                                        // Agrupar compras por mês
                                        $comprasPorMes = [];
                                        foreach($historicoCompras as $compra) {
                                            $mesAno = date('m/Y', strtotime($compra['data_venda']));
                                            $mesNome = date('F Y', strtotime($compra['data_venda']));
                                            
                                            if(!isset($comprasPorMes[$mesAno])) {
                                                $comprasPorMes[$mesAno] = [
                                                    'mes' => $mesNome,
                                                    'total' => 0,
                                                    'quantidade' => 0,
                                                    'compras' => 0
                                                ];
                                            }
                                            
                                            $comprasPorMes[$mesAno]['total'] += $compra['valor_total'];
                                            $comprasPorMes[$mesAno]['quantidade'] += $compra['quantidade'];
                                            $comprasPorMes[$mesAno]['compras']++;
                                        }
                                        
                                        // Ordenar por data (mais recente primeiro)
                                        krsort($comprasPorMes);
                                        ?>
                                        
                                        <div class="space-y-4">
                                            <?php foreach($comprasPorMes as $mes): ?>
                                            <div class="p-4 bg-white/5 rounded-lg border border-white/10">
                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="font-bold text-white"><?= $mes['mes'] ?></span>
                                                    <span class="text-tech-primary font-bold">R$ <?= number_format($mes['total'], 2, ',', '.') ?></span>
                                                </div>
                                                <div class="flex justify-between text-sm text-gray-400">
                                                    <span><?= $mes['compras'] ?> compra<?= $mes['compras'] != 1 ? 's' : '' ?></span>
                                                    <span><?= $mes['quantidade'] ?> ite<?= $mes['quantidade'] > 1 ? 'ns' : 'm' ?></span>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="card">
                                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                                            <i data-lucide="credit-card" class="w-5 h-5 text-tech-primary"></i> 
                                            Métodos de Pagamento
                                        </h3>
                                        
                                        <?php
                                        // Contar por método de pagamento
                                        $metodosPagamento = [];
                                        foreach($historicoCompras as $compra) {
                                            $metodo = $compra['forma_pagamento'];
                                            if(!isset($metodosPagamento[$metodo])) {
                                                $metodosPagamento[$metodo] = 0;
                                            }
                                            $metodosPagamento[$metodo]++;
                                        }
                                        ?>
                                        
                                        <div class="space-y-4">
                                            <?php foreach($metodosPagamento as $metodo => $quantidade): 
                                                // Ícone específico para PIX
                                                if($metodo === 'PIX') {
                                                    $iconeMetodo = 'sparkles';
                                                    $corMetodo = 'text-purple-500';
                                                } else {
                                                    $iconeMetodo = 'credit-card';
                                                    $corMetodo = 'text-tech-primary';
                                                }
                                            ?>
                                            <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg border border-white/10">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-full bg-tech-primary/10 flex items-center justify-center">
                                                        <i data-lucide="<?= $iconeMetodo ?>" class="w-5 h-5 <?= $corMetodo ?>"></i>
                                                    </div>
                                                    <div>
                                                        <span class="font-bold text-white"><?= htmlspecialchars($metodo) ?></span>
                                                        <p class="text-xs text-gray-500"><?= $quantidade ?> vez<?= $quantidade != 1 ? 'es' : '' ?></p>
                                                    </div>
                                                </div>
                                                <span class="text-gray-300">
                                                    <?= round(($quantidade / count($historicoCompras)) * 100) ?>%
                                                </span>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Perfil SIMPLIFICADO (sem ícones) -->
                    <div id="tab-perfil" class="tab-content hidden fade-in">
                        <div class="max-w-4xl mx-auto">
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <!-- Coluna do Avatar -->
                                <div class="lg:col-span-1">
                                    <div class="card">
                                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                                            <i data-lucide="user" class="w-6 h-6 text-tech-primary"></i> Seu Avatar
                                        </h3>
                                        <div class="text-center mb-6">
                                            <div class="avatar-iniciais w-48 h-48 bg-gradient-to-br from-orange-500 to-red-600 border-4 border-tech-primary/30 shadow-lg mx-auto flex items-center justify-center text-white font-bold text-6xl">
                                                <?= htmlspecialchars($iniciais) ?>
                                            </div>
                                            <p class="text-gray-400 text-sm mt-4">Avatar gerado com suas iniciais</p>
                                            <p class="text-gray-500 text-xs">Atualize seu nome para alterar as iniciais</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Coluna dos Dados -->
                                <div class="lg:col-span-2">
                                    <div class="card">
                                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                                            <i data-lucide="user-cog" class="w-6 h-6 text-tech-primary"></i> Editar Meus Dados
                                        </h3>
                                        <form method="POST" id="formPerfil" onsubmit="return validarFormPerfil()">
                                            <input type="hidden" name="acao" value="editar_perfil">
                                            <div class="space-y-4">
                                                <div>
                                                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Nome Completo *</label>
                                                    <input type="text" name="nome" id="nomeInput" value="<?= htmlspecialchars($dadosAluno['nome']) ?>" required 
                                                           class="form-input" 
                                                           oninput="validarNome(this)">
                                                    <p id="nomeErro" class="text-xs text-red-400 mt-1 hidden">O nome não pode conter apenas espaços ou caracteres especiais</p>
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Email *</label>
                                                        <input type="email" name="email" value="<?= htmlspecialchars($dadosAluno['email']) ?>" required class="form-input">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Telefone *</label>
                                                        <input type="text" name="telefone" id="telefoneInput" value="<?= htmlspecialchars($dadosAluno['telefone']) ?>" 
                                                               oninput="mascaraTelefone(this)" maxlength="15" required class="form-input" placeholder="(00) 00000-0000">
                                                    </div>
                                                </div>
                                                <div class="border-t border-white/10 pt-4">
                                                    <label class="block text-xs font-bold text-tech-primary uppercase mb-2">Nova Senha (Opcional)</label>
                                                    <input type="password" name="nova_senha" id="novaSenhaInput" 
                                                           placeholder="Min 8 caracteres, letra, número, símbolo" 
                                                           class="form-input" oninput="validarSenha(this)">
                                                    <div id="requisitosSenha" class="text-xs text-gray-500 mt-2 space-y-1 hidden">
                                                        <p id="reqLength" class="flex items-center gap-1"><i data-lucide="circle" class="w-3 h-3 text-red-400"></i> Mínimo 8 caracteres</p>
                                                        <p id="reqLetter" class="flex items-center gap-1"><i data-lucide="circle" class="w-3 h-3 text-red-400"></i> Pelo menos 1 letra</p>
                                                        <p id="reqNumber" class="flex items-center gap-1"><i data-lucide="circle" class="w-3 h-3 text-red-400"></i> Pelo menos 1 número</p>
                                                        <p id="reqSymbol" class="flex items-center gap-1"><i data-lucide="circle" class="w-3 h-3 text-red-400"></i> Pelo menos 1 símbolo</p>
                                                    </div>
                                                </div>
                                                <button type="submit" id="btnSalvarPerfil" class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-4 rounded-xl shadow-lg transition-all hover:scale-[1.02]">
                                                    <i data-lucide="save" class="w-5 h-5 inline mr-2"></i> Salvar Alterações
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    
                                    <!-- Informações adicionais -->
                                    <div class="card mt-6">
                                        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                            <i data-lucide="info" class="w-5 h-5 text-gray-400"></i> Informações da Conta
                                        </h3>
                                        <div class="space-y-3">
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-400">Data de Nascimento</span>
                                                <span class="text-white"><?= date('d/m/Y', strtotime($dadosAluno['data_nascimento'])) ?></span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-400">CPF</span>
                                                <span class="text-white"><?= htmlspecialchars($dadosAluno['cpf']) ?></span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-400">Gênero</span>
                                                <span class="text-white"><?= htmlspecialchars($dadosAluno['genero']) ?></span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-400">Plano Atual</span>
                                                <span class="bg-tech-primary/20 text-tech-primary px-3 py-1 rounded-full text-xs font-bold"><?= htmlspecialchars($dadosAluno['plano']) ?></span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-400">Membro desde</span>
                                                <span class="text-white"><?= date('d/m/Y', strtotime($dadosAluno['criado_em'])) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- MODAL DA CARTEIRINHA COM QR CODE -->
    <div id="modalCarteirinha" class="<?= $abrirQR ? 'modal-fixed' : 'modal-hidden' ?>">
        <div class="absolute inset-0 bg-black/90 backdrop-blur-md" onclick="fecharCarteirinha()"></div>
        <div class="relative z-10 w-full max-w-md mx-auto animate-slide-up">
            <div class="bg-gradient-to-br from-gray-900 to-black rounded-2xl border border-white/10 p-6 shadow-2xl">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-white flex items-center gap-2">
                        <i data-lucide="qr-code" class="w-6 h-6 text-tech-primary"></i>
                        Sua Carteirinha
                    </h3>
                    <button onclick="fecharCarteirinha()" class="p-2 hover:bg-white/5 rounded-lg transition-colors">
                        <i data-lucide="x" class="w-5 h-5 text-gray-400"></i>
                    </button>
                </div>
                
                <div class="text-center mb-6">
                    <div class="w-48 h-48 mx-auto bg-white p-4 rounded-xl shadow-lg mb-4 relative">
                        <!-- QR Code dinâmico -->
                        <div id="qrcode" class="w-full h-full"></div>
                        <!-- Linha de escaneamento animada -->
                        <div class="qr-scan-line"></div>
                    </div>
                    <p class="text-gray-400 text-sm">Escaneie para registrar entrada/saída</p>
                    <p class="text-xs text-gray-500 mt-1">ID: ALUNO-<?= $idAluno ?></p>
                </div>
                
                <div class="space-y-4">
                    <?php if($statusFrequencia == 'nao_entrou'): ?>
                        <form method="POST">
                            <input type="hidden" name="acao_frequencia" value="entrada">
                            <button type="submit" class="w-full bg-gradient-to-r from-tech-primary to-orange-600 hover:from-orange-600 hover:to-red-600 text-white font-bold py-4 rounded-xl shadow-lg flex items-center justify-center gap-3 transition-all hover:scale-[1.02]">
                                <i data-lucide="log-in" class="w-6 h-6"></i>
                                REGISTRAR ENTRADA
                            </button>
                            <p class="text-xs text-gray-500 text-center mt-2">Inicie seu treino de hoje</p>
                        </form>
                    <?php elseif($statusFrequencia == 'treinando'): ?>
                        <div class="p-4 bg-gradient-to-r from-green-500/10 to-blue-500/10 rounded-xl border border-green-500/30 mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="clock" class="w-5 h-5 text-green-500"></i>
                                    <span class="font-bold text-white">Treinando</span>
                                </div>
                                <span class="text-green-400 text-sm font-bold animate-pulse">ONLINE</span>
                            </div>
                            <p class="text-sm text-gray-400">Entrada: <?= date('H:i', strtotime($horaEntrada)) ?></p>
                            <p class="text-sm text-gray-400 mt-1">Tempo decorrido: <span id="tempoCarteirinha" class="font-mono text-white">00:00:00</span></p>
                        </div>
                        
                        <form method="POST">
                            <input type="hidden" name="acao_frequencia" value="saida">
                            <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white font-bold py-4 rounded-xl shadow-lg flex items-center justify-center gap-3 transition-all hover:scale-[1.02]">
                                <i data-lucide="log-out" class="w-6 h-6"></i>
                                REGISTRAR SAÍDA
                            </button>
                            <p class="text-xs text-gray-500 text-center mt-2">Finalize seu treino de hoje</p>
                        </form>
                    <?php else: ?>
                        <div class="p-4 bg-gradient-to-r from-gray-800/50 to-gray-900/50 rounded-xl border border-gray-700 text-center">
                            <i data-lucide="check-circle" class="w-12 h-12 text-green-500 mx-auto mb-3"></i>
                            <h4 class="font-bold text-white mb-1">Treino Concluído!</h4>
                            <p class="text-gray-400 text-sm">Volte amanhã para mais progresso</p>
                            <div class="mt-3 p-3 bg-black/30 rounded-lg">
                                <p class="text-xs text-gray-500">Tempo de hoje:</p>
                                <p class="text-xl font-mono text-white font-bold"><?= $tempoHoje ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="mt-6 pt-4 border-t border-white/10">
                    <div class="flex items-center justify-between text-sm">
                        <div class="text-gray-400 flex items-center gap-2">
                            <div class="avatar-iniciais w-8 h-8 bg-gradient-to-br from-orange-500 to-red-600 text-white text-xs font-bold flex items-center justify-center">
                                <?= htmlspecialchars($iniciais) ?>
                            </div>
                            <?= htmlspecialchars($primeiroNome) ?>
                        </div>
                        <span class="bg-tech-primary/20 text-tech-primary px-3 py-1 rounded-full text-xs font-bold">
                            <?= htmlspecialchars($planoAluno) ?>
                        </span>
                    </div>
                    <div class="text-xs text-gray-500 mt-2">
                        <i data-lucide="calendar" class="w-3 h-3 inline mr-1"></i>
                        <?= date('d/m/Y') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL ADICIONAR AO CARRINHO (CORRIGIDO) -->
    <div id="modalAdicionar" class="fixed inset-0 z-50 hidden bg-black/90 flex items-center justify-center p-4">
        <div class="absolute inset-0" onclick="fecharModalAdicionar()"></div>
        <div class="relative z-10 w-full max-w-md mx-auto">
            <div class="bg-gradient-to-br from-gray-900 to-black rounded-2xl border border-white/10 p-6 shadow-2xl">
                <h3 id="modalProdutoNome" class="text-xl font-bold text-white mb-4">Adicionar ao Carrinho</h3>
                <form id="formAdicionar" method="POST">
                    <input type="hidden" name="acao" value="adicionar_carrinho">
                    <input type="hidden" id="modalProdutoId" name="produto_id" value="">
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-400 mb-2">Quantidade</label>
                        <div class="flex items-center gap-3">
                            <button type="button" onclick="alterarQuantidadeModal(-1)" class="w-10 h-10 bg-[#1e293b] border border-white/10 rounded flex items-center justify-center hover:bg-white/5 transition-colors">
                                <i data-lucide="minus" class="w-5 h-5"></i>
                            </button>
                            <input type="number" id="modalQuantidade" name="quantidade" value="1" min="1" class="flex-1 text-center bg-[#0f172a] border border-white/10 rounded py-3 text-lg font-bold">
                            <button type="button" onclick="alterarQuantidadeModal(1)" class="w-10 h-10 bg-[#1e293b] border border-white/10 rounded flex items-center justify-center hover:bg-white/5 transition-colors">
                                <i data-lucide="plus" class="w-5 h-5"></i>
                            </button>
                        </div>
                        <p id="modalEstoqueInfo" class="text-xs text-gray-500 mt-2 text-center">Disponível: <span id="modalEstoque"></span> unidades</p>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="fecharModalAdicionar()" class="flex-1 border border-white/10 text-gray-300 hover:bg-white/5 py-3 rounded-xl transition-all">
                            Cancelar
                        </button>
                        <button type="submit" class="flex-1 bg-tech-primary hover:bg-orange-600 text-white font-bold py-3 rounded-xl shadow-lg transition-all">
                            Adicionar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL PAGAMENTO (CORRIGIDO) -->
    <div id="modalPagamento" class="fixed inset-0 z-50 hidden bg-black/90 flex items-center justify-center p-4">
        <div class="absolute inset-0" onclick="fecharModalPagamento()"></div>
        <div class="relative z-10 w-full max-w-md mx-auto">
            <div class="bg-gradient-to-br from-gray-900 to-black rounded-2xl border border-white/10 p-6 shadow-2xl">
                <h3 class="text-xl font-bold text-white mb-6">Finalizar Compra</h3>
                <form method="POST">
                    <input type="hidden" name="acao" value="finalizar_compra">
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-400 mb-3">Forma de Pagamento</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative">
                                <input type="radio" name="forma_pagamento" value="Cartão" class="sr-only peer" checked>
                                <div class="p-4 border border-white/10 rounded-xl text-center cursor-pointer peer-checked:border-tech-primary peer-checked:bg-tech-primary/10 transition-all">
                                    <i data-lucide="credit-card" class="w-6 h-6 text-white mx-auto mb-2"></i>
                                    <span class="text-sm font-medium text-white">Cartão</span>
                                </div>
                            </label>
                            <label class="relative">
                                <input type="radio" name="forma_pagamento" value="PIX" class="sr-only peer">
                                <div class="p-4 border border-white/10 rounded-xl text-center cursor-pointer peer-checked:border-tech-primary peer-checked:bg-tech-primary/10 transition-all">
                                    <i data-lucide="sparkles" class="w-6 h-6 text-white mx-auto mb-2"></i>
                                    <span class="text-sm font-medium text-white">PIX</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="mb-6 p-4 bg-gradient-to-r from-green-500/10 to-emerald-500/10 rounded-xl border border-green-500/20">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-400">Total a pagar:</span>
                            <span id="modalTotalCompra" class="text-2xl font-bold text-white">R$ 0,00</span>
                        </div>
                        <p class="text-xs text-gray-500">Frete grátis para todo o Brasil</p>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="fecharModalPagamento()" class="flex-1 border border-white/10 text-gray-300 hover:bg-white/5 py-3 rounded-xl transition-all">
                            Cancelar
                        </button>
                        <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl shadow-lg transition-all">
                            Confirmar Compra
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL CONCLUSÃO -->
    <div id="modalConclusao" class="fixed inset-0 z-50 hidden bg-black/90 backdrop-blur-md flex items-center justify-center p-4">
        <div class="absolute inset-0" onclick="fecharModalConclusao()"></div>
        <div class="relative z-10 w-full max-w-md mx-auto">
            <div class="bg-gradient-to-br from-gray-900 to-black rounded-2xl border border-white/10 p-8 shadow-2xl text-center">
                <div class="w-20 h-20 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-glow">
                    <i data-lucide="trophy" class="w-10 h-10 text-white"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-2">Parabéns!</h3>
                <p class="text-gray-400 mb-6">Você completou mais um treino. Continue assim!</p>
                <div class="p-6 bg-gradient-to-r from-green-500/10 to-blue-500/10 rounded-xl border border-green-500/30 mb-6">
                    <p class="text-sm text-gray-500 mb-1">Tempo total de hoje:</p>
                    <p id="tempoFinal" class="text-3xl font-mono text-white font-bold">00:00:00</p>
                </div>
                <form method="POST">
                    <input type="hidden" name="acao_frequencia" value="saida">
                    <button type="submit" class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold py-4 rounded-xl shadow-lg flex items-center justify-center gap-3 transition-all">
                        <i data-lucide="check-circle-2" class="w-6 h-6"></i>
                        FINALIZAR TREINO
                    </button>
                    <p class="text-xs text-gray-500 mt-3">Ao confirmar, sua saída será registrada</p>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Dados dos treinos
        const treinosData = <?php echo json_encode($meusTreinos); ?>;
        const horaEntradaServidor = "<?= $horaEntrada ?>";
        const statusFrequencia = "<?= $statusFrequencia ?>";
        const alunoId = <?= $idAluno ?>;
        const alunoNome = "<?= addslashes($nomeCompleto) ?>";
        const alunoPlano = "<?= addslashes($planoAluno) ?>";
        const iniciais = "<?= $iniciais ?>";
        let tempoInterval;
        let qrCodeInstance = null;
        
        // Variáveis para sincronização do cronômetro
        let tempoInicio = null;
        let tempoDecorridoTotal = 0;
        let cronometroAtivo = false;

        // GERAR QR CODE DINÂMICO
        function gerarQRCode() {
            const qrCodeElement = document.getElementById('qrcode');
            if (!qrCodeElement) return;

            // Limpar QR code anterior se existir
            qrCodeElement.innerHTML = '';

            // Criar dados para o QR Code
            const dadosQR = {
                aluno_id: alunoId,
                nome: alunoNome,
                plano: alunoPlano,
                data: new Date().toISOString(),
                tipo: 'carteirinha_techfit',
                acao: statusFrequencia === 'nao_entrou' ? 'entrada' : (statusFrequencia === 'treinando' ? 'saida' : 'consulta')
            };

            const dadosString = JSON.stringify(dadosQR);

            // Tenta usar a biblioteca; se falhar, gera um QR "falso" (SVG) como fallback
            try {
                if (typeof QRCode !== 'undefined') {
                    qrCodeInstance = new QRCode(qrCodeElement, {
                        text: dadosString,
                        width: 180,
                        height: 180,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });
                    return;
                }
                throw new Error('QRCode lib não disponível');
            } catch (error) {
                console.warn('Gerando QR falso (fallback):', error);
                
                // Função de hashing simples para determinismo
                function hashString(str) {
                    let h = 2166136261 >>> 0;
                    for (let i = 0; i < str.length; i++) {
                        h ^= str.charCodeAt(i);
                        h = Math.imul(h, 16777619) >>> 0;
                    }
                    return h;
                }

                const seed = hashString(dadosString);
                const size = 180;
                const modules = 21;
                const svg = [];
                svg.push(`<svg width="${size}" height="${size}" viewBox="0 0 ${modules} ${modules}" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="QR code falso">`);
                svg.push('<rect width="100%" height="100%" fill="#ffffff"/>');

                // desenha padrões de posição (cantos) para parecer mais real
                function drawFinder(x, y) {
                    for (let yy = 0; yy < 7; yy++) {
                        for (let xx = 0; xx < 7; xx++) {
                            const outer = (xx === 0 || xx === 6 || yy === 0 || yy === 6);
                            const inner = (xx >= 2 && xx <= 4 && yy >= 2 && yy <= 4);
                            const fill = outer || inner ? '#000' : '#fff';
                            svg.push(`<rect x="${x + xx}" y="${y + yy}" width="1" height="1" fill="${fill}"/>`);
                        }
                    }
                }

                drawFinder(0, 0);
                drawFinder(modules - 7, 0);
                drawFinder(0, modules - 7);

                // Preenche o restante com um padrão pseudo-aleatório baseado na seed
                for (let row = 0; row < modules; row++) {
                    for (let col = 0; col < modules; col++) {
                        // pula área dos finders
                        if ((row < 7 && col < 7) || (row < 7 && col >= modules - 7) || (row >= modules - 7 && col < 7)) continue;

                        const bit = ((seed >>> ((row * 5 + col) % 32)) ^ (row * 9176 + col * 3749)) & 1;
                        if (bit) {
                            svg.push(`<rect x="${col}" y="${row}" width="1" height="1" fill="#000"/>`);
                        }
                    }
                }

                svg.push('</svg>');

                qrCodeElement.innerHTML = svg.join('');
            }
        }

        // CORREÇÃO DO CRONÔMETRO - Sincronizado em todos os dispositivos
        function iniciarCronometro() {
            if (statusFrequencia !== 'treinando' || !horaEntradaServidor) {
                if (tempoInterval) clearInterval(tempoInterval);
                return;
            }
            
            // Calcular tempo decorrido desde a entrada
            const entrada = new Date(horaEntradaServidor);
            const agora = new Date();
            
            // Verificar se a data/hora da entrada é válida
            if (isNaN(entrada.getTime())) {
                console.error("Hora de entrada inválida:", horaEntradaServidor);
                return;
            }
            
            const diffMs = agora - entrada;
            
            // Se diffMs for negativo, ajustar
            if (diffMs < 0) {
                console.warn("Tempo negativo detectado, ajustando...");
                tempoDecorridoTotal = 0;
                tempoInicio = new Date();
            } else {
                tempoDecorridoTotal = Math.floor(diffMs / 1000);
                tempoInicio = new Date(agora.getTime() - diffMs);
            }
            
            cronometroAtivo = true;
            
            // Atualizar imediatamente
            atualizarTempo();
            
            // Iniciar intervalo para atualização contínua
            if (tempoInterval) clearInterval(tempoInterval);
            tempoInterval = setInterval(atualizarTempo, 1000);
        }
        
        function atualizarTempo() {
            if (!cronometroAtivo || !tempoInicio) return;
            
            const agora = new Date();
            const diffMs = agora - tempoInicio;
            const segundosTotais = tempoDecorridoTotal + Math.floor(diffMs / 1000);
            
            const horas = Math.floor(segundosTotais / 3600);
            const minutos = Math.floor((segundosTotais % 3600) / 60);
            const segundos = segundosTotais % 60;
            
            const tempoFormatado = `${horas.toString().padStart(2, '0')}:${minutos.toString().padStart(2, '0')}:${segundos.toString().padStart(2, '0')}`;
            
            // Atualizar em todos os lugares
            const tempoTreino = document.getElementById('tempoTreino');
            const tempoDecorridoDash = document.getElementById('tempoDecorridoDash');
            const tempoCarteirinha = document.getElementById('tempoCarteirinha');
            const tempoFinal = document.getElementById('tempoFinal');
            
            if (tempoTreino) tempoTreino.textContent = tempoFormatado;
            if (tempoDecorridoDash) tempoDecorridoDash.textContent = tempoFormatado;
            if (tempoCarteirinha) tempoCarteirinha.textContent = tempoFormatado;
            if (tempoFinal) tempoFinal.textContent = tempoFormatado;
            
            return tempoFormatado;
        }
        
        function pararCronometro() {
            cronometroAtivo = false;
            if (tempoInterval) {
                clearInterval(tempoInterval);
                tempoInterval = null;
            }
        }

        // Função para recriar ícones
        function recriarIcones() {
            setTimeout(() => {
                try {
                    lucide.createIcons();
                    // Garantir que todos os ícones sejam visíveis
                    document.querySelectorAll('[data-lucide]').forEach(icon => {
                        icon.style.display = 'inline-block';
                    });
                } catch (error) {
                    console.log("Recriando ícones...");
                }
            }, 50);
        }

        // VALIDAÇÃO DO FORMULÁRIO DE PERFIL
        function validarNome(input) {
            const valor = input.value.trim();
            const nomeErro = document.getElementById('nomeErro');
            
            // Verifica se não está vazio e se tem pelo menos 2 caracteres que não sejam espaços
            if (valor.length < 2 || /^\s*$/.test(valor)) {
                nomeErro.classList.remove('hidden');
                input.classList.add('border-red-500');
                return false;
            } else {
                nomeErro.classList.add('hidden');
                input.classList.remove('border-red-500');
                return true;
            }
        }

        function mascaraTelefone(input) {
            let v = input.value.replace(/\D/g,"");
            v = v.substring(0, 11);
            if (v.length > 10) {
                v = v.replace(/^(\d{2})(\d{5})(\d{4}).*/, "($1) $2-$3");
            } else if (v.length > 6) {
                v = v.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, "($1) $2-$3");
            } else if (v.length > 2) {
                v = v.replace(/^(\d{2})(\d{0,5})/, "($1) $2");
            } else if (v.length > 0) {
                v = v.replace(/^(\d*)/, "($1");
            }
            input.value = v;
        }

        function validarSenha(input) {
            const valor = input.value;
            const requisitosDiv = document.getElementById('requisitosSenha');
            const btnSalvar = document.getElementById('btnSalvarPerfil');
            
            if (valor.length > 0) {
                requisitosDiv.classList.remove('hidden');
                
                // Verificar cada requisito
                const temLength = valor.length >= 8;
                const temLetter = /[A-Za-z]/.test(valor);
                const temNumber = /\d/.test(valor);
                const temSymbol = /[@$!%*#?&]/.test(valor);
                
                // Atualizar ícones
                document.getElementById('reqLength').querySelector('i').className = `w-3 h-3 ${temLength ? 'text-green-500' : 'text-red-400'}`;
                document.getElementById('reqLetter').querySelector('i').className = `w-3 h-3 ${temLetter ? 'text-green-500' : 'text-red-400'}`;
                document.getElementById('reqNumber').querySelector('i').className = `w-3 h-3 ${temNumber ? 'text-green-500' : 'text-red-400'}`;
                document.getElementById('reqSymbol').querySelector('i').className = `w-3 h-3 ${temSymbol ? 'text-green-500' : 'text-red-400'}`;
                
                const senhaValida = temLength && temLetter && temNumber && temSymbol;
                
                if (senhaValida) {
                    input.classList.remove('border-red-500');
                    input.classList.add('border-green-500');
                    btnSalvar.disabled = false;
                    btnSalvar.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    input.classList.remove('border-green-500');
                    input.classList.add('border-red-500');
                    btnSalvar.disabled = true;
                    btnSalvar.classList.add('opacity-50', 'cursor-not-allowed');
                }
                
                return senhaValida;
            } else {
                requisitosDiv.classList.add('hidden');
                input.classList.remove('border-red-500', 'border-green-500');
                btnSalvar.disabled = false;
                btnSalvar.classList.remove('opacity-50', 'cursor-not-allowed');
                return true; // Senha vazia é permitida (opcional)
            }
        }

        function validarFormPerfil() {
            const nomeValido = validarNome(document.getElementById('nomeInput'));
            const senhaValida = validarSenha(document.getElementById('novaSenhaInput'));
            
            // Se senha foi preenchida, deve ser válida
            const senhaInput = document.getElementById('novaSenhaInput');
            if (senhaInput.value.length > 0 && !senhaValida) {
                exibirToast("A senha não atende aos requisitos!", "erro");
                return false;
            }
            
            if (!nomeValido) {
                exibirToast("Por favor, corrija o nome antes de salvar.", "erro");
                return false;
            }
            
            return true;
        }

        // FUNÇÕES DA LOJA (CORRIGIDAS - sem tremedeira)
        function abrirModalAdicionar(produtoId, produtoNome, estoque) {
            document.getElementById('modalProdutoId').value = produtoId;
            document.getElementById('modalProdutoNome').textContent = produtoNome;
            document.getElementById('modalEstoque').textContent = estoque;
            document.getElementById('modalQuantidade').value = 1;
            document.getElementById('modalQuantidade').max = estoque;
            
            // Atualizar mensagem de estoque
            const estoqueInfo = document.getElementById('modalEstoqueInfo');
            if (estoque < 5) {
                estoqueInfo.innerHTML = `Disponível: <span id="modalEstoque" class="text-red-400">${estoque}</span> unidades (Últimas!)`;
            } else {
                estoqueInfo.innerHTML = `Disponível: <span id="modalEstoque">${estoque}</span> unidades`;
            }
            
            document.getElementById('modalAdicionar').classList.remove('hidden');
            recriarIcones();
        }
        
        function fecharModalAdicionar() {
            document.getElementById('modalAdicionar').classList.add('hidden');
        }
        
        function alterarQuantidadeModal(valor) {
            const input = document.getElementById('modalQuantidade');
            const estoque = parseInt(document.getElementById('modalQuantidade').max);
            let novaQuantidade = parseInt(input.value) + valor;
            
            if (novaQuantidade < 1) novaQuantidade = 1;
            if (novaQuantidade > estoque) novaQuantidade = estoque;
            
            input.value = novaQuantidade;
        }
        
        function abrirModalPagamento(total) {
            document.getElementById('modalTotalCompra').textContent = 'R$ ' + total.toFixed(2).replace('.', ',');
            document.getElementById('modalPagamento').classList.remove('hidden');
            recriarIcones();
        }
        
        function fecharModalPagamento() {
            document.getElementById('modalPagamento').classList.add('hidden');
        }
        
        function abrirModalConclusao() { 
            document.getElementById('modalConclusao').classList.remove('hidden'); 
            recriarIcones();
            // Atualizar tempo no modal
            atualizarTempo();
        }
        
        function fecharModalConclusao() {
            document.getElementById('modalConclusao').classList.add('hidden');
        }
        
        function abrirCarteirinha() { 
            const modal = document.getElementById('modalCarteirinha');
            modal.classList.remove('modal-hidden');
            modal.classList.add('modal-fixed');
            recriarIcones();
            
            // Gerar QR Code quando o modal for aberto
            setTimeout(() => {
                gerarQRCode();
            }, 100);
            
            // Atualizar tempo se estiver treinando
            if (statusFrequencia === 'treinando') {
                atualizarTempo();
            }
        }
        
        function fecharCarteirinha() {
            const modal = document.getElementById('modalCarteirinha');
            modal.classList.remove('modal-fixed');
            modal.classList.add('modal-hidden');
            // Remover parâmetro da URL
            const url = new URL(window.location);
            url.searchParams.delete('open_qr');
            window.history.pushState({}, '', url);
        }
        
        // FUNÇÕES DO CARRINHO (CORRIGIDAS - sem tremedeira)
        function atualizarQuantidadeCarrinho(produtoId, valor) {
            const input = document.getElementById(`quantidade-${produtoId}`);
            if (!input) return;
            
            let novaQuantidade = parseInt(input.value) + valor;
            const max = parseInt(input.max);
            
            if (novaQuantidade < 1) novaQuantidade = 1;
            if (novaQuantidade > max) novaQuantidade = max;
            
            input.value = novaQuantidade;
            
            // Enviar formulário automaticamente após pequeno delay
            setTimeout(() => {
                enviarAtualizacaoCarrinho(produtoId, novaQuantidade);
            }, 300);
        }
        
        function atualizarInputCarrinho(produtoId, estoque) {
            const input = document.getElementById(`quantidade-${produtoId}`);
            if (!input) return;
            
            let novaQuantidade = parseInt(input.value);
            
            if (isNaN(novaQuantidade) || novaQuantidade < 1) novaQuantidade = 1;
            if (novaQuantidade > estoque) novaQuantidade = estoque;
            
            input.value = novaQuantidade;
            
            // Enviar formulário automaticamente
            setTimeout(() => {
                enviarAtualizacaoCarrinho(produtoId, novaQuantidade);
            }, 500);
        }
        
        function enviarAtualizacaoCarrinho(produtoId, quantidade) {
            // Criar formulário dinâmico
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            
            const acaoInput = document.createElement('input');
            acaoInput.type = 'hidden';
            acaoInput.name = 'acao';
            acaoInput.value = 'atualizar_carrinho';
            form.appendChild(acaoInput);
            
            const produtoInput = document.createElement('input');
            produtoInput.type = 'hidden';
            produtoInput.name = 'produto_id';
            produtoInput.value = produtoId;
            form.appendChild(produtoInput);
            
            const quantidadeInput = document.createElement('input');
            quantidadeInput.type = 'hidden';
            quantidadeInput.name = 'quantidade';
            quantidadeInput.value = quantidade;
            form.appendChild(quantidadeInput);
            
            document.body.appendChild(form);
            form.submit();
        }
        
        // Função para exibir mensagens toast
        function exibirToast(msg, tipo) { 
            const div = document.createElement('div'); 
            const cor = tipo === 'erro' ? 'border-red-500 text-red-400' : 'border-green-500 text-white'; 
            div.className = `fixed top-5 right-5 z-50 bg-[#1e293b] border-l-4 ${cor} p-4 rounded-lg shadow-2xl flex items-center gap-3 animate-fadeIn`; 
            div.innerHTML = `<i data-lucide="${tipo === 'erro' ? 'alert-circle' : 'check-circle'}"></i> ${msg}`; 
            document.body.appendChild(div); 
            recriarIcones(); 
            setTimeout(() => div.remove(), 4000); 
        }
        
        // Função para alternar entre abas principais
        function switchTab(tabId) {
            // Verificar se pode acessar treinos
            if (tabId === 'treinos' && statusFrequencia !== 'treinando' && statusFrequencia !== 'finalizado') {
                exibirToast("Você precisa registrar entrada para ver os treinos!", "erro");
                abrirCarteirinha();
                return;
            }
            
            // Esconder todas as abas
            document.querySelectorAll('.tab-content').forEach(el => {
                el.classList.add('hidden');
                el.classList.remove('fade-in');
            });
            
            // Remover estilo ativo de todos os botões
            document.querySelectorAll('.nav-item').forEach(el => { 
                el.classList.remove('bg-tech-primary/10', 'text-tech-primary', 'border-tech-primary/20'); 
                el.classList.add('text-gray-400', 'hover:bg-white/5', 'hover:text-white'); 
            }); 
            
            // Mostrar aba selecionada
            const tabElement = document.getElementById('tab-' + tabId);
            if (tabElement) {
                tabElement.classList.remove('hidden');
                setTimeout(() => {
                    tabElement.classList.add('fade-in');
                }, 10);
            }
            
            // Ativar botão correspondente
            const activeBtn = document.querySelector(`button[onclick="switchTab('${tabId}')"]`);
            if (activeBtn) { 
                activeBtn.classList.remove('text-gray-400', 'hover:bg-white/5', 'hover:text-white'); 
                activeBtn.classList.add('bg-tech-primary/10', 'text-tech-primary', 'border-tech-primary/20'); 
            } 
            
            // Atualizar título da página
            const titulos = {
                'dashboard': 'Dashboard',
                'treinos': 'Meus Treinos',
                'loja': 'Loja',
                'compras': 'Minhas Compras',
                'perfil': 'Meu Perfil'
            };
            const pageTitle = document.getElementById('pageTitle');
            if (pageTitle) {
                pageTitle.textContent = titulos[tabId] || 'TechFit';
            }
            
            // Se for aba de treinos, carregar treino A
            if (tabId === 'treinos' && statusFrequencia === 'treinando') {
                setTimeout(() => mudarFicha('A'), 200);
            }
            
            // Atualizar URL
            const url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            window.history.pushState({}, '', url);
            
            // Recriar ícones
            recriarIcones();
        }

        // Função para alternar sub-aba da loja
        function switchSubTabLoja(subTab) {
            const produtosView = document.getElementById('view-produtos');
            const carrinhoView = document.getElementById('view-carrinho');
            const btnProdutos = document.getElementById('btn-produtos');
            const btnCarrinho = document.getElementById('btn-carrinho');
            
            if (subTab === 'produtos') {
                produtosView.classList.remove('hidden');
                carrinhoView.classList.add('hidden');
                btnProdutos.classList.add('text-tech-primary', 'border-b-2', 'border-tech-primary');
                btnProdutos.classList.remove('text-gray-400');
                btnCarrinho.classList.add('text-gray-400');
                btnCarrinho.classList.remove('text-tech-primary', 'border-b-2', 'border-tech-primary');
            } else {
                produtosView.classList.add('hidden');
                carrinhoView.classList.remove('hidden');
                btnCarrinho.classList.add('text-tech-primary', 'border-b-2', 'border-tech-primary');
                btnCarrinho.classList.remove('text-gray-400');
                btnProdutos.classList.add('text-gray-400');
                btnProdutos.classList.remove('text-tech-primary', 'border-b-2', 'border-tech-primary');
            }
            
            // Atualizar URL
            const url = new URL(window.location);
            url.searchParams.set('sub', subTab);
            window.history.pushState({}, '', url);
            
            // Recriar ícones
            recriarIcones();
        }

        // Função para mudar a ficha de treino
        function mudarFicha(letra) {
            // Atualizar botões ativos
            ['A','B','C'].forEach(l => { 
                const btn = document.getElementById(`btn-${l}`); 
                if (l === letra) {
                    btn.className = 'pb-3 text-sm font-bold text-tech-primary border-b-2 border-tech-primary transition-all whitespace-nowrap px-4 hover:text-orange-400'; 
                } else {
                    btn.className = 'pb-3 text-sm font-bold text-gray-400 hover:text-white transition-all whitespace-nowrap px-4'; 
                }
            });
            
            // Limpar lista de exercícios
            const lista = document.getElementById('listaExercicios'); 
            lista.innerHTML = '';
            
            // Obter exercícios da letra selecionada
            const exercicios = treinosData[letra];
            
            if (!exercicios || exercicios.length === 0) { 
                lista.innerHTML = `
                    <div class="text-center py-12 opacity-50 animate-fade-in">
                        <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gradient-to-br from-gray-800 to-gray-900 flex items-center justify-center">
                            <i data-lucide="coffee" class="w-10 h-10 text-gray-500"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-400 mb-2">Dia de Descanso</h3>
                        <p class="text-gray-500">Aproveite para recuperar as energias!</p>
                    </div>
                `; 
            } else {
                // Adicionar cada exercício
                exercicios.forEach((ex, i) => {
                    const html = `
                        <div class="card flex flex-col md:flex-row md:items-center justify-between gap-4 hover:border-tech-primary/30 transition-all animate-fade-in" style="animation-delay: ${i * 50}ms">
                            <div class="flex items-center gap-4">
                                <label class="relative flex items-center cursor-pointer">
                                    <input type="checkbox" class="peer sr-only check-custom" onchange="toggleExercicio(this)">
                                    <div class="w-6 h-6 border-2 border-gray-500 rounded flex items-center justify-center peer-checked:bg-green-500 peer-checked:border-green-500 transition-all">
                                        <i data-lucide="check" class="w-4 h-4 text-white hidden"></i>
                                    </div>
                                </label>
                                <div>
                                    <h4 class="font-bold text-white text-lg">${ex.exercicio}</h4>
                                    <div class="flex gap-3 mt-1 text-xs text-gray-400">
                                        <span class="bg-white/5 px-2 py-1 rounded border border-white/5 flex items-center gap-1">
                                            <i data-lucide="repeat" class="w-3 h-3 text-tech-primary"></i> ${ex.series}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <button onclick="iniciarDescanso(this)" class="btn-descanso bg-[#0f172a] border border-white/10 text-gray-400 hover:text-white px-4 py-2 rounded-lg text-xs font-bold flex items-center justify-center gap-2 transition-all w-full md:w-auto hover:border-tech-primary/50">
                                <i data-lucide="timer" class="w-4 h-4"></i> 
                                <span class="timer-text">60s</span>
                            </button>
                        </div>
                    `;
                    lista.insertAdjacentHTML('beforeend', html);
                });
            }
            
            // Recriar ícones
            recriarIcones();
        }

        // Sistema de timer para descanso
        let timers = new Map();

        function iniciarDescanso(btn) {
            const span = btn.querySelector('.timer-text');
            
            // Se já está rodando, pausar
            if (btn.classList.contains('running')) {
                clearInterval(timers.get(btn));
                btn.classList.remove('running', 'bg-tech-primary', 'text-white', 'border-transparent');
                btn.classList.add('bg-[#0f172a]', 'text-gray-400');
                
                const tempoAtual = parseInt(span.innerText.replace(/\D/g, ''));
                span.innerText = `Pausado (${tempoAtual}s)`;
                return;
            }

            // Se já finalizado, não fazer nada
            if (btn.classList.contains('bg-green-500')) return;

            // Iniciar timer
            btn.classList.add('running', 'bg-tech-primary', 'text-white', 'border-transparent');
            btn.classList.remove('bg-[#0f172a]', 'text-gray-400');
            
            let tempo = parseInt(span.innerText.replace(/\D/g, '')) || 60;
            if (span.innerText.includes('Descanso')) tempo = 60;

            const interval = setInterval(() => {
                tempo--;
                span.innerText = `${tempo}s`;
                
                if (tempo <= 0) {
                    clearInterval(interval);
                    span.innerText = "FIM";
                    btn.classList.remove('bg-tech-primary', 'running');
                    btn.classList.add('bg-green-500', 'cursor-not-allowed');
                    // Vibração (se suportada)
                    if (navigator.vibrate) navigator.vibrate([200, 100, 200]);
                }
            }, 1000);
            
            timers.set(btn, interval);
        }

        // Função para marcar/desmarcar exercício
        function toggleExercicio(checkbox) { 
            const exercicioDiv = checkbox.closest('.card');
            if (exercicioDiv) {
                exercicioDiv.classList.toggle('opacity-50'); 
                exercicioDiv.classList.toggle('grayscale'); 
            }
        }

        // Função para recolher/expandir sidebar CORRIGIDA
        function toggleSidebar() { 
            const sb = document.getElementById('sidebar');
            const icon = document.getElementById('toggleIcon');
            
            if (sb.classList.contains('w-64')) { 
                sb.classList.remove('w-64'); 
                sb.classList.add('w-20', 'sidebar-collapsed'); 
                icon.setAttribute('data-lucide', 'chevron-right'); 
            } else { 
                sb.classList.remove('w-20', 'sidebar-collapsed'); 
                sb.classList.add('w-64'); 
                icon.setAttribute('data-lucide', 'chevron-left'); 
            } 
            recriarIcones(); 
        }

        // Inicializar página
        document.addEventListener('DOMContentLoaded', () => { 
            // Carregar parâmetros da URL
            const params = new URLSearchParams(window.location.search);
            const tab = params.get('tab') || 'dashboard';
            const subTab = params.get('sub') || 'produtos';
            
            // Configurar abas
            switchTab(tab);
            
            if (tab === 'loja') {
                switchSubTabLoja(subTab);
            }
            
            // NOVO: Se for aba de compras, recriar ícones após carregar
            if (tab === 'compras') {
                setTimeout(() => {
                    recriarIcones();
                }, 300);
            }
            
            // Inicializar validação do nome
            const nomeInput = document.getElementById('nomeInput');
            if (nomeInput) {
                validarNome(nomeInput);
            }
            
            // Inicializar treino A (se necessário)
            if (tab === 'treinos' && statusFrequencia === 'treinando') {
                setTimeout(() => mudarFicha('A'), 200);
            }
            
            // Iniciar cronômetro se estiver treinando
            if (statusFrequencia === 'treinando') {
                iniciarCronometro();
            }
            
            // Gerar QR Code inicial se modal estiver aberto
            <?php if ($abrirQR): ?>
                setTimeout(() => {
                    gerarQRCode();
                }, 300);
            <?php endif; ?>
            
            // Recriar ícones após carregar
            setTimeout(() => {
                recriarIcones();
            }, 300);
            
            // Exibir mensagens
            <?php if ($msgCliente): ?>
                setTimeout(() => {
                    exibirToast("<?= addslashes($msgCliente) ?>", "<?= $tipoMsg ?>");
                }, 500);
            <?php endif; ?>
            
            // Abrir carteirinha se necessário
            <?php if ($abrirQR): ?>
                setTimeout(() => {
                    abrirCarteirinha();
                }, 600);
            <?php endif; ?>
        });
        
        // Limpar intervalo ao fechar a página
        window.addEventListener('beforeunload', () => {
            pararCronometro();
        });
    </script>
</body>
</html>