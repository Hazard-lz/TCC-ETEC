<?php

require_once __DIR__ . '/../Services/ClienteService.php';
require_once __DIR__ . '/../Models/Cliente.php';

class ClienteController {

    private $clienteService;
    private $clienteModel;

    public function __construct() {
        $this->clienteService = new ClienteService();
        $this->clienteModel = new Cliente();
    }

    public function salvar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/funcionario/clientes');
            exit;
        }

        $tipoAcesso = $_SESSION['usuario_tipo'] ?? 'comum';

        $id_cliente = $_POST['id_cliente'] ?? '';
        $id_usuario = $_POST['id_usuario'] ?? '';
        $nome = $_POST['nome'] ?? '';
        $telefone = $_POST['telefone'] ?? '';
        $nascimento = $_POST['nascimento'] ?? null;
        $observacoes = $_POST['observacoes'] ?? '';

        // SE FOR UM CADASTRO NOVO
        if (empty($id_cliente)) {
            // CORREÇÃO: Passando as observações para o service
            $resultado = $this->clienteService->registrarClienteRapido($nome, $telefone, $observacoes);
        } 
        // SE FOR EDIÇÃO
        else {
            if ($tipoAcesso === 'admin') {
                $resultado = $this->clienteService->atualizarDadosCliente($id_usuario, $id_cliente, $nome, $telefone, $nascimento, $observacoes);
            } else {
                $resultado = $this->clienteService->atualizarObservacoesCliente($id_cliente, $observacoes);
            }
        }

        $_SESSION[$resultado['sucesso'] ? 'flash_sucesso' : 'flash_erro'] = $resultado['mensagem'];
        header('Location: ' . BASE_URL . '/funcionario/clientes'); 
        exit;
    }

    public function alterarStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/funcionario/clientes');
            exit;
        }

        $tipoAcesso = $_SESSION['usuario_tipo'] ?? 'comum'; 
        
        if ($tipoAcesso !== 'admin') {
            $_SESSION['flash_erro'] = "Acesso negado. Apenas administradores podem inativar clientes.";
            header('Location: ' . BASE_URL . '/funcionario/clientes');
            exit;
        }

        $id_usuario = $_POST['cod_usuario'] ?? '';
        $status_atual = $_POST['status_atual'] ?? '';

        $novo_status = ($status_atual === 'ativo') ? 'inativo' : 'ativo';
        $resultado = $this->clienteService->alterarStatusCliente($id_usuario, $novo_status);

        $_SESSION[$resultado['sucesso'] ? 'flash_sucesso' : 'flash_erro'] = $resultado['mensagem'];
        header('Location: ' . BASE_URL . '/funcionario/clientes');
        exit;
    }

    public function home() {
        // 1. Verificação de sessão
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'comum') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // 2. Transforma o ID do Usuário em ID do Cliente
        $cliente = $this->clienteModel->buscarPorCodUsuario($_SESSION['usuario_id']);

        // 3. Busca o próximo agendamento (se existir)
        $agendamentoModel = new Agendamento();
        $proximoAgendamento = $agendamentoModel->buscarProximoAgendamentoCliente($cliente['id_cliente']);

        if ($proximoAgendamento) {
            $proximoAgendamento['data_display'] = Helpers::dataExtenso(
                $proximoAgendamento['data_agendamento'], 
                $proximoAgendamento['hora_inicio']
            );
        }

        // 4. Busca os Serviços Ativos para listar como "Populares"
        $servicoModel = new Servico();
        $todosServicos = $servicoModel->listarPorStatus('ativo');
        
        // Pega apenas os 3 primeiros para não poluir a página inicial
        $servicosPopulares = array_slice($todosServicos, 0, 3);

        // 5. Saudação Dinâmica baseada na hora atual
        $horaAtual = (int) date('H');
        if ($horaAtual >= 5 && $horaAtual < 12) {
            $saudacao = "Bom dia,";
        } elseif ($horaAtual >= 12 && $horaAtual < 18) {
            $saudacao = "Boa tarde,";
        } else {
            $saudacao = "Boa noite,";
        }

        $clienteNome = $_SESSION['usuario_nome'];

        // 6. Envia tudo para a View
        require_once __DIR__ . '/../../public/views/cliente/main.php';
    }
}