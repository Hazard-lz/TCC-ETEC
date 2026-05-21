<?php
// Bloqueio de segurança (fallback caso o usuário acesse a view diretamente)
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['usuario_id'])) {
    header("Location: " . BASE_URL . "/login");
    exit;
}

$clienteNome = $_SESSION['usuario_nome'] ?? 'Cliente';
$primeiroNome = explode(' ', trim($clienteNome))[0];

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
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/resources/images/favicon.png">

    <!-- SweetAlert2 — necessário para o botão "Sair da Conta" -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/app-cliente.css">
    <?php require_once __DIR__ . '/../partials/onesignal.php'; ?>
</head>

<body>

    <div class="app-wrapper">
        <div class="mobile-container">

            <header class="app-header">
                <div class="greeting">
                    <p><?= $saudacao ?></p>
                    <h2><?= htmlspecialchars($primeiroNome) ?></h2>
                </div>
                <div style="position: relative;">
                    <div id="btnProfileDropdown" class="avatar" style="cursor: pointer;">
                        <?= strtoupper(substr($clienteNome, 0, 1)) ?>
                    </div>
                    
                    <div id="profileMenu" class="profile-menu shadow" style="display: none;">
                        <a href="<?= BASE_URL ?>/perfil" class="profile-dropdown-item"><i class="bi bi-person me-2"></i> Editar Perfil</a>
                        <a href="javascript:void(0)" onclick="confirmarSaida('<?= BASE_URL ?>/login/sair')" class="profile-dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Sair da Conta</a>
                    </div>
                </div>
            </header>

            <main class="app-content">

                <div class="home-hero" style="grid-column: 1 / -1; width: 100%;">
                    <?php if ($proximoAgendamento): ?>
                        <div class="next-appointment-card">
                            <span class="appointment-date">📅 <?= $proximoAgendamento['data_display'] ?></span>
                            <h4 class="appointment-service"><?= htmlspecialchars($proximoAgendamento['nome_servico']) ?></h4>
                            <span class="appointment-pro">com <?= htmlspecialchars($proximoAgendamento['funcionario_nome']) ?></span>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 2rem 0; color: var(--text-muted);">
                            <p style="font-size: 2rem; margin-bottom: 0.5rem;">💅</p>
                            <p>Você não possui agendamentos futuros.</p>
                        </div>
                    <?php endif; ?>

                    <a href="<?= BASE_URL ?>/agendar" class="btn-agendar-agora">
                        <span style="font-size: 1.5rem;">+</span> Novo Agendamento
                    </a>
                </div>

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
                <a href="<?= BASE_URL ?>/cliente/ajuda" class="nav-item">
                    <span class="nav-icon"><i class="bi bi-question-circle" style="font-size: 1.2rem;"></i></span><span>Ajuda</span>
                </a>
            </nav>

        </div>
    </div>

    <script src="<?= BASE_URL ?>/public/resources/js/app-cliente.js"></script>
</body>
</html>