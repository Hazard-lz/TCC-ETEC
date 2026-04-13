<?php
require_once __DIR__ . '/../../database/Conexao.php'; // Para garantir a leitura do .env se não estiver lido

class OneSignalService
{
    private $appId;
    private $restApiKey;
    private $apiUrl = 'https://onesignal.com/api/v1/notifications';

    public function __construct()
    {
        // Garante que as variáveis de ambiente estejam acessíveis chamando o getConexao caso ainda na pagina ninguem tenha chamado
        if (!isset($_ENV['ONESIGNAL_APP_ID'])) {
            try {
                Conexao::getConexao(); 
            } catch(Exception $e) {}
        }
        
        $this->appId = $_ENV['ONESIGNAL_APP_ID'] ?? '4af62891-b85a-4ecd-a224-a5fe4df9a5d5';
        $this->restApiKey = $_ENV['ONESIGNAL_REST_API_KEY'] ?? '';
    }

    /**
     * Envia notificação por Push para um cliente específico através do external_id (cod_usuario)
     */
    public function enviarNotificacao($codUsuario, $mensagem, $url = null, $titulo = "Belezou App")
    {
        $fields = [
            'app_id' => $this->appId,
            'include_aliases' => [
                'external_id' => [strval($codUsuario)]
            ],
            'target_channel' => 'push',
            'headings' => [
                'en' => $titulo,
                'pt' => $titulo
            ],
            'contents' => [
                'en' => $mensagem, // O OneSignal geralmente usa 'en' como padrão, independente do idioma real para simplicidade
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
        $fields = json_encode($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic ' . $this->restApiKey
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Importante para rodar via localhost

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'response' => $response,
            'http_code' => $httpCode
        ];
    }
}
