<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <?= CsrfGuard::metaTag() ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Belezou App</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?? '' ?>/public/resources/images/favicon.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link rel="stylesheet" href="<?= BASE_URL ?? '' ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?? '' ?>/public/resources/css/admin-layout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?? '' ?>/public/resources/css/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?? '' ?>/public/resources/css/funcionario.css">
    <?php require_once __DIR__ . '/../partials/onesignal.php'; ?>
</head>

<body>

    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="page-header" style="margin-bottom: 2rem;">
        <div class="page-title">
            <h2>Meu Perfil</h2>
            <p>Altere os seus dados pessoais e informações de contato.</p>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_sucesso'])): ?>
        <div class="alert alert-success">
            <strong>Sucesso!</strong> <?= $_SESSION['flash_sucesso'] ?>
        </div>
        <?php unset($_SESSION['flash_sucesso']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_erro'])): ?>
        <div class="alert alert-danger">
            <strong>Erro:</strong> <?= htmlspecialchars($_SESSION['flash_erro']) ?>
        </div>
        <?php unset($_SESSION['flash_erro']); ?>
    <?php endif; ?>

    <div class="base-card" style="max-width: 800px; padding: 2rem;">

        <form id="formPerfilFuncionario" action="<?= BASE_URL ?? '' ?>/funcionario/perfil/salvar" method="POST">
            <?= CsrfGuard::campoHidden() ?>

            <h3 class="section-title" style="margin-top: 0;">Dados Pessoais e Acesso</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="nome">Nome Completo</label>
                    <input type="text" id="nome" name="nome" class="form-control"
                        value="<?= htmlspecialchars($funcionario['nome'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="telefone">Telefone / WhatsApp</label>
                    <input type="tel" id="telefone" name="telefone" class="form-control"
                        value="<?= htmlspecialchars($funcionario['telefone'] ?? '') ?>">
                </div>
            </div>

            <div class="form-grid mt-3">
                <div class="form-group">
                    <label for="senha">Nova Senha (deixe em branco para manter a atual)</label>
                    <input type="password" id="senha" name="senha" class="form-control" placeholder="Mínimo 8 caracteres">
                </div>
                <div class="form-group">
                    <label for="confirmar_senha">Confirmar Nova Senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" placeholder="Repita a nova senha">
                </div>
            </div>

            <div class="form-group">
                <label for="email">E-mail de Acesso</label>
                <input type="email" id="email" name="email" class="form-control"
                    value="<?= htmlspecialchars($funcionario['email'] ?? '') ?>" disabled
                    title="Seu e-mail de acesso não pode ser alterado diretamente.">
            </div>

            <h3 class="section-title">Dados Profissionais</h3>
            <div class="form-group">
                <label for="especialidade">Especialidade Principal</label>
                <input type="text" id="especialidade" name="especialidade" class="form-control"
                    value="<?= htmlspecialchars($funcionario['especialidade'] ?? '') ?>" required>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn-primary" style="margin-top: 0;">Salvar Alterações</button>
                <button type="button" class="btn-secondary" onclick="window.history.back()"
                    style="margin-top: 0;">Voltar</button>
            </div>
        </form>

    </div>

    <script src="<?= BASE_URL ?? '' ?>/public/resources/js/admin.js"></script>
</body>

</html>