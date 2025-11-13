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

        // Theme Selector
        'themeSystem': '🖥️ Sistema',
        'themeDark': '🌙 Escuro',
        'themeLight': '☀️ Claro',
        'themeColorblind': '👁️ Daltonismo',
        'themeMono1': '🔳 Mono 1 (Grayscale)',
        'themeMono2': '🏁 Mono 2 (Invertido)',
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

        // Theme Selector
        'themeSystem': '🖥️ System',
        'themeDark': '🌙 Dark',
        'themeLight': '☀️ Light',
        'themeColorblind': '👁️ Colorblind',
        'themeMono1': '🔳 Mono 1 (Grayscale)',
        'themeMono2': '🏁 Mono 2 (Inverted)',
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

        // Theme Selector
        'themeSystem': '🖥️ Sistema',
        'themeDark': '🌙 Oscuro',
        'themeLight': '☀️ Claro',
        'themeColorblind': '👁️ Daltonismo',
        'themeMono1': '🔳 Mono 1 (Escala de grises)',
        'themeMono2': '🏁 Mono 2 (Invertido)',
    },
    // 4. Francês
    'fr': {
        'pageTitle': 'TECHFIT: La Salle de Sport qui Inspire | Forfaits et Activités',
        // Header
        'searchPlaceholder': 'Rechercher Cours, Forfaits ou Produits',
        'loginTooltip': 'Accéder à votre compte',
        'registerTooltip': 'Créer un nouveau compte',
        'menuTooltip': 'Ouvrir le menu de navigation',
        
        // Menu
        'home': 'Accueil',
        'plans': 'Forfaits',
        'classes': 'Cours',
        'products': 'Produits',
        'subscriptions': 'Inscriptions',
        
        // Hero Section
        'heroTitle': 'Transformez Votre Corps, Élevez Votre Esprit',
        'heroSubtitle': 'La salle de sport la plus moderne de São Paulo. Équipements haut de gamme, instructeurs certifiés et resultados garantis.',
        'heroCta': 'Commencez Maintenant',
        
        // Activities Section
        'featuredActivities': 'Activités en Vedette',
        'functionalTraining': 'Entraînement Fonctionnel',
        'yogaStretching': 'Yoga et Étirement',
        'swimmingHydro': 'Natación/Aquagym',
        'boxeFitness': 'Boxe Fitness',
        
        // Products Section
        'trainingProducts': 'Produits pour votre Entraînement',
        'wheyProtein': 'Whey Protéine',
        'thermicBottle': 'Bouteille Isotherme',
        'elasticKit': "Kit d'Élastiques",
        'trainingGlove': 'Gant d\'Entraînement',
        'supplement': 'Complément',
        'accessory': 'Accessoire',
        'apparel': 'Vêtement',

        // Modal
        'modalTitleLogin': 'Accédez à votre Compte',
        'modalTitleRegister': 'Créez votre Compte',
        'modalEmailLabel': 'Email:',
        'modalPasswordLabel': 'Mot de passe:',
        'modalNameLabel': 'Nom:',
        'modalLoginButton': 'Se Connecter',
        'modalRegisterButton': 'Créer un Compte',
        'modalRegisterLinkText': 'Pas de compte?',
        'modalRegisterLinkAction': 'Inscrivez-vous ici',
        'modalLoginLinkText': 'Déjà un compte?',
        'modalLoginLinkAction': 'Se connecter',

        // Theme Selector
        'themeSystem': '🖥️ Système',
        'themeDark': '🌙 Sombre',
        'themeLight': '☀️ Clair',
        'themeColorblind': '👁️ Daltonien',
        'themeMono1': '🔳 Mono 1 (Niveaux de gris)',
        'themeMono2': '🏁 Mono 2 (Inversé)',
    },
    // 5. Alemão
    'de': {
        'pageTitle': 'TECHFIT: Das Fitnessstudio, das Inspiriert | Pläne & Aktivitäten',
        // Header
        'searchPlaceholder': 'Kurse, Pläne ou Produkte suchen',
        'loginTooltip': 'Auf Ihr Konto zugreifen',
        'registerTooltip': 'Neues Konto erstellen',
        'menuTooltip': 'Navigationsmenü öffnen',
        
        // Menu
        'home': 'Startseite',
        'plans': 'Pläne',
        'classes': 'Kurse',
        'products': 'Produkte',
        'subscriptions': 'Anmeldungen',
        
        // Hero Section
        'heroTitle': 'Verwandeln Sie Ihren Körper, Erheben Sie Ihren Geist',
        'heroSubtitle': 'Das modernste Fitnessstudio in São Paulo. Hochwertige Ausstattung, zertifizierte Trainer und garantierte Ergebnisse.',
        'heroCta': 'Jetzt Starten',
        
        // Activities Section
        'featuredActivities': 'Empfohlene Aktivitäten',
        'functionalTraining': 'Funktionelles Training',
        'yogaStretching': 'Yoga & Dehnung',
        'swimmingHydro': 'Schwimmen/Wassergymnastik',
        'boxeFitness': 'Fitness-Boxen',
        
        // Products Section
        'trainingProducts': 'Produkte für Ihr Training',
        'wheyProtein': 'Whey Protein',
        'thermicBottle': 'Thermische Flasche',
        'elasticKit': 'Elastikbänder-Set',
        'trainingGlove': 'Trainingshandschuhe',
        'supplement': 'Nahrungsergänzung',
        'accessory': 'Zubehör',
        'apparel': 'Bekleidung',

        // Modal
        'modalTitleLogin': 'Auf Ihr Konto zugreifen',
        'modalTitleRegister': 'Konto erstellen',
        'modalEmailLabel': 'E-Mail:',
        'modalPasswordLabel': 'Passwort:',
        'modalNameLabel': 'Name:',
        'modalLoginButton': 'Anmelden',
        'modalRegisterButton': 'Konto erstellen',
        'modalRegisterLinkText': 'Kein Konto?',
        'modalRegisterLinkAction': 'Hier registrieren',
        'modalLoginLinkText': 'Bereits ein Konto?',
        'modalLoginLinkAction': 'Anmelden',

        // Theme Selector
        'themeSystem': '🖥️ System',
        'themeDark': '🌙 Dunkel',
        'themeLight': '☀️ Hell',
        'themeColorblind': '👁️ Farbenblind',
        'themeMono1': '🔳 Mono 1 (Graustufen)',
        'themeMono2': '🏁 Mono 2 (Invertiert)',
    },
    // 6. Italiano
    'it': {
        'pageTitle': 'TECHFIT: La Palestra che Ispira | Piani e Attività',
        // Header
        'searchPlaceholder': 'Cerca Corsi, Piani o Prodotti',
        'loginTooltip': 'Accedi al tuo account',
        'registerTooltip': 'Crea un nuovo account',
        'menuTooltip': 'Apri il menu di navigazione',
        
        // Menu
        'home': 'Home',
        'plans': 'Piani',
        'classes': 'Lezioni',
        'products': 'Prodotti',
        'subscriptions': 'Iscrizioni',
        
        // Hero Section
        'heroTitle': 'Trasforma il Tuo Corpo, Eleva la Tua Mente',
        'heroSubtitle': 'La palestra più moderna di San Paolo. Attrezzature all\'avanguardia, istruttori certificati e risultati garantiti.',
        'heroCta': 'Inizia Ora',
        
        // Activities Section
        'featuredActivities': 'Attività in Evidenza',
        'functionalTraining': 'Allenamento Funzionale',
        'yogaStretching': 'Yoga e Stretching',
        'swimmingHydro': 'Nuoto/Idroginnastica',
        'boxeFitness': 'Boxe Fitness',
        
        // Products Section
        'trainingProducts': 'Prodotti per il Tuo Allenamento',
        'wheyProtein': 'Whey Protein',
        'thermicBottle': 'Borraccia Termica',
        'elasticKit': 'Kit Elastici',
        'trainingGlove': 'Guanto da Allenamento',
        'supplement': 'Integratore',
        'accessory': 'Accessorio',
        'apparel': 'Abbigliamento',

        // Modal
        'modalTitleLogin': 'Accedi al tuo Account',
        'modalTitleRegister': 'Crea il tuo Account',
        'modalEmailLabel': 'Email:',
        'modalPasswordLabel': 'Password:',
        'modalNameLabel': 'Nome:',
        'modalLoginButton': 'Accedi',
        'modalRegisterButton': 'Crea Account',
        'modalRegisterLinkText': 'Non hai un account?',
        'modalRegisterLinkAction': 'Registrati qui',
        'modalLoginLinkText': 'Hai già un account?',
        'modalLoginLinkAction': 'Accedi qui',

        // Theme Selector
        'themeSystem': '🖥️ Sistema',
        'themeDark': '🌙 Scuro',
        'themeLight': '☀️ Chiaro',
        'themeColorblind': '👁️ Daltonico',
        'themeMono1': '🔳 Mono 1 (Scala di grigi)',
        'themeMono2': '🏁 Mono 2 (Invertito)',
    },
    // 7. Japonês
    'ja': {
        'pageTitle': 'TECHFIT: インスピレーションを与えるジム | プランとアクティビティ',
        // Header
        'searchPlaceholder': 'クラス、プラン、製品を検索',
        'loginTooltip': 'アカウントにアクセス',
        'registerTooltip': '新しいアカウントを作成',
        'menuTooltip': 'ナビゲーションメニューを開く',
        
        // Menu
        'home': 'ホーム',
        'plans': 'プラン',
        'classes': 'クラス',
        'products': '製品',
        'subscriptions': '登録',
        
        // Hero Section
        'heroTitle': '身体を変え、心を高める',
        'heroSubtitle': 'サンパウロで最もモダンなジム。最先端の設備、認定インストラクター、保証された結果。',
        'heroCta': '今すぐ始める',
        
        // Activities Section
        'featuredActivities': '注目のアクティビティ',
        'functionalTraining': 'ファンクショナルトレーニング',
        'yogaStretching': 'ヨガ＆ストレッチ',
        'swimmingHydro': '水泳/アクアビクス',
        'boxeFitness': 'フィットネスボクシング',
        
        // Products Section
        'trainingProducts': 'トレーニング製品',
        'wheyProtein': 'ホエイプロテイン',
        'thermicBottle': '保温ボトル',
        'elasticKit': 'エラスティックバンドキット',
        'trainingGlove': 'トレーニンググローブ',
        'supplement': 'サプリメント',
        'accessory': 'アクセサリー',
        'apparel': 'アパレル',

        // Modal
        'modalTitleLogin': 'アカウントにアクセス',
        'modalTitleRegister': 'アカウントを作成',
        'modalEmailLabel': 'メール:',
        'modalPasswordLabel': 'パスワード:',
        'modalNameLabel': '名前:',
        'modalLoginButton': 'ログイン',
        'modalRegisterButton': 'アカウント作成',
        'modalRegisterLinkText': 'アカウントをお持ちでない場合',
        'modalRegisterLinkAction': 'こちらで登録',
        'modalLoginLinkText': 'すでにアカウントをお持ちの場合',
        'modalLoginLinkAction': 'こちらでログイン',

        // Theme Selector
        'themeSystem': '🖥️ システム',
        'themeDark': '🌙 ダーク',
        'themeLight': '☀️ ライト',
        'themeColorblind': '👁️ 色覚異常',
        'themeMono1': '🔳 モノ1 (グレースケール)',
        'themeMono2': '🏁 モノ2 (反転)',
    },
    // 8. Coreano
    'ko': {
        'pageTitle': 'TECHFIT: 영감을 주는 체육관 | 플랜 및 활동',
        // Header
        'searchPlaceholder': '수업, 플랜 또는 제품 검색',
        'loginTooltip': '계정에 접속',
        'registerTooltip': '새 계정 만들기',
        'menuTooltip': '내비게이션 메뉴 열기',
        
        // Menu
        'home': '홈',
        'plans': '플랜',
        'classes': '수업',
        'products': '제품',
        'subscriptions': '등록',
        
        // Hero Section
        'heroTitle': '몸을 변화시키고 마음을 높이다',
        'heroSubtitle': '상파울루에서 가장 현대적인 체육관. 최고급 장비, 공인 강사, 보장된 결과.',
        'heroCta': '지금 시작하기',
        
        // Activities Section
        'featuredActivities': '주요 활동',
        'functionalTraining': '기능성 훈련',
        'yogaStretching': '요가 & 스트레칭',
        'swimmingHydro': '수영/수중 에어로빅',
        'boxeFitness': '피트니스 복싱',
        
        // Products Section
        'trainingProducts': '운동 제품',
        'wheyProtein': '웨이 프로틴',
        'thermicBottle': '보온 병',
        'elasticKit': '탄성 밴드 키트',
        'trainingGlove': '트레이닝 장갑',
        'supplement': '보충제',
        'accessory': '액세서리',
        'apparel': '의류',

        // Modal
        'modalTitleLogin': '계정에 접속',
        'modalTitleRegister': '계정 만들기',
        'modalEmailLabel': '이메일:',
        'modalPasswordLabel': '비밀번호:',
        'modalNameLabel': '이름:',
        'modalLoginButton': '로그인',
        'modalRegisterButton': '계정 생성',
        'modalRegisterLinkText': '계정이 없으신가요?',
        'modalRegisterLinkAction': '여기서 등록',
        'modalLoginLinkText': '이미 계정이 있으신가요?',
        'modalLoginLinkAction': '여기서 로그인',

        // Theme Selector
        'themeSystem': '🖥️ 시스템',
        'themeDark': '🌙 다크',
        'themeLight': '☀️ 라이트',
        'themeColorblind': '👁️ 색맹',
        'themeMono1': '🔳 모노 1 (그레이스케일)',
        'themeMono2': '🏁 모노 2 (반전)',
    },
    // 9. Chinês
    'zh': {
        'pageTitle': 'TECHFIT: 激励人心的健身房 | 计划与活动',
        // Header
        'searchPlaceholder': '搜索课程、计划或产品',
        'loginTooltip': '访问您的账户',
        'registerTooltip': '创建新账户',
        'menuTooltip': '打开导航菜单',
        
        // Menu
        'home': '首页',
        'plans': '计划',
        'classes': '课程',
        'products': '产品',
        'subscriptions': '注册',
        
        // Hero Section
        'heroTitle': '改变身体，提升心灵',
        'heroSubtitle': '圣保罗最现代化的健身房。顶级设备，认证教练，保证效果。',
        'heroCta': '立即开始',
        
        // Activities Section
        'featuredActivities': '特色活动',
        'functionalTraining': '功能性训练',
        'yogaStretching': '瑜伽与拉伸',
        'swimmingHydro': '游泳/水中有氧运动',
        'boxeFitness': '健身拳击',
        
        // Products Section
        'trainingProducts': '训练产品',
        'wheyProtein': '乳清蛋白',
        'thermicBottle': '保温瓶',
        'elasticKit': '弹力带套装',
        'trainingGlove': '训练手套',
        'supplement': '补充剂',
        'accessory': '配件',
        'apparel': '服装',

        // Modal
        'modalTitleLogin': '访问您的账户',
        'modalTitleRegister': '创建您的账户',
        'modalEmailLabel': '邮箱:',
        'modalPasswordLabel': '密码:',
        'modalNameLabel': '姓名:',
        'modalLoginButton': '登录',
        'modalRegisterButton': '创建账户',
        'modalRegisterLinkText': '没有账户？',
        'modalRegisterLinkAction': '在此注册',
        'modalLoginLinkText': '已有账户？',
        'modalLoginLinkAction': '在此登录',

        // Theme Selector
        'themeSystem': '🖥️ 系统',
        'themeDark': '🌙 深色',
        'themeLight': '☀️ 浅色',
        'themeColorblind': '👁️ 色盲',
        'themeMono1': '🔳 单色1 (灰度)',
        'themeMono2': '🏁 单色2 (反色)',
    },
    // 10. Russo
    'ru': {
        'pageTitle': 'TECHFIT: Спортзал, который Вдохновляет | Планы и Активности',
        // Header
        'searchPlaceholder': 'Поиск Занятий, Планов или Товаров',
        'loginTooltip': 'Войти в аккаунт',
        'registerTooltip': 'Создать новый аккаунт',
        'menuTooltip': 'Открыть меню навигации',
        
        // Menu
        'home': 'Главная',
        'plans': 'Планы',
        'classes': 'Занятия',
        'products': 'Товары',
        'subscriptions': 'Регистрации',
        
        // Hero Section
        'heroTitle': 'Преобразите Свое Тело, Возвысьте Свой Разум',
        'heroSubtitle': 'Самый современный спортзал в Сан-Паулу. Передовое оборудование, сертифицированные инструкторы и гарантированные результаты.',
        'heroCta': 'Начать Сейчас',
        
        // Activities Section
        'featuredActivities': 'Рекомендуемые Активности',
        'functionalTraining': 'Функциональный Тренинг',
        'yogaStretching': 'Йога и Растяжка',
        'swimmingHydro': 'Плавание/Аквааэробика',
        'boxeFitness': 'Фитнес-Бокс',
        
        // Products Section
        'trainingProducts': 'Товары для Вашей Тренировки',
        'wheyProtein': 'Сывороточный Протеин',
        'thermicBottle': 'Термос',
        'elasticKit': 'Набор Эспандеров',
        'trainingGlove': 'Тренировочные Перчатки',
        'supplement': 'Добавка',
        'accessory': 'Аксессуар',
        'apparel': 'Одежда',

        // Modal
        'modalTitleLogin': 'Войдите в Аккаунт',
        'modalTitleRegister': 'Создайте Аккаунт',
        'modalEmailLabel': 'Email:',
        'modalPasswordLabel': 'Пароль:',
        'modalNameLabel': 'Имя:',
        'modalLoginButton': 'Войти',
        'modalRegisterButton': 'Создать Аккаунт',
        'modalRegisterLinkText': 'Нет аккаунта?',
        'modalRegisterLinkAction': 'Зарегистрируйтесь здесь',
        'modalLoginLinkText': 'Уже есть аккаунт?',
        'modalLoginLinkAction': 'Войти здесь',

        // Theme Selector
        'themeSystem': '🖥️ Система',
        'themeDark': '🌙 Темный',
        'themeLight': '☀️ Светлый',
        'themeColorblind': '👁️ Дальтонизм',
        'themeMono1': '🔳 Моно 1 (Оттенки серого)',
        'themeMono2': '🏁 Моно 2 (Инвертированный)',
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
 * Gerencia o widget VLibras baseado no idioma atual
 * @param {string} lang - O código do idioma
 */
function manageVLibras(lang) {
    const vwContainer = document.querySelector('.vw-container');
    const vlibrasWidget = document.querySelector('div[vw]');
    
    // Remove o widget existente se presente
    if (vlibrasWidget) {
        vlibrasWidget.remove();
    }
    
    // Remove qualquer container do VLibras antigo
    if (vwContainer) {
        vwContainer.remove();
    }
    
    // Só carrega o VLibras para português
    if (lang === 'pt') {
        // Cria o container do VLibras
        const vwDiv = document.createElement('div');
        vwDiv.className = 'vw-container';
        vwDiv.innerHTML = `
            <div vw class="enabled">
                <div vw-access-button class="active"></div>
                <div vw-plugin-wrapper>
                    <div class="vw-plugin-top-wrapper"></div>
                </div>
            </div>
        `;
        
        // Adiciona ao final do body
        document.body.appendChild(vwDiv);
        
        // Recarrega o script do VLibras
        const existingScript = document.querySelector('script[src="https://vlibras.gov.br/app/vlibras-plugin.js"]');
        if (existingScript) {
            existingScript.remove();
        }
        
        const script = document.createElement('script');
        script.src = 'https://vlibras.gov.br/app/vlibras-plugin.js';
        script.onload = function() {
            if (window.VLibras) {
                new window.VLibras.Widget('https://vlibras.gov.br/app');
            }
        };
        document.body.appendChild(script);
    }
}

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

    // 4. Opções do Seletor de Temas (NOVO)
    const themeSelect = document.getElementById('tema-select');
    if (themeSelect) {
        const themeOptions = {
            'system': 'themeSystem',
            'dark': 'themeDark',
            'light': 'themeLight',
            'colorblind': 'themeColorblind',
            'mono1': 'themeMono1',
            'mono2': 'themeMono2'
        };

        // Atualiza cada opção do select
        Array.from(themeSelect.options).forEach(option => {
            const themeKey = themeOptions[option.value];
            if (themeKey && translationMap[themeKey]) {
                option.textContent = translationMap[themeKey];
            }
        });
    }
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
    manageVLibras(lang); // ← GERENCIA O VLIBRAS BASEADO NO IDIOMA
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

// APLICA A TRADUÇÃO E GERENCIA O VLIBRAS QUANDO A PÁGINA CARREGA
document.addEventListener('DOMContentLoaded', () => {
    applyTranslations(currentLanguage);
    manageVLibras(currentLanguage); // ← INICIALIZA O VLIBRAS NO CARREGAMENTO
});