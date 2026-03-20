<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - Belezou App</title>
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/login.css">
</head>
<body>

    <div class="login-wrapper">
        
        <div class="base-card login-card">
            <img src="<?= BASE_URL ?>/public/resources/images/Belezou.png" alt="Belezou App Logo" class="login-logo">
            
            <p style="text-align: center; margin-bottom: 1.5rem; color: #4b5563;">
                Introduza o seu e-mail para receber o código de recuperação.
            </p>

            <?php 
                if (session_status() === PHP_SESSION_NONE) { session_start(); }
                
                if (isset($_SESSION['erro_recuperacao'])) {
                    echo '<div class="error-message" style="display: block; text-align: center; margin-bottom: 1rem; color: #dc2626; background-color: #fee2e2; padding: 10px; border-radius: 8px;">';
                    echo htmlspecialchars($_SESSION['erro_recuperacao']);
                    echo '</div>';
                    unset($_SESSION['erro_recuperacao']);
                }
            ?>

            <form action="<?= BASE_URL ?>/auth/esqueciSenha" method="POST" class="login-form">
                
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Digite seu e-mail" required>
                </div>
                
                <button type="submit" class="btn-primary" style="margin-bottom: 10px;">Enviar Código</button>
                <a href="<?= BASE_URL ?>/login" class="btn-secondary">Voltar ao Login</a>
                
            </form>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/public/resources/js/auth.js"></script>
</body>
</html>