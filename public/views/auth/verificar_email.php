<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
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
            <img src="<?= BASE_URL ?>/public/resources/images/belezou_color.png" alt="Belezou App Logo" class="login-logo" style="margin-bottom: 1rem;">
            
            <h2 style="color: var(--color-purple); font-size: 1.3rem; margin-bottom: 0.5rem; text-align: center;">Verifique o seu E-mail</h2>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem; line-height: 1.4; text-align: center;">
                Enviámos um código de 6 dígitos para <br><strong><?= htmlspecialchars($_SESSION['email_verificacao']) ?></strong>
            </p>

            <?php 
                // Exibe alertas de sucesso (Ex: Novo código enviado)
                if (isset($_SESSION['sucesso_verificacao'])) {
                    echo '<div style="color: #15803d; background-color: #dcfce7; padding: 10px; border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem; text-align: center;">';
                    echo htmlspecialchars($_SESSION['sucesso_verificacao']);
                    echo '</div>';
                    unset($_SESSION['sucesso_verificacao']);
                }

                // Exibe alertas de erro (Ex: Código inválido)
                if (isset($_SESSION['erro_verificacao'])) {
                    echo '<div style="color: #dc2626; background-color: #fee2e2; padding: 10px; border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem; text-align: center;">';
                    echo htmlspecialchars($_SESSION['erro_verificacao']);
                    echo '</div>';
                    unset($_SESSION['erro_verificacao']);
                }
            ?>

            <form action="<?= BASE_URL ?>/verificar-email/validar" method="POST">
                <div class="form-group" style="text-align: center;">
                    <label for="codigo" style="text-align: left; display: block;">Código de Verificação</label>
                    <input type="text" id="codigo" name="codigo" class="form-control" placeholder="000000" maxlength="6" style="font-size: 1.5rem; letter-spacing: 10px; text-align: center; font-weight: bold;" required>
                </div>
                
                <button type="submit" class="btn-primary" style="margin-bottom: 15px;">Confirmar Conta</button>
            </form>

            <form id="formReenviar" action="<?= BASE_URL ?>/verificar-email/reenviar" method="POST">
                <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0; text-align: center;">
                    Não recebeu o e-mail? <br>
                    <button id="btnReenviar" type="submit" style="background: none; border: none; color: var(--color-purple); font-weight: bold; text-decoration: underline; cursor: pointer; padding: 5px; margin-top: 5px; transition: all 0.3s;">
                        Reenviar Código
                    </button>
                    <span id="timerCooldown" style="display: none; color: #9ca3af; font-size: 0.8rem; margin-left: 5px;"></span>
                </p>
            </form>
            
            <p style="margin-top: 1.5rem; font-size: 0.85rem; text-align: center;">
                <a href="<?= BASE_URL ?>/login" style="color: var(--text-muted); text-decoration: none;">Voltar para o Login</a>
            </p>
        </div>
    </div>

    <script>
        // =====================================================================
        // ARQUITETURA UX: Bloqueio Anti-Spam do botão de Reenviar Código
        // =====================================================================
        document.addEventListener('DOMContentLoaded', function() {
            const formReenviar = document.getElementById('formReenviar');
            const btnReenviar = document.getElementById('btnReenviar');
            const spanTimer = document.getElementById('timerCooldown');

            let lastSent = localStorage.getItem('lastCodeSentTimestamp');
            
            if (lastSent) {
                let now = Math.floor(Date.now() / 1000); 
                let diff = now - parseInt(lastSent);
                
                if (diff < 60) {
                    iniciarBloqueio(60 - diff);
                } else {
                    localStorage.removeItem('lastCodeSentTimestamp');
                }
            }

            formReenviar.addEventListener('submit', function(e) {
                // Assim que ele clica para enviar, registramos a hora exata
                localStorage.setItem('lastCodeSentTimestamp', Math.floor(Date.now() / 1000));
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
                        localStorage.removeItem('lastCodeSentTimestamp');
                    }
                }, 1000); 
            }
        });
    </script>
</body>
</html>