<?php

require_once __DIR__ . '/../Services/DisponibilidadeService.php';

class DisponibilidadeController
{
    private $disponibilidadeService;

    public function __construct()
    {
        $this->disponibilidadeService = new DisponibilidadeService();
    }

    // =========================================================================
    // ARQUITETURA: PADRÃO P.R.G. (POST / REDIRECT / GET)
    // Aplicado nos métodos salvar() e excluir(). Recebe o form, processa no Service,
    // salva as mensagens em $_SESSION e força um header("Location: ...")
    // =========================================================================

    public function salvar()
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        // Proteção da rota
        if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['is_funcionario'])) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $idFuncionario = $_SESSION['usuario_id']; 
            $idDisponibilidade = $_POST['id_disponibilidade'] ?? null;
            
            // Recebe o array multidimensional de dias enviado pela nova View
            $diasPost = $_POST['dias'] ?? []; 

            $diasConfigurados = [];
            
            // Filtra o formulário para processar apenas os dias que o utilizador marcou com a checkbox 'ativo'
            foreach ($diasPost as $dia => $dados) {
                if (isset($dados['ativo']) && $dados['ativo'] == '1') {
                    $diasConfigurados[$dia] = [
                        'inicio' => $dados['hora_inicio'] ?? '',
                        'fim' => $dados['hora_fim'] ?? '',
                        'int_inicio' => $dados['intervalo_inicio'] ?? '',
                        'int_fim' => $dados['intervalo_fim'] ?? '',
                        'status' => $dados['status'] ?? 'disponivel'
                    ];
                }
            }

            // O Service agora orquestra tudo sozinho num único método
            $resultado = $this->disponibilidadeService->salvarGrade(
                $idFuncionario, $idDisponibilidade, $diasConfigurados
            );

            // Flash Messages
            if ($resultado['sucesso']) {
                $_SESSION['msg_sucesso'] = $resultado['mensagem'];
            } else {
                $_SESSION['msg_erro'] = $resultado['mensagem'];
            }

            header("Location: " . BASE_URL . "/funcionario/disponibilidade");
            exit;
        }
    }

    public function excluir()
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idDisponibilidade = $_POST['id_disponibilidade'] ?? '';
            
            // O Service agora trata a exclusão de forma lógica (inativarDias)
            $resultado = $this->disponibilidadeService->excluirGrade($idDisponibilidade);

            if ($resultado['sucesso']) {
                $_SESSION['msg_sucesso'] = $resultado['mensagem'];
            } else {
                $_SESSION['msg_erro'] = $resultado['mensagem'];
            }

            header("Location: " . BASE_URL . "/funcionario/disponibilidade");
            exit;
        }
    }

    // =========================================================================
    // ARQUITETURA: COMPORTAMENTO DE API RESTful
    // Este método não foi alterado pois continua a funcionar perfeitamente 
    // com as novas buscas dinâmicas do Model e Service.
    // =========================================================================

    public function buscarHorariosLivres()
    {
        header('Content-Type: application/json');

        // Recebe Payload do JavaScript via Fetch API
        $dados = json_decode(file_get_contents("php://input"), true) ?? $_POST;

        $idFuncionario = isset($dados['id_funcionario']) ? (int)$dados['id_funcionario'] : 0;
        $dataDesejada = isset($dados['data']) ? trim($dados['data']) : '';
        $idServico = isset($dados['id_servico']) ? (int)$dados['id_servico'] : 0;

        // Validação de Bad Request
        if (empty($idFuncionario) || empty($dataDesejada) || empty($idServico)) {
            http_response_code(400); 
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Faltam parâmetros obrigatórios: funcionário, data ou serviço.'
            ]);
            exit;
        }

        try {
            $horariosDisponiveis = $this->disponibilidadeService->calcularHorariosLivres(
                $idFuncionario, $dataDesejada, $idServico
            );

            http_response_code(200); 
            echo json_encode([
                'sucesso' => true,
                'horarios' => $horariosDisponiveis
            ]);
            exit;

        } catch (Exception $e) {
            http_response_code(500); 
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Erro interno ao processar horários.'
            ]);
            error_log("Erro em buscarHorariosLivres: " . $e->getMessage());
            exit;
        }
    }
}
?>