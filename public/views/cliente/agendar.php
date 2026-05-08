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

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/app-cliente.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/agendar.css">

    <script>
        const BASE_URL = '<?= BASE_URL ?>';
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

                <?php if (isset($_SESSION['flash_erro'])): ?>
                    <div class="alert alert-error" style="color: red; margin-bottom: 1rem; text-align: center;">
                        <?= htmlspecialchars($_SESSION['flash_erro']);
                        unset($_SESSION['flash_erro']); ?>
                    </div>
                <?php endif; ?>

                <div class="agendar-layout">

                    <!-- ══ COLUNA ESQUERDA: STEPPER + FORM ══ -->
                    <form action="<?= BASE_URL ?>/agendar" method="POST" id="formWizardAgendamento" class="agendar-form-col">

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
                                <button type="button" class="btn-primary btn-mobile-next" id="btn-next-3" onclick="montarResumo()" disabled>Continuar</button>
                            </div>
                        </div>

                        <!-- ── Passo 4: Confirmação ── -->
                        <div class="step-content" id="step-4">
                            <h3 class="section-title">Confirme o Agendamento</h3>
                            <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Revise os detalhes no resumo e confirme o seu agendamento.</p>
                            <div class="form-actions-mobile" style="display: flex; justify-content: space-between; gap: 0.5rem;">
                                <button type="button" class="btn-secondary btn-voltar-mobile" onclick="voltarPasso(3)">Voltar</button>
                                <!-- Botão de submit visível só no mobile -->
                                <button type="submit" class="btn-primary btn-confirmar btn-mobile-next" id="btn-confirmar-mobile">
                                    ✅ Confirmar
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
            </nav>

        </div>
    </div>

    <script src="<?= BASE_URL ?>/public/resources/js/agendar.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/app-cliente.js"></script>
</body>

</html>