<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <?= CsrfGuard::metaTag() ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Belezou App</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/resources/images/favicon.png">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/login.css">
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">

    <!-- SweetAlert2 para exibição premium dos termos da LGPD -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
</head>

<body>

    <div class="login-wrapper">

        <div class="base-card login-card" style="max-width: 500px;">

            <img src="<?= BASE_URL ?>/public/resources/images/belezou_color.png" alt="Belezou App Logo" class="login-logo" style="margin-bottom: 1rem;">
            <h2 style="color: var(--color-purple); margin-bottom: 0.5rem; font-size: 1.3rem;">Crie sua conta</h2>

            <?php 
                if (isset($_SESSION['erro_cadastro'])) {
                    echo '<div class="alert alert-danger">';
                    echo htmlspecialchars($_SESSION['erro_cadastro']);
                    echo '</div>';
                    unset($_SESSION['erro_cadastro']);
                }
            ?>

            <form id="cadastroForm" action="<?= BASE_URL ?>/cadastro/salvar" method="POST" class="login-form">
                                        <?= CsrfGuard::campoHidden() ?>

                <div class="form-group">
                    <label for="nome">Nome Completo</label>
                    <input type="text" id="nome" name="nome" class="form-control" placeholder="Ex: Maria Oliveira" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;" class="mobile-grid-1">
                    <div class="form-group">
                        <label for="telefone">WhatsApp</label>
                        <input type="tel" id="telefone" name="telefone" class="form-control" placeholder="(11) 99999-9999" required>
                    </div>
                    <div class="form-group">
                        <label for="data_nascimento">Data de Nascimento</label>
                        <input type="date" id="data_nascimento" name="data_nascimento" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="seu@email.com" required>
                </div>

                <div class="form-group">
                    <label for="confirma_email">Confirmar E-mail</label>
                    <input type="email" id="confirma_email" name="confirma_email" class="form-control" placeholder="Repita seu e-mail" required>
                </div>

                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" class="form-control" placeholder="Mínimo 8 caracteres" required>
                </div>
                
                <div class="form-group">
                    <label for="confirma_senha">Confirmar Senha</label>
                    <input type="password" id="confirma_senha" name="confirma_senha" class="form-control" placeholder="Repita a senha" required>
                </div>

                <!-- Checkbox de aceite dos termos LGPD com design elegante -->
                <div class="form-group" style="display: flex; align-items: flex-start; gap: 0.65rem; margin-top: 1.25rem; margin-bottom: 1.5rem; text-align: left;">
                    <input type="checkbox" id="termos" name="termos" value="1" required style="width: 18px; height: 18px; margin-top: 0.15rem; cursor: pointer; border-radius: 4px; border: 1.5px solid var(--border-color); accent-color: var(--color-purple);">
                    <label for="termos" style="font-weight: 500; font-size: 0.85rem; line-height: 1.4; color: var(--text-muted); cursor: pointer; user-select: none; display: inline; margin-bottom: 0;">
                        Li e concordo com os <a href="javascript:void(0)" onclick="mostrarTermosDeUso()" style="color: var(--color-purple); font-weight: 700; text-decoration: none;">Termos de Uso</a> e a <a href="javascript:void(0)" onclick="mostrarPoliticaPrivacidade()" style="color: var(--color-purple); font-weight: 700; text-decoration: none;">Política de Privacidade (LGPD)</a>.
                    </label>
                </div>

                <div id="cadastroError" class="error-message" style="display: none; text-align: center; margin-bottom: 1rem; color: #dc2626;"></div>

                <button type="submit" class="btn-primary" style="margin-top: 0.5rem;">Criar Minha Conta</button>
                
                <p style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-muted);">
                    Já tem uma conta? <a href="<?= BASE_URL ?>/login" style="color: var(--color-purple); font-weight: 600; text-decoration: none;">Faça login aqui</a>
                </p>

            </form>
        </div>

    </div>

    <script src="<?= BASE_URL ?>/public/resources/js/cadastro.js"></script>
</body>

</html>