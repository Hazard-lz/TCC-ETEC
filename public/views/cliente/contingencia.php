<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Aviso Importante - Belezou App</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/resources/images/favicon.png">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/app-cliente.css">
    
    <!-- SweetAlert2 para avisos elegantes -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    <style>
        /* Override de layout para garantir o alinhamento e centralização perfeita tanto em Mobile quanto em Desktop */
        .app-wrapper .mobile-container .app-content {
            display: flex !important;
            flex-direction: column !important;
            justify-content: flex-start !important; /* Evita que o topo seja cortado em telas menores */
            align-items: center !important;
            flex-grow: 1 !important;
            height: 100% !important;
            min-height: 0 !important;
            padding: 1.5rem !important;
            overflow-y: auto !important;
            position: relative;
        }

        /* Efeito Aurora Glow de fundo dinâmico que se adapta às cores do White-Label */
        .app-wrapper .mobile-container .app-content::before,
        .app-wrapper .mobile-container .app-content::after {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.12;
            z-index: 0;
            pointer-events: none;
            transition: background-color 0.5s ease;
        }
        .app-wrapper .mobile-container .app-content::before {
            background: var(--color-purple, #8b5cf6);
            top: 15%;
            left: 5%;
        }
        .app-wrapper .mobile-container .app-content::after {
            background: var(--color-pink, #f45b69);
            bottom: 15%;
            right: 5%;
        }

        /* Card de contingência com glassmorphism premium */
        .contingencia-card {
            margin: auto 0; /* Centralização flexível e segura em qualquer tamanho de tela */
            text-align: center;
            padding: 2.5rem 2rem;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
            width: 100%;
            max-width: 380px;
            animation: card-appear 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 1;
            transition: background-color var(--transition-default), border-color var(--transition-default), box-shadow var(--transition-default);
        }

        /* Suporte completo e refinado para Dark Mode */
        [data-theme="dark"] .contingencia-card {
            background: rgba(45, 55, 72, 0.75);
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
        }

        @keyframes card-appear {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .contingencia-logo-area {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.25rem;
            margin-bottom: 0.25rem;
        }

        .contingencia-logo {
            max-width: 120px;
            height: auto;
            object-fit: contain;
            transition: transform 0.3s ease;
        }
        
        .contingencia-logo:hover {
            transform: scale(1.05);
        }

        /* Ícone de alerta pulsante dinâmico e premium */
        .contingencia-icon {
            font-size: 2.8rem;
            color: var(--color-pink, #f45b69);
            background: rgba(244, 91, 105, 0.1);
            width: 76px;
            height: 76px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            animation: pulse-ring-glow 2s infinite;
            transition: all 0.3s ease;
        }

        @keyframes pulse-ring-glow {
            0% {
                box-shadow: 0 0 0 0 rgba(244, 91, 105, 0.35);
            }
            70% {
                box-shadow: 0 0 0 15px rgba(244, 91, 105, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(244, 91, 105, 0);
            }
        }

        .contingencia-title {
            color: var(--text-main, #2d3748);
            font-size: 1.45rem;
            font-weight: 750;
            margin: 0;
            letter-spacing: -0.02em;
        }

        .contingencia-text {
            color: var(--text-muted, #718096);
            font-size: 0.95rem;
            line-height: 1.6;
            margin: 0;
            max-width: 320px;
        }

        /* Botão de retorno adaptativo e de alta fidelidade visual */
        .btn-contingencia-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--gradient-brand, linear-gradient(135deg, #8b5cf6, #f45b69));
            color: white !important;
            padding: 0.8rem 2.2rem;
            border-radius: 9999px;
            font-weight: 650;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            margin-top: 0.5rem;
        }

        .btn-contingencia-back:hover {
            filter: brightness(1.08);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }
        
        .btn-contingencia-back:active {
            transform: translateY(-0.5px);
        }
    </style>
</head>

<body>

    <div class="app-wrapper">
        <div class="mobile-container">

            <main class="app-content">
                
                <div class="contingencia-card">
                    <div class="contingencia-logo-area">
                        <!-- Logotipo dinâmico (substituído automaticamente se configurado no White-Label) -->
                        <img src="<?= BASE_URL ?>/public/resources/images/favicon.png" class="login-logo contingencia-logo" alt="Logo">
                        
                        <div class="contingencia-icon">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                    </div>
                    
                    <h2 class="contingencia-title">Aviso Importante</h2>
                    
                    <p class="contingencia-text">
                        Gostaríamos de informar que o salão <strong>Belezou App</strong> está temporariamente fechado para novos agendamentos por motivos de força maior ou manutenção.
                    </p>
                    
                    <p class="contingencia-text" style="font-size: 0.85rem; border-top: 1px solid var(--border-color, #e2e8f0); padding-top: 1rem; width: 100%;">
                        Os agendamentos já marcados para este período podem sofrer alterações. Nossa equipe entrará em contato caso seja necessário reagendar seu horário.
                    </p>

                    <a href="<?= BASE_URL ?>/" class="btn-contingencia-back">
                        <i class="bi bi-house"></i> Voltar ao Início
                    </a>
                </div>

            </main>

            <nav class="bottom-nav">
                <a href="<?= BASE_URL ?>/" class="nav-item">
                    <span class="nav-icon">🏠</span><span>Início</span>
                </a>
                <!-- Botão Agendar bloqueado com modal informativo para evitar loops de redirecionamento -->
                <a href="javascript:void(0)" class="nav-item" style="opacity: 0.5; cursor: not-allowed;" onclick="mostrarAvisoSuspenso()">
                    <span class="nav-icon">📅</span><span>Agendar</span>
                </a>
                <a href="<?= BASE_URL ?>/historico" class="nav-item">
                    <span class="nav-icon">🕒</span><span>Histórico</span>
                </a>
                <a href="<?= BASE_URL ?>/perfil" class="nav-item">
                    <span class="nav-icon">👤</span><span>Perfil</span>
                </a>
                <a href="<?= BASE_URL ?>/cliente/ajuda" class="nav-item">
                    <span class="nav-icon"><i class="bi bi-question-circle" style="font-size: 1.2rem;"></i></span><span>Ajuda</span>
                </a>
            </nav>

        </div>
    </div>

    <script>
        function mostrarAvisoSuspenso() {
            const swalOptions = window._swalDefaults || {
                customClass: {
                    popup: 'swal-belezou-popup',
                    title: 'swal-belezou-title',
                    htmlContainer: 'swal-belezou-text',
                    confirmButton: 'swal-belezou-btn-confirm',
                    cancelButton: 'swal-belezou-btn-cancel'
                },
                buttonsStyling: false,
                showClass: { popup: 'swal-belezou-show' },
                hideClass: { popup: 'swal-belezou-hide' }
            };

            Swal.fire({
                ...swalOptions,
                title: 'Agendamentos Suspensos',
                text: 'O salão Belezou App está temporariamente fechado para novas reservas. Agradecemos a compreensão.',
                icon: 'warning',
                confirmButtonText: 'Entendido'
            });
        }
    </script>
</body>

</html>
