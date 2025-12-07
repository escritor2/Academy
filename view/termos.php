<!DOCTYPE html>
<html lang="pt-br" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icons/halter.png">
    <title>Termos de Uso - TechFit</title>

    <!-- Importando Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Configuração da Paleta de Cores TechFit -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        tech: {
                            900: '#111827', 
                            800: '#1f2937', 
                            700: '#374151', 
                            primary: '#ea580c', 
                            primaryHover: '#c2410c',
                            text: '#f3f4f6', 
                            muted: '#9ca3af' 
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <!-- Ícones Lucide -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Fonte Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #111827;
            color: #f3f4f6;
            overflow-x: hidden;
            line-height: 1.7;
        }
        h2 {
            border-left: 4px solid #ea580c;
            padding-left: 1rem;
            margin-top: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
            font-size: 1.5rem; /* 2xl */
        }
    </style>
</head>
<body class="antialiased selection:bg-tech-primary selection:text-white">

    <!-- Navbar Simplificada -->
    <nav class="fixed w-full z-50 bg-tech-900/90 backdrop-blur-md border-b border-tech-700/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <a href="areacliente.php" class="flex items-center gap-2 group cursor-pointer hover:opacity-80 transition-opacity">
                    <i data-lucide="arrow-left" class="h-6 w-6 text-tech-muted group-hover:text-white transition-colors"></i>
                    <span class="font-bold text-2xl tracking-tighter text-white">TECH<span class="text-tech-primary">FIT</span></span>
                </a>
                <span class="text-tech-muted text-sm font-medium hidden md:block">Documento Legal Complexo</span>
            </div>
        </div>
    </nav>

    <!-- Conteúdo dos Termos de Uso -->
    <section class="pt-28 pb-16 bg-tech-900">
        <div class="max-w-4xl mx-auto px-4">
            <h1 class="text-4xl md:text-5xl font-extrabold mb-8 text-center text-tech-primary">
                Termos & Condições de Uso (T&C)
            </h1>
            <p class="text-center text-tech-muted mb-12 text-lg">
                **Importante:** Este é um documento extenso e juridicamente denso. Recomenda-se a leitura integral para garantir total compreensão de seus direitos e obrigações. (Ou seja, não leia, clique em Concordo. 😉)
            </p>

            <div class="bg-tech-800 p-8 md:p-12 rounded-xl shadow-2xl border border-tech-700/50">
                <p class="text-sm italic text-tech-muted mb-6">
                    Última Atualização: 07 de Dezembro de 2025.
                </p>

                <h2 class="text-tech-primary">1. Introdução e Aceitação dos Termos</h2>
                <p>
                    Bem-vindo à TechFit ("Nós", "Nosso", "A Empresa"). Ao acessar ou utilizar nossa plataforma digital e serviços de fitness avançado, você ("Usuário", "Você") reconhece e concorda integralmente com a versão mais recente destes Termos e Condições de Uso (doravante, "T&C"). A continuidade do uso após qualquer alteração implica aceitação tácita e irrestrita das novas cláusulas. Caso você discorde de qualquer parte destes T&C, você deverá cessar imediatamente o uso de nossos serviços, encerrar sua conta de forma permanente e, se aplicável, notificar a nossa Divisão de Compliance através de um formulário preenchido à mão e enviado por carta registrada.
                </p>

                <h2 class="text-tech-primary">2. Licença de Uso Limitada e Revogável</h2>
                <p>
                    Concedemos a você uma licença pessoal, não exclusiva, intransferível, não sublicenciável, temporária e revogável para acessar e utilizar os Serviços da TechFit estritamente de acordo com estes T&C e qualquer adendo contratual subsequente. Esta licença se restringe ao uso da interface visual e funcional das aplicações. Fica expressamente proibida qualquer tentativa de engenharia reversa, descompilação, mineração de dados ou qualquer forma de acesso ao código-fonte proprietário do nosso Algoritmo de Progressão Ponderada (APP). Qualquer violação desta seção resultará em uma multa contratual de 100% sobre o valor restante do seu plano anual, mais os custos processuais.
                </p>
                <h3 class="text-white mt-4 font-bold text-lg">2.1. Condições de Revogação</h3>
                <p class="pl-4 text-tech-muted">
                    2.1.1. A Licença será sumariamente revogada se o Usuário faltar a mais de 3 (três) treinos em dias úteis consecutivos sem apresentar atestado médico homologado pelo nosso corpo clínico ou se for pego utilizando pesos de elevação não rastreados pelo nosso sistema de sensores inerciais.
                </p>

                <h2 class="text-tech-primary">3. Modificações do Serviço e Interrupção Não Programada</h2>
                <p>
                    A TechFit se reserva o direito, a seu exclusivo critério e a qualquer momento, de modificar, suspender ou descontinuar, temporária ou permanentemente, o Serviço (ou qualquer parte dele) com ou sem aviso prévio. O Usuário reconhece que a TechFit não será responsável perante o Usuário ou terceiros por qualquer modificação, suspensão ou descontinuação do Serviço, incluindo, mas não se limitando a, indisponibilidade de máquinas de cardio específicas durante picos de manutenção ou atrasos na sincronização de dados do seu relógio inteligente.
                </p>

                <h2 class="text-tech-primary">4. Propriedade Intelectual (PI) e Ativos Digitais</h2>
                <p>
                    Todo o conteúdo, software, algoritmos (incluindo o APP), projetos de interface, fotografias, vídeos de demonstração de exercícios, e qualquer material presente na plataforma são de propriedade exclusiva da TechFit ou de seus licenciadores e estão protegidos por leis de direitos autorais, marcas registradas e patentes internacionais. A única PI que você detém é o suor gerado durante a execução de exercícios, o qual, no entanto, é monitorado e registrado como dado biométrico.
                </p>
                <h3 class="text-white mt-4 font-bold text-lg">4.1. Conteúdo Gerado pelo Usuário (CGU)</h3>
                <p class="pl-4 text-tech-muted">
                    Ao postar fotos de "pump" ou "selfies de treino" em fóruns internos ou mídias sociais utilizando nossa hashtag oficial, você concede à TechFit uma licença perpétua, mundial, irrevogável, livre de royalties, totalmente paga e sublicenciável para usar, reproduzir, modificar, adaptar, publicar, traduzir, criar trabalhos derivados, distribuir e exibir tal CGU em qualquer formato e em quaisquer canais de mídia, conhecidos ou posteriormente desenvolvidos, sem necessidade de pagamento ou atribuição adicional.
                </p>

                <h2 class="text-tech-primary">5. Exclusão de Garantias e Limitação de Responsabilidade</h2>
                <p>
                    O Serviço é fornecido "no estado em que se encontra" e "conforme disponível", sem garantias de qualquer tipo, expressas ou implícitas, incluindo, mas não se limitando a, garantias implícitas de comercialização, adequação a uma finalidade específica ou não violação. A TechFit não garante que (i) o Serviço atenderá às suas expectativas estéticas de desenvolvimento muscular; (ii) o Serviço será ininterrupto, oportuno, seguro ou livre de erros de programação; (iii) os resultados obtidos com o uso do Serviço serão precisos ou confiáveis, especialmente se você omitir dados de consumo de pizza de seus registros diários.
                </p>
                <p class="mt-4">
                    Em nenhuma hipótese a TechFit será responsável por quaisquer danos indiretos, incidentais, especiais, consequenciais ou exemplares, incluindo, mas não se limitando a, danos por perda de lucros, boa vontade, uso, dados ou outras perdas intangíveis (mesmo que a TechFit tenha sido avisada da possibilidade de tais danos) resultantes de: (a) o uso ou a incapacidade de usar o Serviço; (b) a substituição de bens e serviços decorrentes de bens, dados, informações ou serviços adquiridos ou obtidos ou mensagens recebidas ou transações realizadas através do Serviço; (c) acesso não autorizado ou alteração de suas transmissões ou dados biométricos.
                </p>

                <h2 class="text-tech-primary">6. Indenização e Compensação</h2>
                <p>
                    Você concorda em indenizar e isentar a TechFit, suas subsidiárias, afiliadas, executivos, agentes, co-branded partners ou outros parceiros e funcionários de qualquer reivindicação ou demanda, incluindo honorários advocatícios razoáveis, feita por terceiros devido ou resultante do Conteúdo que você enviar, postar ou transmitir através do Serviço, seu uso do Serviço, sua conexão com o Serviço, sua violação dos T&C ou sua violação de quaisquer direitos de terceiros. Esta cláusula inclui a obrigação de indenizar a TechFit se você acidentalmente derrubar um peso na ponta do pé de outro usuário e o evento for considerado "falha de modulação de força induzida por UI/UX deficiente" por um tribunal.
                </p>

                <h2 class="text-tech-primary">7. Disposições Finais e Foro</h2>
                <p>
                    Estes T&C constituem o acordo integral entre você e a TechFit e regem o uso do Serviço, substituindo quaisquer acordos anteriores entre você e a TechFit sobre o Serviço. Você também pode estar sujeito a termos e condições adicionais que se aplicam quando você usa serviços de afiliados, conteúdo de terceiros ou software de terceiros. O não exercício ou execução de qualquer direito ou disposição destes T&C pela TechFit não constituirá uma renúncia a tal direito ou disposição. Se qualquer disposição destes T&C for considerada inválida por um tribunal de jurisdição competente, as partes concordam que o tribunal deve tentar dar efeito às intenções das partes conforme refletido na disposição, e as outras disposições dos T&C permanecerão em pleno vigor e efeito.
                </p>
                <h3 class="text-white mt-4 font-bold text-lg">7.1. Lei Aplicável</h3>
                <p class="pl-4 text-tech-muted">
                    7.1.1. Todos os litígios decorrentes ou relacionados a estes T&C serão regidos e interpretados de acordo com as leis da nossa sede principal (atualmente a Sala do Servidor B2), independentemente de seus princípios de conflitos de leis.
                </p>
                <h3 class="text-white mt-4 font-bold text-lg">7.2. O Fim</h3>
                <p class="pl-4 text-tech-muted">
                    7.2.1. Sim, você chegou ao final.
                </p>

            </div>

            <!-- Botões de Ação -->
            <div class="mt-12 flex justify-center space-x-6">
                <a href="areacliente.php?termos_aceitos=true" class="inline-flex items-center justify-center bg-tech-primary text-white px-8 py-4 rounded-lg font-bold hover:bg-tech-primaryHover transition-colors shadow-lg shadow-tech-primary/30">
                    <i data-lucide="check" class="w-5 h-5 mr-2"></i> Concordo com os Termos
                </a>
                <a href="areacliente.php?termos_aceitos=false" class="inline-flex items-center justify-center bg-tech-700 text-white px-8 py-4 rounded-lg font-bold hover:bg-tech-muted transition-colors shadow-lg shadow-tech-700/30">
                    <i data-lucide="x" class="w-5 h-5 mr-2"></i> Discordo (Sair)
                </a>
            </div>
            <p class="text-center text-sm text-red-500 mt-4">
                *O clique em "Discordo" pode resultar no encerramento da sua sessão.
            </p>
        </div>
    </section>

    <!-- Footer Igual ao Index -->
    <footer class="bg-black text-gray-400 py-12 border-t border-tech-700">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <div class="flex justify-center items-center gap-2 mb-4">
                <i data-lucide="dumbbell" class="h-6 w-6 text-tech-primary"></i>
                <span class="font-bold text-xl text-white">TECH<span class="text-tech-primary">FIT</span></span>
            </div>
            <p class="text-sm">&copy; 2023 TechFit. Todos os direitos reservados. (E sim, você leu tudo.)</p>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>