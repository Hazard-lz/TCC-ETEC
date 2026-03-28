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

            <form id="formReenviarRecuperacao" action="<?= BASE_URL ?>/auth/reenviar-codigo-recuperacao" method="POST">
                <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0; text-align: center;">
                    Não recebeu o e-mail? <br>
                    <button id="btnReenviarRecuperacao" type="submit" style="background: none; border: none; color: var(--color-purple); font-weight: bold; text-decoration: underline; cursor: pointer; padding: 5px; margin-top: 5px; transition: all 0.3s;">
                        Reenviar Código
                    </button>
                    <span id="timerCooldownRecuperacao" style="display: none; color: #9ca3af; font-size: 0.8rem; margin-left: 5px;"></span>
                </p>
            </form>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/public/resources/js/auth.js"></script>
    <script>
        // =====================================================================
        // ARQUITETURA UX: Bloqueio Anti-Spam (60 Segundos) para Recuperação
        // =====================================================================
        document.addEventListener('DOMContentLoaded', function() {
            const formReenviar = document.getElementById('formReenviarRecuperacao');
            const btnReenviar = document.getElementById('btnReenviarRecuperacao');
            const spanTimer = document.getElementById('timerCooldownRecuperacao');

            // Usamos uma chave diferente no LocalStorage para não conflitar com a tela de contas novas
            let lastSent = localStorage.getItem('lastRecoveryCodeSentTimestamp');
            
            if (lastSent) {
                let now = Math.floor(Date.now() / 1000); 
                let diff = now - parseInt(lastSent);
                
                if (diff < 60) {
                    iniciarBloqueio(60 - diff);
                } else {
                    localStorage.removeItem('lastRecoveryCodeSentTimestamp');
                }
            }

            formReenviar.addEventListener('submit', function(e) {
                localStorage.setItem('lastRecoveryCodeSentTimestamp', Math.floor(Date.now() / 1000));
            });

            function iniciarBloqueio(segundosRestantes) {
                btnReenviar.disabled = true;
                btnReenviar.style.color = '#9ca3af'; 
                btnReenviar.style.cursor = 'not-allowed'; 
                spanTimer.style.display = 'inline';
                
                let interval = setInterval(function() {
                    spanTimer.innerText = `(Aguarde ${segundosRestantes}s)`;
                    segundosRestantes--;
                    
                    if (segundosRestantes < 0) {
                        clearInterval(interval);
                        btnReenviar.disabled = false;
                        btnReenviar.style.color = 'var(--color-purple)'; 
                        btnReenviar.style.cursor = 'pointer';
                        spanTimer.style.display = 'none';
                        localStorage.removeItem('lastRecoveryCodeSentTimestamp');
                    }
                }, 1000); 
            }
        });
    </script>
</body>
</html>