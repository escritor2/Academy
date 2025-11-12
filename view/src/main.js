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