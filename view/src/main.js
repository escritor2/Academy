// --- SCRIPT DO MENU SANDUÍCHE (CORRIGIDO) ---
document.addEventListener('DOMContentLoaded', function() {
    const btnSandwich = document.getElementById('btn-sandwich');
    const sandwichMenu = document.getElementById('sandwich-menu');
    const menuOverlay = document.getElementById('menu-overlay');

    function toggleMenu() {
        const isActive = sandwichMenu.classList.contains('ativo');
        sandwichMenu.classList.toggle('ativo');
        menuOverlay.classList.toggle('ativo');
        
        // Previne scroll do body quando menu está aberto
        document.body.style.overflow = isActive ? 'auto' : 'hidden';
    }

    if (btnSandwich && sandwichMenu && menuOverlay) {
        btnSandwich.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleMenu();
        });
        
        menuOverlay.addEventListener('click', toggleMenu);
        
        // Fecha menu ao clicar em um item
        document.querySelectorAll('.sandwich-menu__item').forEach(item => {
            item.addEventListener('click', toggleMenu);
        });
    }
});
// --- FIM DO SCRIPT DO MENU ---

// Modal
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
        
        modalTitulo.dataset.translate = 'modalTitleLogin'; 
        if (window.applyTranslations) {
            window.applyTranslations(localStorage.getItem('language') || 'pt');
        }
    });
}

if (btnCadastro) {
    btnCadastro.addEventListener('click', (e) => {
        e.preventDefault();
        modal.classList.add('ativo');
        formLogin.style.display = 'none';
        formCadastro.style.display = 'block';
        
        modalTitulo.dataset.translate = 'modalTitleRegister'; 
        if (window.applyTranslations) {
            window.applyTranslations(localStorage.getItem('language') || 'pt');
        }
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
        
        modalTitulo.dataset.translate = 'modalTitleRegister';
        if (window.applyTranslations) {
            window.applyTranslations(localStorage.getItem('language') || 'pt');
        }
    });
}

if (linkMudarLogin) {
    linkMudarLogin.addEventListener('click', (e) => {
        e.preventDefault();
        formLogin.style.display = 'block';
        formCadastro.style.display = 'none';
        
        modalTitulo.dataset.translate = 'modalTitleLogin';
        if (window.applyTranslations) {
            window.applyTranslations(localStorage.getItem('language') || 'pt');
        }
    });
}

// Scroll effect no header
window.addEventListener('scroll', () => {
    const header = document.querySelector('.cabecalho');
    if (window.scrollY > 50) {
        header.style.padding = '0.5rem 5%';
    } else {
        header.style.padding = '1rem 5%';
    }
});

// --- SCRIPT DE TEMA ---
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
// --- FIM DO SCRIPT DE TEMA ---

// --- SCRIPT DO CARROSSEL ---
document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('.carousel-button');

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            const carouselId = button.dataset.carousel;
            const carouselList = document.getElementById(carouselId);
            
            if (carouselList) {
                const carouselWrapper = carouselList.parentElement;
                
                // Calcula o quanto rolar
                const firstItem = carouselList.querySelector('.atividade__item, .produto__item');
                if (!firstItem) return;

                const scrollAmount = firstItem.offsetWidth + 32; 
                const direction = button.classList.contains('prev') ? -1 : 1;

                carouselWrapper.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
            }
        });
    });
});
// --- FIM DO SCRIPT DO CARROSSEL ---

// --- EFEITOS PREMIUM MELHORADOS ---
document.addEventListener('DOMContentLoaded', function() {
    // Sistema de partículas para cards
    const cards = document.querySelectorAll('.produto__item, .atividade__item');
    
    cards.forEach(card => {
        card.addEventListener('mouseenter', function(e) {
            createCardParticles(e, this);
        });
        
        // Adiciona scanner
        const scanner = document.createElement('div');
        scanner.className = 'scanner';
        this.appendChild(scanner);
    });
    
    function createCardParticles(e, element) {
        const particles = 12;
        const rect = element.getBoundingClientRect();
        
        for (let i = 0; i < particles; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            
            // Posição aleatória dentro do card
            const x = Math.random() * rect.width;
            const y = Math.random() * rect.height;
            
            // Velocidade e direção aleatórias
            const angle = Math.random() * Math.PI * 2;
            const speed = 1 + Math.random() * 2;
            const vx = Math.cos(angle) * speed;
            const vy = Math.sin(angle) * speed;
            
            // Tamanho e cor aleatórios
            const size = 2 + Math.random() * 4;
            const hue = 15 + Math.random() * 30; // Tons laranja
            const saturation = 80 + Math.random() * 20;
            const lightness = 50 + Math.random() * 20;
            
            particle.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                background: hsl(${hue}, ${saturation}%, ${lightness}%);
                border-radius: 50%;
                pointer-events: none;
                z-index: 10000;
                left: ${x}px;
                top: ${y}px;
                --tx: ${vx};
                --ty: ${vy};
                animation: particleExplosion 1.2s ease-out forwards;
                box-shadow: 0 0 10px hsl(${hue}, ${saturation}%, ${lightness}%);
            `;
            
            element.appendChild(particle);
            
            setTimeout(() => {
                if (particle.parentNode) {
                    particle.remove();
                }
            }, 1200);
        }
    }
    
    // Efeito de ripple nos botões
    const buttons = document.querySelectorAll('.utilitario__botao, .utilitario__link, .hero__cta, .form__botao--submit');
    
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            createRipple(e, this);
        });
    });
    
    function createRipple(e, element) {
        const ripple = document.createElement('div');
        const rect = element.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        
        ripple.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 50%;
            pointer-events: none;
            left: ${x}px;
            top: ${y}px;
            transform: scale(0);
            animation: ripple 0.6s ease-out forwards;
        `;
        
        element.style.position = 'relative';
        element.style.overflow = 'hidden';
        element.appendChild(ripple);
        
        setTimeout(() => {
            ripple.remove();
        }, 600);
    }
    
    // Efeito de digitação para o hero
    const heroTitle = document.querySelector('.hero__titulo');
    if (heroTitle) {
        // Remove a animação de digitação após completar
        setTimeout(() => {
            heroTitle.style.animation = 'glow 2s ease-in-out infinite alternate';
            heroTitle.style.borderRight = 'none';
        }, 3500);
    }
    
    // Efeito parallax suave no hero
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const hero = document.querySelector('.hero');
        if (hero) {
            hero.style.transform = `translateY(${scrolled * 0.5}px)`;
        }
    });
    
    // Intersection Observer para animação dos cards
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observa todos os cards
    document.querySelectorAll('.produto__item, .atividade__item').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
});

// Adiciona estilos CSS para os novos efeitos
const premiumStyles = document.createElement('style');
premiumStyles.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    .produto__item, .atividade__item {
        transform-style: preserve-3d;
        perspective: 1000px;
    }
    
    .produto__conteudo, .atividade__conteudo {
        transform: translateZ(20px);
    }
    
    /* Efeito de loading para imagens */
    .produto__imagem img, .atividade__imagem img {
        transition: filter 0.3s ease;
    }
    
    .produto__imagem img.loading, .atividade__imagem img.loading {
        filter: blur(10px);
    }
`;
document.head.appendChild(premiumStyles);
