<?php

require_once __DIR__ . '/../Services/AgendamentoService.php';
require_once __DIR__ . '/../Models/Cliente.php';
require_once __DIR__ . '/../Models/Funcionario.php';

class AgendamentoController {

    private $agendamentoService;
    private $clienteModel;
    private $funcionarioModel;

    public function __construct() {
        $this->agendamentoService = new AgendamentoService();
        $this->clienteModel = new Cliente();
        $this->funcionarioModel = new Funcionario();
    }

    public function carregarTelaCliente() {
        // Verifica se o usuário comum está logado
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'comum') {
            $_SESSION['flash_erro'] = "Faça login para agendar um horário.";
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // Busca os dados ativos no banco
        $servicoModel = new Servico();
        
        // Busca os dados ativos no banco
        $servicos = $servicoModel->listarPorStatus('ativo');

        // Renderiza a View passando as variáveis $servicos e $profissionais
        require_once __DIR__ . '/../../public/views/cliente/agendar.php';
    }
    /**
     * Action: POST para /agendar (Cliente) ou /funcionario/agenda (Funcionário)
     * Responsável por receber os dados do form de agendamento.
     */
    public function salvar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['flash_erro'] = "Método de requisição inválido.";
            header('Location: ' . BASE_URL . '/');
            exit;
        }

        // 1. Receber os dados base do formulário HTML
        $id_servico = $_POST['id_servico'] ?? '';
        $id_funcionario_prestador = $_POST['id_funcionario'] ?? '';
        $data = $_POST['data'] ?? '';
        $hora_inicio = $_POST['hora'] ?? '';

        if (empty($id_servico) || empty($id_funcionario_prestador) || empty($data) || empty($hora_inicio)) {
            $_SESSION['flash_erro'] = "Por favor, preencha todos os campos obrigatórios (Serviço, Profissional, Data e Hora).";
            $this->redirecionarAposErro();
        }

        // 2. Identificar quem está a fazer a ação através da sessão
        $id_usuario_logado = $_SESSION['usuario_id'];
        $tipo_usuario_logado = $_SESSION['usuario_tipo'];
        
        $id_cliente = null;
        $id_funcionario_criador = null;

        // 3. Tradução de IDs conforme a arquitetura de "Herança" no BD
        if ($tipo_usuario_logado === 'comum') {
            // O próprio cliente está a agendar pelo telemóvel/site
            $cliente = $this->clienteModel->buscarPorCodUsuario($id_usuario_logado);
            if (!$cliente) {
                $_SESSION['flash_erro'] = "Perfil de cliente não encontrado. Atualize o seu cadastro.";
                header('Location: ' . BASE_URL . '/perfil');
                exit;
            }
            $id_cliente = $cliente['id_cliente'];
            
        } else {
            // Um admin ou funcionário está a agendar para um cliente (via balcão)
            $id_cliente = $_POST['id_cliente'] ?? '';
            
            if (empty($id_cliente)) {
                $_SESSION['flash_erro'] = "É obrigatório selecionar um cliente para o agendamento.";
                header('Location: ' . BASE_URL . '/funcionario/agenda'); 
                exit;
            }

            // Descobre o ID do funcionário que está a criar este agendamento (auditoria)
            $funcionarioCriador = $this->funcionarioModel->buscarPorCodUsuario($id_usuario_logado);
            if ($funcionarioCriador) {
                $id_funcionario_criador = $funcionarioCriador['id_funcionario'];
            }
        }

        // 4. Chamar o Service com as regras de negócio pesadas
        $resultado = $this->agendamentoService->realizarAgendamento(
            $id_cliente, 
            $id_funcionario_prestador, 
            $id_servico, 
            $data, 
            $hora_inicio, 
            $id_funcionario_criador
        );

        // 5. Feedback Visual e Redirecionamento
        if ($resultado['sucesso']) {
            $_SESSION['flash_sucesso'] = $resultado['mensagem'];
            
            // Redireciona para as novas rotas limpas
            if ($tipo_usuario_logado === 'comum') {
                header('Location: ' . BASE_URL . '/historico'); 
            } else {
                header('Location: ' . BASE_URL . '/funcionario/agenda');
            }
            exit;
        } else {
            $_SESSION['flash_erro'] = $resultado['mensagem'];
            $this->redirecionarAposErro();
        }
    }

    /**
     * Action: POST para /funcionario/agenda/status
     * Responsável por cancelar ou concluir um agendamento.
     */
    public function alterarStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['flash_erro'] = "Método inválido.";
            header('Location: ' . BASE_URL . '/');
            exit;
        }

        $id_agendamento = $_POST['id_agendamento'] ?? '';
        $novo_status = $_POST['novo_status'] ?? '';

        if (empty($id_agendamento) || empty($novo_status)) {
            $_SESSION['flash_erro'] = "Dados insuficientes para alterar o status.";
            $this->redirecionarAposErro();
        }

        $resultado = $this->agendamentoService->alterarStatus($id_agendamento, $novo_status);

        if ($resultado['sucesso']) {
            $_SESSION['flash_sucesso'] = $resultado['mensagem'];
        } else {
            $_SESSION['flash_erro'] = $resultado['mensagem'];
        }

        $this->redirecionarAposErro();
    }

    /**
     * Utilitário privado para garantir que o utilizador volta para a tela correta.
     */
    private function redirecionarAposErro() {
        if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'comum') {
            header('Location: ' . BASE_URL . '/agendar');
        } else {
            // Volta para a página anterior (referer) com fallback para a nova rota da agenda
            $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/funcionario/agenda';
            header('Location: ' . $referer);
        }
        exit;
    }

    public function historicoCliente() {
        // 1. Verificação de Segurança
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'comum') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // 2. Transforma o ID do Usuário da sessão no ID do Cliente do Schema
        $cliente = $this->clienteModel->buscarPorCodUsuario($_SESSION['usuario_id']);
        if (!$cliente) {
            $_SESSION['flash_erro'] = "Perfil de cliente não encontrado.";
            header('Location: ' . BASE_URL . '/perfil');
            exit;
        }

        // 3. Busca os dados brutos
        $agendamentoModel = new Agendamento();
        $agendamentos = $agendamentoModel->listarPorCliente($cliente['id_cliente']);

        // 4. Separação de Lógica e Formatação (Data Prep)
        // ARQUITETURA: O Controller mastiga os dados. A View apenas renderiza.
        $proximos = [];
        $anteriores = [];

        if ($agendamentos) {
            foreach ($agendamentos as $ag) {
                // Formata os dados para o padrão PT-BR visual
                $dataConvertida = new DateTime($ag['data_agendamento']);
                $ag['data_formatada'] = $dataConvertida->format('d/m/Y');
                $ag['hora_formatada'] = substr($ag['hora_inicio'], 0, 5); // Pega só o "HH:MM"
                $ag['preco_formatado'] = number_format($ag['preco_cobrado'], 2, ',', '.');

                // Lógica de segregação de abas
                if (in_array($ag['status'], ['pendente', 'marcado'])) {
                    $proximos[] = $ag;
                } else {
                    // cancelado, concluido
                    $anteriores[] = $ag;
                }
            }
        }

        // 5. Injeta as variáveis no HTML
        require_once __DIR__ . '/../../public/views/cliente/historico.php';
    }

    public function agendaFuncionario() {
        $id_usuario = $_SESSION['usuario_id'];
        $funcionario = $this->funcionarioModel->buscarPorCodUsuario($id_usuario);
        
        if (!$funcionario) {
            $_SESSION['flash_erro'] = "Perfil de funcionário não encontrado.";
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        date_default_timezone_set('America/Sao_Paulo');
        $dataFiltro = $_GET['data'] ?? date('Y-m-d');

        // 1. Descobrir o DOMINGO base e as datas de navegação
        $dataBase = new DateTime($dataFiltro);
        $dataAnterior = (clone $dataBase)->modify('-7 days')->format('Y-m-d');
        $dataProxima = (clone $dataBase)->modify('+7 days')->format('Y-m-d');
        $hoje = date('Y-m-d');

        // Lógica matemática infalível para ir buscar o Domingo desta semana (0 = Domingo)
        $diaSemana = (int)$dataBase->format('w'); 
        $domingoBase = clone $dataBase;
        $domingoBase->modify("-{$diaSemana} days");

        $mesNome = Helpers::MESES[(int)$domingoBase->format('m')];
        $ano = $domingoBase->format('Y');

        $agendamentoModel = new Agendamento();

        // 2. ARQUITETURA: Cria o array de 7 dias perfeitinho para a View não se partir
        $diasSemanaInfo = [];
        $nomesDias = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
        
        $loopData = clone $domingoBase;
        for ($i = 0; $i < 7; $i++) {
            $dataAtual = $loopData->format('Y-m-d');
            $diasSemanaInfo[] = [
                'data' => $dataAtual,
                'nome' => $nomesDias[$i],
                'dia_num' => $loopData->format('d'),
                'is_today' => ($dataAtual === $hoje),
                'agendamentos' => $agendamentoModel->listarAgendaFuncionario($funcionario['id_funcionario'], $dataAtual) ?: []
            ];
            $loopData->modify('+1 day'); // Avança para o dia seguinte
        }

        // 3. Dados para o Modal
        $servicoModel = new Servico();
        $servicos = $servicoModel->listarPorStatus('ativo');

        $clientes = $this->clienteModel->listarTodos();
        $profissionais = $this->funcionarioModel->listarTodos();

        require_once __DIR__ . '/../../public/views/funcionario/agendamentos.php';
    }
}