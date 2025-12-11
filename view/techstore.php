<?php
// techstore.php
session_start();
require_once __DIR__ . '/../Model/ProdutoDAO.php';
require_once __DIR__ . '/../Model/VendaDAO.php';

$produtoDao = new ProdutoDAO();
$vendaDao = new VendaDAO();
$idUsuario = $_SESSION['usuario_id'] ?? 0;

// --- CARRINHO ---
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Mensagens
$msg = '';
$tipoMsg = '';

// Processar ações do carrinho
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
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
            $msg = "Produto adicionado ao carrinho!";
            $tipoMsg = 'sucesso';
        } else {
            $msg = "Estoque insuficiente!";
            $tipoMsg = 'erro';
        }
    }
    
    // REMOVER DO CARRINHO
    if ($_POST['acao'] === 'remover_carrinho') {
        $produto_id = $_POST['produto_id'];
        if (isset($_SESSION['carrinho'][$produto_id])) {
            unset($_SESSION['carrinho'][$produto_id]);
            $msg = "Produto removido do carrinho!";
            $tipoMsg = 'sucesso';
        }
    }
    
    // ATUALIZAR QUANTIDADE
    if ($_POST['acao'] === 'atualizar_carrinho') {
        $produto_id = $_POST['produto_id'];
        $quantidade = $_POST['quantidade'];
        
        if ($quantidade > 0) {
            $_SESSION['carrinho'][$produto_id] = $quantidade;
            $msg = "Carrinho atualizado!";
            $tipoMsg = 'sucesso';
        } else {
            unset($_SESSION['carrinho'][$produto_id]);
            $msg = "Produto removido!";
            $tipoMsg = 'sucesso';
        }
    }
    
    // FINALIZAR COMPRA
   if ($_POST['acao'] === 'finalizar_compra') {
    $forma_pagamento = $_POST['forma_pagamento'];
    $total = 0;
    $erros = [];
    
    // Coletar dados do pagamento baseado na forma escolhida
    $dados_pagamento = [];
    
    if ($forma_pagamento === 'Cartão') {
        $dados_pagamento = [
            'tipo' => 'cartao',
            'nome' => $_POST['nome_cartao'] ?? '',
            'numero' => $_POST['numero_cartao'] ?? '',
            'validade' => $_POST['validade_cartao'] ?? '',
            'cvv' => $_POST['cvv_cartao'] ?? ''
        ];
        
        // Validação básica do cartão
        if (empty($dados_pagamento['nome']) || empty($dados_pagamento['numero']) || 
            empty($dados_pagamento['validade']) || empty($dados_pagamento['cvv'])) {
            $erros[] = "Preencha todos os dados do cartão!";
        }
    } elseif ($forma_pagamento === 'PIX') {
        $dados_pagamento = [
            'tipo' => 'pix',
            'chave' => $_POST['chave_pix'] ?? ''
        ];
        
        if (empty($dados_pagamento['chave'])) {
            $erros[] = "Informe a chave PIX!";
        }
    }
    
    if (empty($erros)) {
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
            
            // Registrar venda com dados de pagamento em JSON
            if ($vendaDao->registrarVenda(
                $idUsuario, 
                $produto_id, 
                $quantidade, 
                $produto['preco'], 
                $forma_pagamento,
                $dados_pagamento
            )) {
                // Atualizar estoque
                $novoEstoque = $produto['estoque'] - $quantidade;
                $produtoDao->atualizarEstoque($produto_id, $novoEstoque);
                $total += $produto['preco'] * $quantidade;
            } else {
                $erros[] = "Erro ao registrar venda do produto {$produto['nome']}";
            }
        }
    }
    
    if (empty($erros)) {
        $_SESSION['carrinho'] = [];
        $msg = "Compra realizada com sucesso! Total: R$ " . number_format($total, 2, ',', '.');
        $tipoMsg = 'sucesso';
    } else {
        $msg = implode("<br>", $erros);
        $tipoMsg = 'erro';
    }
}
}

// --- DADOS ---
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

// Determinar aba atual
$subTab = $_GET['sub'] ?? 'produtos';
?>

<!DOCTYPE html>
<html lang="pt-br" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icons/halter.png">
    <title>Loja TechFit - Suplementos e Equipamentos</title>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * {
            box-sizing: border-box;
        }
        
        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(180deg, #0b1120 0%, #0f172a 100%);
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
        
        /* Toast notification */
        .toast {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 9999;
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        /* Modal styles */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.9);
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .modal-content {
            background: linear-gradient(145deg, #0f172a 0%, #1e293b 100%);
            border-radius: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            max-width: 28rem;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .grid-cols-1 {
                grid-template-columns: 1fr !important;
            }
            
            .text-2xl {
                font-size: 1.5rem;
            }
            
            .text-3xl {
                font-size: 1.75rem;
            }
        }
        
        /* Checkbox custom */
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
        
        /* Quantity input */
        .quantity-btn {
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #1e293b;
            border: 1px solid #334155;
            border-radius: 0.5rem;
            color: white;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .quantity-btn:hover {
            background-color: #2d3748;
            border-color: #ea580c;
        }
        
        .quantity-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* Animação de saída do toast */
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    </style>
</head>
<body class="text-gray-100 min-h-screen">
    <!-- Header -->
    <header class="sticky top-0 z-40 bg-[#111827]/95 backdrop-blur-xl border-b border-white/5">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="bg-gradient-to-br from-orange-500 to-red-600 p-2 rounded-lg shadow-lg">
                        <i data-lucide="shopping-bag" class="w-6 h-6 text-white"></i>
                    </div>
                    <h1 class="text-xl font-bold tracking-wide">TECH<span class="text-orange-500">FIT</span> STORE</h1>
                </div>
                
                <div class="flex items-center gap-4">
                    <a href="../index.php" class="text-gray-400 hover:text-white transition-colors text-sm">
                        <i data-lucide="home" class="w-5 h-5 inline mr-1"></i> Home
                    </a>
                    <?php if(isset($_SESSION['usuario_id'])): ?>
                        <a href="paginacliente.php" class="text-gray-400 hover:text-white transition-colors text-sm">
                            <i data-lucide="user" class="w-5 h-5 inline mr-1"></i> Área do Aluno
                        </a>
                    <?php endif; ?>
                    <button onclick="switchSubTab('carrinho')" class="relative">
                        <div class="p-2 hover:bg-white/5 rounded-lg transition-colors">
                            <i data-lucide="shopping-cart" class="w-6 h-6"></i>
                            <?php if(count($_SESSION['carrinho']) > 0): ?>
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center">
                                    <?= count($_SESSION['carrinho']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="container mx-auto px-4">
            <div class="flex gap-4 border-b border-white/10">
                <button onclick="switchSubTab('produtos')" id="btn-produtos" class="pb-3 text-sm font-bold <?= $subTab == 'produtos' ? 'text-orange-500 border-b-2 border-orange-500' : 'text-gray-400 hover:text-white' ?> transition-all whitespace-nowrap px-4">
                    Produtos
                </button>
                <button onclick="switchSubTab('carrinho')" id="btn-carrinho" class="pb-3 text-sm font-bold <?= $subTab == 'carrinho' ? 'text-orange-500 border-b-2 border-orange-500' : 'text-gray-400 hover:text-white' ?> transition-all whitespace-nowrap px-4">
                    Carrinho (<?= count($_SESSION['carrinho']) ?>)
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Toast Notification -->
        <?php if($msg): ?>
        <div class="toast">
            <div class="card <?= $tipoMsg == 'erro' ? 'border-red-500/50 bg-red-500/10' : 'border-green-500/50 bg-green-500/10' ?>">
                <div class="flex items-center gap-3">
                    <i data-lucide="<?= $tipoMsg == 'erro' ? 'alert-circle' : 'check-circle' ?>" class="w-5 h-5 <?= $tipoMsg == 'erro' ? 'text-red-400' : 'text-green-400' ?>"></i>
                    <span class="text-sm"><?= htmlspecialchars($msg) ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Produtos -->
        <div id="view-produtos" class="<?= $subTab == 'produtos' ? '' : 'hidden' ?> fade-in">
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-white mb-2">Nossos Produtos</h2>
                <p class="text-gray-400">Encontre os melhores suplementos e equipamentos para seu treino</p>
            </div>
            
            <!-- Filtros -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="md:col-span-2">
                    <form method="GET" class="relative">
                        <input type="hidden" name="sub" value="produtos">
                        <input type="text" name="busca_produto" value="<?= htmlspecialchars($termoBuscaProd) ?>" 
                               placeholder="Buscar produtos..." 
                               class="w-full bg-[#0f172a] border border-white/10 rounded-xl pl-12 pr-4 py-3 focus:border-orange-500 outline-none">
                        <button type="submit" class="absolute left-4 top-3 text-gray-500 hover:text-white">
                            <i data-lucide="search" class="w-5 h-5"></i>
                        </button>
                    </form>
                </div>
                <div>
                    <form method="GET">
                        <input type="hidden" name="sub" value="produtos">
                        <input type="hidden" name="busca_produto" value="<?= htmlspecialchars($termoBuscaProd) ?>">
                        <select name="categoria" onchange="this.form.submit()" 
                                class="w-full bg-[#0f172a] border border-white/10 rounded-xl px-4 py-3 focus:border-orange-500 outline-none">
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
            </div>
            
            <!-- Lista de Produtos -->
            <?php if(empty($listaProdutos)): ?>
                <div class="text-center py-12">
                    <i data-lucide="package-x" class="w-20 h-20 mx-auto text-gray-500 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-400 mb-2">Nenhum produto encontrado</h3>
                    <p class="text-gray-500">Tente buscar por outro termo ou categoria</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <?php foreach($listaProdutos as $prod): 
                        // Definir ícone e cores por categoria
                        $icone = 'shopping-bag';
                        $cor = 'text-gray-400';
                        $bg = 'bg-white/5';
                        
                        switch($prod['categoria']) {
                            case 'Suplemento':
                                $icone = 'zap';
                                $cor = 'text-purple-400';
                                $bg = 'bg-purple-500/10';
                                break;
                            case 'Roupa':
                                $icone = 'shirt';
                                $cor = 'text-blue-400';
                                $bg = 'bg-blue-500/10';
                                break;
                            case 'Bebida':
                                $icone = 'glass-water';
                                $cor = 'text-cyan-400';
                                $bg = 'bg-cyan-500/10';
                                break;
                            case 'Equipamento':
                                $icone = 'dumbbell';
                                $cor = 'text-orange-400';
                                $bg = 'bg-orange-500/10';
                                break;
                            case 'Alimento':
                                $icone = 'apple';
                                $cor = 'text-green-400';
                                $bg = 'bg-green-500/10';
                                break;
                            case 'Acessório':
                                $icone = 'watch';
                                $cor = 'text-yellow-400';
                                $bg = 'bg-yellow-500/10';
                                break;
                        }
                        
                        $semEstoque = $prod['estoque'] <= 0;
                        $poucoEstoque = $prod['estoque'] > 0 && $prod['estoque'] < 5;
                    ?>
                    <div class="card <?= $semEstoque ? 'opacity-50' : '' ?>">
                        <!-- Categoria Badge -->
                        <div class="flex justify-between items-start mb-4">
                            <span class="text-xs font-bold uppercase <?= $cor ?>"><?= htmlspecialchars($prod['categoria']) ?></span>
                            <?php if($poucoEstoque): ?>
                                <span class="text-xs bg-red-500/20 text-red-400 px-2 py-1 rounded-full">Últimas unidades</span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Imagem/Ícone do Produto -->
                        <div class="h-40 <?= $bg ?> rounded-xl flex items-center justify-center mb-4 relative">
                            <i data-lucide="<?= $icone ?>" class="w-16 h-16 <?= $cor ?>"></i>
                            <?php if($semEstoque): ?>
                                <div class="absolute inset-0 bg-black/50 rounded-xl flex items-center justify-center">
                                    <span class="bg-red-500 text-white text-sm font-bold px-3 py-1 rounded">ESGOTADO</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Informações do Produto -->
                        <h3 class="font-bold text-white text-lg mb-2"><?= htmlspecialchars($prod['nome']) ?></h3>
                        
                        <?php if(!empty($prod['descricao'])): ?>
                            <p class="text-gray-400 text-sm mb-3 line-clamp-2"><?= htmlspecialchars($prod['descricao']) ?></p>
                        <?php endif; ?>
                        
                        <div class="flex justify-between items-center mt-4">
                            <div>
                                <span class="text-2xl font-bold text-orange-500">R$ <?= number_format($prod['preco'], 2, ',', '.') ?></span>
                                <p class="text-xs text-gray-500">Estoque: <?= $prod['estoque'] ?> un.</p>
                            </div>
                            
                            <?php if($semEstoque): ?>
                                <button disabled class="p-3 bg-gray-700 rounded-lg text-gray-400 cursor-not-allowed">
                                    <i data-lucide="x" class="w-5 h-5"></i>
                                </button>
                            <?php else: ?>
                                <button onclick="abrirModalAdicionar(<?= $prod['id'] ?>, '<?= htmlspecialchars(addslashes($prod['nome'])) ?>', <?= $prod['estoque'] ?>)" 
                                        class="p-3 bg-orange-500 rounded-lg text-white hover:bg-orange-600 transition-colors">
                                    <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Carrinho -->
        <div id="view-carrinho" class="<?= $subTab == 'carrinho' ? '' : 'hidden' ?> fade-in">
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-white mb-2">Seu Carrinho</h2>
                <p class="text-gray-400">Revise seus itens antes de finalizar a compra</p>
            </div>
            
            <?php if(empty($itensCarrinho)): ?>
                <div class="text-center py-12">
                    <i data-lucide="shopping-cart" class="w-24 h-24 mx-auto text-gray-500 mb-6"></i>
                    <h3 class="text-2xl font-bold text-gray-400 mb-3">Seu carrinho está vazio</h3>
                    <p class="text-gray-500 mb-8">Adicione produtos incríveis para começar suas compras!</p>
                    <button onclick="switchSubTab('produtos')" class="btn-primary">
                        <i data-lucide="shopping-bag" class="w-5 h-5 inline mr-2"></i>
                        Explorar Produtos
                    </button>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Itens do Carrinho -->
                    <div class="lg:col-span-2">
                        <div class="card mb-6">
                            <h3 class="text-lg font-bold text-white mb-6">Itens no Carrinho (<?= count($itensCarrinho) ?>)</h3>
                            
                            <div class="space-y-4">
                                <?php foreach($itensCarrinho as $item): ?>
                                <div class="flex flex-col sm:flex-row items-center gap-4 p-4 bg-[#0f172a] rounded-xl border border-white/5">
                                    <div class="w-16 h-16 bg-white/5 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i data-lucide="package" class="w-8 h-8 text-gray-400"></i>
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-bold text-white text-lg mb-1"><?= htmlspecialchars($item['nome']) ?></h4>
                                        <p class="text-orange-500 font-bold">R$ <?= number_format($item['preco'], 2, ',', '.') ?></p>
                                        <p class="text-gray-400 text-sm">Estoque disponível: <?= $item['estoque'] ?></p>
                                    </div>
                                    
                                    <div class="flex items-center gap-4">
                                        <!-- Controle de Quantidade -->
                                        <div class="flex items-center gap-2">
                                            <button type="button" onclick="atualizarQuantidade(<?= $item['id'] ?>, -1)" 
                                                    class="quantity-btn" <?= $item['quantidade'] <= 1 ? 'disabled' : '' ?>>
                                                <i data-lucide="minus" class="w-4 h-4"></i>
                                            </button>
                                            
                                            <input type="number" id="quantidade-<?= $item['id'] ?>" 
                                                   value="<?= $item['quantidade'] ?>" min="1" max="<?= $item['estoque'] ?>" 
                                                   class="w-16 text-center bg-[#0f172a] border border-white/10 rounded py-2"
                                                   onchange="atualizarInput(<?= $item['id'] ?>, <?= $item['estoque'] ?>)">
                                            
                                            <button type="button" onclick="atualizarQuantidade(<?= $item['id'] ?>, 1)" 
                                                    class="quantity-btn" <?= $item['quantidade'] >= $item['estoque'] ? 'disabled' : '' ?>>
                                                <i data-lucide="plus" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                        
                                        <!-- Remover Item -->
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="acao" value="remover_carrinho">
                                            <input type="hidden" name="produto_id" value="<?= $item['id'] ?>">
                                            <button type="submit" class="p-2 text-red-400 hover:bg-red-500/20 rounded-lg transition-colors">
                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                            </button>
                                        </form>
                                    </div>
                                    
                                    <div class="text-right">
                                        <p class="text-gray-400 text-sm">Subtotal</p>
                                        <p class="text-xl font-bold text-white">R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Cupom de Desconto -->
                            <div class="mt-6 pt-6 border-t border-white/5">
                                <form class="flex gap-2">
                                    <input type="text" placeholder="Código do cupom" class="flex-1 bg-[#0f172a] border border-white/10 rounded-lg px-4 py-3">
                                    <button type="button" class="px-6 bg-white/5 border border-white/10 rounded-lg hover:bg-white/10 transition-colors">
                                        Aplicar
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Continuar Comprando -->
                        <button onclick="switchSubTab('produtos')" class="w-full border border-white/10 text-gray-300 hover:bg-white/5 py-3 rounded-xl transition-all">
                            <i data-lucide="arrow-left" class="w-5 h-5 inline mr-2"></i>
                            Continuar Comprando
                        </button>
                    </div>
                    
                    <!-- Resumo do Pedido -->
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
                                
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Desconto</span>
                                    <span class="text-green-400">R$ 0,00</span>
                                </div>
                                
                                <div class="border-t border-white/10 pt-3">
                                    <div class="flex justify-between">
                                        <span class="text-white font-bold text-lg">Total</span>
                                        <span class="text-orange-500 text-2xl font-bold">R$ <?= number_format($totalCarrinho, 2, ',', '.') ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <button onclick="abrirModalPagamento(<?= $totalCarrinho ?>)" 
                                    class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-xl shadow-lg flex items-center justify-center gap-2 transition-all mb-4">
                                <i data-lucide="credit-card" class="w-5 h-5"></i>
                                FINALIZAR COMPRA
                            </button>
                            
                            <p class="text-xs text-gray-500 text-center">
                                <i data-lucide="lock" class="w-3 h-3 inline mr-1"></i>
                                Compra 100% segura
                            </p>
                            
                            <!-- Métodos de Pagamento -->
                            <div class="mt-6 pt-6 border-t border-white/5">
                                <p class="text-gray-400 text-sm mb-3">Métodos de pagamento:</p>
                                <div class="flex gap-2">
                                    <div class="p-2 bg-white/5 rounded-lg">
                                        <i data-lucide="credit-card" class="w-5 h-5 text-gray-400"></i>
                                    </div>
                                    <div class="p-2 bg-white/5 rounded-lg">
                                        <i data-lucide="qr-code" class="w-5 h-5 text-gray-400"></i>
                                    </div>
                                    <div class="p-2 bg-white/5 rounded-lg">
                                        <i data-lucide="smartphone" class="w-5 h-5 text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="mt-12 border-t border-white/5 py-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="bg-gradient-to-br from-orange-500 to-red-600 p-2 rounded-lg">
                            <i data-lucide="dumbbell" class="w-6 h-6 text-white"></i>
                        </div>
                        <span class="text-xl font-bold">TECH<span class="text-orange-500">FIT</span></span>
                    </div>
                    <p class="text-gray-400 text-sm">Sua academia completa com produtos de alta qualidade para otimizar seus resultados.</p>
                </div>
                
                <div>
                    <h4 class="font-bold text-white mb-4">Links Rápidos</h4>
                    <ul class="space-y-2">
                        <li><a href="../index.php" class="text-gray-400 hover:text-white transition-colors text-sm">Início</a></li>
                        <li><a href="paginacliente.php" class="text-gray-400 hover:text-white transition-colors text-sm">Área do Aluno</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors text-sm">Sobre Nós</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors text-sm">Contato</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-bold text-white mb-4">Contato</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li class="flex items-center gap-2">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                            contato@techfit.com
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="phone" class="w-4 h-4"></i>
                            (11) 99999-9999
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                            São Paulo - SP
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="mt-8 pt-8 border-t border-white/5 text-center">
                <p class="text-gray-500 text-sm">© 2024 TechFit Store. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Modal Adicionar ao Carrinho -->
    <div id="modalAdicionar" class="modal-overlay hidden">
        <div class="modal-content p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 id="modalProdutoNome" class="text-xl font-bold text-white">Adicionar ao Carrinho</h3>
                <button onclick="fecharModalAdicionar()" class="p-2 hover:bg-white/5 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5 text-gray-400"></i>
                </button>
            </div>
            
            <form id="formAdicionar" method="POST">
                <input type="hidden" name="acao" value="adicionar_carrinho">
                <input type="hidden" id="modalProdutoId" name="produto_id" value="">
                
                <div class="mb-6">
                    <label class="block text-gray-400 mb-2">Quantidade</label>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="alterarQuantidadeModal(-1)" class="quantity-btn">
                            <i data-lucide="minus" class="w-5 h-5"></i>
                        </button>
                        
                        <input type="number" id="modalQuantidade" name="quantidade" value="1" min="1" 
                               class="flex-1 text-center bg-[#0f172a] border border-white/10 rounded py-3 text-lg font-bold">
                        
                        <button type="button" onclick="alterarQuantidadeModal(1)" class="quantity-btn">
                            <i data-lucide="plus" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <p id="modalEstoqueInfo" class="text-sm text-gray-500 mt-2 text-center">
                        Disponível: <span id="modalEstoque"></span> unidades
                    </p>
                </div>
                
                <div class="flex gap-3">
                    <button type="button" onclick="fecharModalAdicionar()" 
                            class="flex-1 border border-white/10 text-gray-300 hover:bg-white/5 py-3 rounded-xl transition-all">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="flex-1 bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 rounded-xl shadow-lg transition-all">
                        Adicionar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Pagamento -->
    <div id="modalPagamento" class="modal-overlay hidden">
        <div class="modal-content p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-white">Finalizar Compra</h3>
                <button onclick="fecharModalPagamento()" class="p-2 hover:bg-white/5 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5 text-gray-400"></i>
                </button>
            </div>
            
            <form method="POST" id="formPagamento">
                <input type="hidden" name="acao" value="finalizar_compra">
                
                <div class="mb-6">
                    <label class="block text-gray-400 mb-3">Forma de Pagamento</label>
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <label class="relative">
                            <input type="radio" name="forma_pagamento" value="Cartão" class="sr-only peer" checked onchange="mostrarCamposPagamento()">
                            <div class="p-4 border border-white/10 rounded-xl text-center cursor-pointer peer-checked:border-orange-500 peer-checked:bg-orange-500/10 transition-all">
                                <i data-lucide="credit-card" class="w-6 h-6 text-white mx-auto mb-2"></i>
                                <span class="text-sm font-medium text-white">Cartão</span>
                            </div>
                        </label>
                        <label class="relative">
                            <input type="radio" name="forma_pagamento" value="PIX" class="sr-only peer" onchange="mostrarCamposPagamento()">
                            <div class="p-4 border border-white/10 rounded-xl text-center cursor-pointer peer-checked:border-orange-500 peer-checked:bg-orange-500/10 transition-all">
                                <i data-lucide="qr-code" class="w-6 h-6 text-white mx-auto mb-2"></i>
                                <span class="text-sm font-medium text-white">PIX</span>
                            </div>
                        </label>
                    </div>
                    
                    <!-- Campos do Cartão -->
                    <div id="camposCartao">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-gray-400 mb-2">Nome no Cartão</label>
                                <input type="text" name="nome_cartao" placeholder="Nome completo" 
                                       class="w-full bg-[#0f172a] border border-white/10 rounded-xl px-4 py-3" required>
                            </div>
                            
                            <div>
                                <label class="block text-gray-400 mb-2">Número do Cartão</label>
                                <input type="text" name="numero_cartao" placeholder="0000 0000 0000 0000" maxlength="19"
                                       class="w-full bg-[#0f172a] border border-white/10 rounded-xl px-4 py-3" required>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-400 mb-2">Validade</label>
                                    <input type="text" name="validade_cartao" placeholder="MM/AA" maxlength="5"
                                           class="w-full bg-[#0f172a] border border-white/10 rounded-xl px-4 py-3" required>
                                </div>
                                <div>
                                    <label class="block text-gray-400 mb-2">CVV</label>
                                    <input type="text" name="cvv_cartao" placeholder="123" maxlength="4"
                                           class="w-full bg-[#0f172a] border border-white/10 rounded-xl px-4 py-3" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Campos do PIX -->
                    <div id="camposPix" class="hidden">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-gray-400 mb-2">Chave PIX</label>
                                <input type="text" name="chave_pix" placeholder="CPF, e-mail ou telefone" 
                                       class="w-full bg-[#0f172a] border border-white/10 rounded-xl px-4 py-3" required>
                            </div>
                            <div class="p-4 bg-blue-500/10 border border-blue-500/20 rounded-xl">
                                <p class="text-sm text-blue-300">
                                    <i data-lucide="info" class="w-4 h-4 inline mr-2"></i>
                                    Após confirmar a compra, você receberá um QR Code para pagamento
                                </p>
                            </div>
                        </div>
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
                    <button type="button" onclick="fecharModalPagamento()" 
                            class="flex-1 border border-white/10 text-gray-300 hover:bg-white/5 py-3 rounded-xl transition-all">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl shadow-lg transition-all">
                        Confirmar Compra
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Inicializar ícones
        lucide.createIcons();
        
        // Funções da Loja
        function switchSubTab(subTab) {
            const produtosView = document.getElementById('view-produtos');
            const carrinhoView = document.getElementById('view-carrinho');
            const btnProdutos = document.getElementById('btn-produtos');
            const btnCarrinho = document.getElementById('btn-carrinho');
            
            if (subTab === 'produtos') {
                produtosView.classList.remove('hidden');
                carrinhoView.classList.add('hidden');
                btnProdutos.classList.add('text-orange-500', 'border-b-2', 'border-orange-500');
                btnProdutos.classList.remove('text-gray-400');
                btnCarrinho.classList.add('text-gray-400');
                btnCarrinho.classList.remove('text-orange-500', 'border-b-2', 'border-orange-500');
            } else {
                produtosView.classList.add('hidden');
                carrinhoView.classList.remove('hidden');
                btnCarrinho.classList.add('text-orange-500', 'border-b-2', 'border-orange-500');
                btnCarrinho.classList.remove('text-gray-400');
                btnProdutos.classList.add('text-gray-400');
                btnProdutos.classList.remove('text-orange-500', 'border-b-2', 'border-orange-500');
            }
            
            // Atualizar URL
            const url = new URL(window.location);
            url.searchParams.set('sub', subTab);
            window.history.pushState({}, '', url);
            
            // Recriar ícones
            lucide.createIcons();
        }
        
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
            lucide.createIcons();
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
            // Converter para formato brasileiro
            const totalFormatado = total.toFixed(2).replace('.', ',');
            document.getElementById('modalTotalCompra').textContent = 'R$ ' + totalFormatado;
            document.getElementById('modalPagamento').classList.remove('hidden');
            
            // Mostrar campos corretos
            mostrarCamposPagamento();
            lucide.createIcons();
        }
        
        function fecharModalPagamento() {
            document.getElementById('modalPagamento').classList.add('hidden');
        }
        
        function mostrarCamposPagamento() {
            const formaPagamento = document.querySelector('input[name="forma_pagamento"]:checked').value;
            const camposCartao = document.getElementById('camposCartao');
            const camposPix = document.getElementById('camposPix');
            
            if (formaPagamento === 'Cartão') {
                camposCartao.classList.remove('hidden');
                camposPix.classList.add('hidden');
                
                // Tornar campos do cartão obrigatórios
                document.querySelectorAll('#camposCartao input').forEach(input => {
                    input.required = true;
                });
                // Tornar campos PIX não obrigatórios
                document.querySelectorAll('#camposPix input').forEach(input => {
                    input.required = false;
                });
            } else {
                camposCartao.classList.add('hidden');
                camposPix.classList.remove('hidden');
                
                // Tornar campos PIX obrigatórios
                document.querySelectorAll('#camposPix input').forEach(input => {
                    input.required = true;
                });
                // Tornar campos do cartão não obrigatórios
                document.querySelectorAll('#camposCartao input').forEach(input => {
                    input.required = false;
                });
            }
        }
        
        // Funções do Carrinho
        function atualizarQuantidade(produtoId, valor) {
            const input = document.getElementById(`quantidade-${produtoId}`);
            if (!input) return;
            
            let novaQuantidade = parseInt(input.value) + valor;
            const max = parseInt(input.max);
            
            if (novaQuantidade < 1) novaQuantidade = 1;
            if (novaQuantidade > max) novaQuantidade = max;
            
            input.value = novaQuantidade;
            
            // Enviar formulário automaticamente
            setTimeout(() => {
                enviarAtualizacao(produtoId, novaQuantidade);
            }, 300);
        }
        
        function atualizarInput(produtoId, estoque) {
            const input = document.getElementById(`quantidade-${produtoId}`);
            if (!input) return;
            
            let novaQuantidade = parseInt(input.value);
            
            if (isNaN(novaQuantidade) || novaQuantidade < 1) novaQuantidade = 1;
            if (novaQuantidade > estoque) novaQuantidade = estoque;
            
            input.value = novaQuantidade;
            
            // Enviar formulário automaticamente
            setTimeout(() => {
                enviarAtualizacao(produtoId, novaQuantidade);
            }, 500);
        }
        
        function enviarAtualizacao(produtoId, quantidade) {
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
        
        // Formatar número do cartão e validade
        function formatarNumeroCartao(input) {
            let value = input.value.replace(/\D/g, '');
            value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
            input.value = value.substring(0, 19);
        }
        
        function formatarValidadeCartao(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            input.value = value.substring(0, 5);
        }
        
        // Adicionar event listeners para formatação
        document.addEventListener('DOMContentLoaded', function() {
            // Formatar número do cartão
            const numeroCartaoInput = document.querySelector('input[name="numero_cartao"]');
            if (numeroCartaoInput) {
                numeroCartaoInput.addEventListener('input', function(e) {
                    formatarNumeroCartao(e.target);
                });
            }
            
            // Formatar validade do cartão
            const validadeCartaoInput = document.querySelector('input[name="validade_cartao"]');
            if (validadeCartaoInput) {
                validadeCartaoInput.addEventListener('input', function(e) {
                    formatarValidadeCartao(e.target);
                });
            }
            
            // Mostrar campos corretos ao abrir modal de pagamento
            mostrarCamposPagamento();
        });
        
        // Fechar toast automaticamente
        setTimeout(() => {
            const toast = document.querySelector('.toast');
            if (toast) {
                setTimeout(() => {
                    toast.style.animation = 'slideOut 0.3s ease-out';
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            }
        }, 100);
        
        // Inicializar com a aba correta
        document.addEventListener('DOMContentLoaded', () => {
            const subTab = '<?= $subTab ?>';
            if (subTab === 'carrinho') {
                switchSubTab('carrinho');
            }
        });
    </script>
</body>
</html>