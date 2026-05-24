<?php
require_once __DIR__ . '/../Models/Configuracao.php';
require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Services/EmailService.php';
require_once __DIR__ . '/../Services/OneSignalService.php';
require_once __DIR__ . '/../Helpers/CsrfGuard.php';

class ConfiguracaoController {

    private $configModel;

    public function __construct() {
        $this->configModel = new Configuracao();
    }

    /**
     * Carrega a página de configurações do sistema para o administrador.
     */
    public function carregarTela() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Recupera o status atual do funcionamento (padrão 'ativo')
        $statusFuncionamento = $this->configModel->obterValor('status_funcionamento', 'ativo');
        
        // Recupera configurações da marca (White-Label)
        $corPrimaria = $this->configModel->obterValor('cor_primaria', '#f45b69');
        $corSecundaria = $this->configModel->obterValor('cor_secundaria', '#8b5cf6');
        $logoUrl = $this->configModel->obterValor('logo_url', '');

        // Trata strings vazias ou inválidas que porventura estejam no banco de dados
        $corPrimaria = trim($corPrimaria ?? '');
        if (empty($corPrimaria) || $corPrimaria[0] !== '#') {
            $corPrimaria = '#f45b69';
        }
        $corSecundaria = trim($corSecundaria ?? '');
        if (empty($corSecundaria) || $corSecundaria[0] !== '#') {
            $corSecundaria = '#8b5cf6';
        }

        // Recupera a antecedência mínima de cancelamento (padrão '24' horas)
        $antecedenciaCancelamento = $this->configModel->obterValor('antecedencia_cancelamento_horas', '24');

        // Recupera o limite futuro de agendamento (padrão 'sem_limite')
        $limiteAgendamentoFuturo = $this->configModel->obterValor('limite_agendamento_futuro_dias', 'sem_limite');

        require_once __DIR__ . '/../../public/views/admin/configuracoes.php';
    }

    /**
     * Salva as configurações básicas de funcionamento do sistema.
     */
    public function salvar() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Validação de CSRF
        if (!CsrfGuard::validar()) {
            $_SESSION['flash_erro'] = "Falha de validação CSRF. Tente novamente.";
            header("Location: " . BASE_URL . "/admin/configuracoes");
            exit;
        }

        $status = $_POST['status_funcionamento'] ?? 'ativo';
        if (!in_array($status, ['ativo', 'inativo'])) {
            $status = 'ativo';
        }

        $corPrimaria = trim($_POST['cor_primaria'] ?? '');
        if (empty($corPrimaria) || $corPrimaria[0] !== '#') {
            $corPrimaria = '#f45b69';
        }

        $corSecundaria = trim($_POST['cor_secundaria'] ?? '');
        if (empty($corSecundaria) || $corSecundaria[0] !== '#') {
            $corSecundaria = '#8b5cf6';
        }
        $logoUrl = $_POST['logo_url'] ?? '';
        $antecedencia = $_POST['antecedencia_cancelamento_horas'] ?? '24';
        $limiteFuturo = $_POST['limite_agendamento_futuro_dias'] ?? 'sem_limite';
        if (!in_array($limiteFuturo, ['sem_limite', '7', '14', '21', '30', '60', '90', '180'])) {
            $limiteFuturo = 'sem_limite';
        }

        // Tenta salvar todas as configurações
        $sucessoStatus = $this->configModel->salvar('status_funcionamento', $status);
        $sucessoCorPrim = $this->configModel->salvar('cor_primaria', $corPrimaria);
        $sucessoCorSec = $this->configModel->salvar('cor_secundaria', $corSecundaria);
        $sucessoLogo = $this->configModel->salvar('logo_url', $logoUrl);
        $sucessoAntecedencia = $this->configModel->salvar('antecedencia_cancelamento_horas', $antecedencia);
        $sucessoLimiteFuturo = $this->configModel->salvar('limite_agendamento_futuro_dias', $limiteFuturo);

        if ($sucessoStatus || $sucessoCorPrim || $sucessoCorSec || $sucessoLogo || $sucessoAntecedencia || $sucessoLimiteFuturo) {
            $_SESSION['flash_sucesso'] = "Configurações e identidade visual atualizadas com sucesso!";
        } else {
            $_SESSION['flash_erro'] = "Nenhuma alteração foi realizada ou erro ao salvar no banco.";
        }

        header("Location: " . BASE_URL . "/admin/configuracoes");
        exit;
    }

    /**
     * Dispara um e-mail e push notification em lote para todos os clientes ativos comunicando o fechamento do salão.
     */
    public function dispararComunicadoLote() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Validação CSRF
        if (!CsrfGuard::validar()) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Token CSRF inválido.']);
            exit;
        }

        // Só permite para administradores
        if (($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Acesso negado. Apenas administradores podem disparar comunicados em lote.']);
            exit;
        }

        $usuarioModel = new Usuario();
        $clientes = $usuarioModel->buscarTodosAtivosComum();

        if (empty($clientes)) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Nenhum cliente ativo encontrado no sistema para envio do comunicado.']);
            exit;
        }

        $oneSignal = new OneSignalService();
        $emailService = new EmailService();

        $enviadosPush = 0;
        $enviadosEmail = 0;

        // Padrão do sistema para o fechamento global
        $mensagemPush = "⚠️ Comunicado Importante: O salão Belezou App está temporariamente fechado para novos agendamentos. Agradecemos a compreensão.";
        $assuntoEmail = "Aviso Importante: Alteração no Funcionamento - Belezou App";

        foreach ($clientes as $cliente) {
            $nomeCliente = $cliente['nome'];
            $emailCliente = $cliente['email'];
            $codUsuario = $cliente['id_usuario'];

            // 1. Dispara Push Notification via OneSignal
            try {
                $retornoPush = $oneSignal->enviarNotificacao($codUsuario, $mensagemPush, BASE_URL . '/', "Comunicado Oficial");
                if ($retornoPush && isset($retornoPush['http_code']) && $retornoPush['http_code'] == 200) {
                    $enviadosPush++;
                }
            } catch (Exception $e) {
                error_log("Erro ao enviar push em lote para ID {$codUsuario}: " . $e->getMessage());
            }

            // 2. Dispara e-mail via EmailService
            if (!empty($emailCliente)) {
                $corpoHtml = "<div style='padding: 20px; font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 8px; background-color: #ffffff;'>
                    <h2 style='color: #8b5cf6; margin-top: 0;'>Olá, {$nomeCliente}!</h2>
                    <p style='line-height: 1.6; font-size: 15px;'>Gostaríamos de informar que o salão <strong>Belezou App</strong> estará temporariamente fechado para novos agendamentos por motivos de força maior ou manutenção.</p>
                    <p style='line-height: 1.6; font-size: 15px;'>Os agendamentos já marcados para este período podem sofrer alterações. Nossa equipe entrará em contato caso seja necessário reagendar seu horário.</p>
                    <p style='line-height: 1.6; font-size: 15px;'>Agradecemos a sua valiosa compreensão e estamos à disposição para qualquer dúvida adicional.</p>
                    <hr style='border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;'>
                    <p style='font-size: 13px; color: #718096; line-height: 1.4;'>Atenciosamente,<br><strong>Equipe Belezou App</strong></p>
                </div>";

                try {
                    $retornoEmail = $emailService->enviar($emailCliente, $nomeCliente, $assuntoEmail, $corpoHtml);
                    if ($retornoEmail['sucesso']) {
                        $enviadosEmail++;
                    }
                } catch (Exception $e) {
                    error_log("Erro ao enviar e-mail em lote para {$emailCliente}: " . $e->getMessage());
                }
            }
        }

        echo json_encode([
            'sucesso' => true,
            'mensagem' => "Comunicado enviado! {$enviadosPush} push notifications e {$enviadosEmail} e-mails disparados."
        ]);
        exit;
    }
}
