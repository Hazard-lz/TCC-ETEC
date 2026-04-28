<?php
require_once __DIR__ . '/../../database/Conexao.php';

class OneSignalService
{
    private $appId;
    private $restApiKey;
    private $apiUrl = 'https://api.onesignal.com/notifications';

    public function __construct()
    {
        if (!isset($_ENV['ONESIGNAL_APP_ID'])) {
            try {
                Conexao::getConexao(); 
            } catch(Exception $e) {}
        }
        
        $this->appId = $_ENV['ONESIGNAL_APP_ID'] ?? '4af62891-b85a-4ecd-a224-a5fe4df9a5d5';
        $this->restApiKey = $_ENV['ONESIGNAL_REST_API_KEY'] ?? '';
    }

    /**
     * Envia notificação por Push para um usuário específico através do subscription_id salvo no banco.
     */
    public function enviarNotificacao($codUsuario, $mensagem, $url = null, $titulo = "Belezou App")
    {
        // Busca o subscription_id do OneSignal no banco de dados
        $conn = Conexao::getConexao();
        $stmt = $conn->prepare("SELECT onesignal_sub_id FROM usuarios WHERE id_usuario = :id AND onesignal_sub_id IS NOT NULL");
        $stmt->execute([':id' => $codUsuario]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario || empty($usuario['onesignal_sub_id'])) {
            error_log("OneSignal: Nenhum subscription_id encontrado para o usuario $codUsuario");
            return ['response' => '{"errors":["no subscription_id in database"]}', 'http_code' => 0];
        }

        $fields = [
            'app_id' => $this->appId,
            'include_subscription_ids' => [$usuario['onesignal_sub_id']],
            'headings' => [
                'en' => $titulo,
                'pt' => $titulo
            ],
            'contents' => [
                'en' => $mensagem,
                'pt' => $mensagem
            ]
        ];

        if ($url) {
            $fields['url'] = $url;
        }

        return $this->dispararCurl($fields);
    }

    private function dispararCurl($fields)
    {
        $fieldsJson = json_encode($fields);

        // Detecta se a chave é v2 (os_v2_app_...) para usar o header correto
        $authPrefix = (str_starts_with($this->restApiKey, 'os_v2_')) ? 'Key' : 'Basic';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            "Authorization: $authPrefix " . $this->restApiKey
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldsJson);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        error_log("OneSignal push response HTTP $httpCode: $response");

        return [
            'response' => $response,
            'http_code' => $httpCode
        ];
    }
}
