<?php

// Importa as classes do PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/BaseService.php';

class EmailService extends BaseService {

    private $mail;

    public function __construct() {
        // Instancia o PHPMailer passando 'true' para habilitar as exceções
        $this->mail = new PHPMailer(true);
        $this->configurarServidor();
    }

    private function configurarServidor() {
        try {
            // Configurações do Servidor (Puxando do seu .env)
            // $this->mail->SMTPDebug = SMTP::DEBUG_SERVER; // Descomente esta linha apenas se der erro e precisar investigar
            $this->mail->isSMTP();                                      
            $this->mail->Host       = $_ENV['MAIL_HOST'];                     
            $this->mail->SMTPAuth   = true;                                   
            $this->mail->Username   = $_ENV['MAIL_USER'];                     
            $this->mail->Password   = $_ENV['MAIL_PASS'];                     
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port       = $_ENV['MAIL_PORT']; 
            
            // Força o charset para UTF-8 (evita problemas com acentuação)
            $this->mail->CharSet = 'UTF-8';

            // Remetente Padrão (Quem está enviando)
            $this->mail->setFrom($_ENV['MAIL_USER'], 'Belezou App');

        } catch (Exception $e) {
            error_log("Erro ao configurar PHPMailer: {$this->mail->ErrorInfo}");
        }
    }

    /**
     * Função genérica para disparar e-mails
     */
    public function enviar($paraEmail, $paraNome, $assunto, $corpoHtml, $corpoTextoPuro = '') {
        try {
            // Destinatário
            $this->mail->clearAddresses(); // Limpa destinatários anteriores (importante se usar em loop)
            $this->mail->addAddress($paraEmail, $paraNome);

            // Conteúdo
            $this->mail->isHTML(true); // Define que o corpo tem tags HTML
            $this->mail->Subject = $assunto;
            $this->mail->Body    = $corpoHtml;
            
            // Versão em texto puro para clientes de e-mail antigos ou regras de anti-spam
            $this->mail->AltBody = empty($corpoTextoPuro) ? strip_tags($corpoHtml) : $corpoTextoPuro;

            $this->mail->send();
            return $this->sucesso('E-mail enviado com sucesso.');

        } catch (Exception $e) {
            error_log("Erro ao enviar e-mail para {$paraEmail}. Mailer Error: {$this->mail->ErrorInfo}");
            return $this->erro('Não foi possível enviar o e-mail no momento.');
        }
    }
}
?>