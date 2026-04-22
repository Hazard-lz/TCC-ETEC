<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Senha de Acesso - Belezou App</title>
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/login.css">
</head>
<body>

    <div class="login-wrapper">
        
        <div class="base-card login-card">
            <img src="<?= BASE_URL ?>/public/resources/images/belezou_color.png" alt="Belezou App Logo" class="login-logo">
            
            <h2 style="text-align: center; margin-bottom: 0.5rem; color: var(--color-purple);">Bem-vindo à Equipe!</h2>
            <p style="text-align: center; margin-bottom: 1.5rem; color: #4b5563;">
                Conclua seu cadastro criando sua senha de acesso.
            </p>

            <?php 
                // ARQUITETURA: Tratamento de feedbacks visuais.
                // Aqui nós lemos a sessão 'flash_erro' que definimos no FuncionarioController.php 
                // caso o usuário digite senhas diferentes ou deixe o campo em branco.
                if (session_status() === PHP_SESSION_NONE) { session_start(); }
                
                if (isset($_SESSION['flash_erro'])) {
                    echo '<div class="error-message" style="display: block; text-align: center; margin-bottom: 1rem; color: #dc2626; background-color: #fee2e2; padding: 10px; border-radius: 8px;">';
                    echo htmlspecialchars($_SESSION['flash_erro']);
                    echo '</div>';
                    unset($_SESSION['flash_erro']);
                }
            ?>

            <form action="<?= BASE_URL ?>/setup-funcionario/salvar" method="POST" class="login-form">
                
                <input type="hidden" name="email" value="<?= htmlspecialchars($email ?? '') ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">

                <div class="form-group">
                    <label for="nova_senha">Criar Senha</label>
                    <input type="password" id="nova_senha" name="nova_senha" class="form-control" placeholder="Mínimo 8 caracteres" required minlength="8">
                </div>

                <div class="form-group">
                    <label for="confirma_senha">Confirmar Senha</label>
                    <input type="password" id="confirma_senha" name="confirma_senha" class="form-control" placeholder="Repita a senha" required minlength="8">
                </div>
                
                <button type="submit" class="btn-primary" style="margin-bottom: 10px;">Finalizar e Acessar</button>
                
            </form>

            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 15px; text-align: center;">
                Link expirado ou com erro? <br>
                <span style="font-weight: bold;">Solicite ao administrador que reenvie seu acesso.</span>
            </p>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/public/resources/js/auth.js"></script>
</body>
</html>