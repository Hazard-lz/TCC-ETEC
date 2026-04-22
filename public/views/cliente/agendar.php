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

                <div class="stepper">
                    <div class="step-indicator active" id="ind-1">1</div>
                    <div class="step-indicator" id="ind-2">2</div>
                    <div class="step-indicator" id="ind-3">3</div>
                    <div class="step-indicator" id="ind-4">4</div>
                </div>

                <?php if (isset($_SESSION['flash_erro'])): ?>
                    <div class="alert alert-error" style="color: red; margin-bottom: 1rem; text-align: center;">
                        <?= htmlspecialchars($_SESSION['flash_erro']);
                        unset($_SESSION['flash_erro']); ?>
                    </div>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>/agendar" method="POST" id="formWizardAgendamento">

                    <input type="hidden" name="id_servico" id="servico_id" required>
                    <input type="hidden" id="servico_nome">

                    <input type="hidden" name="id_funcionario" id="funcionario_id" required>
                    <input type="hidden" id="funcionario_nome">

                    <input type="hidden" name="hora" id="horario_selecionado" required>

                    <div class="step-content active" id="step-1">
                        <h3 class="section-title">Escolha o Serviço</h3>
                        <div class="cards-container">
                            <?php if (!empty($servicos)): ?>
                                <?php foreach ($servicos as $svc): ?>
                                    <div class="base-card selectable-card" style="padding: 1rem; margin-bottom: 0.8rem;" onclick="selecionarServico('<?= $svc['id_servico'] ?>', '<?= htmlspecialchars($svc['nome_servico']) ?>', this)">
                                        <h4 style="color: var(--text-main); font-size: 1.1rem; margin-bottom: 0.3rem;"><?= htmlspecialchars($svc['nome_servico']) ?></h4>
                                        <p style="color: var(--text-muted); font-size: 0.9rem;">Duração: <?= $svc['duracao'] ?> min | R$ <?= number_format($svc['preco'], 2, ',', '.') ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="text-align: center; color: var(--text-muted);">Nenhum serviço disponível no momento.</p>
                            <?php endif; ?>
                        </div>
                        <div class="form-actions" style="margin-top: 1.5rem; text-align: right;">
                            <button type="button" class="btn-primary" id="btn-next-1" disabled style="width: auto; padding: 0.8rem 1.5rem; border-radius: 25px;">Continuar</button>
                        </div>
                    </div>

                    <div class="step-content" id="step-2">
                        <h3 class="section-title">Escolha o Profissional</h3>

                        <div class="cards-container" id="container-profissionais">
                            <p style="text-align: center; color: var(--text-muted);">Aguardando seleção de serviço...</p>
                        </div>

                        <div class="form-actions" style="margin-top: 1.5rem; display: flex; justify-content: space-between;">
                            <button type="button" class="btn-secondary" onclick="voltarPasso(1)" style="width: auto; padding: 0.8rem 1.5rem; border-radius: 25px; background: #e2e8f0; color: #333; border: none;">Voltar</button>
                            <button type="button" class="btn-primary" id="btn-next-2" disabled style="width: auto; padding: 0.8rem 1.5rem; border-radius: 25px;">Continuar</button>
                        </div>
                    </div>

                    <div class="step-content" id="step-3">
                        <h3 class="section-title">Data e Horário</h3>
                        <div class="form-group" style="margin-bottom: 1.5rem;">
                            <label for="data_agendamento" style="display: block; margin-bottom: 0.5rem; color: var(--text-main);">Escolha a data:</label>
                            <input type="date" name="data" id="data_agendamento" onchange="liberarHorarios()" required style="width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 10px; font-family: inherit;">
                        </div>

                        <div id="box-horarios" style="display: none; margin-top: 20px;">
                            <p style="color: var(--text-main); margin-bottom: 0.8rem;">Horários Disponíveis:</p>
                            <div class="horarios-container" id="container-horarios-dinamicos" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                            </div>
                        </div>

                        <div class="form-actions" style="margin-top: 2rem; display: flex; justify-content: space-between;">
                            <button type="button" class="btn-secondary" onclick="voltarPasso(2)" style="width: auto; padding: 0.8rem 1.5rem; border-radius: 25px; background: #e2e8f0; color: #333; border: none;">Voltar</button>
                            <button type="button" class="btn-primary" id="btn-next-3" onclick="montarResumo()" disabled style="width: auto; padding: 0.8rem 1.5rem; border-radius: 25px;">Continuar</button>
                        </div>
                    </div>

                    <div class="step-content" id="step-4">
                        <h3 class="section-title">Confirme o Agendamento</h3>
                        <div class="resumo-box base-card" style="padding: 1.5rem; line-height: 1.8;">
                            <p><strong>Serviço:</strong> <span id="resumo_servico"></span></p>
                            <p><strong>Profissional:</strong> <span id="resumo_pro"></span></p>
                            <p><strong>Quando:</strong> <span id="resumo_datahora"></span></p>
                        </div>
                        <div class="form-actions" style="margin-top: 2rem; display: flex; justify-content: space-between;">
                            <button type="button" class="btn-secondary" onclick="voltarPasso(3)" style="width: auto; padding: 0.8rem 1.5rem; border-radius: 25px; background: #e2e8f0; color: #333; border: none;">Voltar</button>
                            <button type="submit" class="btn-primary" style="width: auto; padding: 0.8rem 1.5rem; border-radius: 25px; background: #10b981;">Confirmar</button>
                        </div>
                    </div>
                </form>
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
</body>

</html>