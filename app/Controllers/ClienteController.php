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

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
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

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
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
}
?>