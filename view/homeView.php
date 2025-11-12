<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <link rel="icon" href="img/logo-icon.png"> 
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="src/front.css">
    
    <title data-translate="pageTitle">TECHFIT: A Academia que Inspira | Planos e Atividades</title>
</head>
<body>
    <header class="cabecalho">
        <div class="cabecalho__logo-busca">
            
            <div class="logo">
                <img src="img/logo-techfit-dark.png" alt="TechFit Logo" class="logo-dark">
                <img src="img/logo-techfit-light.png" alt="TechFit Logo" class="logo-light">
            </div>

            <div class="busca">
                <input type="text" placeholder="Buscar Aulas, Planos ou Produtos" class="busca__input" data-translate-placeholder="searchPlaceholder">
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
                    <option value="mono1">🔳 Mono 1 (Grayscale)</option>
                    <option value="mono2">🏁 Mono 2 (Invertido)</option>
                </select>
            </div>

            <span class="utilitario__idioma">PT</span>
            
            <a href="#" class="utilitario__link utilitario__link--icon" id="btn-login" aria-label="Login" title="Acessar sua conta" data-translate-title="loginTooltip">👤</a>
            <a href="#" class="utilitario__link utilitario__link--icon" id="btn-cadastro" aria-label="Cadastro" title="Criar uma nova conta" data-translate-title="registerTooltip">📋</a>
            
            <button id="btn-sandwich" class="sandwich-button" aria-label="Abrir menu" title="Abrir menu de navegação" data-translate-title="menuTooltip">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <main class="conteudo-principal">
        <section class="hero">
            <div class="hero__conteudo">
                <h1 class="hero__titulo" data-translate="heroTitle">Transforme Seu Corpo, Eleve Sua Mente</h1>
                <p class="hero__subtitulo" data-translate="heroSubtitle">A academia mais moderna de São Paulo. Equipamentos de ponta, instrutores certificados e resultados garantidos.</p>
                <a href="#" class="hero__cta" data-translate="heroCta">Comece Agora</a>
            </div>
        </section>

        <section class="secao-atividades">
            <h2 class="secao__titulo" data-translate="featuredActivities">Atividades em Destaque</h2>
            <div class="secao__lista">
                <div class="atividade__item">
                    <h3 style="text-align:center" data-translate="functionalTraining">Treinamento Funcional</h3><br>
                    <div class="atividade__conteudo"><img src="img/ex-funcional.png" alt="Treinamento Funcional"></div>
                    <button class="atividade__navegacao"></button>
                </div>
                
                <div class="atividade__item">
                    <h3 style="text-align:center" data-translate="yogaStretching">Yoga e Alongamento</h3><br>
                    <div class="atividade__conteudo"><img src="img/yoga.png" alt="Yoga e Alongamento"></div>
                    <button class="atividade__navegacao"></button>
                </div>
                
                <div class="atividade__item">
                    <h3 style="text-align:center" data-translate="swimmingHydro">Natação/Hidroginástica</h3><br>
                    <div class="atividade__conteudo"><img src="img/natacao.png" alt="Natação/Hidroginástica"></div>
                    <button class="atividade__navegacao"></button>
                </div>
                
                <div class="atividade__item">
                    <h3 style="text-align:center" data-translate="boxeFitness">Boxe Fitness</h3><br>
                    <div class="atividade__conteudo"><img src="img/boxe.png" alt="Boxe Fitness"></div>
                    <button class="atividade__navegacao"></button>
                </div>
            </div>
        </section>

        <section class="secao produtos">
            <h2 class="secao__titulo" data-translate="trainingProducts">Produtos para seu Treino</h2>
            <div class="secao__lista">
                <div class="produto__item">
                    <h3 style="text-align:center" data-translate="wheyProtein">Whey Protein</h3><br>
                    <div class="produto__imagem"><img src="img/whey.png" alt="Whey Protein"></div>
                    <p class="produto__info"><span data-translate="supplement">Suplemento</span>, Chocolate, R$ 129,90</p>
                </div>
                <div class="produto__item">
                    <h3 style="text-align:center" data-translate="thermicBottle">Garrafa Térmica</h3><br>
                    <div class="produto__imagem"><img src="img/garrafa.png" alt="Garrafa Térmica"></div>
                    <p class="produto__info"><span data-translate="accessory">Acessório</span>, 1 Litro, R$ 45,00</p>
                </div>
                <div class="produto__item">
                    <h3 style="text-align:center" data-translate="elasticKit">Kit Elásticos</h3><br>
                    <div class="produto__imagem"><img src="img/kit-elasticos.png" alt="Kit Elásticos"></div>
                    <p class="produto__info"><span data-translate="accessory">Acessório</span>, 5 Níveis, R$ 89,90</p>
                </div>
                <div class="produto__item">
                    <h3 style="text-align:center" data-translate="trainingGlove">Luva de Treino</h3><br>
                    <div class="produto__imagem"><img src="img/luvas.png" alt="Luva de Treino"></div>
                    <p class="produto__info"><span data-translate="apparel">Vestuário</span>, Couro, R$ 55,00</p>
                </div>
            </div>
        </section>
    </main>

    <div id="modal-auth" class="modal">
        <div class="modal__conteudo">
            <button class="modal__fechar">&times;</button>
            
            <h3 class="modal__titulo" id="modal-titulo" data-translate="modalTitleLogin">Acesse sua Conta</h3>
            
            <form id="form-login" class="modal__form">
                <div class="form__grupo">
                    <label for="login-email" class="form__label" data-translate="modalEmailLabel">Email:</label>
                    <input type="email" id="login-email" class="form__input" required>
                </div>
                <div class="form__grupo">
                    <label for="login-senha" class="form__label" data-translate="modalPasswordLabel">Senha:</label>
                    <input type="password" id="login-senha" class="form__input" required>
                </div>
                <button type="submit" class="form__botao--submit" data-translate="modalLoginButton">Entrar</button>
                <p class="form__link-alternativo">
                    <span data-translate="modalRegisterLinkText">Não tem conta?</span> <a href="#" id="link-mudar-cadastro" data-translate="modalRegisterLinkAction">Cadastre-se aqui</a>
                </p>
            </form>

            <form id="form-cadastro" class="modal__form" style="display:none;">
                 <div class="form__grupo">
                    <label for="cadastro-nome" class="form__label" data-translate="modalNameLabel">Nome:</label>
                    <input type="text" id="cadastro-nome" class="form__input" required>
                </div>
                <div class="form__grupo">
                    <label for="cadastro-email" class="form__label" data-translate="modalEmailLabel">Email:</label>
                    <input type="email" id="cadastro-email" class="form__input" required>
                </div>
                <div class="form__grupo">
                    <label for="cadastro-senha" class="form__label" data-translate="modalPasswordLabel">Senha:</label>
                    <input type="password" id="cadastro-senha" class="form__input" required>
                </div>
                
                <button type="submit" class="form__botao--submit" data-translate="modalRegisterButton">Criar Conta</button>
                <p class="form__link-alternativo">
                    <span data-translate="modalLoginLinkText">Já tem conta?</span> <a href="#" id="link-mudar-login" data-translate="modalLoginLinkAction">Fazer Login</a>
                </p>
            </form>
        </div>
    </div>
    
    <div id="menu-overlay" class="overlay"></div>
    <nav id="sandwich-menu" class="sandwich-menu">
        <a href="#" class="sandwich-menu__item" data-translate="home">Home</a>
        <a href="#" class="sandwich-menu__item" data-translate="plans">Planos</a>
        <a href="#" class="sandwich-menu__item" data-translate="classes">Aulas</a>
        <a href="#" class="sandwich-menu__item" data-translate="products">Produtos</a>
        <a href="#" class="sandwich-menu__item" data-translate="subscriptions">Inscrições</a>
    </nav>
    
    <div vw class="enabled">
        <div vw-access-button class="active"></div>
        <div vw-plugin-wrapper>
            <div class="vw-plugin-top-wrapper"></div>
        </div>
    </div>
    <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
    <script>
        new window.VLibras.Widget('https://vlibras.gov.br/app');
    </script>

    <script src="src/main.js"></script>
    <script src="src/traducao.js"></script>
</body>
</html>