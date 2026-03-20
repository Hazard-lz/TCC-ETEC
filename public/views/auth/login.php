<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Belezou App</title>
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/login.css">
</head>
<body>

    <div class="login-wrapper">
        
        <div class="base-card login-card">
            <img src="<?= BASE_URL ?>/public/resources/images/Belezou.png" alt="Belezou App Logo" class="login-logo">
            
            <?php 
                // FEEDBACK LOOP: Se o Controller enviou uma mensagem de SUCESSO, exibimos a faixa verde!
                if (isset($_SESSION['msg_sucesso'])) {
                    echo '<div style="color: #15803d; background-color: #dcfce7; padding: 10px; border-radius: 8px; text-align: center; margin-bottom: 1rem; font-weight: bold;">';
                    echo htmlspecialchars($_SESSION['msg_sucesso']);
                    echo '</div>';
                    // Apaga a mensagem para ela sumir se o utilizador atualizar a página
                    unset($_SESSION['msg_sucesso']);
                }
                
                // Se o Controller enviou uma mensagem de ERRO DE LOGIN
                if (isset($_SESSION['erro_login'])) {
                    echo '<div class="error-message" style="display: block; text-align: center; margin-bottom: 1rem; color: #dc2626; background-color: #fee2e2; padding: 10px; border-radius: 8px;">';
                    echo htmlspecialchars($_SESSION['erro_login']);
                    echo '</div>';
                    unset($_SESSION['erro_login']);
                }
            ?>

            <form id="loginForm" action="<?= BASE_URL ?>/login/autenticar" method="POST" class="login-form">
                
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Digite seu e-mail" required>
                </div>
                
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" class="form-control" placeholder="Digite sua senha" required>
                    
                    <div class="forgot-password-container">
                        <a href="<?= BASE_URL ?>/recuperar-senha">Esqueci a minha senha</a>
                    </div>
                    
                    <div id="loginError" class="error-message" style="display: none; text-align: center; margin-top: 1rem; color: #dc2626;">Por favor, preencha todos os campos corretamente.</div>
                </div>
                
                <button type="submit" class="btn-primary">Entrar</button>
                <a href="<?= BASE_URL ?>/cadastro" class="btn-secondary">Cadastre-se</a>
                
            </form>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/public/resources/js/auth.js"></script>
</body>
</html>