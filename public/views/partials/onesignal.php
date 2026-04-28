<?php if(isset($_SESSION['usuario_id'])): ?>
<!-- OneSignal SDK -->
<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<script>
  window.OneSignalDeferred = window.OneSignalDeferred || [];
  OneSignalDeferred.push(async function(OneSignal) {
    await OneSignal.init({
      appId: "4af62891-b85a-4ecd-a224-a5fe4df9a5d5",
      allowLocalhostAsSecureOrigin: true,
      notifyButton: {
        enable: true,
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

    // Envia o subscription_id ao servidor para salvar no banco
    // (necessário porque o external_id do OneSignal não funciona via API)
    const subId = OneSignal.User.PushSubscription.id;
    if (subId) {
        fetch("<?= BASE_URL ?>/api/onesignal/registrar", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ subscription_id: subId })
        }).then(r => r.json()).then(d => {
            if (d.sucesso) console.log("OneSignal subscription_id salvo no servidor:", subId);
        }).catch(e => console.log("Erro ao salvar subscription_id:", e));
    }

    // Detecta mudanças no subscription_id (ex: usuário revogou e permitiu de novo)
    OneSignal.User.PushSubscription.addEventListener('change', function(event) {
        const newSubId = event.current.id;
        if (newSubId) {
            fetch("<?= BASE_URL ?>/api/onesignal/registrar", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ subscription_id: newSubId })
            });
        }
    });
    
    // Captura o momento exato em que a notificação Push chega COM O SITE ABERTO (Foco)
    OneSignal.Notifications.addEventListener('foregroundWillDisplay', function(event) {
        let titulo = event.notification.title || "Atualização de Agendamento!";
        let mensagem = event.notification.body || "Você tem uma nova notificação do Belezou App.";
        
        alert(titulo + "\n\n" + mensagem);
    });
  });
</script>
<?php endif; ?>
