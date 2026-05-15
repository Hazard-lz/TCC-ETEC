<?php
// Descobre se quem está logado é gerência (admin ou subadmin)
$isGerencia = in_array($_SESSION['usuario_tipo'] ?? '', ['admin', 'subadmin']);

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
    
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <?php require_once __DIR__ . '/../partials/onesignal.php'; ?>
</head>

<body>

    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="dashboard-header" style="margin-bottom: 2rem;">
        <h3>Olá, <?= htmlspecialchars($nomePrimeiro) ?>! 👋</h3>
        <p><?= $isGerencia ? 'Acompanhe o desempenho geral do salão em tempo real.' : 'Aqui está o resumo do seu dia de trabalho.' ?>
        </p>
    </div>

    <div class="summary-grid">
        <?php if ($isGerencia): ?>
            <div class="summary-card">
                <div class="card-icon" style="background-color: rgba(139, 92, 246, 0.1); color: var(--color-purple);">📅
                </div>
                <div class="card-info">
                    <h4>Geral Hoje</h4><span class="card-value"><?= $totalAgendamentosHoje ?></span><span
                        class="card-label">agendamentos totais</span>
                </div>
            </div>
            <div class="summary-card">
                <div class="card-icon" style="background-color: rgba(46, 204, 113, 0.1); color: #2ecc71;">💰</div>
                <div class="card-info">
                    <h4>Faturamento</h4><span class="card-value">R$ <?= $faturamentoFormatado ?></span><span
                        class="card-label">neste mês</span>
                </div>
            </div>
            <div class="summary-card">
                <div class="card-icon" style="background-color: rgba(52, 152, 219, 0.1); color: #3498db;">👥</div>
                <div class="card-info">
                    <h4>Clientes</h4><span class="card-value"><?= $totalClientes ?></span><span
                        class="card-label">registados no total</span>
                </div>
            </div>
        <?php else: ?>
            <div class="summary-card">
                <div class="card-icon" style="background-color: rgba(139, 92, 246, 0.1); color: var(--color-purple);">📅
                </div>
                <div class="card-info">
                    <h4>Meus Hoje</h4><span class="card-value"><?= $totalAgendamentosHoje ?></span><span
                        class="card-label">clientes na agenda</span>
                </div>
            </div>
            <div class="summary-card">
                <div class="card-icon" style="background-color: rgba(244, 91, 105, 0.1); color: var(--color-pink);">⏳</div>
                <div class="card-info">
                    <h4>Pendentes</h4><span class="card-value"><?= $qtdPendentes ?></span><span
                        class="card-label">aguardando ação</span>
                </div>
            </div>
            <div class="summary-card">
                <div class="card-icon" style="background-color: rgba(46, 204, 113, 0.1); color: #2ecc71;">💵</div>
                <div class="card-info">
                    <h4>Faturado</h4><span class="card-value">R$ <?= $faturamentoFormatado ?></span><span
                        class="card-label">neste mês</span>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="base-card mt-4">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="margin: 0; color: var(--text-main);">
                <?= $isGerencia ? 'Todos os Próximos Atendimentos' : 'Minha Agenda (Próximos)' ?>
            </h3>

            <?php if ($isGerencia): ?>
                <button onclick="window.location.href='<?= BASE_URL ?>/funcionario/agenda'" class="btn-primary"
                    style="width: auto; margin: 0; padding: 0.5rem 1rem;">Ver Agenda Completa</button>
            <?php endif; ?>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Horário</th>
                        <th>Cliente</th>
                        <th>Serviço</th>
                        <th>Status</th>
                        <?php if ($isGerencia): ?>
                            <th>Ações</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($proximosAgendamentos)): ?>
                        <?php foreach ($proximosAgendamentos as $ag):
                            // Formata a hora para HH:MM
                            $horaFormatada = substr($ag['hora_inicio'], 0, 5);

                            // Define as classes CSS baseadas no status
                            $statusSlug = $ag['status'] === 'pendente' ? 'pendente' : 'marcado';
                            $classeBadge = 'badge-' . $statusSlug;
                            $classeRow = 'row-' . $statusSlug;
                            ?>
                            <tr class="<?= $classeRow ?>">
                                <td style="color: var(--text-muted);"><?= date('d/m/Y', strtotime($ag['data_agendamento'])) ?>
                                </td>
                                <td style="font-weight: bold; color: var(--color-purple);"><?= $horaFormatada ?></td>
                                <td><?= htmlspecialchars($ag['cliente_nome']) ?></td>
                                <td><?= htmlspecialchars($ag['nome_servico']) ?></td>
                                <td><span class="badge <?= $classeBadge ?>"><?= ucfirst($ag['status']) ?></span></td>

                                <?php if ($isGerencia): ?>
                                    <td>
                                        <div class="action-buttons">
                                            <button onclick="window.location.href='<?= BASE_URL ?>/funcionario/agenda'"
                                                class="btn-action btn-edit" title="Ver na Agenda">📅</button>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= $isGerencia ? '6' : '5' ?>"
                                style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                Nenhum agendamento futuro encontrado.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/public/resources/js/admin.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // ─── Exibição de Alertas (Flash Messages) via SweetAlert ───
            <?php if (isset($_SESSION['flash_sucesso'])): ?>
                Swal.fire({
                    title: 'Sucesso!',
                    text: '<?= $_SESSION['flash_sucesso']; unset($_SESSION['flash_sucesso']); ?>',
                    icon: 'success',
                    customClass: {
                        popup: 'swal-belezou-popup',
                        title: 'swal-belezou-title',
                        htmlContainer: 'swal-belezou-text',
                        confirmButton: 'swal-belezou-btn-confirm'
                    },
                    buttonsStyling: false
                });
            <?php endif; ?>

            <?php if (isset($_SESSION['flash_erro'])): ?>
                Swal.fire({
                    title: 'Ops!',
                    text: '<?= $_SESSION['flash_erro']; unset($_SESSION['flash_erro']); ?>',
                    icon: 'error',
                    customClass: {
                        popup: 'swal-belezou-popup',
                        title: 'swal-belezou-title',
                        htmlContainer: 'swal-belezou-text',
                        confirmButton: 'swal-belezou-btn-danger'
                    },
                    buttonsStyling: false
                });
            <?php endif; ?>

            // ─── Refresh Dinâmico (Polling) ───
            // Recarrega o dashboard a cada 30 segundos para manter os números reais
            setInterval(() => {
                if (!document.hidden) {
                    window.location.reload();
                }
            }, 30000);
        });
    </script>
</body>

</html>