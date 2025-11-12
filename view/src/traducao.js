// --- SISTEMA DE TRADUÇÃO MULTI-IDIOMA ---

const translations = {
    // 1. Português
    'pt': {
        'pageTitle': 'TECHFIT: A Academia que Inspira | Planos e Atividades',
        // Header
        'searchPlaceholder': 'Buscar Aulas, Planos ou Produtos',
        'loginTooltip': 'Acessar sua conta',
        'registerTooltip': 'Criar uma nova conta',
        'menuTooltip': 'Abrir menu de navegação',
        
        // Menu
        'home': 'Home',
        'plans': 'Planos',
        'classes': 'Aulas',
        'products': 'Produtos',
        'subscriptions': 'Inscrições',
        
        // Hero Section
        'heroTitle': 'Transforme Seu Corpo, Eleve Sua Mente',
        'heroSubtitle': 'A academia mais moderna de São Paulo. Equipamentos de ponta, instrutores certificados e resultados garantidos.',
        'heroCta': 'Comece Agora',
        
        // Activities Section
        'featuredActivities': 'Atividades em Destaque',
        'functionalTraining': 'Treinamento Funcional',
        'yogaStretching': 'Yoga e Alongamento',
        'swimmingHydro': 'Natação/Hidroginástica',
        'boxeFitness': 'Boxe Fitness',
        
        // Products Section
        'trainingProducts': 'Produtos para seu Treino',
        'wheyProtein': 'Whey Protein',
        'thermicBottle': 'Garrafa Térmica',
        'elasticKit': 'Kit Elásticos',
        'trainingGlove': 'Luva de Treino',
        'supplement': 'Suplemento',
        'accessory': 'Acessório',
        'apparel': 'Vestuário',

        // Modal
        'modalTitleLogin': 'Acesse sua Conta',
        'modalTitleRegister': 'Crie sua Conta',
        'modalEmailLabel': 'Email:',
        'modalPasswordLabel': 'Senha:',
        'modalNameLabel': 'Nome:',
        'modalLoginButton': 'Entrar',
        'modalRegisterButton': 'Criar Conta',
        'modalRegisterLinkText': 'Não tem conta?',
        'modalRegisterLinkAction': 'Cadastre-se aqui',
        'modalLoginLinkText': 'Já tem conta?',
        'modalLoginLinkAction': 'Fazer Login',
    },
    // 2. Inglês
    'en': {
        'pageTitle': 'TECHFIT: The Gym that Inspires | Plans & Activities',
        // Header
        'searchPlaceholder': 'Search Classes, Plans, or Products',
        'loginTooltip': 'Access your account',
        'registerTooltip': 'Create a new account',
        'menuTooltip': 'Open navigation menu',
        
        // Menu
        'home': 'Home',
        'plans': 'Plans',
        'classes': 'Classes',
        'products': 'Products',
        'subscriptions': 'Subscriptions',
        
        // Hero Section
        'heroTitle': 'Transform Your Body, Elevate Your Mind',
        'heroSubtitle': 'The most modern gym in São Paulo. Top-tier equipment, certified instructors, and guaranteed results.',
        'heroCta': 'Start Now',
        
        // Activities Section
        'featuredActivities': 'Featured Activities',
        'functionalTraining': 'Functional Training',
        'yogaStretching': 'Yoga & Stretching',
        'swimmingHydro': 'Swimming/Water Aerobics',
        'boxeFitness': 'Fitness Boxing',
        
        // Products Section
        'trainingProducts': 'Products for your Workout',
        'wheyProtein': 'Whey Protein',
        'thermicBottle': 'Thermic Bottle',
        'elasticKit': 'Elastic Bands Kit',
        'trainingGlove': 'Training Gloves',
        'supplement': 'Supplement',
        'accessory': 'Accessory',
        'apparel': 'Apparel',

        // Modal
        'modalTitleLogin': 'Access Your Account',
        'modalTitleRegister': 'Create Your Account',
        'modalEmailLabel': 'Email:',
        'modalPasswordLabel': 'Password:',
        'modalNameLabel': 'Name:',
        'modalLoginButton': 'Login',
        'modalRegisterButton': 'Create Account',
        'modalRegisterLinkText': "Don't have an account?",
        'modalRegisterLinkAction': 'Register here',
        'modalLoginLinkText': 'Already have an account?',
        'modalLoginLinkAction': 'Login here',
    },
    // 3. Espanhol
    'es': {
        'pageTitle': 'TECHFIT: El Gimnasio que Inspira | Planes y Actividades',
        // Header
        'searchPlaceholder': 'Buscar Clases, Planes o Productos',
        'loginTooltip': 'Acceder a su cuenta',
        'registerTooltip': 'Crear una nueva cuenta',
        'menuTooltip': 'Abrir menú de navegación',
        
        // Menu
        'home': 'Inicio',
        'plans': 'Planes',
        'classes': 'Clases',
        'products': 'Productos',
        'subscriptions': 'Inscripciones',
        
        // Hero Section
        'heroTitle': 'Transforma Tu Cuerpo, Eleva Tu Mente',
        'heroSubtitle': 'El gimnasio más moderno de São Paulo. Equipos de última generación, instructores certificados y resultados garantizados.',
        'heroCta': 'Empieza Ahora',
        
        // Activities Section
        'featuredActivities': 'Actividades Destacadas',
        'functionalTraining': 'Entrenamiento Funcional',
        'yogaStretching': 'Yoga y Estiramiento',
        'swimmingHydro': 'Natación/Hidrogimnasia',
        'boxeFitness': 'Boxeo Fitness',
        
        // Products Section
        'trainingProducts': 'Productos para tu Entrenamiento',
        'wheyProtein': 'Whey Protein',
        'thermicBottle': 'Botella Térmica',
        'elasticKit': 'Kit de Elásticos',
        'trainingGlove': 'Guante de Entrenamiento',
        'supplement': 'Suplemento',
        'accessory': 'Accesorio',
        'apparel': 'Vestimenta',

        // Modal
        'modalTitleLogin': 'Accede a tu Cuenta',
        'modalTitleRegister': 'Crea tu Cuenta',
        'modalEmailLabel': 'Correo:',
        'modalPasswordLabel': 'Contraseña:',
        'modalNameLabel': 'Nombre:',
        'modalLoginButton': 'Entrar',
        'modalRegisterButton': 'Crear Cuenta',
        'modalRegisterLinkText': '¿No tienes cuenta?',
        'modalRegisterLinkAction': 'Regístrate aquí',
        'modalLoginLinkText': '¿Ya tienes cuenta?',
        'modalLoginLinkAction': 'Iniciar sesión',
    },

    // --- PREENCHA OS 7 IDIOMAS RESTANTES AQUI ---
    // 4. Francês (Exemplo)
    'fr': {
        'pageTitle': 'TECHFIT: Le gymnase qui inspire...',
        'searchPlaceholder': 'Rechercher des Cours, Plans ou Produits',
        'loginTooltip': 'Accéder à votre compte',
        'registerTooltip': 'Créer un nouveau compte',
        'menuTooltip': 'Ouvrir le menu de navigation',
        'home': 'Accueil',
        // ... (preencha todas as outras chaves)
    },
    // 5. Alemão (Exemplo)
    'de': {
        'pageTitle': 'TECHFIT: Das Fitnessstudio, das inspiriert...',
        'searchPlaceholder': 'Kurse, Pläne oder Produkte suchen',
        // ... (preencha todas as outras chaves)
    },
    // 6. Italiano (Exemplo)
    'it': {
        'pageTitle': 'TECHFIT: La palestra che ispira...',
        // ... (preencha todas as outras chaves)
    },
    // 7. Japonês (Exemplo)
    'ja': {
        'pageTitle': 'TECHFIT: インスピレーションを与えるジム...',
        // ... (preencha todas as outras chaves)
    },
    // 8. Coreano (Exemplo)
    'ko': {
        'pageTitle': 'TECHFIT: 영감을 주는 체육관...',
        // ... (preencha todas as outras chaves)
    },
    // 9. Chinês (Exemplo)
    'zh': {
        'pageTitle': 'TECHFIT: 激励人心的健身房...',
        // ... (preencha todas as outras chaves)
    },
    // 10. Russo (Exemplo)
    'ru': {
        'pageTitle': 'TECHFIT: Спортзал, который вдохновляет...',
        // ... (preencha todas as outras chaves)
    }
};

const languageOptions = [
    { code: 'pt', name: 'Português', flag: '🇧🇷' },
    { code: 'en', name: 'English', flag: '🇺🇸' },
    { code: 'es', name: 'Español', flag: '🇪🇸' },
    { code: 'fr', name: 'Français', flag: '🇫🇷' },
    { code: 'de', name: 'Deutsch', flag: '🇩🇪' },
    { code: 'it', name: 'Italiano', flag: '🇮🇹' },
    { code: 'ja', name: '日本語', flag: '🇯🇵' },
    { code: 'ko', name: '한국어', flag: '🇰🇷' },
    { code: 'zh', name: '中文', flag: '🇨🇳' },
    { code: 'ru', name: 'Русский', flag: '🇷🇺' },
];

let currentLanguage = localStorage.getItem('language') || 'pt'; // Padrão 'pt'

/**
 * Aplica as traduções na página com base no idioma selecionado.
 * @param {string} lang - O código do idioma (ex: 'pt', 'en').
 */
function applyTranslations(lang) {
    if (!translations[lang]) {
        console.warn(`Traduções para o idioma '${lang}' não encontradas. Usando 'pt'.`);
        lang = 'pt'; // Volta para o português se a tradução não existir
    }

    const translationMap = translations[lang];
    
    // 0. Traduzir o Título da Página (NOVO)
    const pageTitleKey = document.querySelector('title').dataset.translate;
    if (pageTitleKey && translationMap[pageTitleKey]) {
        document.title = translationMap[pageTitleKey];
    }

    // 1. Elementos com texto (textContent)
    document.querySelectorAll('[data-translate]').forEach(element => {
        const key = element.dataset.translate;
        if (translationMap[key]) {
            element.textContent = translationMap[key];
        } else if (translations['pt'][key]) {
            // Fallback para português se a chave específica não existir no idioma
            element.textContent = translations['pt'][key];
        }
    });

    // 2. Placeholders de Inputs
    document.querySelectorAll('[data-translate-placeholder]').forEach(element => {
        const key = element.dataset.translatePlaceholder;
        if (translationMap[key]) {
            element.placeholder = translationMap[key];
        } else if (translations['pt'][key]) {
            element.placeholder = translations['pt'][key];
        }
    });

    // 3. Tooltips (title)
    document.querySelectorAll('[data-translate-title]').forEach(element => {
        const key = element.dataset.translateTitle;
        if (translationMap[key]) {
            element.title = translationMap[key];
        } else if (translations['pt'][key]) {
            element.title = translations['pt'][key];
        }
    });
}

/**
 * Muda o idioma, salva a preferência e aplica a tradução.
 * @param {string} lang - O código do idioma (ex: 'pt', 'en').
 */
function changeLanguage(lang) {
    currentLanguage = lang;
    localStorage.setItem('language', lang);
    
    const langButton = document.querySelector('.utilitario__idioma');
    if (langButton) {
        langButton.textContent = lang.toUpperCase();
    }
    
    applyTranslations(lang);
}

// Exporta a função para que o main.js possa usá-la
window.applyTranslations = applyTranslations;


// --- LÓGICA DO DROPDOWN ---

const langSelector = document.querySelector('.utilitario__idioma');

if (langSelector) {
    langSelector.textContent = currentLanguage.toUpperCase(); 
    langSelector.addEventListener('click', function(event) {
        event.preventDefault();
        event.stopPropagation(); // Impede que o clique feche o menu imediatamente
        createLanguageDropdown(event.clientX, event.clientY);
    });
}

function createLanguageDropdown(x, y) {
    const oldDropdown = document.querySelector('.language-dropdown');
    if (oldDropdown) {
        oldDropdown.remove();
        return; // Fecha se já estiver aberto
    }

    const dropdown = document.createElement('div');
    dropdown.className = 'language-dropdown';
    
    // Ajusta a posição para não sair da tela
    let leftPos = x - 100; // Tenta centralizar
    if (leftPos < 10) leftPos = 10; // Impede de sair pela esquerda
    if (leftPos + 200 > window.innerWidth) leftPos = window.innerWidth - 210; // Impede de sair pela direita

    dropdown.style.cssText = `
        position: fixed;
        top: ${y + 20}px; 
        left: ${leftPos}px;
        width: 200px;
        background: var(--dark);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        padding: 0.5rem;
        z-index: 4000;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(10px);
    `;

    languageOptions.forEach(lang => {
        const option = document.createElement('div');
        option.style.cssText = `
            padding: 0.5rem 1rem;
            color: var(--light);
            cursor: pointer;
            border-radius: 5px;
            margin: 0.1rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.3s ease;
        `;
        option.innerHTML = `${lang.flag} ${lang.name}`;
        
        if (lang.code === currentLanguage) {
            option.style.background = 'var(--primary)';
        }
        
        option.addEventListener('mouseenter', function() {
            if (lang.code !== currentLanguage) {
                 this.style.background = 'rgba(255, 255, 255, 0.1)';
            }
        });
        
        option.addEventListener('mouseleave', function() {
            if (lang.code !== currentLanguage) {
                this.style.background = 'transparent';
            }
        });
        
        option.addEventListener('click', function() {
            changeLanguage(lang.code);
            dropdown.remove();
        });
        
        dropdown.appendChild(option);
    });
    
    document.body.appendChild(dropdown);
    
    // Fecha o dropdown se clicar fora
    setTimeout(() => {
        document.addEventListener('click', function closeDropdown(e) {
            if (!dropdown.contains(e.target)) {
                dropdown.remove();
                document.removeEventListener('click', closeDropdown);
            }
        }, { once: true });
    }, 0);
}

// Adiciona estilos CSS para o dropdown
const style = document.createElement('style');
style.textContent = `
    .language-dropdown {
        animation: slideInUp 0.3s ease;
    }
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);

// APLICA A TRADUÇÃO QUANDO A PÁGINA CARREGA
document.addEventListener('DOMContentLoaded', () => {
    applyTranslations(currentLanguage);
});