<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?= CsrfGuard::metaTag() ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Belezou App</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/resources/images/favicon.png">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/login.css">
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
</head>
<body>

    <div class="login-wrapper">
        
        <div class="base-card login-card">
            <img src="<?= BASE_URL ?>/public/resources/images/belezou_color.png" alt="Belezou App Logo" class="login-logo">
            
            <?php 
                // FEEDBACK LOOP: Mensagem genérica de sucesso
                if (isset($_SESSION['msg_sucesso'])) {
                    echo '<div class="alert alert-success">';
                    echo htmlspecialchars($_SESSION['msg_sucesso']);
                    echo '</div>';
                    unset($_SESSION['msg_sucesso']);
                }

                // ARQUITETURA UX: Correção do feedback de Redefinição de Senha e Logout
                if (isset($_SESSION['sucesso_login'])) {
                    echo '<div class="alert alert-success">';
                    echo htmlspecialchars($_SESSION['sucesso_login']);
                    echo '</div>';
                    unset($_SESSION['sucesso_login']);
                }
                
                // Erros com Link HTML (ex: Verificação de E-mail pendente)
                if (isset($_SESSION['erro_login_html'])) {
                    echo '<div class="alert alert-danger">';
                    echo $_SESSION['erro_login_html']; 
                    echo '</div>';
                    unset($_SESSION['erro_login_html']);
                }
                // Erros normais de texto (Senha incorreta, etc)
                elseif (isset($_SESSION['erro_login'])) {
                    echo '<div class="alert alert-danger">';
                    echo htmlspecialchars($_SESSION['erro_login']);
                    echo '</div>';
                    unset($_SESSION['erro_login']);
                }
            ?>

            <form id="loginForm" action="<?= BASE_URL ?>/login/autenticar" method="POST" class="login-form">
                                        <?= CsrfGuard::campoHidden() ?>
                
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Digite seu e-mail" required>
                </div>
                
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" class="form-control" placeholder="Digite sua senha" required>
                    
                    <div class="forgot-password-container">
                        <a href="<?= BASE_URL ?>/recuperar-senha" class="forgot-password">Esqueci a minha senha</a>
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