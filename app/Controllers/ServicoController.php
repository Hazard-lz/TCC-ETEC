<?php

require_once __DIR__ . '/../Services/ServicoService.php';
require_once __DIR__ . '/../Models/Servico.php';
require_once __DIR__ . '/../Services/FuncionarioService.php'; 

class ServicoController {

    private $servicoService;
    private $servicoModel;
    private $funcionarioService; // Adicionado

    public function __construct() {
        $this->servicoService = new ServicoService();
        $this->servicoModel = new Servico();
        $this->funcionarioService = new FuncionarioService(); 
    }

    /**
     * ==========================================
     * ÁREA DO ADMINISTRADOR
     * ==========================================
     */

    public function listarTodos() {
        $ativos = $this->servicoModel->listarPorStatus('ativo') ?? [];
        $inativos = $this->servicoModel->listarPorStatus('inativo') ?? [];
        
        $todos = array_merge($ativos, $inativos);
        
        echo json_encode([
            'status' => 'sucesso',
            'dados' => $todos
        ]);
    }

    public function cadastrar() {
        $dados = json_decode(file_get_contents("php://input"), true) ?? $_POST;

        $nome = $dados['nome_servico'] ?? '';
        $descricao = $dados['descricao'] ?? '';
        $preco = $dados['preco'] ?? '';
        $duracao = $dados['duracao'] ?? '';

        $resultado = $this->servicoService->registrarServico($nome, $descricao, $preco, $duracao);
        
        echo json_encode($resultado);
    }

    public function editar() {
        // Força o cabeçalho como JSON para evitar falhas de leitura no JavaScript
        header('Content-Type: application/json');
        
        $dados = json_decode(file_get_contents("php://input"), true) ?? $_POST;

        $id = $dados['id_servico'] ?? '';
        $nome = $dados['nome_servico'] ?? '';
        $descricao = $dados['descricao'] ?? '';
        $preco = $dados['preco'] ?? '';
        $duracao = $dados['duracao'] ?? '';
        $status = $dados['status'] ?? ''; // Pega o status do modal

        // 1. Atualiza os dados vitais do serviço
        $resultado = $this->servicoService->atualizarDadosServico($id, $nome, $descricao, $preco, $duracao);

        // 2. Se a edição de texto funcionou E o usuário mandou um status, atualiza o status também!
        if ($resultado['sucesso'] === true && !empty($status)) {
            $this->servicoService->alterarStatusServico($id, $status);
        }

        echo json_encode($resultado);
        exit; // Encerra o script imediatamente após imprimir o JSON (MUITO IMPORTANTE)
    }

    public function alterarStatus() {
        $dados = json_decode(file_get_contents("php://input"), true) ?? $_POST;

        $id = $dados['id_servico'] ?? '';
        $status = $dados['status'] ?? '';

        $resultado = $this->servicoService->alterarStatusServico($id, $status);
        
        echo json_encode($resultado);
    }

    /**
     * ==========================================
     * ÁREA DO FUNCIONÁRIO
     * ==========================================
     */

    public function listarAtivos() {
        $ativos = $this->servicoModel->listarPorStatus('ativo');
        
        echo json_encode([
            'status' => 'sucesso',
            'dados' => $ativos
        ]);
    }

    // Função finalizada para o funcionário vincular os serviços
    public function vincularServicos() {
        $dados = json_decode(file_get_contents("php://input"), true) ?? $_POST;
        
        $id_funcionario = $dados['id_funcionario'] ?? null;
        $servicos_selecionados = $dados['servicos'] ?? [];
        
        if (!$id_funcionario) {
            echo json_encode(['status' => 'erro', 'mensagem' => 'ID do funcionário não informado.']);
            return;
        }

        $resultado = $this->funcionarioService->atualizarServicosFuncionario($id_funcionario, $servicos_selecionados);
        
        echo json_encode($resultado);
    }
}
?>