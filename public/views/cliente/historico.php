<?php
// Bloqueia o acesso de quem não fez login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: " . BASE_URL . "/login");
    exit;
}
$mostrarAnterioresAtivo = (!empty($_GET['data_inicio']) || !empty($_GET['data_fim']) || ($_GET['tab'] ?? '') === 'anteriores');
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <?= CsrfGuard::metaTag() ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Histórico - Belezou App</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/resources/images/favicon.png">

    <!-- SweetAlert2 — confirmações de cancelamento -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/app-cliente.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/historico.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/modal.css">
    
    <!-- Flatpickr (Calendário Estilizado) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>

    <script>
        const BASE_URL = '<?= BASE_URL ?>';
        const LIMITE_FUTURO_DIAS = '<?= htmlspecialchars($limiteFuturoDias ?? "sem_limite") ?>';
    </script>
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
                    <button class="tab-btn <?= !$mostrarAnterioresAtivo ? 'active' : '' ?>" onclick="mudarAba('proximos', this)">Próximos</button>
                    <button class="tab-btn <?= $mostrarAnterioresAtivo ? 'active' : '' ?>" onclick="mudarAba('anteriores', this)">Anteriores</button>
                </div>

                <?php
                // Função auxiliar para mapear o status do BD para as tuas classes de CSS
                function getBadgeCss($status)
                {
                    $map = [
                        'pendente' => ['card' => 'status-pendente', 'badge' => 'badge-orange', 'label' => 'Pendente'],
                        'marcado' => ['card' => 'status-marcado', 'badge' => 'badge-purple', 'label' => 'Marcado'],
                        'concluido' => ['card' => 'status-concluido', 'badge' => 'badge-green', 'label' => 'Concluído'],
                        'cancelado' => ['card' => 'status-cancelado', 'badge' => 'badge-pink', 'label' => 'Cancelado']
                    ];
                    return $map[$status] ?? ['card' => '', 'badge' => '', 'label' => ucfirst($status)];
                }
                ?>

                <div id="aba-proximos" class="tab-content <?= !$mostrarAnterioresAtivo ? 'active' : '' ?> history-grid">
                    <div id="proximos-cards-list" style="display: contents;">
                        <?php if (!empty($proximos)): ?>
                            <?php foreach ($proximos as $ag):
                                $estilo = getBadgeCss($ag['status']);
                                $podeCancelar = false;
                                if ($ag['status'] === 'pendente') {
                                    $podeCancelar = true;
                                } elseif ($ag['status'] === 'marcado') {
                                    date_default_timezone_set('America/Sao_Paulo');
                                    $dataHoraAgendamento = new DateTime($ag['data_agendamento'] . ' ' . $ag['hora_inicio']);
                                    $agora = new DateTime();
                                    if ($dataHoraAgendamento > $agora) {
                                        $intervalo = $agora->diff($dataHoraAgendamento);
                                        $horasDiferenca = ($intervalo->days * 24) + $intervalo->h + ($intervalo->i / 60);
                                        if ($horasDiferenca >= $antecedenciaHoras) {
                                            $podeCancelar = true;
                                        }
                                    }
                                }
                                ?>
                                <div class="history-card <?= $estilo['card'] ?>">
                                    <div class="history-header">
                                        <span class="history-date">📅 <?= $ag['data_formatada'] ?> às
                                            <?= $ag['hora_formatada'] ?></span>
                                        <span class="history-badge <?= $estilo['badge'] ?>"><?= $estilo['label'] ?></span>
                                    </div>
                                    <div class="history-body">
                                        <div>
                                            <div class="history-service"><?= htmlspecialchars($ag['nome_servico']) ?></div>
                                            <div class="history-pro">com <?= htmlspecialchars($ag['funcionario_nome']) ?></div>
                                        </div>
                                        <div class="history-price">R$ <?= $ag['preco_formatado'] ?></div>
                                    </div>
                                    <?php if ($podeCancelar): ?>
                                        <div style="margin-top: 15px; border-top: 1px solid var(--border-color); padding-top: 10px; display: flex; gap: 10px;">
                                            <form id="form-cancelar-<?= $ag['id_agendamento'] ?>" action="<?= BASE_URL ?>/historico/cancelar" method="POST" style="flex: 1;">
                                                <?= CsrfGuard::campoHidden() ?>
                                                <input type="hidden" name="id_agendamento" value="<?= $ag['id_agendamento'] ?>">
                                                <button type="button"
                                                    onclick="cancelarAgendamento(<?= $ag['id_agendamento'] ?>)"
                                                    style="width: 100%; padding: 10px; border-radius: 8px; background-color: #ef4444; color: white; border: none; cursor: pointer; font-weight: 600; font-size: 0.85rem;">Cancelar</button>
                                            </form>
                                            <button type="button"
                                                onclick="abrirModalRemarcar(<?= $ag['id_agendamento'] ?>, '<?= htmlspecialchars($ag['nome_servico'], ENT_QUOTES) ?>', '<?= htmlspecialchars($ag['funcionario_nome'], ENT_QUOTES) ?>', <?= $ag['cod_funcionario'] ?>, <?= $ag['id_servico'] ?>, '<?= $ag['status'] ?>')"
                                                style="flex: 1; padding: 10px; border-radius: 8px; background-color: var(--color-purple); color: white; border: none; cursor: pointer; font-weight: 600; font-size: 0.85rem;">Remarcar</button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="bi bi-calendar2-x"></i>
                                <p>Não tem agendamentos futuros.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div id="aba-anteriores" class="tab-content <?= $mostrarAnterioresAtivo ? 'active' : '' ?> history-grid">
                    <!-- Filtro de Data para Histórico Passado -->
                    <div class="filter-section" style="margin-bottom: 1.5rem; max-width: 100%; grid-column: 1 / -1; width: 100%;">
                        <form action="<?= BASE_URL ?>/historico" method="GET" class="client-filter-form">
                            <!-- De: -->
                            <div class="filter-field" style="display: flex; flex-direction: column; gap: 0.3rem; margin-bottom: 0;">
                                <label for="data_inicio" style="font-weight: 600; font-size: 0.85rem; color: var(--text-main);">De:</label>
                                <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="<?= htmlspecialchars($_GET['data_inicio'] ?? '') ?>" placeholder="Selecione..." style="height: 38px; border-radius: 8px; border: 1px solid var(--border-color); padding: 0.5rem; font-family: inherit; font-size: 0.9rem; background: var(--surface-color); color: var(--text-main); width: 100%;">
                            </div>
                            <!-- Até: -->
                            <div class="filter-field" style="display: flex; flex-direction: column; gap: 0.3rem; margin-bottom: 0;">
                                <label for="data_fim" style="font-weight: 600; font-size: 0.85rem; color: var(--text-main);">Até:</label>
                                <input type="date" name="data_fim" id="data_fim" class="form-control" value="<?= htmlspecialchars($_GET['data_fim'] ?? '') ?>" placeholder="Selecione..." style="height: 38px; border-radius: 8px; border: 1px solid var(--border-color); padding: 0.5rem; font-family: inherit; font-size: 0.9rem; background: var(--surface-color); color: var(--text-main); width: 100%;">
                            </div>
                            
                            <!-- Botão Filtrar -->
                            <?php $hasFilter = (!empty($_GET['data_inicio']) || !empty($_GET['data_fim'])); ?>
                            <button type="submit" class="btn-primary filter-btn" style="height: 38px; display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; font-weight: 600; font-size: 0.9rem; border-radius: 8px; border: none; background: var(--gradient-brand, linear-gradient(135deg, #8b5cf6, #ec4899)); color: white; padding: 0; width: 100%; <?= !$hasFilter ? 'grid-column: span 2;' : '' ?>"><i class="bi bi-filter"></i> Filtrar</button>
                            
                            <!-- Botão Limpar -->
                            <?php if ($hasFilter): ?>
                                <a href="<?= BASE_URL ?>/historico?tab=anteriores" class="btn-secondary" style="height: 38px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 0.9rem; border: 1px solid var(--border-color); background: var(--surface-color); color: var(--text-main); padding: 0; width: 100%;">Limpar</a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; width: 100%; grid-column: 1 / -1;">
                        <h4 style="margin: 0; color: var(--text-main); font-size: 0.95rem; font-weight: 600;">Total: <span id="total-anteriores-count" style="font-weight: 700; color: var(--color-purple);"><?= count($anteriores) ?> agendamentos</span></h4>
                    </div>

                    <div id="anteriores-cards-list" style="display: contents;">
                        <?php if (!empty($anteriores)): ?>
                            <?php foreach ($anteriores as $ag):
                                $estilo = getBadgeCss($ag['status']);
                                ?>
                                <div class="history-card <?= $estilo['card'] ?>">
                                    <div class="history-header">
                                        <span class="history-date">📅 <?= $ag['data_formatada'] ?> às
                                            <?= $ag['hora_formatada'] ?></span>
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
                            <div class="empty-state">
                                <i class="bi bi-clock-history"></i>
                                <p>Sem agendamentos anteriores.</p>
                            </div>
                        <?php endif; ?>
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
                <a href="<?= BASE_URL ?>/cliente/ajuda" class="nav-item">
                    <span class="nav-icon"><i class="bi bi-question-circle" style="font-size: 1.2rem;"></i></span><span>Ajuda</span>
                </a>
            </nav>

        </div>
    </div>

    <!-- Modal de Remarcação -->
    <div id="modalRemarcar" class="modal-overlay">
        <div class="modal-content" style="max-width: 450px;">
            <div class="modal-header">
                <h3>Remarcar Horário</h3>
                <button type="button" class="btn-close" onclick="fecharModalRemarcar()">&times;</button>
            </div>
            
            <div class="modal-body">
                <form action="<?= BASE_URL ?>/historico/remarcar" method="POST" id="formRemarcar">
                    <?= CsrfGuard::campoHidden() ?>
                    <input type="hidden" name="id_agendamento" id="remarcar-id-agendamento">
                    
                    <div style="background: rgba(139, 92, 246, 0.04); border-left: 4px solid var(--color-purple); padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border-top: 1px solid rgba(139,92,246,0.08); border-right: 1px solid rgba(139,92,246,0.08); border-bottom: 1px solid rgba(139,92,246,0.08);">
                        <p style="margin: 0; font-size: 0.95rem; color: var(--text-main); font-weight: 600;">
                            Serviço: <span id="remarcar-nome-servico" style="font-weight: 700; color: var(--color-purple);">--</span>
                        </p>
                        <p style="margin: 6px 0 0 0; font-size: 0.88rem; color: var(--text-muted); font-weight: 500;">
                            Profissional: <span id="remarcar-nome-profissional" style="font-weight: 700; color: var(--text-main);">--</span>
                        </p>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label for="remarcar-data" style="display: block; margin-bottom: 0.6rem; color: var(--text-main); font-weight: 700; font-size: 0.92rem;">Escolha a nova data:</label>
                        <input type="date" name="data" id="remarcar-data" onchange="atualizarHorariosRemarcar()" required class="form-control" placeholder="Selecione a data..." style="font-weight: 600; font-size: 0.95rem;">
                    </div>

                    <div id="remarcar-box-horarios" style="display: none; margin-top: 20px; margin-bottom: 1.5rem;">
                        <label style="display: block; color: var(--text-main); margin-bottom: 0.8rem; font-weight: 700; font-size: 0.92rem;">Horários Disponíveis:</label>
                        <div class="horarios-container" id="remarcar-container-horarios" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                            <!-- Horários preenchidos dinamicamente -->
                        </div>
                        <input type="hidden" name="hora" id="remarcar-hora-selecionada" required>
                    </div>

                    <button type="submit" class="btn-primary" id="btn-remarcar-confirmar" style="width: 100%; margin-top: 1rem;" disabled>Confirmar Remarcação</button>
                </form>
            </div>
        </div>
    </div>


    <script src="<?= BASE_URL ?>/public/resources/js/historico.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/app-cliente.js"></script>

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
                        confirmButton: 'swal-belezou-btn-confirm',
                        icon: 'swal-belezou-icon'
                    },
                    buttonsStyling: false,
                    showClass: { popup: 'swal-belezou-show' },
                    hideClass: { popup: 'swal-belezou-hide' }
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
                        confirmButton: 'swal-belezou-btn-danger',
                        icon: 'swal-belezou-icon'
                    },
                    buttonsStyling: false,
                    showClass: { popup: 'swal-belezou-show' },
                    hideClass: { popup: 'swal-belezou-hide' }
                });
            <?php endif; ?>

            // ─── Refresh Dinâmico por AJAX (Polling) ───
            // Atualiza os dados a cada 30 segundos sem recarregar a página
            setInterval(() => {
                const modalRemarcar = document.getElementById('modalRemarcar');
                const isModalAberto = modalRemarcar && modalRemarcar.classList.contains('active');
                
                if (!document.hidden && !isModalAberto && typeof atualizarHistoricoPorAjax === 'function') {
                    atualizarHistoricoPorAjax();
                }
            }, 30000);
        });
    </script>

</html>