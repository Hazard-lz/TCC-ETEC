<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - Belezou</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin-layout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin.css">

    <!-- OneSignal SDK -->
    <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
    <script>
      window.OneSignalDeferred = window.OneSignalDeferred || [];
      OneSignalDeferred.push(async function(OneSignal) {
        await OneSignal.init({
          appId: "4af62891-b85a-4ecd-a224-a5fe4df9a5d5",
          // Pode configurar options de UI aqui caso queira modificar o prompt
        });

        // Configura permissão ao entrar caso não tenha sido dada ainda
        if (OneSignal.Notifications.permission === "default") {
            await OneSignal.Slidedown.promptPush();
        }
      });
    </script>
</head>
<body class="bg-light">
   
<?php
// Prepara os dados do usuário da sessão para o JavaScript
$userData = json_encode([
    'cod_usuario' => $_SESSION['usuario_id'] ?? null,
    'nome' => $_SESSION['usuario_nome'] ?? 'Usuário',
    'tipo' => $_SESSION['usuario_tipo'] ?? 'funcionario'
]);
?>

<script>
    localStorage.setItem('belezou_user', '<?= $userData ?>');

    // Mapeia o usuário logado perante o OneSignal para puder receber os envios do backend via external_id
    OneSignalDeferred.push(async function(OneSignal) {
        const userId = <?= json_encode((string)($_SESSION['usuario_id'] ?? '')) ?>;
        if (userId) {
            await OneSignal.login(userId);
        }
    });
</script>

<script src="<?= BASE_URL ?>/public/resources/js/menu_global.js"></script>

<div id="conteudo-temporario" style="display: none;">