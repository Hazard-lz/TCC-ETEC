<?php
// Bloqueio de segurança e inicialização limpa
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Descobre se quem está logado é admin ou não
$isAdmin = ($_SESSION['usuario_tipo'] === 'admin');

// Pega apenas o primeiro nome para uma saudação amigável
$nomePrimeiro = explode(' ', $_SESSION['usuario_nome'] ?? 'Profissional')[0];

// ==========================================
// VARIÁVEIS INJETADAS PELO CONTROLLER
// ==========================================
$totalAgendamentosHoje = $totalAgendamentosHoje ?? 0;
$faturamentoFormatado = $faturamentoFormatado ?? '0,00';
$totalClientes = $totalClientes ?? 0;
$proximosAgendamentos = $proximosAgendamentos ?? [];

// Calcula quantos agendamentos estão "pendentes" na lista de próximos
$qtdPendentes = 0;
foreach ($proximosAgendamentos as $ag) {
    if ($ag['status'] === 'pendente') {
        $qtdPendentes++;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Inicial - Belezou App</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin-layout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/listas.css"> 
</head>
<body>

    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="dashboard-header" style="margin-bottom: 2rem;">
        <h3>Olá, <?= htmlspecialchars($nomePrimeiro) ?>! 👋</h3>
        <p><?= $isAdmin ? 'Acompanhe o desempenho geral do salão em tempo real.' : 'Aqui está o resumo do seu dia de trabalho.' ?></p>
    </div>

    <div class="summary-grid">
        <?php if ($isAdmin): ?>
            <div class="summary-card">
                <div class="card-icon" style="background-color: rgba(139, 92, 246, 0.1); color: var(--color-purple);">📅</div>
                <div class="card-info">
                    <h4>Geral Hoje</h4><span class="card-value"><?= $totalAgendamentosHoje ?></span><span class="card-label">agendamentos totais</span>
                </div>
            </div>
            <div class="summary-card">
                <div class="card-icon" style="background-color: rgba(46, 204, 113, 0.1); color: #2ecc71;">💰</div>
                <div class="card-info">
                    <h4>Faturamento</h4><span class="card-value">R$ <?= $faturamentoFormatado ?></span><span class="card-label">neste mês</span>
                </div>
            </div>
            <div class="summary-card">
                <div class="card-icon" style="background-color: rgba(52, 152, 219, 0.1); color: #3498db;">👥</div>
                <div class="card-info">
                    <h4>Clientes</h4><span class="card-value"><?= $totalClientes ?></span><span class="card-label">registados no total</span>
                </div>
            </div>
        <?php else: ?>
            <div class="summary-card">
                <div class="card-icon" style="background-color: rgba(139, 92, 246, 0.1); color: var(--color-purple);">📅</div>
                <div class="card-info">
                    <h4>Meus Hoje</h4><span class="card-value"><?= $totalAgendamentosHoje ?></span><span class="card-label">clientes na agenda</span>
                </div>
            </div>
            <div class="summary-card">
                <div class="card-icon" style="background-color: rgba(244, 91, 105, 0.1); color: var(--color-pink);">⏳</div>
                <div class="card-info">
                    <h4>Pendentes</h4><span class="card-value"><?= $qtdPendentes ?></span><span class="card-label">aguardando ação</span>
                </div>
            </div>
            <div class="summary-card">
                <div class="card-icon" style="background-color: rgba(46, 204, 113, 0.1); color: #2ecc71;">💵</div>
                <div class="card-info">
                    <h4>Faturado</h4><span class="card-value">R$ <?= $faturamentoFormatado ?></span><span class="card-label">neste mês</span>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="base-card mt-4">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="margin: 0; color: var(--text-main);"><?= $isAdmin ? 'Todos os Próximos Atendimentos' : 'Minha Agenda (Próximos)' ?></h3>
            
            <?php if ($isAdmin): ?>
                <button onclick="window.location.href='<?= BASE_URL ?>/funcionario/agenda'" class="btn-primary" style="width: auto; margin: 0; padding: 0.5rem 1rem;">Ver Agenda Completa</button>
            <?php endif; ?>
        </div>
        
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Horário</th>
                        <th>Cliente</th>
                        <th>Serviço</th>
                        <th>Status</th>
                        <?php if ($isAdmin): ?><th>Ações</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($proximosAgendamentos)): ?>
                        <?php foreach ($proximosAgendamentos as $ag): 
                            // Formata a hora para HH:MM
                            $horaFormatada = substr($ag['hora_inicio'], 0, 5);
                            
                            // Define a classe CSS do status
                            $classeStatus = 'badge-marcado'; // Fallback
                            if ($ag['status'] === 'pendente') $classeStatus = 'badge-pendente'; // (Podes criar esta classe no CSS com cor de laranja se quiseres)
                        ?>
                        <tr>
                            <td style="font-weight: bold; color: var(--color-purple);"><?= $horaFormatada ?></td>
                            <td><?= htmlspecialchars($ag['cliente_nome']) ?></td>
                            <td><?= htmlspecialchars($ag['nome_servico']) ?></td>
                            <td><span class="badge <?= $classeStatus ?>"><?= ucfirst($ag['status']) ?></span></td>
                            
                            <?php if ($isAdmin): ?>
                                <td>
                                    <div class="action-buttons">
                                        <button onclick="window.location.href='<?= BASE_URL ?>/funcionario/agenda'" class="btn-action btn-edit" title="Ver na Agenda">📅</button>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= $isAdmin ? '5' : '4' ?>" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                Nenhum agendamento futuro encontrado.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/public/resources/js/admin.js"></script>
</body>
</html>