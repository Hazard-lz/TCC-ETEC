<?php
// Se ele caiu aqui de paraquedas sem se registar, manda de volta
if (!isset($_SESSION['email_verificacao'])) {
    header("Location: " . BASE_URL . "/login");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar E-mail - Belezou App</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/login.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="base-card login-card">
            <img src="<?= BASE_URL ?>/public/resources/images/Belezou.png" alt="Belezou App Logo" class="login-logo" style="margin-bottom: 1rem;">
            
            <h2 style="color: var(--color-purple); font-size: 1.3rem; margin-bottom: 0.5rem;">Verifique o seu E-mail</h2>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem; line-height: 1.4;">
                Enviámos um código de 6 dígitos para <br><strong><?= htmlspecialchars($_SESSION['email_verificacao']) ?></strong>
            </p>

            <?php 
                if (isset($_SESSION['erro_verificacao'])) {
                    echo '<div style="color: #dc2626; background-color: #fee2e2; padding: 10px; border-radius: 8px; margin-bottom: 1rem;">';
                    echo htmlspecialchars($_SESSION['erro_verificacao']);
                    echo '</div>';
                    unset($_SESSION['erro_verificacao']);
                }
            ?>

            <form action="<?= BASE_URL ?>/verificar-email/validar" method="POST">
                <div class="form-group" style="text-align: center;">
                    <label for="codigo" style="text-align: left;">Código de Verificação</label>
                    <input type="text" id="codigo" name="codigo" class="form-control" placeholder="000000" maxlength="6" style="font-size: 1.5rem; letter-spacing: 10px; text-align: center; font-weight: bold;" required>
                </div>
                
                <button type="submit" class="btn-primary">Confirmar Conta</button>
            </form>
            
            <p style="margin-top: 1.5rem; font-size: 0.85rem;">
                <a href="<?= BASE_URL ?>/login" style="color: var(--text-muted); text-decoration: none;">Voltar para o Login</a>
            </p>
        </div>
    </div>
</body>
</html>