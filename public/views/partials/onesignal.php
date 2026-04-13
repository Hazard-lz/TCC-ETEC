<?php if(isset($_SESSION['usuario_id'])): ?>
<!-- OneSignal SDK -->
<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<script>
  window.OneSignalDeferred = window.OneSignalDeferred || [];
  OneSignalDeferred.push(async function(OneSignal) {
    await OneSignal.init({
      appId: "4af62891-b85a-4ecd-a224-a5fe4df9a5d5",
      allowLocalhostAsSecureOrigin: true, // Força funcionamento no localhost sem HTTPS
      notifyButton: {
        enable: true, // Habilita o "Sino" no canto inferior direito
      }
    });

    console.log("OneSignal Inicializado. Estado da Permissão atual:", OneSignal.Notifications.permission);

    // Pede permissão de Notificação no Primeiro Acesso
    if (OneSignal.Notifications.permission === "default" || !OneSignal.Notifications.permission) {
        await OneSignal.Slidedown.promptPush();
    }

    const userId = <?= json_encode((string)$_SESSION['usuario_id']) ?>;
    if (userId) {
        await OneSignal.login(userId);
    }
    
    // Captura o momento exato em que a notificação Push chega COM O SITE ABERTO (Foco)
    OneSignal.Notifications.addEventListener('foregroundWillDisplay', function(event) {
        // Dispara um alerta na tela do navegador para ter certeza de que você viu!
        // event.preventDefault(); // (Se você quiser bloquear o sino do Windows e deixar só o Alerta de tela, descomente isso)
        
        let titulo = event.notification.title || "Atualização de Agendamento!";
        let mensagem = event.notification.body || "Você tem uma nova notificação do Belezou App.";
        
        alert(titulo + "\n\n" + mensagem);
    });
  });
</script>
<?php endif; ?>
