<?php
require_once __DIR__ . '/../../../app/Helpers/Helpers.php';

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
    <?= CsrfGuard::metaTag() ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Inicial - Belezou App</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin-layout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/listas.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/modal.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/agenda.css">
    
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <?php require_once __DIR__ . '/../partials/onesignal.php'; ?>
</head>

<body>

    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="dashboard-banner">
        <div class="banner-overlay"></div>
        <div class="banner-content">
            <div class="banner-text">
                <span class="banner-greeting">Olá, <?= htmlspecialchars($nomePrimeiro) ?>! <span class="wave-emoji">👋</span></span>
                <h2 class="banner-title"><?= $isGerencia ? 'Bem-vindo ao seu painel administrativo' : 'Pronto para mais um dia de sucesso?' ?></h2>
                <p class="banner-subtitle">
                    <?= $isGerencia ? 'Acompanhe o desempenho geral do salão, gerencie agendamentos e analise métricas em tempo real.' : 'Aqui está o resumo da sua agenda e o andamento do seu dia de trabalho.' ?>
                </p>
            </div>
            <div class="banner-stats">
                <div class="banner-date-badge">
                    <i class="bi bi-calendar-check-fill"></i>
                    <span><?= Helpers::dataExtenso(date('Y-m-d')) ?></span>
                </div>
                <div id="alerta-pendentes" class="banner-pending-badge" style="display: none;">
                    <span class="pulse-ring"></span>
                    <i class="bi bi-bell-fill"></i>
                    <span><span id="contador-pendentes">0</span> pendente(s)</span>
                </div>
            </div>
        </div>
    </div>

    <div class="summary-grid">
        <?php if ($isGerencia): ?>
            <div class="summary-card">
                <div class="card-icon" style="background-color: rgba(139, 92, 246, 0.1); color: var(--color-purple);"><i class="bi bi-calendar3"></i>
                </div>
                <div class="card-info">
                    <h4>Geral Hoje</h4><span class="card-value" id="val-total-hoje"><?= $totalAgendamentosHoje ?></span><span
                        class="card-label">agendamentos totals</span>
                </div>
            </div>
            <div class="summary-card">
                <div class="card-icon" style="background-color: rgba(46, 204, 113, 0.1); color: #2ecc71;"><i class="bi bi-currency-dollar"></i></div>
                <div class="card-info">
                    <h4>Faturamento</h4><span class="card-value">R$ <span id="val-faturamento"><?= $faturamentoFormatado ?></span></span><span
                        class="card-label">neste mês</span>
                </div>
            </div>
            <div class="summary-card">
                <div class="card-icon" style="background-color: rgba(52, 152, 219, 0.1); color: #3498db;"><i class="bi bi-people-fill"></i></div>
                <div class="card-info">
                    <h4>Clientes</h4><span class="card-value" id="val-total-clientes"><?= $totalClientes ?></span><span
                        class="card-label">registados no total</span>
                </div>
            </div>
        <?php else: ?>
            <div class="summary-card">
                <div class="card-icon" style="background-color: rgba(139, 92, 246, 0.1); color: var(--color-purple);"><i class="bi bi-calendar3"></i>
                </div>
                <div class="card-info">
                    <h4>Meus Hoje</h4><span class="card-value" id="val-total-hoje"><?= $totalAgendamentosHoje ?></span><span
                        class="card-label">clientes na agenda</span>
                </div>
            </div>
            <div class="summary-card">
                <div class="card-icon" style="background-color: rgba(245, 158, 11, 0.1); color: #d97706;"><i class="bi bi-hourglass-split"></i></div>
                <div class="card-info">
                    <h4>Pendentes</h4><span class="card-value" id="val-pendentes"><?= $qtdPendentes ?></span><span
                        class="card-label">aguardando ação</span>
                </div>
            </div>
            <div class="summary-card">
                <div class="card-icon" style="background-color: rgba(46, 204, 113, 0.1); color: #2ecc71;"><i class="bi bi-wallet2"></i></div>
                <div class="card-info">
                    <h4>Faturado</h4><span class="card-value">R$ <span id="val-faturamento"><?= $faturamentoFormatado ?></span></span><span
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
                        <th>Profissional</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="tabela-proximos-corpo">
                    <?php if (!empty($proximosAgendamentos)): ?>
                        <?php foreach ($proximosAgendamentos as $ag):
                            // Formata a hora para HH:MM
                            $horaFormatada = substr($ag['hora_inicio'], 0, 5);

                            // Define as classes CSS baseadas no status
                            $statusSlug = $ag['status'] === 'pendente' ? 'pendente' : 'marcado';
                            $classeBadge = 'badge-' . $statusSlug;
                            $classeRow = 'row-' . $statusSlug;
                            ?>
                            <tr class="<?= $classeRow ?> row-agendamento-<?= $ag['id_agendamento'] ?>">
                                <td style="color: var(--text-muted);"><?= date('d/m/Y', strtotime($ag['data_agendamento'])) ?>
                                </td>
                                <td style="font-weight: bold; color: var(--text-main);"><?= $horaFormatada ?></td>
                                <td><?= htmlspecialchars($ag['cliente_nome']) ?></td>
                                <td><?= htmlspecialchars($ag['nome_servico']) ?></td>
                                <td><?= htmlspecialchars($ag['profissional_nome'] ?? 'Não definido') ?></td>
                                <td><span class="badge <?= $classeBadge ?>"><?= ucfirst($ag['status']) ?></span></td>

                                <td>
                                    <div class="action-buttons">
                                        <?php if ($ag['status'] === 'pendente'): ?>
                                            <button onclick="confirmarAgendamentoDireto(<?= $ag['id_agendamento'] ?>)" class="btn-action" style="background:#10b981; color:white; border-radius:6px; border:none; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; padding: 0; font-size:0.8rem;" title="Confirmar"><i class="bi bi-check-lg"></i></button>
                                            <button onclick="recusarAgendamentoDireto(<?= $ag['id_agendamento'] ?>)" class="btn-action" style="background:#ef4444; color:white; border-radius:6px; border:none; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; padding: 0; font-size:0.8rem;" title="Recusar"><i class="bi bi-x-lg"></i></button>
                                        <?php elseif ($isGerencia): ?>
                                            <button onclick="window.location.href='<?= BASE_URL ?>/funcionario/agenda?data=<?= $ag['data_agendamento'] ?>'"
                                                class="btn-action btn-edit" title="Ver na Agenda"><i class="bi bi-calendar-event"></i></button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7"
                                style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                Nenhum agendamento futuro encontrado.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Novo modal de lista de agendamentos pendentes -->
    <div id="modalListaPendentes" class="modal-overlay">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3>Agendamentos Pendentes</h3>
                <button data-close-modal class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="lista-pendentes-body" id="lista-pendentes-container">
                    <!-- Gerado dinamicamente via JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalhes -->
    <div id="modalDetalhes" class="modal-overlay">
        <div class="modal-content" style="max-width: 420px;">
            <div class="modal-header">
                <h3>Detalhes do Agendamento</h3>
                <button data-close-modal class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <div style="display:flex; flex-direction:column; gap:0.75rem; margin-bottom:1.25rem;">
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <span style="font-size:1.2rem; width:24px; text-align:center; color: var(--color-purple);"><i class="bi bi-person-fill"></i></span>
                        <div>
                            <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Cliente</div>
                            <div style="font-weight:600; color:var(--text-main);" id="detalhesCliente"></div>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <span style="font-size:1.2rem; width:24px; text-align:center; color: var(--color-purple);"><i class="bi bi-scissors"></i></span>
                        <div>
                            <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Serviço</div>
                            <div style="font-weight:600; color:var(--text-main);" id="detalhesServico"></div>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <span style="font-size:1.2rem; width:24px; text-align:center; color: var(--color-purple);"><i class="bi bi-briefcase-fill"></i></span>
                        <div>
                            <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Profissional</div>
                            <div style="font-weight:600; color:var(--text-main);" id="detalhesProfissional"></div>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <span style="font-size:1.2rem; width:24px; text-align:center; color: var(--color-purple);"><i class="bi bi-calendar3"></i></span>
                        <div>
                            <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Data</div>
                            <div style="font-weight:600; color:var(--text-main);" id="detalhesData"></div>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <span style="font-size:1.2rem; width:24px; text-align:center; color: var(--color-purple);"><i class="bi bi-clock-fill"></i></span>
                        <div>
                            <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Horário</div>
                            <div style="font-weight:600; color:var(--text-main);" id="detalhesHorario"></div>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <span style="font-size:1.2rem; width:24px; text-align:center; color: var(--color-purple);"><i class="bi bi-clipboard-data-fill"></i></span>
                        <div>
                            <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Status</div>
                            <span id="detalhesStatus" style="font-size:0.8rem; font-weight:700; padding:0.2rem 0.7rem; border-radius:20px;"></span>
                        </div>
                    </div>
                </div>

                <hr style="border:none; border-top:1px solid var(--border-color); margin-bottom:1.25rem;">

                <div id="areaAcoes" style="display: flex; gap: 0.5rem; flex-direction: column;">
                    <div id="boxAcoesPendente" style="display: none; gap: 0.5rem; flex-direction: column;">
                        <form action="<?= BASE_URL ?>/funcionario/agenda/status" method="POST">
                            <?= CsrfGuard::campoHidden() ?>
                            <input type="hidden" name="id_agendamento" class="inputIdAgendamento">
                            <input type="hidden" name="novo_status" value="marcado">
                            <button type="submit" class="btn-primary" style="background:#10b981; width:100%; box-shadow:0 4px 12px rgba(16,185,129,0.3);">✔ Confirmar Agendamento</button>
                        </form>
                        <form action="<?= BASE_URL ?>/funcionario/agenda/status" method="POST">
                            <?= CsrfGuard::campoHidden() ?>
                            <input type="hidden" name="id_agendamento" class="inputIdAgendamento">
                            <input type="hidden" name="novo_status" value="cancelado">
                            <button type="button" class="btn-secondary" style="width:100%; color:#ef4444; border-color:#ef4444;" onclick="event.preventDefault(); Swal.fire({title: 'Atenção', text: 'Deseja realmente recusar este agendamento?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', cancelButtonColor: '#6c757d', confirmButtonText: 'Confirmar', cancelButtonText: 'Cancelar'}).then((result) => { if (result.isConfirmed) { this.closest('form').submit(); } });">✕ Recusar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/public/resources/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/modal.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const isGerencia = <?= json_encode($isGerencia) ?>;
            const baseUrl = <?= json_encode(BASE_URL) ?>;

            function escapeHtml(str) {
                if (!str) return '';
                return str.replace(/&/g, '&amp;')
                          .replace(/</g, '&lt;')
                          .replace(/>/g, '&gt;')
                          .replace(/"/g, '&quot;')
                          .replace(/'/g, '&#039;');
            }

            // Sintetiza um chime agradável via Web Audio API para notificação
            function playChime() {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
                    osc.frequency.setValueAtTime(880, ctx.currentTime + 0.15); // A5
                    gain.gain.setValueAtTime(0.08, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.45);
                    osc.start(ctx.currentTime);
                    osc.stop(ctx.currentTime + 0.45);
                } catch (e) {
                    console.warn('Web Audio API não suportada ou bloqueada:', e);
                }
            }

            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });

            // ─── Carregamento Instantâneo com Cache Local (PWA-style) ───
            const cacheDashboard = localStorage.getItem('belezou_dashboard_cache');
            if (cacheDashboard) {
                try {
                    const cached = JSON.parse(cacheDashboard);
                    const valTotalHoje = document.getElementById('val-total-hoje');
                    if (valTotalHoje) valTotalHoje.textContent = cached.totalAgendamentosHoje;
                    const valFaturamento = document.getElementById('val-faturamento');
                    if (valFaturamento) valFaturamento.textContent = cached.faturamentoFormatado;
                    const valTotalClientes = document.getElementById('val-total-clientes');
                    if (valTotalClientes) valTotalClientes.textContent = cached.totalClientes;
                    const valPendentes = document.getElementById('val-pendentes');
                    if (valPendentes) valPendentes.textContent = cached.qtdPendentes;
                    atualizarTabela(cached.proximosAgendamentos);
                } catch (e) {
                    console.error('Erro ao ler cache offline do dashboard:', e);
                }
            }

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
 
            // ─── Atualização Dinâmica do Dashboard via AJAX (Fetch) ───
            function atualizarDashboard() {
                fetch(baseUrl + '/funcionario/dashboard?ajax=1', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    localStorage.setItem('belezou_dashboard_cache', JSON.stringify(data));

                    const valTotalHoje = document.getElementById('val-total-hoje');
                    if (valTotalHoje) valTotalHoje.textContent = data.totalAgendamentosHoje;
                    const valFaturamento = document.getElementById('val-faturamento');
                    if (valFaturamento) valFaturamento.textContent = data.faturamentoFormatado;
                    const valTotalClientes = document.getElementById('val-total-clientes');
                    if (valTotalClientes) valTotalClientes.textContent = data.totalClientes;
                    const valPendentes = document.getElementById('val-pendentes');
                    if (valPendentes) valPendentes.textContent = data.qtdPendentes;
                    atualizarTabela(data.proximosAgendamentos);
                })
                .catch(err => console.error('Erro ao atualizar dashboard:', err));
            }

            function atualizarTabela(proximos) {
                const tbody = document.getElementById('tabela-proximos-corpo');
                if (!tbody) return;
                if (!proximos || proximos.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; padding: 2rem; color: var(--text-muted);">Nenhum agendamento futuro encontrado.</td></tr>`;
                    return;
                }
                let html = '';
                proximos.forEach(ag => {
                    const statusSlug = ag.status === 'pendente' ? 'pendente' : 'marcado';
                    const badgeClass = 'badge-' + statusSlug;
                    const rowClass = 'row-' + statusSlug;
                    html += `<tr class="${rowClass} row-agendamento-${ag.id_agendamento}">
                        <td style="color: var(--text-muted);">${ag.data_agendamento}</td>
                        <td style="font-weight: bold; color: var(--text-main);">${ag.hora_inicio}</td>
                        <td>${escapeHtml(ag.cliente_nome)}</td>
                        <td>${escapeHtml(ag.nome_servico)}</td>
                        <td>${escapeHtml(ag.profissional_nome || 'Não definido')}</td>
                        <td><span class="badge ${badgeClass}">${ag.status_ucfirst}</span></td>
                        <td>
                            <div class="action-buttons">`;
                    if (ag.status === 'pendente') {
                        html += `
                            <button onclick="confirmarAgendamentoDireto(${ag.id_agendamento})" class="btn-action" style="background:#10b981; color:white; border-radius:6px; border:none; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; padding: 0; font-size:0.8rem;" title="Confirmar"><i class="bi bi-check-lg"></i></button>
                            <button onclick="recusarAgendamentoDireto(${ag.id_agendamento})" class="btn-action" style="background:#ef4444; color:white; border-radius:6px; border:none; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; padding: 0; font-size:0.8rem;" title="Recusar"><i class="bi bi-x-lg"></i></button>
                        `;
                    } else if (isGerencia) {
                        html += `<button onclick="window.location.href='${baseUrl}/funcionario/agenda?data=${ag.data_agendamento_raw}'" class="btn-action btn-edit" title="Ver na Agenda"><i class="bi bi-calendar-event"></i></button>`;
                    }
                    html += `</div></td></tr>`;
                });
                tbody.innerHTML = html;
            }

            // ─── Sistema de Alerta de Agendamentos Pendentes ───
            let cachePendentes = [];
            function atualizarAlertasPendentes(isFirstLoad = false) {
                fetch(baseUrl + '/api/agendamentos-pendentes')
                    .then(res => res.json())
                    .then(data => {
                        const novosPendentes = data || [];
                        if (!isFirstLoad && novosPendentes.length > cachePendentes.length) {
                            playChime();
                            Toast.fire({
                                icon: 'info',
                                title: 'Novo agendamento pendente recebido!'
                            });
                        }
                        cachePendentes = novosPendentes;
                        const alerta = document.getElementById('alerta-pendentes');
                        const contador = document.getElementById('contador-pendentes');
                        if (cachePendentes.length > 0) {
                            alerta.style.display = 'inline-flex';
                            contador.textContent = cachePendentes.length;
                        } else {
                            alerta.style.display = 'none';
                        }
                    })
                    .catch(err => console.error('Erro ao buscar pendentes:', err));
            }

            document.getElementById('alerta-pendentes').addEventListener('click', () => {
                const container = document.getElementById('lista-pendentes-container');
                if (cachePendentes.length === 0) {
                    container.innerHTML = '<p style="text-align:center; color:var(--text-muted); padding:1rem;">Nenhum agendamento pendente encontrado.</p>';
                } else {
                    let html = '';
                    cachePendentes.forEach(p => {
                        html += `<div class="pendente-item-card" onclick="verDetalhesPendente(${p.id_agendamento})">
                            <div class="pendente-item-info">
                                <div class="pendente-item-cliente"><i class="bi bi-person-fill"></i> ${escapeHtml(p.cliente_nome || '')}</div>
                                <div class="pendente-item-servico"><i class="bi bi-scissors"></i> ${escapeHtml(p.nome_servico || '')}</div>
                            </div>
                            <div class="pendente-item-meta">
                                <span class="pendente-item-data"><i class="bi bi-calendar3"></i> ${p.data_formatada || ''}</span>
                                <span class="pendente-item-hora"><i class="bi bi-clock"></i> ${p.hora_inicio_formatada || ''}</span>
                            </div>
                        </div>`;
                    });
                    container.innerHTML = html;
                }
                openModal('#modalListaPendentes');
            });

            window.verDetalhesPendente = function(idAgendamento) {
                const item = cachePendentes.find(p => p.id_agendamento == idAgendamento);
                if (!item) return;
                closeModal('#modalListaPendentes');
                setTimeout(() => {
                    document.getElementById('detalhesCliente').textContent = item.cliente_nome || '';
                    document.getElementById('detalhesServico').textContent = item.nome_servico || '';
                    document.getElementById('detalhesProfissional').textContent = item.profissional_nome || '';
                    document.getElementById('detalhesData').textContent = item.data_formatada || '';
                    document.getElementById('detalhesHorario').textContent = (item.hora_inicio_formatada || '') + ' - ' + (item.hora_fim_formatada || '');
                    const statusEl = document.getElementById('detalhesStatus');
                    statusEl.textContent = 'Pendente';
                    statusEl.className = 'status-badge status-badge-pendente';
                    document.querySelectorAll('.inputIdAgendamento').forEach(input => { input.value = idAgendamento; });
                    document.getElementById('boxAcoesPendente').style.display = 'flex';
                    openModal('#modalDetalhes');
                }, 200);
            };

            document.querySelectorAll('#modalDetalhes form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    fetch(this.action, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(() => {
                        closeModal('#modalDetalhes');
                        const novoStatus = formData.get('novo_status');
                        const msg = novoStatus === 'marcado' ? 'Agendamento confirmado com sucesso!' : 'Agendamento recusado com sucesso!';
                        Swal.fire({ title: 'Sucesso!', text: msg, icon: 'success', customClass: { popup: 'swal-belezou-popup', title: 'swal-belezou-title', htmlContainer: 'swal-belezou-text', confirmButton: 'swal-belezou-btn-confirm' }, buttonsStyling: false });
                        atualizarDashboard();
                        atualizarAlertasPendentes();
                    })
                    .catch(err => {
                        console.error('Erro ao alterar status:', err);
                        Swal.fire({ title: 'Erro!', text: 'Não foi possível atualizar o status.', icon: 'error', customClass: { popup: 'swal-belezou-popup', title: 'swal-belezou-title', htmlContainer: 'swal-belezou-text', confirmButton: 'swal-belezou-btn-danger' }, buttonsStyling: false });
                    });
                });
            });

            // ─── Lógica de Optimistic UI e Ações Diretas na Tabela ───
            window.confirmarAgendamentoDireto = function(id) {
                const tbody = document.getElementById('tabela-proximos-corpo');
                const oldHtml = tbody.innerHTML;
                const oldCacheDashboard = localStorage.getItem('belezou_dashboard_cache');

                // Alteração otimista de forma instantânea
                    const row = document.querySelector('.row-agendamento-' + id);
                if (row) {
                    row.className = 'row-marcado row-agendamento-' + id;
                    const badge = row.querySelector('.badge');
                    if (badge) {
                        badge.className = 'badge badge-marcado';
                        badge.textContent = 'Marcado';
                    }
                    const actionContainer = row.querySelector('.action-buttons');
                    if (actionContainer) {
                        if (isGerencia) {
                            let dataRaw = '';
                            if (oldCacheDashboard) {
                                try {
                                    const parsed = JSON.parse(oldCacheDashboard);
                                    const ag = parsed.proximosAgendamentos.find(x => x.id_agendamento == id);
                                    if (ag) dataRaw = ag.data_agendamento_raw;
                                } catch(e){}
                            }
                            actionContainer.innerHTML = `<button onclick="window.location.href='${baseUrl}/funcionario/agenda?data=${dataRaw}'" class="btn-action btn-edit" title="Ver na Agenda"><i class="bi bi-calendar-event"></i></button>`;
                        } else {
                            actionContainer.innerHTML = '';
                        }
                    }
                }

                const valPendentes = document.getElementById('val-pendentes');
                if (valPendentes) {
                    const currentVal = parseInt(valPendentes.textContent) || 0;
                    if (currentVal > 0) valPendentes.textContent = currentVal - 1;
                }

                const formData = new FormData();
                formData.append('id_agendamento', id);
                formData.append('novo_status', 'marcado');
                formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                fetch(baseUrl + '/funcionario/agenda/status', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(() => {
                    Toast.fire({ icon: 'success', title: 'Agendamento confirmado com sucesso!' });
                    atualizarDashboard();
                    atualizarAlertasPendentes();
                })
                .catch(err => {
                    console.error('Erro na confirmação otimista:', err);
                    tbody.innerHTML = oldHtml;
                    if (oldCacheDashboard) localStorage.setItem('belezou_dashboard_cache', oldCacheDashboard);
                    
                    Swal.fire({
                        title: 'Erro!',
                        text: 'Não foi possível confirmar o agendamento.',
                        icon: 'error',
                        customClass: { popup: 'swal-belezou-popup', title: 'swal-belezou-title', htmlContainer: 'swal-belezou-text', confirmButton: 'swal-belezou-btn-danger' },
                        buttonsStyling: false
                    });
                    atualizarDashboard();
                    atualizarAlertasPendentes();
                });
            };

            window.recusarAgendamentoDireto = function(id) {
                Swal.fire({
                    title: 'Atenção',
                    text: 'Deseja realmente recusar este agendamento?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Confirmar',
                    cancelButtonText: 'Cancelar',
                    customClass: { popup: 'swal-belezou-popup', title: 'swal-belezou-title', htmlContainer: 'swal-belezou-text', confirmButton: 'swal-belezou-btn-danger', cancelButton: 'swal-belezou-btn-confirm' },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        const tbody = document.getElementById('tabela-proximos-corpo');
                        const oldHtml = tbody.innerHTML;
                        const oldCacheDashboard = localStorage.getItem('belezou_dashboard_cache');

                        const row = document.querySelector('.row-agendamento-' + id);
                        if (row) row.style.display = 'none';

                        const valPendentes = document.getElementById('val-pendentes');
                        if (valPendentes) {
                            const currentVal = parseInt(valPendentes.textContent) || 0;
                            if (currentVal > 0) valPendentes.textContent = currentVal - 1;
                        }

                        const formData = new FormData();
                        formData.append('id_agendamento', id);
                        formData.append('novo_status', 'cancelado');
                        formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                        fetch(baseUrl + '/funcionario/agenda/status', {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(() => {
                            Toast.fire({ icon: 'success', title: 'Agendamento recusado com sucesso!' });
                            atualizarDashboard();
                            atualizarAlertasPendentes();
                        })
                        .catch(err => {
                            console.error('Erro na recusa otimista:', err);
                            tbody.innerHTML = oldHtml;
                            if (oldCacheDashboard) localStorage.setItem('belezou_dashboard_cache', oldCacheDashboard);

                            Swal.fire({
                                title: 'Erro!',
                                text: 'Não foi possível recusar o agendamento.',
                                icon: 'error',
                                customClass: { popup: 'swal-belezou-popup', title: 'swal-belezou-title', htmlContainer: 'swal-belezou-text', confirmButton: 'swal-belezou-btn-danger' },
                                buttonsStyling: false
                            });
                            atualizarDashboard();
                            atualizarAlertasPendentes();
                        });
                    }
                });
            };

            // ─── Atualização Periódica via Polling AJAX (Evita sobrecarga no servidor) ───
            function inicializarPolling() {
                // Executa a cada 30 segundos (30000ms)
                setInterval(() => {
                    atualizarDashboard();
                    atualizarAlertasPendentes();
                }, 30000);
            }

            // Inicialização
            atualizarDashboard();
            atualizarAlertasPendentes(true); // Flag true impede chime no primeiro load
            inicializarPolling();
        });
    </script>
</body>

</html>