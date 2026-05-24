<?php

require_once __DIR__ . '/../Services/DisponibilidadeService.php';
require_once __DIR__ . '/../Models/Funcionario.php';

class DisponibilidadeController
{
    private $disponibilidadeService;
    private $funcionarioModel;

    public function __construct()
    {
        $this->disponibilidadeService = new DisponibilidadeService();
        $this->funcionarioModel = new Funcionario();
    }

    private function getFuncionarioLogadoId() {
        if (!isset($_SESSION['usuario_id'])) return null;
        $funcionario = $this->funcionarioModel->buscarPorCodUsuario($_SESSION['usuario_id']);
        return $funcionario ? $funcionario['id_funcionario'] : null;
    }

    public function salvar()
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['is_funcionario'])) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $idFuncionario = $this->getFuncionarioLogadoId();
            if (!$idFuncionario) {
                $_SESSION['msg_erro'] = "Perfil de funcionário não encontrado.";
                header("Location: " . BASE_URL . "/funcionario/disponibilidade");
                exit;
            }
            
            // Se vier vazio, é null (Nova Grade)
            $idDisponibilidade = empty($_POST['id_disponibilidade']) ? null : $_POST['id_disponibilidade'];
            
            $nomeGrade = $_POST['nome_grade'] ?? 'Minha Grade';
            $antecedenciaHoras = isset($_POST['antecedencia_horas']) ? (int)$_POST['antecedencia_horas'] : 0;
            $isAtiva = isset($_POST['is_ativa']) && $_POST['is_ativa'] == '1';
            
            $diasPost = $_POST['dias'] ?? []; 
            $diasConfigurados = [];
            
            foreach ($diasPost as $dia => $dados) {
                $isAtivo = isset($dados['ativo']) && $dados['ativo'] == '1';
                
                $diasConfigurados[$dia] = [
                    'inicio' => $dados['hora_inicio'] ?? '',
                    'fim' => $dados['hora_fim'] ?? '',
                    'int_inicio' => $dados['intervalo_inicio'] ?? '',
                    'int_fim' => $dados['intervalo_fim'] ?? '',
                    'status' => $isAtivo ? 'disponivel' : 'indisponivel'
                ];
            }

            $resultado = $this->disponibilidadeService->salvarGrade(
                $idFuncionario, $idDisponibilidade, $nomeGrade, $isAtiva, $diasConfigurados, $antecedenciaHoras
            );

            if ($resultado['sucesso']) {
                $_SESSION['msg_sucesso'] = $resultado['mensagem'];
                
                // =========================================================================
                // ARQUITETURA UX: LIMPANDO O ESTADO APÓS SUCESSO
                // =========================================================================
                // Por que fazer isso? 
                // A sessão estava mantendo o estado 'nova' ou o ID da grade antiga.
                // Limpando essa variável, forçamos o ciclo PRG (Post/Redirect/Get) a 
                // recarregar a página em seu estado neutro. O modal virá fechado e o 
                // sistema voltará a exibir a grade que for a "Principal/Ativa" por padrão.
                // =========================================================================
                $_SESSION['grade_visualizada'] = ''; 

            } else {
                $_SESSION['msg_erro'] = $resultado['mensagem'];
            }

            header("Location: " . BASE_URL . "/funcionario/disponibilidade");
            exit;
        }
    }

    public function ativar()
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idFuncionario = $this->getFuncionarioLogadoId();
            $idDisponibilidade = $_POST['id_disponibilidade'] ?? '';
            
            $resultado = $this->disponibilidadeService->ativarGrade($idFuncionario, $idDisponibilidade);

            if ($resultado['sucesso']) {
                $_SESSION['msg_sucesso'] = $resultado['mensagem'];
            } else {
                $_SESSION['msg_erro'] = $resultado['mensagem'];
            }

            header("Location: " . BASE_URL . "/funcionario/disponibilidade");
            exit;
        }
    }

    public function salvarAntecedencia()
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idFuncionario = $this->getFuncionarioLogadoId();
            $idDisponibilidade = $_POST['id_disponibilidade'] ?? '';
            $antecedenciaHoras = isset($_POST['antecedencia_horas']) ? (int)$_POST['antecedencia_horas'] : 0;
            
            // To update just the antecedencia, we will reuse the service method but fetching the current data
            // wait, DisponibilidadeModel already has atualizarGrade. We don't have a specific service method for just updating antecedencia,
            // but we can call a new method on the model directly since it's a simple update.
            require_once __DIR__ . '/../Models/Disponibilidade.php';
            $dispModel = new Disponibilidade();
            
            // Security check
            $todas = $dispModel->buscarGradesFuncionario($idFuncionario);
            $grade = null;
            foreach ($todas as $g) {
                if ($g['id_disponibilidade'] == $idDisponibilidade) {
                    $grade = $g; break;
                }
            }
            
            if ($grade) {
                $dispModel->atualizarGrade($idDisponibilidade, $grade['nome_grade'], $antecedenciaHoras);
                $_SESSION['msg_sucesso'] = "Antecedência mínima atualizada com sucesso.";
            } else {
                $_SESSION['msg_erro'] = "Grade não encontrada.";
            }

            header("Location: " . BASE_URL . "/funcionario/disponibilidade");
            exit;
        }
    }

    /**
     * ARQUITETURA: Armazena a grade selecionada na sessão para esconder o ID da URL.
     */
    public function selecionarGradeVisao()
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Armazena a escolha na sessão do usuário
            $_SESSION['grade_visualizada'] = $_POST['grade_selecionada'] ?? '';
            
            // Recarrega a página com a URL limpa
            header("Location: " . BASE_URL . "/funcionario/disponibilidade");
            exit;
        }
    }

    public function excluir()
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idFuncionario = $this->getFuncionarioLogadoId(); // Trava de segurança IDOR
            $idDisponibilidade = $_POST['id_disponibilidade'] ?? '';
            
            $resultado = $this->disponibilidadeService->excluirGrade($idDisponibilidade, $idFuncionario);

            if ($resultado['sucesso']) {
                $_SESSION['msg_sucesso'] = $resultado['mensagem'];
            } else {
                $_SESSION['msg_erro'] = $resultado['mensagem'];
            }

            header("Location: " . BASE_URL . "/funcionario/disponibilidade");
            exit;
        }
    }

    public function buscarHorariosLivres()
    {
        date_default_timezone_set('America/Sao_Paulo');
        header('Content-Type: application/json');
        $dados = json_decode(file_get_contents("php://input"), true) ?? $_POST;

        $idFuncionario = isset($dados['id_funcionario']) ? (int)$dados['id_funcionario'] : 0;
        $dataDesejada = isset($dados['data']) ? trim($dados['data']) : '';
        $idServico = isset($dados['id_servico']) ? (int)$dados['id_servico'] : 0;
        $idAgendamentoIgnorar = isset($dados['id_agendamento_ignorar']) ? (int)$dados['id_agendamento_ignorar'] : null;

        if (empty($idFuncionario) || empty($dataDesejada) || empty($idServico)) {
            http_response_code(400); 
            echo json_encode(['sucesso' => false, 'mensagem' => 'Faltam parâmetros obrigatórios.']);
            exit;
        }

        // Validação de limite futuro de agendamento se for usuário comum (cliente)
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $tipoUsuario = $_SESSION['usuario_tipo'] ?? '';
        if ($tipoUsuario === 'comum') {
            require_once __DIR__ . '/../Models/Configuracao.php';
            $configModel = new Configuracao();
            $limiteDiasVal = $configModel->obterValor('limite_agendamento_futuro_dias', 'sem_limite');
            if ($limiteDiasVal !== 'sem_limite' && is_numeric($limiteDiasVal)) {
                $limiteDiasInt = (int)$limiteDiasVal;
                $maxDataObj = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
                $maxDataObj->modify("+{$limiteDiasInt} days");
                $maxData = $maxDataObj->format('Y-m-d');
                
                if ($dataDesejada > $maxData) {
                    http_response_code(400);
                    echo json_encode(['sucesso' => false, 'mensagem' => 'A data selecionada excede o limite permitido para agendamentos futuros.']);
                    exit;
                }
            }
        }

        try {
            $horariosDisponiveis = $this->disponibilidadeService->calcularHorariosLivres(
                $idFuncionario, $dataDesejada, $idServico, $idAgendamentoIgnorar
            );

            http_response_code(200); 
            echo json_encode(['sucesso' => true, 'horarios' => $horariosDisponiveis]);
            exit;

        } catch (Exception $e) {
            http_response_code(500); 
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro interno ao processar horários.']);
            error_log("Erro em buscarHorariosLivres: " . $e->getMessage());
            exit;
        }
    }

    public function salvarBloqueio()
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['is_funcionario'])) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idFuncionario = $this->getFuncionarioLogadoId();
            if (!$idFuncionario) {
                $_SESSION['flash_erro'] = "Perfil de funcionário não encontrado.";
                header("Location: " . BASE_URL . "/funcionario/agenda");
                exit;
            }

            $data = $_POST['data_bloqueio'] ?? '';
            $inicio = $_POST['hora_inicio'] ?? '';
            $fim = $_POST['hora_fim'] ?? '';
            $motivo = $_POST['motivo'] ?? 'Bloqueio Manual';

            if (empty($data) || empty($inicio) || empty($fim)) {
                $_SESSION['flash_erro'] = "Todos os campos de horário do bloqueio são obrigatórios.";
                header("Location: " . BASE_URL . "/funcionario/agenda");
                exit;
            }

            if (strtotime($fim) <= strtotime($inicio)) {
                $_SESSION['flash_erro'] = "O horário de término do bloqueio deve ser posterior ao de início.";
                header("Location: " . BASE_URL . "/funcionario/agenda");
                exit;
            }

            require_once __DIR__ . '/../Models/Disponibilidade.php';
            $dispModel = new Disponibilidade();
            
            $conflito = $dispModel->buscarBloqueiosDia($idFuncionario, $data);
            if (is_array($conflito)) {
                foreach ($conflito as $b) {
                    if (strtotime($inicio) < strtotime($b['hora_fim']) && strtotime($fim) > strtotime($b['hora_inicio'])) {
                        $_SESSION['flash_erro'] = "Este intervalo colide com outro bloqueio já existente.";
                        header("Location: " . BASE_URL . "/funcionario/agenda");
                        exit;
                    }
                }
            }

            $sucesso = $dispModel->cadastrarBloqueio($idFuncionario, $data, $inicio, $fim, $motivo);

            if ($sucesso) {
                $_SESSION['flash_sucesso'] = "Horário bloqueado com sucesso!";
            } else {
                $_SESSION['flash_erro'] = "Erro ao cadastrar bloqueio na agenda.";
            }

            header("Location: " . BASE_URL . "/funcionario/agenda");
            exit;
        }
    }

    public function excluirBloqueio()
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['is_funcionario'])) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idFuncionario = $this->getFuncionarioLogadoId();
            $idBloqueio = $_POST['id_bloqueio'] ?? '';

            if (empty($idBloqueio)) {
                $_SESSION['flash_erro'] = "ID do bloqueio não informado.";
                header("Location: " . BASE_URL . "/funcionario/agenda");
                exit;
            }

            require_once __DIR__ . '/../Models/Disponibilidade.php';
            $dispModel = new Disponibilidade();

            $sucesso = $dispModel->excluirBloqueio($idBloqueio, $idFuncionario);

            if ($sucesso) {
                $_SESSION['flash_sucesso'] = "Bloqueio de horário removido com sucesso!";
            } else {
                $_SESSION['flash_erro'] = "Falha ao remover o bloqueio de horário.";
            }

            header("Location: " . BASE_URL . "/funcionario/agenda");
            exit;
        }
    }
}