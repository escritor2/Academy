<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <link rel="icon" href="img/logo-icon.png"> 
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <title>TECHFIT: A Academia que Inspira | Planos e Atividades</title>
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

/* ☀️ TEMA CLARO */
html[data-theme="light"] {
    --primary: #FF6B35;
    --secondary: #004E89;
    --accent: #F7B801;
    --dark: #F4F7FC;
    --light: #1A1A2E;
}
html[data-theme="light"] .cabecalho {
    background: rgba(244, 247, 252, 0.8);
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}
html[data-theme="light"] .cabecalho:hover {
    background: rgba(244, 247, 252, 0.95);
}
html[data-theme="light"] .busca__input {
    border: 2px solid rgba(0, 0, 0, 0.1);
    background: rgba(0, 0, 0, 0.05);
    color: var(--light);
}
html[data-theme="light"] .busca__input::placeholder {
    color: rgba(26, 26, 46, 0.6);
}
html[data-theme="light"] .busca__input:focus {
    background: rgba(0, 0, 0, 0.1);
    border-color: var(--primary);
}
html[data-theme="light"] .sandwich-menu__item {
    color: var(--light);
}
html[data-theme="light"] .sandwich-menu {
    background: var(--dark);
}
html[data-theme="light"] .sandwich-button span {
    background: var(--light);
}
html[data-theme="light"] .utilitario__botao, 
html[data-theme="light"] .utilitario__link,
html[data-theme="light"] #tema-select {
    background: rgba(0, 0, 0, 0.05);
    border: 2px solid rgba(0, 0, 0, 0.1);
    color: var(--light);
}
html[data-theme="light"] .utilitario__idioma {
    color: var(--light);
}
html[data-theme="light"] .utilitario__botao:hover, 
html[data-theme="light"] .utilitario__link:hover,
html[data-theme="light"] #tema-select:hover {
    background: var(--gradient-2);
    border-color: transparent;
    color: #FFFFFF;
}
html[data-theme="light"] #btn-cadastro {
    background: var(--gradient-2);
    border: none;
    color: #FFFFFF;
}
html[data-theme="light"] .produto__item,
html[data-theme="light"] .atividade__item {
    background: rgba(255, 255, 255, 0.8);
    border: 2px solid rgba(0, 0, 0, 0.1);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}
html[data-theme="light"] .produto__item:hover,
html[data-theme="light"] .atividade__item:hover {
     border-color: var(--primary);
     box-shadow: 0 20px 40px rgba(255, 107, 53, 0.3);
}
html[data-theme="light"] .produto__info {
    color: rgba(26, 26, 46, 0.7);
}
html[data-theme="light"] .produto__preco {
    color: var(--primary); 
}
html[data-theme="light"] .modal__conteudo {
    background: rgba(244, 247, 252, 0.98);
    border: 2px solid rgba(0, 0, 0, 0.1);
}
html[data-theme="light"] .modal__fechar {
    background: rgba(0, 0, 0, 0.1);
    color: var(--light);
}
html[data-theme="light"] .modal__fechar:hover {
    background: var(--gradient-2);
    color: #FFFFFF;
}
html[data-theme="light"] .form__input {
    background: rgba(0, 0, 0, 0.05);
    border: 2px solid rgba(0, 0, 0, 0.1);
    color: var(--light);
}
html[data-theme="light"] .form__input:focus {
    background: rgba(0, 0, 0, 0.1);
    border-color: var(--primary);
}
html[data-theme="light"] .form__link-alternativo {
    color: rgba(26, 26, 46, 0.7);
}
html[data-theme="light"] #tema-select {
   background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%231A1A2E%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-13%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2013l128%20128c3.5%203.5%207.8%205.4%2013%205.4s9.4-1.8%2013-5.4l128-128c3.5-3.5%205.4-7.8%205.4-13%200-5-1.8-9.4-5.4-13z%22%2F%3E%3C%2Fsvg%3E');
}
html[data-theme="light"] #tema-select option {
    background: var(--dark);
    color: var(--light);
}
html[data-theme="light"] .logo .logo-light {
    display: block;
}
html[data-theme="light"] .logo .logo-dark {
    display: none;
}

/* 👁️ TEMA DALTONISMO */
html[data-theme="colorblind"] {
    --dark: #000000;  
    --light: #FFFFFF; 
    --primary: var(--accent);
    --secondary: #004E89;
    --accent: #F7B801;
    --gradient-1: var(--secondary);
    --gradient-2: var(--accent);
    --gradient-3: var(--secondary);
    filter: contrast(1.75); 
}

/* 🔳 TEMAS MONOCROMÁTICOS */
html[data-theme="mono1"] {
    filter: grayscale(100%);
}
html[data-theme="mono2"] {
    filter: grayscale(100%) invert(100%);
}

body {
    font-family: 'Inter', sans-serif;
    background: var(--dark);
    color: var(--light);
    overflow-x: hidden;
    transition: background 0.3s ease, color 0.3s ease;
}

/* Header com glassmorphism */
.cabecalho {
    position: fixed;
    top: 0;
    width: 100%;
    padding: 1rem 2%;
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
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.cabecalho__logo-busca {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.logo {
    width: 60px; 
    height: 60px; 
    border-radius: 50%; 
    overflow: hidden; 
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    flex-shrink: 0;
    position: relative;
}
.logo::after {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transform: rotate(0deg);
    transition: transform 0.6s ease;
}
.logo:hover {
    transform: scale(1.15) rotate(360deg);
}
.logo:hover::after {
    transform: rotate(180deg);
}
.logo img {
    height: 100%; 
    width: 100%; 
    object-fit: cover;
    position: relative;
    z-index: 1;
}

.logo .logo-dark {
    display: block;
}
.logo .logo-light {
    display: none;
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
    transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}
.busca__input:focus {
    outline: none;
    border-color: var(--primary);
    background: rgba(255, 255, 255, 0.1);
    width: 400px;
    box-shadow: 0 10px 30px rgba(255, 107, 53, 0.3);
}
.busca__botao {
    position: absolute;
    right: 5px;
    background: var(--gradient-2);
    border: none;
    padding: 0.6rem 1.2rem;
    border-radius: 50px;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    color: #FFFFFF;
}
.busca__botao:hover {
    transform: scale(1.2) rotate(15deg);
    box-shadow: 0 5px 15px rgba(245, 87, 108, 0.5);
}

.menu {
    display: none;
}
.cabecalho__utilitarios {
    display: flex;
    flex-direction: row; 
    gap: 0.75rem;
    align-items: center;
    flex-wrap: wrap; 
    justify-content: flex-end; 
}

.label-invisivel {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

#tema-select {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23FFFFFF%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-13%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2013l128%20128c3.5%203.5%207.8%205.4%2013%205.4s9.4-1.8%2013-5.4l128-128c3.5-3.5%205.4-7.8%205.4-13%200-5-1.8-9.4-5.4-13z%22%2F%3E%3C%2Fsvg%3E');
    background-repeat: no-repeat;
    background-position: right 1.2rem center;
    background-size: 0.8rem;
    padding-right: 3rem; 
}

#tema-select option {
    background: var(--dark);
    color: var(--light);
    font-family: 'Inter', sans-serif;
}

.utilitario__botao, .utilitario__link {
    padding: 0.6rem 1.2rem;
    background: rgba(255, 255, 255, 0.05);
    border: 2px solid rgba(255, 255, 255, 0.1);
    border-radius: 50px;
    color: var(--light);
    text-decoration: none;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    font-weight: 600;
    font-size: 0.9rem;
    position: relative;
    overflow: hidden;
}

.utilitario__botao::before, .utilitario__link::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    transition: width 0.5s ease, height 0.5s ease;
}

.utilitario__botao:hover::before, .utilitario__link:hover::before {
    width: 300px;
    height: 300px;
}

.utilitario__botao:hover, .utilitario__link:hover {
    background: var(--gradient-2);
    border-color: transparent;
    transform: translateY(-3px) scale(1.05);
    color: #FFFFFF;
    box-shadow: 0 10px 25px rgba(245, 87, 108, 0.4);
}
.utilitario__link--icon {
    font-size: 1.2rem;
    line-height: 1;
    padding: 0.6rem; 
}
#btn-cadastro {
    font-size: 1.4rem; 
}

/* MENU SANDUÍCHE */
.sandwich-button {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.1);
    border: 2px solid rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    justify-content: space-around;
    align-items: center;
    padding: 8px;
    cursor: pointer;
    z-index: 2001;
    transition: all 0.3s ease;
}
.sandwich-button:hover {
    background: var(--primary);
    transform: scale(1.1) rotate(90deg);
}
.sandwich-button span {
    width: 100%;
    height: 3px;
    background: var(--light);
    border-radius: 3px;
    transition: all 0.3s ease;
}

.sandwich-menu {
    position: fixed;
    top: 0;
    right: 0; 
    width: 280px;
    height: 100vh;
    background: var(--dark);
    z-index: 3000;
    padding-top: 100px;
    transform: translateX(100%); 
    transition: transform 0.3s ease-in-out;
    border-left: 2px solid rgba(255, 255, 255, 0.1); 
}
.sandwich-menu.ativo {
    transform: translateX(0); 
}
.sandwich-menu__item {
    display: block;
    padding: 1rem 2rem;
    color: var(--light);
    text-decoration: none;
    font-size: 1.2rem;
    font-weight: 600;
    transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    position: relative;
}
.sandwich-menu__item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 4px;
    background: var(--primary);
    transform: scaleY(0);
    transition: transform 0.3s ease;
}
.sandwich-menu__item:hover::before {
    transform: scaleY(1);
}
.sandwich-menu__item:hover {
    background: rgba(255, 107, 53, 0.1);
    color: var(--primary);
    padding-left: 2.5rem;
    transform: translateX(5px);
}

.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(5px);
    z-index: 2999;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease-in-out;
}
.overlay.ativo {
    opacity: 1;
    pointer-events: all;
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
    color: #FFFFFF;
    transition: all 0.5s ease;
}
.hero:hover {
    transform: scale(1.01);
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5);
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
.hero::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -10%;
    width: 400px;
    height: 400px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 50%;
    animation: float 8s ease-in-out infinite reverse;
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
    font-size: 3.5rem;
    font-weight: 900;
    margin-bottom: 1rem;
    line-height: 1.2;
    white-space: normal;
    overflow: hidden;
    border-right: 3px solid #FFFFFF;
    max-width: 100%;
    display: inline-block;
}

.hero__titulo span {
    display: inline-block;
    opacity: 0;
    animation: fadeInChar 0.1s forwards;
}

@keyframes fadeInChar {
    to { opacity: 1; }
}

@keyframes blink {
    0%, 50% { border-color: #FFFFFF; }
    51%, 100% { border-color: transparent; }
}

.hero__subtitulo {
    font-size: 1.3rem;
    margin-bottom: 2rem;
    opacity: 0;
    animation: fadeInUp 1s ease 2s forwards;
}
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 0.9;
        transform: translateY(0);
    }
}
.hero__cta {
    display: inline-block;
    padding: 1rem 3rem;
    background: white;
    color: #1A1A2E;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 800;
    font-size: 1.1rem;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    position: relative;
    overflow: hidden;
    opacity: 0;
    animation: fadeInUp 1s ease 3s forwards;
}
.hero__cta::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(255, 107, 53, 0.3);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    transition: width 0.6s ease, height 0.6s ease;
}
.hero__cta:hover::before {
    width: 300px;
    height: 300px;
}
.hero__cta:hover {
    transform: translateY(-8px) scale(1.1);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
    background: linear-gradient(135deg, #ffffff 0%, #f5f5f5 100%);
}

.secao__titulo {
    font-size: 2.5rem;
    font-weight: 900;
    margin-bottom: 2rem;
    text-align: center;
    background: var(--gradient-2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    position: relative;
    display: inline-block;
    width: 100%;
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}
.secao__titulo::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%) scaleX(0);
    width: 60%;
    height: 4px;
    background: var(--gradient-2);
    border-radius: 2px;
    transition: transform 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}
.secao__titulo:hover::after {
    transform: translateX(-50%) scaleX(1);
}
.secao__titulo:hover {
    transform: scale(1.05);
    filter: brightness(1.3) drop-shadow(0 0 20px rgba(245, 87, 108, 0.5));
}

/* CARROSSEL */
.secao-atividades,
.secao.produtos {
    position: relative; 
    padding: 0 4rem; 
}

.carousel-wrapper {
    overflow-x: auto; 
    scroll-behavior: smooth; 
    scroll-snap-type: x mandatory; 
    scrollbar-width: none;
    -ms-overflow-style: none;
}
.carousel-wrapper::-webkit-scrollbar {
    display: none;
}

.carousel-wrapper .secao__lista {
    display: flex;
    flex-wrap: nowrap;
    gap: 2rem;
    margin-bottom: 0; 
    padding-bottom: 1.5rem; 
}

.carousel-wrapper .atividade__item,
.carousel-wrapper .produto__item {
    scroll-snap-align: start; 
    flex: 0 0 300px;
}

.carousel-button {
    position: absolute;
    top: 55%; 
    transform: translateY(-50%);
    z-index: 10;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    font-size: 2rem;
    font-weight: bold;
    color: white;
    cursor: pointer;
    backdrop-filter: blur(5px);
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}
.carousel-button:hover {
    background: var(--primary);
    transform: translateY(-50%) scale(1.2) rotate(360deg);
    box-shadow: 0 10px 30px rgba(255, 107, 53, 0.6);
}
.carousel-button.prev {
    left: 1rem;
}
.carousel-button.next {
    right: 1rem;
}

.atividade__item {
    border-radius: 20px;
    overflow: hidden;
    position: relative;
    cursor: pointer;
    transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    color: var(--light);
    background: rgba(255, 255, 255, 0.05);
    border: 2px solid rgba(255, 255, 255, 0.1);
    padding: 2rem;
}
.atividade__item::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.6s ease;
    z-index: 1;
}
.atividade__item::after {
    content: '✨';
    position: absolute;
    top: 1rem;
    left: 1rem;
    font-size: 2rem;
    opacity: 0;
    transition: all 0.4s ease;
    z-index: 3;
}
.atividade__item:hover::after {
    opacity: 1;
    transform: rotate(180deg) scale(1.3);
}
.atividade__item:hover {
    transform: translateY(-20px) scale(1.05) rotate(-2deg);
    box-shadow: 0 30px 60px rgba(255, 107, 53, 0.5);
    border-color: var(--primary);
    background: rgba(255, 107, 53, 0.1);
}
.atividade__item:hover::before {
    left: 100%;
}

.atividade__item h3 {
    position: relative;
    z-index: 2;
    transition: all 0.3s ease;
}
.atividade__item:hover h3 {
    color: var(--primary);
    transform: scale(1.05);
    text-shadow: 0 0 10px rgba(255, 107, 53, 0.5);
}

.atividade__conteudo {
    position: relative;
    width: 100%;
    height: 250px;
    border-radius: 15px;
    overflow: hidden;
    margin-bottom: 1rem;
    transition: all 0.4s ease;
    z-index: 2;
}
.atividade__conteudo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: all 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}
.atividade__item:hover .atividade__conteudo img {
    transform: scale(1.2) rotate(3deg);
    filter: brightness(1.3) saturate(1.4) contrast(1.1);
}
.atividade__item:hover .atividade__conteudo {
    box-shadow: 0 15px 40px rgba(255, 107, 53, 0.6);
    border-radius: 20px;
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
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    z-index: 3;
}
.atividade__navegacao::after {
    content: '→';
    font-size: 1.5rem;
    color: white;
    transition: all 0.3s ease;
}
.atividade__navegacao:hover {
    background: var(--primary);
    transform: rotate(90deg) scale(1.2);
    box-shadow: 0 10px 20px rgba(255, 107, 53, 0.5);
}
.atividade__item:hover .atividade__navegacao {
    top: 50%;
    right: 50%;
    transform: translate(50%, -50%) scale(1.4);
}

/* PRODUTOS */
.produto__item {
    background: rgba(255, 255, 255, 0.05);
    border: 2px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 2rem;
    transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
    cursor: pointer;
}
.produto__item::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.6s ease;
}
.produto__item::after {
    content: '💎';
    position: absolute;
    top: 1rem;
    right: 1rem;
    font-size: 2rem;
    opacity: 0;
    transition: all 0.4s ease;
}
.produto__item:hover::after {
    opacity: 1;
    transform: rotate(360deg) scale(1.3);
}
.produto__item:hover::before {
    left: 100%;
}
.produto__item:hover {
    border-color: var(--primary);
    transform: translateY(-15px) scale(1.05) rotate(2deg);
    box-shadow: 0 25px 50px rgba(255, 107, 53, 0.5);
    background: rgba(255, 107, 53, 0.08);
}

.produto__item h3 {
    transition: all 0.3s ease;
}
.produto__item:hover h3 {
    color: var(--primary);
    transform: scale(1.05);
    text-shadow: 0 0 10px rgba(255, 107, 53, 0.5);
}

.produto__imagem {
    height: 200px;
    background: none;
    border-radius: 15px;
    display: block;
    margin-bottom: 1rem;
    overflow: hidden;
    transition: all 0.4s ease;
}
.produto__imagem img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: all 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}
.produto__item:hover .produto__imagem {
    border-radius: 20px;
    box-shadow: 0 15px 30px rgba(255, 107, 53, 0.5);
}
.produto__item:hover .produto__imagem img {
    transform: scale(1.2) rotate(-3deg);
    filter: brightness(1.2) saturate(1.3);
}

.produto__detalhes {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1rem;
    gap: 1rem;
    transition: all 0.3s ease;
}
.produto__item:hover .produto__detalhes {
    transform: translateY(-5px);
}
.produto__info {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.9rem;
    line-height: 1.4;
    transition: all 0.3s ease;
}
.produto__item:hover .produto__info {
    color: rgba(255, 255, 255, 0.95);
    font-weight: 600;
}
.produto__preco {
    color: var(--primary);
    font-size: 1.1rem;
    font-weight: 800;
    white-space: nowrap;
    flex-shrink: 0;
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    position: relative;
}
.produto__preco::before {
    content: '🔥';
    position: absolute;
    left: -25px;
    opacity: 0;
    transition: all 0.3s ease;
}
.produto__item:hover .produto__preco::before {
    opacity: 1;
    left: -30px;
}
.produto__item:hover .produto__preco {
    transform: scale(1.2);
    text-shadow: 0 0 20px rgba(255, 107, 53, 0.8);
    color: #FFD700;
}

/* MODAL */
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
    animation: fadeIn 0.3s ease;
}
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
.modal__conteudo {
    background: rgba(26, 26, 46, 0.95);
    border: 2px solid rgba(255, 255, 255, 0.1);
    border-radius: 30px;
    padding: 3rem;
    width: 90%;
    max-width: 500px;
    position: relative;
    animation: slideInUp 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(50px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
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
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}
.modal__fechar:hover {
    background: var(--gradient-2);
    transform: rotate(180deg) scale(1.2);
    box-shadow: 0 5px 15px rgba(245, 87, 108, 0.5);
}
.modal__titulo {
    font-size: 2rem;
    margin-bottom: 2rem;
    text-align: center;
    color: var(--light);
    background: var(--gradient-2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.form__grupo {
    margin-bottom: 1.5rem;
}
.form__label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--light);
    transition: all 0.3s ease;
}
.form__input:focus + .form__label {
    color: var(--primary);
}
.form__input {
    width: 100%;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.05);
    border: 2px solid rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    color: var(--light);
    font-size: 1rem;
    transition: all 0.3s ease;
}
.form__input:focus {
    outline: none;
    border-color: var(--primary);
    background: rgba(255, 255, 255, 0.1);
    box-shadow: 0 0 20px rgba(255, 107, 53, 0.3);
    transform: translateY(-2px);
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
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    margin-top: 1rem;
    position: relative;
    overflow: hidden;
}
.form__botao--submit::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    transition: width 0.5s ease, height 0.5s ease;
}
.form__botao--submit:hover::before {
    width: 400px;
    height: 400px;
}
.form__botao--submit:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 15px 40px rgba(245, 87, 108, 0.5);
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
    transition: all 0.3s ease;
}
.form__link-alternativo a:hover {
    color: var(--light);
    text-shadow: 0 0 10px rgba(255, 107, 53, 0.5);
}

/* RESPONSIVE */
@media (max-width: 1200px) { 
    .cabecalho {
        flex-wrap: wrap; 
        justify-content: space-between; 
        gap: 1rem; 
    }
    .hero__titulo {
        font-size: 3rem;
    }
}

@media (max-width: 768px) {
    .busca {
        display: none;
    }
    .cabecalho__logo-busca {
        gap: 1rem;
    }
    .cabecalho__utilitarios {
        flex-basis: 100%; 
        justify-content: flex-end; 
        gap: 0.5rem;
    }
     .utilitario__botao, .utilitario__link, #tema-select {
         padding: 0.5rem 0.8rem;
         font-size: 0.8rem;
     }
     .utilitario__link--icon {
         padding: 0.5rem;
     }
     #tema-select {
         padding-right: 2rem;
         background-position: right 0.8rem center;
         background-size: 0.6rem;
     }
     
    .carousel-wrapper .atividade__item,
    .carousel-wrapper .produto__item {
        flex: 0 0 80%;
    }
    .secao-atividades,
    .secao.produtos {
        padding: 0;
    }
    .carousel-button {
        left: 0.5rem;
    }
    .carousel-button.next {
        right: 0.5rem;
    }
    .hero__titulo {
        font-size: 2.5rem;
    }
    .hero {
        height: auto;
        min-height: 50vh;
        padding: 2rem;
    }
}

@media (max-width: 600px) { 
    .cabecalho {
        padding: 0.8rem 2%;
        justify-content: space-between; 
    }
    .cabecalho__logo-busca {
        gap: 0.5rem;
    }
    .busca {
        display: none; 
    }
    .cabecalho__utilitarios {
        flex-basis: auto; 
        gap: 0.5rem;
    }
    #btn-login, #btn-cadastro, .utilitario__idioma {
        display: none;
    }
    .carousel-wrapper .atividade__item,
    .carousel-wrapper .produto__item {
        flex: 0 0 90%;
    }
    .hero__titulo {
        font-size: 1.8rem;
    }
}
    </style>
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
                    <option value="mono1">🔳 Mono 1 (Grayscale)</option>
                    <option value="mono2">⬛ Mono 2 (Invertido)</option>
                </select>
            </div>

            <span class="utilitario__idioma">PT</span>
            
            <a href="#" class="utilitario__link utilitario__link--icon" id="btn-login" aria-label="Login" title="Acessar sua conta">👤</a>
            <a href="#" class="utilitario__link utilitario__link--icon" id="btn-cadastro" aria-label="Cadastro" title="Criar uma nova conta">📋</a>
            
            <button id="btn-sandwich" class="sandwich-button" aria-label="Abrir menu" title="Abrir menu de navegação">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <main class="conteudo-principal">
        <section class="hero">
            <div class="hero__conteudo">
                <h1 class="hero__titulo" id="typing-title"></h1>
                <p class="hero__subtitulo">A academia mais moderna de São Paulo. Equipamentos de ponta, instrutores certificados e resultados garantidos.</p>
                <a href="#" class="hero__cta">Comece Agora</a>
            </div>
        </section>

        <section class="secao-atividades">
            <h2 class="secao__titulo">Atividades em Destaque</h2>
            
            <button class="carousel-button prev" data-carousel="atividades-carousel" aria-label="Anterior">‹</button>
            <div class="carousel-wrapper">
                <div class="secao__lista" id="atividades-carousel">
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
            </div>
            <button class="carousel-button next" data-carousel="atividades-carousel" aria-label="Próximo">›</button>
        </section>

        <section class="secao produtos">
            <h2 class="secao__titulo">Produtos para seu Treino</h2>
            
            <button class="carousel-button prev" data-carousel="produtos-carousel" aria-label="Anterior">‹</button>
            <div class="carousel-wrapper">
                <div class="secao__lista" id="produtos-carousel">
                    <div class="produto__item">
                        <h3 style="text-align:center">Whey Protein</h3><br>
                        <div class="produto__imagem"><img src="img/whey.png" alt="Whey Protein"></div>
                        <div class="produto__detalhes">
                            <p class="produto__info">Suplemento, Chocolate</p>
                            <p class="produto__preco">R$ 129,90</p>
                        </div>
                    </div>
                    
                    <div class="produto__item">
                        <h3 style="text-align:center">Garrafa Térmica</h3><br>
                        <div class="produto__imagem"><img src="img/garrafa.png" alt="Garrafa Térmica"></div>
                        <div class="produto__detalhes">
                            <p class="produto__info">Acessório, 1 Litro</p>
                            <p class="produto__preco">R$ 45,00</p>
                        </div>
                    </div>
                    
                    <div class="produto__item">
                        <h3 style="text-align:center">Kit Elásticos</h3><br>
                        <div class="produto__imagem"><img src="img/kit-elasticos.png" alt="Kit Elásticos"></div>
                        <div class="produto__detalhes">
                            <p class="produto__info">Acessório, 5 Níveis</p>
                            <p class="produto__preco">R$ 89,90</p>
                        </div>
                    </div>
                    
                    <div class="produto__item">
                        <h3 style="text-align:center">Luva de Treino</h3><br>
                        <div class="produto__imagem"><img src="img/luvas.png" alt="Luva de Treino"></div>
                        <div class="produto__detalhes">
                            <p class="produto__info">Vestuário, Couro</p>
                            <p class="produto__preco">R$ 55,00</p>
                        </div>
                    </div>
                </div>
            </div>
            <button class="carousel-button next" data-carousel="produtos-carousel" aria-label="Próximo">›</button>
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

    <script>
// EFEITO DE DIGITAÇÃO NO TÍTULO
const typingTitle = document.getElementById('typing-title');
const text = 'Transforme Seu Corpo, Eleve Sua Mente';
let charIndex = 0;
let isDeleting = false;

function typeEffect() {
    if (!isDeleting && charIndex < text.length) {
        typingTitle.textContent += text.charAt(charIndex);
        charIndex++;
        setTimeout(typeEffect, 100);
    } else if (!isDeleting && charIndex === text.length) {
        setTimeout(() => {
            isDeleting = true;
            typeEffect();
        }, 3000);
    } else if (isDeleting && charIndex > 0) {
        typingTitle.textContent = text.substring(0, charIndex - 1);
        charIndex--;
        setTimeout(typeEffect, 50);
    } else if (isDeleting && charIndex === 0) {
        isDeleting = false;
        setTimeout(typeEffect, 500);
    }
}

// Inicia o efeito após 500ms
setTimeout(typeEffect, 500);

// MENU SANDUÍCHE
const btnSandwich = document.getElementById('btn-sandwich');
const sandwichMenu = document.getElementById('sandwich-menu');
const menuOverlay = document.getElementById('menu-overlay');

function toggleMenu() {
    sandwichMenu.classList.toggle('ativo');
    menuOverlay.classList.toggle('ativo');
}

if (btnSandwich && sandwichMenu && menuOverlay) {
    btnSandwich.addEventListener('click', toggleMenu);
    menuOverlay.addEventListener('click', toggleMenu);
}

// MODAL
const modal = document.getElementById('modal-auth');
const btnLogin = document.getElementById('btn-login');
const btnCadastro = document.getElementById('btn-cadastro');
const btnFechar = document.querySelector('.modal__fechar');
const formLogin = document.getElementById('form-login');
const formCadastro = document.getElementById('form-cadastro');
const linkMudarCadastro = document.getElementById('link-mudar-cadastro');
const linkMudarLogin = document.getElementById('link-mudar-login');
const modalTitulo = document.getElementById('modal-titulo');

if (btnLogin) {
    btnLogin.addEventListener('click', (e) => {
        e.preventDefault();
        modal.classList.add('ativo');
        formLogin.style.display = 'block';
        formCadastro.style.display = 'none';
        modalTitulo.textContent = 'Acesse sua Conta';
    });
}

if (btnCadastro) {
    btnCadastro.addEventListener('click', (e) => {
        e.preventDefault();
        modal.classList.add('ativo');
        formLogin.style.display = 'none';
        formCadastro.style.display = 'block';
        modalTitulo.textContent = 'Crie sua Conta';
    });
}

if (btnFechar) {
    btnFechar.addEventListener('click', () => {
        modal.classList.remove('ativo');
    });
}

if (modal) {
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('ativo');
        }
    });
}

if (linkMudarCadastro) {
    linkMudarCadastro.addEventListener('click', (e) => {
        e.preventDefault();
        formLogin.style.display = 'none';
        formCadastro.style.display = 'block';
        modalTitulo.textContent = 'Crie sua Conta';
    });
}

if (linkMudarLogin) {
    linkMudarLogin.addEventListener('click', (e) => {
        e.preventDefault();
        formLogin.style.display = 'block';
        formCadastro.style.display = 'none';
        modalTitulo.textContent = 'Acesse sua Conta';
    });
}

// SCROLL EFFECT
window.addEventListener('scroll', () => {
    const header = document.querySelector('.cabecalho');
    if (window.scrollY > 50) {
        header.style.padding = '0.5rem 5%';
    } else {
        header.style.padding = '1rem 5%';
    }
});

// TEMA
const themeSelect = document.getElementById('tema-select');
const htmlEl = document.documentElement;

function applyTheme(theme) {
    let themeToApply = theme;
    
    if (theme === 'system') {
        const systemPrefersLight = window.matchMedia('(prefers-color-scheme: light)').matches;
        themeToApply = systemPrefersLight ? 'light' : 'dark';
    }
    
    htmlEl.setAttribute('data-theme', themeToApply);
    localStorage.setItem('theme', theme);
    
    if (themeSelect) {
        themeSelect.value = theme;
    }
}

if (themeSelect) {
    themeSelect.addEventListener('change', (e) => {
        applyTheme(e.target.value);
    });
}

window.matchMedia('(prefers-color-scheme: light)').addEventListener('change', () => {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'system') {
        applyTheme('system');
    }
});

(function onPageLoad() {
    const savedTheme = localStorage.getItem('theme') || 'system';
    applyTheme(savedTheme);
})();

// CARROSSEL
document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('.carousel-button');

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            const carouselId = button.dataset.carousel;
            const carouselList = document.getElementById(carouselId);
            
            if (carouselList) {
                const carouselWrapper = carouselList.parentElement;
                const firstItem = carouselList.querySelector('.atividade__item, .produto__item');
                if (!firstItem) return;

                const scrollAmount = firstItem.offsetWidth + 32;
                const direction = button.classList.contains('prev') ? -1 : 1;

                carouselWrapper.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
            }
        });
    });
});
    </script>
</body>
</html>