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
    <?php require_once __DIR__ . '/../partials/onesignal.php'; ?>
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

                <?php 
                // Função auxiliar para mapear o status do BD para as tuas classes de CSS
                function getBadgeCss($status) {
                    $map = [
                        'pendente' => ['card' => 'status-pendente', 'badge' => 'badge-orange', 'label' => 'Pendente'],
                        'marcado'  => ['card' => 'status-marcado', 'badge' => 'badge-purple', 'label' => 'Marcado'],
                        'concluido'=> ['card' => 'status-concluido', 'badge' => 'badge-green', 'label' => 'Concluído'],
                        'cancelado'=> ['card' => 'status-cancelado', 'badge' => 'badge-pink', 'label' => 'Cancelado']
                    ];
                    return $map[$status] ?? ['card' => '', 'badge' => '', 'label' => ucfirst($status)];
                }
                ?>

                <div id="aba-proximos" class="tab-content active history-grid">
                    <?php if (!empty($proximos)): ?>
                        <?php foreach ($proximos as $ag): 
                            $estilo = getBadgeCss($ag['status']);
                        ?>
                            <div class="history-card <?= $estilo['card'] ?>">
                                <div class="history-header">
                                    <span class="history-date">📅 <?= $ag['data_formatada'] ?> às <?= $ag['hora_formatada'] ?></span>
                                    <span class="history-badge <?= $estilo['badge'] ?>"><?= $estilo['label'] ?></span>
                                </div>
                                <div class="history-body">
                                    <div>
                                        <div class="history-service"><?= htmlspecialchars($ag['nome_servico']) ?></div>
                                        <div class="history-pro">com <?= htmlspecialchars($ag['funcionario_nome']) ?></div>
                                    </div>
                                    <div class="history-price">R$ <?= $ag['preco_formatado'] ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: var(--text-muted); margin-top: 2rem;">Não tens agendamentos futuros.</p>
                    <?php endif; ?>
                </div> 
                
                <div id="aba-anteriores" class="tab-content history-grid">
                    <?php if (!empty($anteriores)): ?>
                        <?php foreach ($anteriores as $ag): 
                            $estilo = getBadgeCss($ag['status']);
                        ?>
                            <div class="history-card <?= $estilo['card'] ?>">
                                <div class="history-header">
                                    <span class="history-date">📅 <?= $ag['data_formatada'] ?> às <?= $ag['hora_formatada'] ?></span>
                                    <span class="history-badge <?= $estilo['badge'] ?>"><?= $estilo['label'] ?></span>
                                </div>
                                <div class="history-body">
                                    <div>
                                        <div class="history-service"><?= htmlspecialchars($ag['nome_servico']) ?></div>
                                        <div class="history-pro">com <?= htmlspecialchars($ag['funcionario_nome']) ?></div>
                                    </div>
                                    <div class="history-price">R$ <?= $ag['preco_formatado'] ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: var(--text-muted); margin-top: 2rem;">Ainda não tens histórico de visitas.</p>
                    <?php endif; ?>
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


    <script src="<?= BASE_URL ?>/public/resources/js/historico.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/app-cliente.js"></script>
</body>
</html>