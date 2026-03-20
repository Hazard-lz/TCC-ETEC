<?php
// Bloqueia o acesso de quem não fez login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: " . BASE_URL . "/login");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Histórico - Belezou App</title>
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/app-cliente.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/historico.css">
</head>
<body>

    <div class="app-wrapper">
        <div class="mobile-container">
            
            <header class="app-header" style="justify-content: center;">
                <h2 style="color: var(--text-main); font-size: 1.2rem;">Meus Agendamentos</h2>
            </header>

            <main class="app-content">
                
                <div class="tabs-container">
                    <button class="tab-btn active" onclick="mudarAba('proximos', this)">Próximos</button>
                    <button class="tab-btn" onclick="mudarAba('anteriores', this)">Anteriores</button>
                </div>

                <div id="aba-proximos" class="tab-content active history-grid">
                    
                    <div class="history-card status-marcado">
                        <div class="history-header">
                            <span class="history-date">📅 25/03/2026 às 10:00</span>
                            <span class="history-badge badge-purple">Marcado</span>
                        </div>
                        <div class="history-body">
                            <div>
                                <div class="history-service">Corte Feminino</div>
                                <div class="history-pro">com Maria Oliveira</div>
                            </div>
                            <div class="history-price">R$ 60,00</div>
                        </div>
                    </div>

                    <div class="history-card status-pendente">
                        <div class="history-header">
                            <span class="history-date">📅 31/03/2026 às 14:30</span>
                            <span class="history-badge badge-orange">Pendente</span>
                        </div>
                        <div class="history-body">
                            <div>
                                <div class="history-service">Manicure</div>
                                <div class="history-pro">Qualquer Profissional</div>
                            </div>
                            <div class="history-price">R$ 30,00</div>
                        </div>
                    </div>

                </div> 
                
                <div id="aba-anteriores" class="tab-content history-grid">
                    
                    <div class="history-card status-concluido">
                        <div class="history-header">
                            <span class="history-date">📅 06/03/2026 às 15:00</span>
                            <span class="history-badge badge-green">Concluído</span>
                        </div>
                        <div class="history-body">
                            <div>
                                <div class="history-service">Luzes ou Mechas</div>
                                <div class="history-pro">com Maria Oliveira</div>
                            </div>
                            <div class="history-price">R$ 200,00</div>
                        </div>
                    </div>

                    <div class="history-card status-cancelado">
                        <div class="history-header">
                            <span class="history-date">📅 14/02/2026 às 09:30</span>
                            <span class="history-badge badge-pink">Cancelado</span>
                        </div>
                        <div class="history-body">
                            <div>
                                <div class="history-service">Hidratação Capilar</div>
                                <div class="history-pro">com Fernanda Costa</div>
                            </div>
                            <div class="history-price">R$ 50,00</div>
                        </div>
                    </div>

                </div> 
            </main>

            <nav class="bottom-nav">
                <a href="<?= BASE_URL ?>/" class="nav-item">
                    <span class="nav-icon">🏠</span><span>Início</span>
                </a>
                <a href="<?= BASE_URL ?>/agendar" class="nav-item">
                    <span class="nav-icon">📅</span><span>Agendar</span>
                </a>
                <a href="<?= BASE_URL ?>/historico" class="nav-item active">
                    <span class="nav-icon">🕒</span><span>Histórico</span>
                </a>
                <a href="<?= BASE_URL ?>/perfil" class="nav-item">
                    <span class="nav-icon">👤</span><span>Perfil</span>
                </a>
            </nav>

        </div>
    </div>

    <button id="themeToggle" class="btn-theme-toggle" title="Alternar Tema Escuro/Claro">🌓</button>

    <script src="<?= BASE_URL ?>/public/resources/js/historico.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/app-cliente.js"></script>
</body>
</html>