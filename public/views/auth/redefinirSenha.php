<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - Belezou App</title>
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/login.css">
</head>
<body>

    <div class="login-wrapper">
        
        <div class="base-card login-card">
            <img src="<?= BASE_URL ?>/public/resources/images/Belezou.png" alt="Belezou App Logo" class="login-logo">
            
            <p style="text-align: center; margin-bottom: 1.5rem; color: #4b5563;">
                Introduza o código recebido e a sua nova senha.
            </p>

            <?php 
                if (session_status() === PHP_SESSION_NONE) { session_start(); }
                
                if (isset($_SESSION['sucesso_recuperacao'])) {
                    echo '<div style="color: #15803d; background-color: #dcfce7; padding: 10px; border-radius: 8px; text-align: center; margin-bottom: 1rem; font-weight: bold;">';
                    echo htmlspecialchars($_SESSION['sucesso_recuperacao']);
                    echo '</div>';
                    unset($_SESSION['sucesso_recuperacao']);
                }
                
                if (isset($_SESSION['erro_redefinicao'])) {
                    echo '<div class="error-message" style="display: block; text-align: center; margin-bottom: 1rem; color: #dc2626; background-color: #fee2e2; padding: 10px; border-radius: 8px;">';
                    echo htmlspecialchars($_SESSION['erro_redefinicao']);
                    echo '</div>';
                    unset($_SESSION['erro_redefinicao']);
                }
            ?>

            <form action="<?= BASE_URL ?>/auth/redefinirSenha" method="POST" class="login-form">
                
                <div class="form-group">
                    <label for="codigo">Código de Verificação</label>
                    <input type="text" id="codigo" name="codigo" class="form-control" placeholder="Ex: 123456" required maxlength="6" style="letter-spacing: 2px; text-align: center; font-weight: bold;">
                </div>

                <div class="form-group">
                    <label for="nova_senha">Nova Senha</label>
                    <input type="password" id="nova_senha" name="nova_senha" class="form-control" placeholder="Mínimo 8 caracteres" required minlength="8">
                </div>

                <div class="form-group">
                    <label for="confirma_senha">Confirmar Nova Senha</label>
                    <input type="password" id="confirma_senha" name="confirma_senha" class="form-control" placeholder="Repita a nova senha" required minlength="8">
                </div>
                
                <button type="submit" class="btn-primary" style="margin-bottom: 10px;">Alterar Senha</button>
                <a href="<?= BASE_URL ?>/login" class="btn-secondary">Cancelar e Voltar</a>
                
            </form>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/public/resources/js/auth.js"></script>
</body>
</html>