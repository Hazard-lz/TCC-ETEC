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

    <script>
        const BASE_URL = '<?= BASE_URL ?>';
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
                    <button class="tab-btn active" onclick="mudarAba('proximos', this)">Próximos</button>
                    <button class="tab-btn" onclick="mudarAba('anteriores', this)">Anteriores</button>
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

                <div id="aba-proximos" class="tab-content active history-grid">
                    <?php if (!empty($proximos)): ?>
                        <?php foreach ($proximos as $ag):
                            $estilo = getBadgeCss($ag['status']);
                            $podeCancelar = false;
                            $dataAgendamento = new DateTime($ag['data_agendamento']);
                            $hoje = new DateTime(date('Y-m-d'));
                            if ($ag['status'] === 'pendente' || ($dataAgendamento > $hoje && $ag['status'] === 'marcado')) {
                                $podeCancelar = true;
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
                                                style="width: 100%; padding: 10px; border-radius: 8px; background-color: var(--color-pink); color: white; border: none; cursor: pointer; font-weight: 600; font-size: 0.85rem;">Cancelar</button>
                                        </form>
                                        <button type="button"
                                            onclick="abrirModalRemarcar(<?= $ag['id_agendamento'] ?>, '<?= htmlspecialchars($ag['nome_servico'], ENT_QUOTES) ?>', '<?= htmlspecialchars($ag['funcionario_nome'], ENT_QUOTES) ?>', <?= $ag['cod_funcionario'] ?>, <?= $ag['id_servico'] ?>, '<?= $ag['status'] ?>')"
                                            style="flex: 1; padding: 10px; border-radius: 8px; background-color: var(--color-purple); color: white; border: none; cursor: pointer; font-weight: 600; font-size: 0.85rem;">Remarcar</button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: var(--text-muted); margin-top: 2rem;">Não tem agendamentos
                            futuros.</p>
                    <?php endif; ?>
                </div>

                <div id="aba-anteriores" class="tab-content history-grid">
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
                        <p style="text-align: center; color: var(--text-muted); margin-top: 2rem;">Ainda não tens histórico
                            de visitas.</p>
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
                        <input type="date" name="data" id="remarcar-data" onchange="atualizarHorariosRemarcar()" required class="form-control" style="font-weight: 600; font-size: 0.95rem;">
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
            // Se houver agendamentos próximos, verifica atualizações a cada 30 segundos
            <?php if (!empty($proximos)): ?>
                setInterval(() => {
                    // Só recarrega se a página estiver visível E o modal de remarcação não estiver aberto
                    const modalRemarcar = document.getElementById('modalRemarcar');
                    const isModalAberto = modalRemarcar && modalRemarcar.classList.contains('active');
                    
                    if (!document.hidden && !isModalAberto) {
                        window.location.reload();
                    }
                }, 30000);
            <?php endif; ?>
        });
    </script>

</html>