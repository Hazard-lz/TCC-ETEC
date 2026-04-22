<?php
/**
 * CRON JOB: notificar_24h_agendamentos.php
 * Finalidade: Enviar Push Notification lembrando o cliente 24h antes.
 * Uso: Executar com PHP CLI, ex: `php c:\xampp\htdocs\TCC-ETEC\cron\notificar_24h_agendamentos.php`
 */

require_once __DIR__ . '/../database/Conexao.php';
require_once __DIR__ . '/../app/Models/Agendamento.php';
require_once __DIR__ . '/../app/Services/OneSignalService.php';

try {
    echo "[" . date('Y-m-d H:i:s') . "] Iniciando disparo do OneSignal de 24 horas...\n";
    
    // Inicia a Conexão para carregar o .env para quem roda offline
    Conexao::getConexao();

    $agendamentoModel = new Agendamento();
    $agendamentosAmanha = $agendamentoModel->buscarAgendamentosAmanha();

    if (empty($agendamentosAmanha)) {
        echo "Nenhum agendamento futuro em exatas 24 horas.\n";
        exit;
    }

    $oneSignal = new OneSignalService();
    $sucessos = 0;

    foreach ($agendamentosAmanha as $agendamento) {
        if (!empty($agendamento['cliente_cod_usuario'])) {
            $horaFormatada = substr($agendamento['hora_inicio'], 0, 5); // 09:30:00 -> 09:30
            $mensagem = "Não esqueça seu agendamento amanhã às " . $horaFormatada . ".";
            
            $retorno = $oneSignal->enviarNotificacao($agendamento['cliente_cod_usuario'], $mensagem, 'http://localhost/TCC-ETEC/historico');
            
            echo "Notificando Usuario {$agendamento['cliente_cod_usuario']} (Ag. ID: {$agendamento['id_agendamento']}) => Code HTTP: " . $retorno['http_code'] . "\n";
            $sucessos++;
        }
    }

    echo "[" . date('Y-m-d H:i:s') . "] Finalizado. {$sucessos} notificações disparadas com sucesso.\n";

} catch (Exception $e) {
    error_log("CRON ERROR - OneSignal 24h: " . $e->getMessage());
    echo "Falha na execucao: " . $e->getMessage() . "\n";
}
