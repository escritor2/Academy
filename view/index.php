<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH );
require __DIR__ . '/../controller/HomeController.php';

switch ($uri){
    case "/":
        homeControl();
        break;
    default:
        echo "Erro 404 - Página não encontrada";
        break;
        
}


?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TECHFIT: A Academia que Inspira | Planos e Atividades</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
</head>
<style>
       * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #FF6B35;
            --secondary: #004E89;
            --accent: #F7B801;
            --dark: #1A1A2E;
            --light: #FFFFFF;
            --gradient-1: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-2: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --gradient-3: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark);
            color: var(--light);
            overflow-x: hidden;
        }

        /* Header com glassmorphism */
        .cabecalho {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 1rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(26, 26, 46, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .cabecalho:hover {
            background: rgba(26, 26, 46, 0.95);
        }

        .cabecalho__logo-busca {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .logo {
            width: 60px;
            height: 60px;
            background: var(--gradient-1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 1.5rem;
            transition: transform 0.3s ease;
        }

        .logo:hover {
            transform: rotate(360deg) scale(1.1);
        }

        .busca {
            position: relative;
            display: flex;
            align-items: center;
        }

        .busca__input {
            padding: 0.8rem 3rem 0.8rem 1.5rem;
            border: 2px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50px;
            color: var(--light);
            font-size: 0.9rem;
            width: 300px;
            transition: all 0.3s ease;
        }

        .busca__input:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.1);
            width: 400px;
        }

        .busca__botao {
            position: absolute;
            right: 5px;
            background: var(--gradient-2);
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .busca__botao:hover {
            transform: scale(1.1);
        }

        .menu {
            display: flex;
            gap: 2rem;
        }

        .menu__item {
            color: var(--light);
            text-decoration: none;
            font-weight: 600;
            position: relative;
            transition: color 0.3s ease;
        }

        .menu__item::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 3px;
            background: var(--gradient-2);
            transition: width 0.3s ease;
        }

        .menu__item:hover {
            color: var(--primary);
        }

        .menu__item:hover::after {
            width: 100%;
        }

        .cabecalho__utilitarios {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .utilitario__botao, .utilitario__link {
            padding: 0.6rem 1.2rem;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 50px;
            color: var(--light);
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .utilitario__botao:hover, .utilitario__link:hover {
            background: var(--gradient-2);
            border-color: transparent;
            transform: translateY(-2px);
        }

        #btn-cadastro {
            background: var(--gradient-2);
            border: none;
        }

        /* Hero Section */
        .conteudo-principal {
            margin-top: 100px;
            padding: 3rem 5%;
        }

        .hero {
            height: 70vh;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--gradient-1);
            border-radius: 30px;
            padding: 4rem;
            margin-bottom: 4rem;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 600px;
            height: 600px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(180deg); }
        }

        .hero__conteudo {
            z-index: 1;
            max-width: 600px;
        }

        .hero__titulo {
            font-size: 4rem;
            font-weight: 900;
            margin-bottom: 1rem;
            line-height: 1.1;
            animation: slideInLeft 0.8s ease;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .hero__subtitulo {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .hero__cta {
            display: inline-block;
            padding: 1rem 3rem;
            background: white;
            color: var(--dark);
            border-radius: 50px;
            text-decoration: none;
            font-weight: 800;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .hero__cta:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
        }

        /* Seções */
        .secao__titulo {
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 2rem;
            text-align: center;
            background: var(--gradient-2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .secao__lista {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        /* Atividades com hover effect incrível */
        .atividade__item {
            height: 350px;
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            cursor: pointer;
            transition: all 0.5s ease;
        }

        .atividade__item:nth-child(1) { background: var(--gradient-1); }
        .atividade__item:nth-child(2) { background: var(--gradient-2); }
        .atividade__item:nth-child(3) { background: var(--gradient-3); }
        .atividade__item:nth-child(4) { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }

        .atividade__item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .atividade__item:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
        }

        .atividade__item:hover::before {
            background: rgba(0, 0, 0, 0);
        }

        .atividade__conteudo {
            position: absolute;
            bottom: 2rem;
            left: 2rem;
            font-size: 1.8rem;
            font-weight: 800;
            z-index: 1;
            text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.5);
        }

        .atividade__navegacao {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 2;
        }

        .atividade__navegacao::after {
            content: '→';
            font-size: 1.5rem;
            color: white;
        }

        .atividade__navegacao:hover {
            background: rgba(255, 255, 255, 0.4);
            transform: rotate(90deg);
        }

        /* Produtos com cards modernos */
        .produto__item {
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .produto__item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .produto__item:hover::before {
            left: 100%;
        }

        .produto__item:hover {
            border-color: var(--primary);
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(255, 107, 53, 0.3);
        }

        .produto__imagem {
            height: 200px;
            background: var(--gradient-2);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        .produto__info {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        /* Modal moderno */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal.ativo {
            display: flex;
        }

        .modal__conteudo {
            background: rgba(26, 26, 46, 0.95);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 3rem;
            width: 90%;
            max-width: 500px;
            position: relative;
            animation: slideInUp 0.4s ease;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal__fechar {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .modal__fechar:hover {
            background: var(--gradient-2);
            transform: rotate(90deg);
        }

        .modal__titulo {
            font-size: 2rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .form__grupo {
            margin-bottom: 1.5rem;
        }

        .form__label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .form__input {
            width: 100%;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form__input:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.1);
        }

        .form__botao--submit {
            width: 100%;
            padding: 1rem;
            background: var(--gradient-2);
            border: none;
            border-radius: 15px;
            color: white;
            font-size: 1.1rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .form__botao--submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(245, 87, 108, 0.4);
        }

        .form__link-alternativo {
            text-align: center;
            margin-top: 1rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .form__link-alternativo a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .menu {
                display: none;
            }
            
            .hero__titulo {
                font-size: 2.5rem;
            }
            
            .secao__lista {
                grid-template-columns: 1fr;
            }
        }
</style>
<body>
    <header class="cabecalho">
        <div class="cabecalho__logo-busca">
            <a href="/" title="Página Inicial TechFit">
                <div class="logo"></div>
            </a>
            <div class="busca">
                <input type="text" placeholder="Buscar Aulas, Planos ou Produtos" class="busca__input">
                <button class="busca__botao">🔍</button>
            </div>
        </div>
        
        <nav class="menu">
            <a href="#" class="menu__item">Home</a>
            <a href="#" class="menu__item">Planos</a>
            <a href="#" class="menu__item">Aulas</a>
            <a href="#" class="menu__item">Produtos</a>
            <a href="#" class="menu__item">Inscrições</a>
        </nav>
        
        <div class="cabecalho__utilitarios">
            <button class="utilitario__botao" id="btn-modo">🌙 Modo</button>
            <span class="utilitario__idioma">PT</span>
            <a href="#" class="utilitario__link" id="btn-login">Login</a>
            <a href="#" class="utilitario__link" id="btn-cadastro">Cadastro</a>
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
                    <div class="atividade__conteudo">Treinamento Funcional</div>
                    <button class="atividade__navegacao"></button>
                </div>
                
                <div class="atividade__item">
                    <div class="atividade__conteudo">Yoga e Alongamento</div>
                    <button class="atividade__navegacao"></button>
                </div>
                
                <div class="atividade__item">
                    <div class="atividade__conteudo">Natação/Hidroginástica</div>
                    <button class="atividade__navegacao"></button>
                </div>
                
                <div class="atividade__item">
                    <div class="atividade__conteudo">Boxe Fitness</div>
                    <button class="atividade__navegacao"></button>
                </div>
            </div>
        </section>

        <section class="secao produtos">
            <h2 class="secao__titulo">Produtos para seu Treino</h2>
            <div class="secao__lista">
                <div class="produto__item">
                    <div class="produto__imagem">💪 Whey Protein</div>
                    <p class="produto__info">Suplemento, Chocolate, R$ 129,90</p>
                </div>
                <div class="produto__item">
                    <div class="produto__imagem">🥤 Garrafa Térmica</div>
                    <p class="produto__info">Acessório, 1 Litro, R$ 45,00</p>
                </div>
                <div class="produto__item">
                    <div class="produto__imagem">🏋️ Kit Elásticos</div>
                    <p class="produto__info">Acessório, 5 Níveis, R$ 89,90</p>
                </div>
                <div class="produto__item">
                    <div class="produto__imagem">🧤 Luva de Treino</div>
                    <p class="produto__info">Vestuário, Couro, R$ 55,00</p>
                </div>
            </div>
        </section>
    </main>

    <div id="modal-auth" class="modal">
        <div class="modal__conteudo">
            <button class="modal__fechar">&times;</button>
            
            <h3 class="modal__titulo">Acesse sua Conta</h3>
            
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
    <script>
     // Modal
        const modal = document.getElementById('modal-auth');
        const btnLogin = document.getElementById('btn-login');
        const btnCadastro = document.getElementById('btn-cadastro');
        const btnFechar = document.querySelector('.modal__fechar');
        const formLogin = document.getElementById('form-login');
        const formCadastro = document.getElementById('form-cadastro');
        const linkMudarCadastro = document.getElementById('link-mudar-cadastro');
        const linkMudarLogin = document.getElementById('link-mudar-login');

        btnLogin.addEventListener('click', (e) => {
            e.preventDefault();
            modal.classList.add('ativo');
            formLogin.style.display = 'block';
            formCadastro.style.display = 'none';
        });

        btnCadastro.addEventListener('click', (e) => {
            e.preventDefault();
            modal.classList.add('ativo');
            formLogin.style.display = 'none';
            formCadastro.style.display = 'block';
        });

        btnFechar.addEventListener('click', () => {
            modal.classList.remove('ativo');
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('ativo');
            }
        });

        linkMudarCadastro.addEventListener('click', (e) => {
            e.preventDefault();
            formLogin.style.display = 'none';
            formCadastro.style.display = 'block';
        });

        linkMudarLogin.addEventListener('click', (e) => {
            e.preventDefault();
            formLogin.style.display = 'block';
            formCadastro.style.display = 'none';
        });

        // Scroll effect no header
        window.addEventListener('scroll', () => {
            const header = document.querySelector('.cabecalho');
            if (window.scrollY > 50) {
                header.style.padding = '0.5rem 5%';
            } else {
                header.style.padding = '1rem 5%';
            }
        });

        // Animação nas atividades
        const atividades = document.querySelectorAll('.atividade__item');
        atividades.forEach((ativ, index) => {
            ativ.style.animationDelay = `${index * 0.1}s`;
        });
    </script>
</body>
</html>