<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Belezou App</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/login.css">
</head>

<body>

    <div class="login-wrapper">

        <div class="base-card login-card" style="max-width: 500px;">

            <img src="<?= BASE_URL ?>/public/resources/images/Belezou.png" alt="Belezou App Logo" class="login-logo" style="margin-bottom: 1rem;">
            <h2 style="color: var(--color-purple); margin-bottom: 0.5rem; font-size: 1.3rem;">Crie sua conta</h2>

            <?php 
                if (isset($_SESSION['erro_cadastro'])) {
                    echo '<div class="error-message" style="display: block; text-align: center; margin-bottom: 1rem; color: #dc2626; background-color: #fee2e2; padding: 10px; border-radius: 8px;">';
                    echo htmlspecialchars($_SESSION['erro_cadastro']);
                    echo '</div>';
                    unset($_SESSION['erro_cadastro']);
                }
            ?>

            <form id="cadastroForm" action="<?= BASE_URL ?>/cadastro/salvar" method="POST" class="login-form">

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

                <div id="cadastroError" class="error-message" style="display: none; text-align: center; margin-bottom: 1rem; color: #dc2626;"></div>

                <button type="submit" class="btn-primary" style="margin-top: 1rem;">Criar Minha Conta</button>
                
                <p style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-muted);">
                    Já tem uma conta? <a href="<?= BASE_URL ?>/login" style="color: var(--color-purple); font-weight: 600; text-decoration: none;">Faça login aqui</a>
                </p>

            </form>
        </div>

    </div>

    <script src="<?= BASE_URL ?>/public/resources/js/cadastro.js"></script>
</body>

</html>