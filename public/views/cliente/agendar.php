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
    <title>Novo Agendamento - Belezou App</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/resources/images/favicon.png">

    <!-- SweetAlert2 — alertas de profissionais e erros de rede -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/app-cliente.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/agendar.css">

    <script>
        const BASE_URL = '<?= BASE_URL ?>';
        const LIMITE_FUTURO_DIAS = '<?= htmlspecialchars($limiteFuturoDias ?? "sem_limite") ?>';
    </script>
    <?php require_once __DIR__ . '/../partials/onesignal.php'; ?>
</head>

<body>

    <div class="app-wrapper">
        <div class="mobile-container">

            <header class="app-header">
                <div class="greeting">
                    <p>Agendamento</p>
                    <h2>Novo Horário</h2>
                </div>
                <a href="<?= BASE_URL ?>/" style="text-decoration: none; font-size: 1.5rem; color: var(--text-main);">✕</a>
            </header>

            <main class="app-content">

                <!-- Os alertas agora são exibidos via SweetAlert no final da página -->

                <div class="agendar-layout">

                    <!-- ══ COLUNA ESQUERDA: STEPPER + FORM ══ -->
                    <form action="<?= BASE_URL ?>/agendar" method="POST" id="formWizardAgendamento" class="agendar-form-col">
                                        <?= CsrfGuard::campoHidden() ?>

                        <!-- Inputs ocultos -->
                        <input type="hidden" name="id_servico" id="servico_id" required>
                        <input type="hidden" id="servico_nome">
                        <input type="hidden" id="servico_preco">

                        <input type="hidden" name="id_funcionario" id="funcionario_id" required>
                        <input type="hidden" id="funcionario_nome">

                        <input type="hidden" name="hora" id="horario_selecionado" required>

                        <!-- Stepper fica no TOPO da coluna esquerda -->
                        <div class="stepper">
                            <div class="step-indicator active" id="ind-1" data-passo="1" role="button" title="Serviço">
                                <span class="step-num">1</span>
                                <span class="step-label">Serviço</span>
                            </div>
                            <div class="step-connector"></div>
                            <div class="step-indicator" id="ind-2" data-passo="2" role="button" title="Profissional">
                                <span class="step-num">2</span>
                                <span class="step-label">Profissional</span>
                            </div>
                            <div class="step-connector"></div>
                            <div class="step-indicator" id="ind-3" data-passo="3" role="button" title="Data/Hora">
                                <span class="step-num">3</span>
                                <span class="step-label">Data/Hora</span>
                            </div>
                            <div class="step-connector"></div>
                            <div class="step-indicator" id="ind-4" data-passo="4" role="button" title="Confirmar">
                                <span class="step-num">4</span>
                                <span class="step-label">Confirmar</span>
                            </div>
                        </div>

                        <!-- ── Passo 1: Serviços ── -->
                        <div class="step-content active" id="step-1">
                            <h3 class="section-title">Escolha o Serviço</h3>
                            <div class="form-group mb-3" style="margin-bottom: 1.5rem;">
                                <input type="text" id="busca-servico" class="form-control" placeholder="Pesquisar serviço...">
                            </div>
                            <div class="cards-container">
                                <?php if (!empty($servicos)): ?>
                                    <?php foreach ($servicos as $svc): ?>
                                        <div class="base-card selectable-card"
                                             style="padding: 1rem; margin-bottom: 0.8rem;"
                                             data-preco="<?= $svc['preco'] ?>"
                                             onclick="selecionarServico('<?= $svc['id_servico'] ?>', '<?= htmlspecialchars($svc['nome_servico'], ENT_QUOTES) ?>', <?= $svc['preco'] ?>, this)">
                                            <h4 style="color: var(--text-main); font-size: 1.1rem; margin-bottom: 0.3rem;"><?= htmlspecialchars($svc['nome_servico']) ?></h4>
                                            <p style="color: var(--text-muted); font-size: 0.9rem;">Duração: <?= $svc['duracao'] ?> min | R$ <?= number_format($svc['preco'], 2, ',', '.') ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p style="text-align: center; color: var(--text-muted);">Nenhum serviço disponível no momento.</p>
                                <?php endif; ?>
                            </div>
                            <!-- Botão "Continuar" no mobile (oculto no desktop via CSS) -->
                            <div class="form-actions-mobile">
                                <button type="button" class="btn-primary btn-mobile-next" id="btn-next-1" disabled>Continuar</button>
                            </div>
                        </div>

                        <!-- ── Passo 2: Profissionais ── -->
                        <div class="step-content" id="step-2">
                            <h3 class="section-title">Escolha o Profissional</h3>
                            <div class="form-group mb-3" style="margin-bottom: 1.5rem; display: none;" id="box-busca-profissional">
                                <input type="text" id="busca-profissional" class="form-control" placeholder="Pesquisar profissional...">
                            </div>
                            <div class="cards-container" id="container-profissionais">
                                <p style="text-align: center; color: var(--text-muted);">Aguardando seleção de serviço...</p>
                            </div>
                            <div class="form-actions-mobile" style="display: flex; justify-content: space-between; gap: 0.5rem;">
                                <button type="button" class="btn-secondary btn-voltar-mobile" onclick="voltarPasso(1)">Voltar</button>
                                <button type="button" class="btn-primary btn-mobile-next" id="btn-next-2" disabled>Continuar</button>
                            </div>
                        </div>

                        <!-- ── Passo 3: Data e Hora ── -->
                        <div class="step-content" id="step-3">
                            <h3 class="section-title">Data e Horário</h3>
                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <label for="data_agendamento" style="display: block; margin-bottom: 0.5rem; color: var(--text-main);">Escolha a data:</label>
                                <input type="date" name="data" id="data_agendamento" onchange="liberarHorarios()" required style="width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 10px; font-family: inherit;">
                            </div>
                            <div id="box-horarios" style="display: none; margin-top: 20px;">
                                <p style="color: var(--text-main); margin-bottom: 0.8rem;">Horários Disponíveis:</p>
                                <div class="horarios-container" id="container-horarios-dinamicos" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;"></div>
                            </div>
                            <div class="form-actions-mobile" style="display: flex; justify-content: space-between; gap: 0.5rem;">
                                <button type="button" class="btn-secondary btn-voltar-mobile" onclick="voltarPasso(2)">Voltar</button>
                                <button type="button" class="btn-primary btn-mobile-next" id="btn-next-3" onclick="montarResumo()" disabled>Revisar agendamento</button>
                            </div>
                        </div>

                        <!-- ── Passo 4: Confirmação ── -->
                        <div class="step-content" id="step-4">
                            <h3 class="section-title">Confirme o Agendamento</h3>
                            <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Revise os detalhes no resumo e confirme o seu agendamento.</p>
                            
                            <!-- CUPOM ESTÉTICO DE AGENDAMENTO (WHITE-LABEL STYLED TICKET) -->
                            <div class="ticket-cupom" style="background: var(--surface-color); border: 2px dashed var(--border-color); border-radius: 16px; padding: 2rem; margin-bottom: 2rem; position: relative; box-shadow: 0 10px 25px rgba(0,0,0,0.03); overflow: hidden;">
                                <!-- Semi-círculos nas laterais para efeito de ticket físico -->
                                <div style="position: absolute; left: -10px; top: 50%; transform: translateY(-50%); width: 20px; height: 20px; border-radius: 50%; background: var(--bg-color); border-right: 2px dashed var(--border-color); z-index: 2;"></div>
                                <div style="position: absolute; right: -10px; top: 50%; transform: translateY(-50%); width: 20px; height: 20px; border-radius: 50%; background: var(--bg-color); border-left: 2px dashed var(--border-color); z-index: 2;"></div>
                                
                                <div class="ticket-header" style="text-align: center; border-bottom: 2px dashed var(--border-color); padding-bottom: 1.25rem; margin-bottom: 1.5rem;">
                                    <span style="font-size: 1.5rem;">✨</span>
                                    <h4 style="margin: 0.5rem 0 0 0; color: var(--color-purple); font-size: 1.3rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Belezou App</h4>
                                    <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: var(--text-muted);">TICKET DE RESERVA</p>
                                </div>
                                
                                <div class="ticket-body" style="display: flex; flex-direction: column; gap: 1.2rem;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed rgba(0,0,0,0.05); padding-bottom: 0.5rem;">
                                        <span style="color: var(--text-muted); font-size: 0.95rem; display: flex; align-items: center; gap: 0.5rem;"><i class="bi bi-scissors" style="color: var(--color-purple);"></i> Serviço:</span>
                                        <span id="cupom-servico" style="color: var(--text-main); font-weight: 600; font-size: 1rem; text-align: right;">A selecionar...</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed rgba(0,0,0,0.05); padding-bottom: 0.5rem;">
                                        <span style="color: var(--text-muted); font-size: 0.95rem; display: flex; align-items: center; gap: 0.5rem;"><i class="bi bi-person-badge" style="color: var(--color-purple);"></i> Profissional:</span>
                                        <span id="cupom-profissional" style="color: var(--text-main); font-weight: 600; font-size: 1rem; text-align: right;">A selecionar...</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed rgba(0,0,0,0.05); padding-bottom: 0.5rem;">
                                        <span style="color: var(--text-muted); font-size: 0.95rem; display: flex; align-items: center; gap: 0.5rem;"><i class="bi bi-calendar-event" style="color: var(--color-purple);"></i> Data e Hora:</span>
                                        <span id="cupom-datahora" style="color: var(--text-main); font-weight: 600; font-size: 1rem; text-align: right;">A selecionar...</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed rgba(0,0,0,0.05); padding-bottom: 0.5rem;">
                                        <span style="color: var(--text-muted); font-size: 0.95rem; display: flex; align-items: center; gap: 0.5rem;"><i class="bi bi-hourglass-split" style="color: var(--color-purple);"></i> Duração:</span>
                                        <span id="cupom-duracao" style="color: var(--text-main); font-weight: 600; font-size: 1rem; text-align: right;">A selecionar...</span>
                                    </div>
                                    
                                    <div style="border-top: 1px solid var(--border-color); padding-top: 1.2rem; margin-top: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                                        <span style="color: var(--text-main); font-weight: 700; font-size: 1.1rem;">VALOR TOTAL:</span>
                                        <span id="cupom-preco" style="color: var(--color-pink); font-weight: 800; font-size: 1.4rem;">R$ --</span>
                                    </div>
                                </div>
                            </div>

                            <div class="step-4-actions">
                                <button type="button" class="btn-secondary btn-voltar-step4" onclick="voltarPasso(3)">Voltar</button>
                                <button type="submit" class="btn-primary btn-confirmar btn-confirmar-step4" id="btn-confirmar-desktop-mobile">
                                    Marcar Agendamento
                                </button>
                            </div>
                        </div>

                    </form>

                    <!-- ══ COLUNA DIREITA: RESUMO LATERAL ══ -->
                    <aside id="resumo-lateral">
                        <div class="resumo-lateral-inner">
                            <h3 class="resumo-lateral-titulo">📋 Resumo do Agendamento</h3>

                            <div class="resumo-item">
                                <span class="resumo-label">Serviço</span>
                                <span class="resumo-valor placeholder" id="lat-servico">A selecionar...</span>
                            </div>

                            <div class="resumo-item">
                                <span class="resumo-label">Profissional</span>
                                <span class="resumo-valor placeholder" id="lat-profissional">A selecionar...</span>
                            </div>

                            <div class="resumo-item">
                                <span class="resumo-label">Data / Hora</span>
                                <span class="resumo-valor placeholder" id="lat-datahora">A selecionar...</span>
                            </div>

                            <div class="resumo-total">
                                <span class="resumo-total-label">Total a Pagar</span>
                                <span class="resumo-total-valor" id="lat-preco">R$ --</span>
                            </div>

                            <!-- Botão global — visível apenas no desktop -->
                            <button type="button" id="btn-continuar-global" class="btn-primary btn-global-continuar" disabled>
                                Continuar
                            </button>

                            <!-- Botão voltar global — visível apenas no desktop de forma dinâmica -->
                            <button type="button" id="btn-voltar-global" class="btn-secondary btn-global-voltar" style="display: none;">
                                Voltar
                            </button>
                        </div>
                    </aside>

                </div><!-- /.agendar-layout -->
            </main>

            <nav class="bottom-nav">
                <a href="<?= BASE_URL ?>/" class="nav-item">
                    <span class="nav-icon">🏠</span><span>Início</span>
                </a>
                <a href="<?= BASE_URL ?>/agendar" class="nav-item active">
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

    <script src="<?= BASE_URL ?>/public/resources/js/agendar.js?v=<?= time() ?>"></script>
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
        });
    </script>
</body>

</html>