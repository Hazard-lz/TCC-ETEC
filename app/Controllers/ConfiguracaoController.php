<?php
// =========================================================================
// CONTROLLER DE CONFIGURAÇÕES (ConfiguracaoController.php)
// =========================================================================
// Responsável por gerenciar as configurações gerais do salão, carregar a
// tela administrativa de parametrização e enviar comunicados oficiais
// em lote (e-mail e notificações push) para os clientes cadastrados.
// =========================================================================

require_once __DIR__ . '/../Models/Configuracao.php';
require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Services/EmailService.php';
require_once __DIR__ . '/../Services/OneSignalService.php';
require_once __DIR__ . '/../Helpers/CsrfGuard.php';

class ConfiguracaoController {

    private $configModel;

    /**
     * Inicializa o controller instanciando o modelo de dados de configuração.
     */
    public function __construct() {
        $this->configModel = new Configuracao();
    }

    /**
     * Carrega a tela de configurações do sistema para o administrador.
     * Recupera as variáveis do banco ou define valores padrões caso não estejam salvas.
     */
    public function carregarTela() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Recupera o status de funcionamento do estabelecimento (ativo ou inativo)
        $statusFuncionamento = $this->configModel->obterValor('status_funcionamento', 'ativo');
        
        // 2. Recupera cores primária/secundária do tema White-Label e URL da logo
        $corPrimaria = $this->configModel->obterValor('cor_primaria', '#f45b69');
        $corSecundaria = $this->configModel->obterValor('cor_secundaria', '#8b5cf6');
        $logoUrl = $this->configModel->obterValor('logo_url', '');

        // Trata strings vazias ou códigos hexadecimais inválidos
        $corPrimaria = trim($corPrimaria ?? '');
        if (empty($corPrimaria) || $corPrimaria[0] !== '#') {
            $corPrimaria = '#f45b69';
        }
        $corSecundaria = trim($corSecundaria ?? '');
        if (empty($corSecundaria) || $corSecundaria[0] !== '#') {
            $corSecundaria = '#8b5cf6';
        }

        // 3. Recupera o tempo mínimo de antecedência necessário para cancelamento de agendamentos
        $antecedenciaCancelamento = $this->configModel->obterValor('antecedencia_cancelamento_horas', '24');

        // 4. Recupera o limite de dias no futuro para o qual o cliente pode agendar um horário
        $limiteAgendamentoFuturo = $this->configModel->obterValor('limite_agendamento_futuro_dias', 'sem_limite');

        // 5. Recupera as informações de localização física e mapa para a página de Ajuda
        $salaoEndereco = $this->configModel->obterValor('salao_endereco', 'Av. Dr. Adhemar de Barros, 1000 - Vila Adyana, São José dos Campos - SP');
        $salaoMapaIframe = $this->configModel->obterValor('salao_mapa_iframe', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3657.1975870072793!2d-45.894336!3d-23.200788!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x94cc161f36a4bb49%3A0x6e9f64bfcb005e83!2sAv.%20Dr.%20Adhemar%20de%20Barros%2C%201000%20-%20Vila%20Adyana%2C%20S%C3%A3o%20Jos%C3%A9%20dos%20Campos%20-%20SP%2C%2012245-010!5e0!3m2!1spt-BR!2sbr!4v1700000000000!5m2!1spt-BR!2sbr');
        $salaoMapaLink = $this->configModel->obterValor('salao_mapa_link', 'https://maps.google.com/?q=Av.+Dr.+Adhemar+de+Barros,+1000+-+Vila+Adyana,+São+José+dos+Campos+-+SP');

        // Inclui e renderiza o arquivo de visualização das configurações administrativas
        require_once __DIR__ . '/../../public/views/admin/configuracoes.php';
    }

    /**
     * Valida e salva todas as alterações enviadas pelo formulário de configurações.
     */
    public function salvar() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Validação obrigatória do token CSRF para impedir ataques de submissão externa
        if (!CsrfGuard::validar()) {
            $_SESSION['flash_erro'] = "Falha de validação CSRF. Tente novamente.";
            header("Location: " . BASE_URL . "/admin/configuracoes");
            exit;
        }

        // Filtra e normaliza o status de funcionamento
        $status = $_POST['status_funcionamento'] ?? 'ativo';
        if (!in_array($status, ['ativo', 'inativo'])) {
            $status = 'ativo';
        }

        // Sanitização das cores do tema
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
        
        // Filtra o limite de dias futuros para agendamentos
        $limiteFuturo = $_POST['limite_agendamento_futuro_dias'] ?? 'sem_limite';
        if (!in_array($limiteFuturo, ['sem_limite', '7', '14', '21', '30', '60', '90', '180'])) {
            $limiteFuturo = 'sem_limite';
        }

        // Captura e limpa as variáveis de localização física
        $salaoEndereco = trim($_POST['salao_endereco'] ?? '');
        $salaoMapaIframe = trim($_POST['salao_mapa_iframe'] ?? '');
        $salaoMapaLink = trim($_POST['salao_mapa_link'] ?? '');

        // Persiste cada uma das chaves/valores de configuração no banco de dados
        $sucessoStatus = $this->configModel->salvar('status_funcionamento', $status);
        $sucessoCorPrim = $this->configModel->salvar('cor_primaria', $corPrimaria);
        $sucessoCorSec = $this->configModel->salvar('cor_secundaria', $corSecundaria);
        $sucessoLogo = $this->configModel->salvar('logo_url', $logoUrl);
        $sucessoAntecedencia = $this->configModel->salvar('antecedencia_cancelamento_horas', $antecedencia);
        $sucessoLimiteFuturo = $this->configModel->salvar('limite_agendamento_futuro_dias', $limiteFuturo);
        $sucessoEndereco = $this->configModel->salvar('salao_endereco', $salaoEndereco);
        $sucessoMapaIframe = $this->configModel->salvar('salao_mapa_iframe', $salaoMapaIframe);
        $sucessoMapaLink = $this->configModel->salvar('salao_mapa_link', $salaoMapaLink);

        // Define mensagens flash apropriadas para feedback visual na View
        if ($sucessoStatus || $sucessoCorPrim || $sucessoCorSec || $sucessoLogo || $sucessoAntecedencia || $sucessoLimiteFuturo || $sucessoEndereco || $sucessoMapaIframe || $sucessoMapaLink) {
            $_SESSION['flash_sucesso'] = "Configurações, identidade visual e localização atualizadas com sucesso!";
        } else {
            $_SESSION['flash_erro'] = "Nenhuma alteração foi realizada ou erro ao salvar no banco.";
        }

        header("Location: " . BASE_URL . "/admin/configuracoes");
        exit;
    }

    /**
     * Dispara um comunicado importante em lote para todos os clientes ativos.
     * Envia e-mail e notificação push (via OneSignal) simultaneamente.
     * Retorna a resposta estruturada em JSON para chamadas AJAX.
     */
    public function dispararComunicadoLote() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Validação obrigatória de CSRF
        if (!CsrfGuard::validar()) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Token CSRF inválido.']);
            exit;
        }

        // Controle de acesso: Apenas o administrador geral do salão pode disparar mensagens em lote
        if (($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Acesso negado. Apenas administradores podem disparar comunicados em lote.']);
            exit;
        }

        // Busca todos os usuários do tipo "cliente" que estão com cadastro ativo
        $usuarioModel = new Usuario();
        $clientes = $usuarioModel->buscarTodosAtivosComum();

        if (empty($clientes)) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Nenhum cliente ativo encontrado no sistema para envio do comunicado.']);
            exit;
        }

        // Instancia os serviços de envio de notificações e e-mails
        $oneSignal = new OneSignalService();
        $emailService = new EmailService();

        $enviadosPush = 0;
        $enviadosEmail = 0;

        // Textos padrões informativos para o comunicado de encerramento temporário
        $mensagemPush = "⚠️ Comunicado Importante: O salão Belezou App está temporariamente fechado para novos agendamentos. Agradecemos a compreensão.";
        $assuntoEmail = "Aviso Importante: Alteração no Funcionamento - Belezou App";

        // Itera sobre todos os clientes ativos disparando os alertas
        foreach ($clientes as $cliente) {
            $nomeCliente = $cliente['nome'];
            $emailCliente = $cliente['email'];
            $codUsuario = $cliente['id_usuario'];

            // 1. Envia a notificação Push via OneSignal (se o dispositivo do cliente estiver cadastrado)
            try {
                $retornoPush = $oneSignal->enviarNotificacao($codUsuario, $mensagemPush, BASE_URL . '/', "Comunicado Oficial");
                if ($retornoPush && isset($retornoPush['http_code']) && $retornoPush['http_code'] == 200) {
                    $enviadosPush++;
                }
            } catch (Exception $e) {
                error_log("Erro ao enviar push em lote para ID {$codUsuario}: " . $e->getMessage());
            }

            // 2. Envia e-mail estruturado em HTML via EmailService (se o cliente tiver e-mail válido cadastrado)
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

        // Retorna o sumário de envios bem sucedidos para exibição no front-end
        echo json_encode([
            'sucesso' => true,
            'mensagem' => "Comunicado enviado! {$enviadosPush} push notifications e {$enviadosEmail} e-mails disparados."
        ]);
        exit;
    }
}

