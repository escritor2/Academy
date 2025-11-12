<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <link rel="icon" href="img/logo-icon.png"> 
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="src/front.css">
    
    <title>TECHFIT: A Academia que Inspira | Planos e Atividades</title>
</head>
<body>
    <header class="cabecalho">
        <div class="cabecalho__logo-busca">
            
            <div class="logo">
                <img src="img/logo-techfit-dark.png" alt="TechFit Logo" class="logo-dark">
                <img src="img/logo-techfit-light.png" alt="TechFit Logo" class="logo-light">
            </div>

            <div class="busca">
                <input type="text" placeholder="Buscar Aulas, Planos ou Produtos" class="busca__input">
                <button class="busca__botao">🔍</button>
            </div>
        </div>
        
        <div class="cabecalho__utilitarios">
            
            <div class="seletor-tema">
                <label for="tema-select" class="label-invisivel">Selecionar Tema</label>
                <select id="tema-select" class="utilitario__botao">
                    <option value="system">🖥️ Sistema</option>
                    <option value="dark">🌙 Escuro</option>
                    <option value="light">☀️ Claro</option>
                    <option value="colorblind">👁️ Daltonismo</option>
                </select>
            </div>

            <span class="utilitario__idioma">PT</span>
            
            <a href="#" class="utilitario__link utilitario__link--icon" id="btn-login" aria-label="Login">👤</a>
            <a href="#" class="utilitario__link utilitario__link--icon" id="btn-cadastro" aria-label="Cadastro">📝</a>
            
            <button id="btn-sandwich" class="sandwich-button" aria-label="Abrir menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <main class="conteudo-principal">
        <section class="hero">
            <div class="hero__conteudo">
                <h1 class="hero__titulo">Transforme Seu Corpo, Eleve Sua Mente</h1>
                <p class="hero__subtitulo">A academia mais moderna de São Paulo. Equipamentos de ponta, instrutores certificados e resultados garantidos.</p>
                <a href="#" class="hero__cta">Comece Agora</a>
            </div>
        </section>

        <section class="secao-atividades">
            <h2 class="secao__titulo">Atividades em Destaque</h2>
            <div class="secao__lista">
                <div class="atividade__item">
                    <h3 style="text-align:center">Treinamento Funcional</h3><br>
                    <div class="atividade__conteudo"><img src="img/ex-funcional.png" alt="Treinamento Funcional"></div>
                    <button class="atividade__navegacao"></button>
                </div>
                
                <div class="atividade__item">
                    <h3 style="text-align:center">Yoga e Alongamento</h3><br>
                    <div class="atividade__conteudo"><img src="img/yoga.png" alt="Yoga e Alongamento"></div>
                    <button class="atividade__navegacao"></button>
                </div>
                
                <div class="atividade__item">
                    <h3 style="text-align:center">Natação/Hidroginástica</h3><br>
                    <div class="atividade__conteudo"><img src="img/natacao.png" alt="Natação/Hidroginástica"></div>
                    <button class="atividade__navegacao"></button>
                </div>
                
                <div class="atividade__item">
                    <h3 style="text-align:center">Boxe Fitness</h3><br>
                    <div class="atividade__conteudo"><img src="img/boxe.png" alt="Boxe Fitness"></div>
                    <button class="atividade__navegacao"></button>
                </div>
            </div>
        </section>

        <section class="secao produtos">
            <h2 class="secao__titulo">Produtos para seu Treino</h2>
            <div class="secao__lista">
                <div class="produto__item">
                    <h3 style="text-align:center">Whey Protein</h3><br>
                    <div class="produto__imagem"><img src="img/whey.png" alt="Whey Protein"></div>
                    <p class="produto__info">Suplemento, Chocolate, R$ 129,90</p>
                </div>
                <div class="produto__item">
                    <h3 style="text-align:center">Garrafa Térmica</h3><br>
                    <div class="produto__imagem"><img src="img/garrafa.png" alt="Garrafa Térmica"></div>
                    <p class="produto__info">Acessório, 1 Litro, R$ 45,00</p>
                </div>
                <div class="produto__item">
                    <h3 style="text-align:center">Kit Elásticos</h3><br>
                    <div class="produto__imagem"><img src="img/kit-elasticos.png" alt="Kit Elásticos"></div>
                    <p class="produto__info">Acessório, 5 Níveis, R$ 89,90</p>
                </div>
                <div class="produto__item">
                    <h3 style="text-align:center">Luva de Treino</h3><br>
                    <div class="produto__imagem"><img src="img/luvas.png" alt="Luva de Treino"></div>
                    <p class="produto__info">Vestuário, Couro, R$ 55,00</p>
                </div>
            </div>
        </section>
    </main>

    <div id="modal-auth" class="modal">
        <div class="modal__conteudo">
            <button class="modal__fechar">&times;</button>
            
            <h3 class="modal__titulo" id="modal-titulo">Acesse sua Conta</h3>
            
            <form id="form-login" class="modal__form">
                <div class="form__grupo">
                    <label for="login-email" class="form__label">Email:</label>
                    <input type="email" id="login-email" class="form__input" required>
                </div>
                <div class="form__grupo">
                    <label for="login-senha" class="form__label">Senha:</label>
                    <input type="password" id="login-senha" class="form__input" required>
                </div>
                <button type="submit" class="form__botao--submit">Entrar</button>
                <p class="form__link-alternativo">
                    Não tem conta? <a href="#" id="link-mudar-cadastro">Cadastre-se aqui</a>
                </p>
            </form>

            <form id="form-cadastro" class="modal__form" style="display:none;">
                 <div class="form__grupo">
                    <label for="cadastro-nome" class="form__label">Nome:</label>
                    <input type="text" id="cadastro-nome" class="form__input" required>
                </div>
                <div class="form__grupo">
                    <label for="cadastro-email" class="form__label">Email:</label>
                    <input type="email" id="cadastro-email" class="form__input" required>
                </div>
                <div class="form__grupo">
                    <label for="cadastro-senha" class="form__label">Senha:</label>
                    <input type="password" id="cadastro-senha" class="form__input" required>
                </div>
                
                <button type="submit" class="form__botao--submit">Criar Conta</button>
                <p class="form__link-alternativo">
                    Já tem conta? <a href="#" id="link-mudar-login">Fazer Login</a>
                </p>
            </form>
        </div>
    </div>
    
    <div id="menu-overlay" class="overlay"></div>
    <nav id="sandwich-menu" class="sandwich-menu">
        <a href="#" class="sandwich-menu__item">Home</a>
        <a href="#" class="sandwich-menu__item">Planos</a>
        <a href="#" class="sandwich-menu__item">Aulas</a>
        <a href="#" class="sandwich-menu__item">Produtos</a>
        <a href="#" class="sandwich-menu__item">Inscrições</a>
    </nav>
    
<script src="src/main.js"></script>
<script src="src/traducao.js"></script>
</body>
</html>