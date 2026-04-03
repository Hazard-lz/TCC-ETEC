<?php
// Bloqueio de segurança (fallback caso o utilizador aceda à view diretamente)
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['usuario_id'])) {
    header("Location: " . BASE_URL . "/login");
    exit;
}

$clienteNome = $_SESSION['usuario_nome'] ?? 'Cliente';

// ==========================================
// LÓGICA DE UI: SAUDAÇÃO BASEADA NO TEMPO
// ==========================================
// Força o fuso horário do Brasil para não pegar a hora errada do servidor
date_default_timezone_set('America/Sao_Paulo'); 

$horaAtual = (int) date('H');

if ($horaAtual >= 5 && $horaAtual < 12) {
    $saudacao = "Bom dia,";
} elseif ($horaAtual >= 12 && $horaAtual < 18) {
    $saudacao = "Boa tarde,";
} else {
    $saudacao = "Boa noite,";
}

// Inicializa as outras variáveis como nulas/vazias para evitar o erro "unset" 
// caso o Controller não as envie por algum motivo.
$proximoAgendamento = $proximoAgendamento ?? null;
$servicosPopulares = $servicosPopulares ?? [];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Belezou App</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/app-cliente.css">
</head>

<body>

    <div class="app-wrapper">
        <div class="mobile-container">

            <header class="app-header">
                <div class="greeting">
                    <p><?= $saudacao ?></p>
                    <h2><?= htmlspecialchars($clienteNome) ?></h2>
                </div>
                <div class="avatar">
                    <?= substr($clienteNome, 0, 1) ?>
                </div>
            </header>

            <main class="app-content">

                <?php if ($proximoAgendamento): ?>
                    <div class="next-appointment-card" style="grid-column: 1 / -1;">
                        <span class="appointment-date">📅 <?= $proximoAgendamento['data_display'] ?></span>
                        <h4 class="appointment-service"><?= htmlspecialchars($proximoAgendamento['nome_servico']) ?></h4>
                        <span class="appointment-pro">com <?= htmlspecialchars($proximoAgendamento['funcionario_nome']) ?></span>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 2rem 0; color: var(--text-muted); grid-column: 1 / -1;">
                        <p style="font-size: 2rem; margin-bottom: 0.5rem;">💅</p>
                        <p>Você não possui agendamentos futuros.</p>
                    </div>
                <?php endif; ?>

                <a href="<?= BASE_URL ?>/agendar" class="btn-agendar-agora">
                    <span style="font-size: 1.5rem;">+</span> Novo Agendamento
                </a>

                <h3 class="section-title">Serviços Populares</h3>
                
                <?php if (!empty($servicosPopulares)): ?>
                    <?php foreach ($servicosPopulares as $servico): ?>
                        <div class="base-card" style="padding: 1.2rem; display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                            <div>
                                <h4 style="color: var(--text-main); font-size: 1.1rem; margin-bottom: 0.3rem;"><?= htmlspecialchars($servico['nome_servico']) ?></h4>
                                <p style="color: var(--text-muted); font-size: 0.9rem;">
                                    R$ <?= number_format($servico['preco'], 2, ',', '.') ?> • <?= $servico['duracao'] ?> min
                                </p>
                            </div>
                            <a href="<?= BASE_URL ?>/agendar" class="btn-primary" style="width: auto; margin: 0; padding: 0.5rem 1.2rem; border-radius: 20px; font-size: 0.9rem; text-decoration: none; display: inline-block;">
                                Agendar
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: var(--text-muted);">Nenhum serviço disponível no momento.</p>
                <?php endif; ?>

            </main>

            <nav class="bottom-nav">
                <a href="<?= BASE_URL ?>/" class="nav-item active">
                    <span class="nav-icon">🏠</span><span>Início</span>
                </a>
                <a href="<?= BASE_URL ?>/agendar" class="nav-item">
                    <span class="nav-icon">📅</span><span>Agendar</span>
                </a>
                <a href="<?= BASE_URL ?>/historico" class="nav-item">
                    <span class="nav-icon">🕒</span><span>Histórico</span>
                </a>
                <a href="<?= BASE_URL ?>/perfil" class="nav-item">
                    <span class="nav-icon">👤</span><span>Perfil</span>
                </a>
            </nav>

        </div>
    </div>

    <script src="<?= BASE_URL ?>/public/resources/js/app-cliente.js"></script>
</body>
</html>