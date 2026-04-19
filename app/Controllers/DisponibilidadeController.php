<?php

require_once __DIR__ . '/../Services/DisponibilidadeService.php';

class DisponibilidadeController
{
    private $disponibilidadeService;

    public function __construct()
    {
        $this->disponibilidadeService = new DisponibilidadeService();
    }

    public function salvar()
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['is_funcionario'])) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $idFuncionario = $_SESSION['usuario_id']; 
            
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
            $idFuncionario = $_SESSION['usuario_id'];
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
            $idFuncionario = $_SESSION['usuario_id'];
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
     * ARQUITETURA: Guarda a grelha selecionada na sessão para esconder o ID da URL.
     */
    public function selecionarGradeVisao()
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Guarda a escolha na sessão do utilizador
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
            $idFuncionario = $_SESSION['usuario_id']; // Trava de segurança IDOR
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
        header('Content-Type: application/json');
        $dados = json_decode(file_get_contents("php://input"), true) ?? $_POST;

        $idFuncionario = isset($dados['id_funcionario']) ? (int)$dados['id_funcionario'] : 0;
        $dataDesejada = isset($dados['data']) ? trim($dados['data']) : '';
        $idServico = isset($dados['id_servico']) ? (int)$dados['id_servico'] : 0;

        if (empty($idFuncionario) || empty($dataDesejada) || empty($idServico)) {
            http_response_code(400); 
            echo json_encode(['sucesso' => false, 'mensagem' => 'Faltam parâmetros obrigatórios.']);
            exit;
        }

        try {
            $horariosDisponiveis = $this->disponibilidadeService->calcularHorariosLivres(
                $idFuncionario, $dataDesejada, $idServico
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
}