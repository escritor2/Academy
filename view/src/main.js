// --- SCRIPT DO MENU SANDUÍCHE ---
const btnSandwich = document.getElementById('btn-sandwich');
const sandwichMenu = document.getElementById('sandwich-menu');
const menuOverlay = document.getElementById('menu-overlay');

// Função para abrir/fechar o menu
function toggleMenu() {
    sandwichMenu.classList.toggle('ativo');
    menuOverlay.classList.toggle('ativo');
}

// Event Listeners para o menu
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
const modalTitulo = document.getElementById('modal-titulo'); // Pega o título do modal

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

/**
 * Aplica o tema selecionado e o salva no localStorage.
 * @param {string} theme - A escolha do usuário no dropdown (ex: 'system', 'light', 'mono1').
 */
function applyTheme(theme) {
    
    let themeToApply = theme;
    
    // Se a escolha for 'system', descobrimos qual tema aplicar
    if (theme === 'system') {
        const systemPrefersLight = window.matchMedia('(prefers-color-scheme: light)').matches;
        themeToApply = systemPrefersLight ? 'light' : 'dark';
    }
    
    // Aplicamos o tema final (ex: 'light', 'dark', 'colorblind', 'mono1', 'mono2')
    // O CSS vai cuidar de todas as regras de estilo com base neste atributo
    htmlEl.setAttribute('data-theme', themeToApply);
    
    // Salvamos a *escolha original* do usuário (ex: 'system')
    localStorage.setItem('theme', theme); 
    
    // Garantimos que o dropdown mostre a *escolha original*
    if (themeSelect) {
        themeSelect.value = theme; 
    }
}

// 1. Ouve mudanças no seletor de tema
if (themeSelect) {
    themeSelect.addEventListener('change', (e) => {
        applyTheme(e.target.value);
    });
}

// 2. Ouve mudanças no tema do *sistema operacional*
window.matchMedia('(prefers-color-scheme: light)').addEventListener('change', () => {
    const savedTheme = localStorage.getItem('theme');
    // Se o usuário tiver escolhido 'system', atualiza o tema
    if (savedTheme === 'system') {
        applyTheme('system');
    }
});

// 3. Aplica o tema salvo ao carregar a página
(function onPageLoad() {
    // Pega o tema salvo, ou usa 'system' como padrão
    const savedTheme = localStorage.getItem('theme') || 'system';
    applyTheme(savedTheme);
})();
// --- FIM DO SCRIPT DE TEMA ---