<?php
if (isset($_SESSION['usuario_id'])):
    // Garante que o Conexao.php está carregado
    require_once __DIR__ . '/../../../database/Conexao.php';
    
    // Tenta buscar o ID de todas as fontes possíveis
    $appId = $_ENV['ONESIGNAL_APP_ID'] ?? getenv('ONESIGNAL_APP_ID') ?? '';

    // Se estiver vazio, tenta forçar o carregamento do banco (que carrega o .env)
    if (empty($appId)) {
        try {
            Conexao::getConexao();
            $appId = $_ENV['ONESIGNAL_APP_ID'] ?? getenv('ONESIGNAL_APP_ID') ?? '';
        } catch (Exception $e) {}
    }
    ?>
    <!-- OneSignal SDK -->
    <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
    <script>
        window.OneSignalDeferred = window.OneSignalDeferred || [];
        OneSignalDeferred.push(async function (OneSignal) {
            if (!"<?= $appId ?>") {
                console.error("OneSignal: App ID não configurado no .env");
                return;
            }

            await OneSignal.init({
                appId: "<?= $appId ?>",
                allowLocalhostAsSecureOrigin: true,
                // Configuração crucial para subpastas e mobile
                serviceWorkerPath: '<?= BASE_URL ?>/OneSignalSDKWorker.js',
                serviceWorkerParam: { scope: '<?= BASE_URL ?>/' },
                notifyButton: {
                    enable: true,
                    size: 'medium',
                    position: 'bottom-right',
                    colors: {
                        'circle.background': '#8b5cf6',
                        'badge.background': '#8b5cf6',
                    },
                    displayPredicate: function() {
                        // Não mostra o sino em páginas de login/cadastro
                        return !window.location.pathname.includes('login') && !window.location.pathname.includes('cadastro');
                    }
                }
            });

            // Vincula o usuário logado (External ID)
            const userId = <?= json_encode((string) $_SESSION['usuario_id']) ?>;
            if (userId) {
                await OneSignal.login(userId);
            }

            // Função para registrar o ID de inscrição no servidor
            const registrarNoServidor = async () => {
                const subId = OneSignal.User.PushSubscription.id;
                if (subId) {
                    try {
                        const res = await fetch("<?= BASE_URL ?>/api/onesignal/registrar", {
                            method: "POST",
                            headers: { "Content-Type": "application/json" },
                            body: JSON.stringify({ subscription_id: subId })
                        });
                        const data = await res.json();
                        if (data.sucesso) console.log("OneSignal: Subscription ID registrado com sucesso.");
                    } catch (err) {
                        console.error("OneSignal: Erro ao registrar no servidor", err);
                    }
                }
            };

            // Tenta registrar o ID inicial
            registrarNoServidor();

            // Escuta mudanças (ex: quando o usuário aceita a notificação pela primeira vez)
            OneSignal.User.PushSubscription.addEventListener('change', registrarNoServidor);

            // Se o usuário ainda não decidiu (está em "default"), mostra o prompt amigável
            // No mobile (iOS), isso é essencial.
            if (OneSignal.Notifications.permissionNative === "default") {
                // Pequeno atraso para não assustar o usuário assim que a página carrega
                setTimeout(async () => {
                    await OneSignal.Slidedown.promptPush();
                }, 2000);
            }

            // Listener para alertas em tempo real se o app estiver aberto
            OneSignal.Notifications.addEventListener('foregroundWillDisplay', function (event) {
                let titulo = event.notification.title || "Belezou App";
                let mensagem = event.notification.body || "Você tem uma nova mensagem.";
                // Opcional: usar um modal mais bonito em vez de alert
                alert(titulo + "\n\n" + mensagem);
            });
        });
    </script>
<?php endif; ?>