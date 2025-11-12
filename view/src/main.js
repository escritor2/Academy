// --- SCRIPT DO MENU SANDUÍCHE ---
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

// --- SCRIPT DE TEMA (LÓGICA SIMPLIFICADA) ---

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


// --- NOVO SCRIPT DO CARROSSEL ---
document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('.carousel-button');

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            const carouselId = button.dataset.carousel;
            const carouselList = document.getElementById(carouselId);
            
            if (carouselList) {
                const carouselWrapper = carouselList.parentElement;
                
                // Calcula o quanto rolar
                // Pega o primeiro item do carrossel para saber a largura
                const firstItem = carouselList.querySelector('.atividade__item, .produto__item');
                if (!firstItem) return;

                // Largura do item + gap (2rem = 32px)
                const scrollAmount = firstItem.offsetWidth + 32; 
                const direction = button.classList.contains('prev') ? -1 : 1;

                carouselWrapper.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
            }
        });
    });
});
// --- FIM DO SCRIPT DO CARROSSEL ---