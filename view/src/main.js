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
                
                const firstItem = carouselList.querySelector('.atividade__item, .produto__item');
                if (!firstItem) return;

                const scrollAmount = firstItem.offsetWidth + 32; // Largura do item + gap (32px = 2rem)
                const direction = button.classList.contains('prev') ? -1 : 1;

                carouselWrapper.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
            }
        });
    });
});
// --- FIM DO SCRIPT DO CARROSSEL ---


// --- NOVOS SCRIPTS DE NAVEGAÇÃO E PESQUISA (COM CORREÇÃO) ---
document.addEventListener('DOMContentLoaded', () => {
    
    // --- Elementos de Navegação ---
    const navLinks = document.querySelectorAll('#sandwich-menu .sandwich-menu__item');
    const mainSections = document.querySelectorAll('.conteudo-principal > section');
    const allItems = document.querySelectorAll('.atividade__item, .produto__item');

    // --- Elementos da Pesquisa ---
    const searchButton = document.querySelector('.busca__botao');
    const searchInput = document.querySelector('.busca__input');
    const heroSection = document.getElementById('secao-hero'); // Pega o Hero

    // --- Função de Navegação ---
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault(); 
            const targetId = link.dataset.target;

            // 1. Esconde todas as seções
            mainSections.forEach(section => {
                section.style.display = 'none';
            });
            
            // 2. Mostra todos os itens (para resetar qualquer pesquisa)
            allItems.forEach(item => {
                item.style.display = ''; // Reverte para o CSS padrão
            });
            
            // 3. Mostra a(s) seção(ões) alvo
            if (targetId === 'home') {
                mainSections.forEach(section => {
                    section.style.display = 'block';
                });

                // --- !! CORREÇÃO ADICIONADA AQUI !! ---
                // Limpa a barra de pesquisa para resetar o estado.
                if (searchInput) {
                    searchInput.value = ""; 
                }
                // --- FIM DA CORREÇÃO ---

            } else if (targetId) {
                const targetSection = document.getElementById(targetId);
                if (targetSection) {
                    targetSection.style.display = 'block';
                }
            }
            
            toggleMenu(); // Fecha o menu
        });
    });

    // --- Função de Pesquisa (MODIFICADA) ---
    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase().trim();

        if (searchTerm.length > 0) {
            // --- ESTAMOS A PESQUISAR ---
            
            // 1. Esconde o Hero
            if (heroSection) {
                heroSection.style.display = 'none';
            }

            // 2. Mostra as secções de conteúdo (atividades, produtos)
            mainSections.forEach(section => {
                if (section.id !== 'secao-hero') { // Não mexe no hero aqui
                    section.style.display = 'block';
                }
            });

            // 3. Itera e filtra os itens
            allItems.forEach(item => {
                const titleElement = item.querySelector('h3');
                if (titleElement) {
                    const title = titleElement.textContent.toLowerCase();
                    if (title.includes(searchTerm)) {
                        item.style.display = ''; // Mostra
                    } else {
                        item.style.display = 'none'; // Esconde
                    }
                }
            });

            // 4. Esconde secções que ficaram vazias
            mainSections.forEach(section => {
                if (section.id === 'secao-hero') return; // Ignora o hero
                
                const allItemsInSection = section.querySelectorAll('.atividade__item, .produto__item');
                if (allItemsInSection.length === 0) return; 

                let allHidden = true;
                allItemsInSection.forEach(item => {
                    if (item.style.display !== 'none') {
                        allHidden = false; 
                    }
                });

                if (allHidden) {
                    section.style.display = 'none'; 
                }
            });

        } else {
            // --- PESQUISA LIMPA ---
            // Reseta a página (mostra tudo)
            
            // 1. Mostra todas as seções
            mainSections.forEach(section => {
                section.style.display = 'block';
            });

            // 2. Mostra todos os itens
            allItems.forEach(item => {
                item.style.display = '';
            });
        }
    }

    if (searchButton) {
        searchButton.addEventListener('click', performSearch);
    }
    
    if (searchInput) {
        searchInput.addEventListener('input', performSearch); 
    }
});
// --- FIM DOS NOVOS SCRIPTS ---