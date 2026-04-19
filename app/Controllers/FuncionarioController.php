<?php

require_once __DIR__ . '/../Services/FuncionarioService.php';
require_once __DIR__ . '/../Models/Funcionario.php';
require_once __DIR__ . '/../Models/Usuario.php'; // Adicionado para lidar com o nível de acesso

class FuncionarioController {

    private $funcionarioService;
    private $funcionarioModel;
    private $usuarioModel;

    public function __construct() {
        $this->funcionarioService = new FuncionarioService();
        $this->funcionarioModel = new Funcionario();
        $this->usuarioModel = new Usuario(); 
    }

    /**
     * Rota principal que recebe o POST do formulário HTML
     * Action: /funcionario/salvar
     */
    public function salvar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['flash_erro'] = "Método inválido.";
            header('Location: ' . BASE_URL . '/admin/funcionarios');
            exit;
        }

        $id_funcionario = $_POST['id_funcionario'] ?? '';
        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $telefone = $_POST['telefone'] ?? null;
        $especialidade = $_POST['especialidade'] ?? '';
        $salario = $_POST['salario'] ?? null;
        $tipo = $_POST['tipo'] ?? 'comum'; 

        if (empty($id_funcionario)) {
            // CADASTRAR NOVO (Sem a senha)
            $resultado = $this->funcionarioService->registrarFuncionario(
                $nome, $email, $telefone, $especialidade, $salario, $tipo
            );
        } else {
            // EDITAR EXISTENTE
            $funcionarioAtual = $this->funcionarioModel->buscarPorId($id_funcionario);
            if (!$funcionarioAtual) {
                $_SESSION['flash_erro'] = "Funcionário não encontrado.";
                header('Location: ' . BASE_URL . '/admin/funcionarios');
                exit;
            }

            $id_usuario = $funcionarioAtual['cod_usuario'];
            $resultado = $this->funcionarioService->atualizarDadosFuncionario(
                $id_usuario, $id_funcionario, $nome, $telefone, $especialidade, $salario
            );

            if ($resultado['sucesso']) {
                $this->usuarioModel->atualizarTipo($id_usuario, $tipo);
            }
        }

        // REDIRECIONAMENTO LIMPO (Usa a sessão em vez de ?sucesso=1 na URL)
        if ($resultado['sucesso']) {
            $_SESSION['flash_sucesso'] = $resultado['mensagem'] ?? "Operação realizada com sucesso.";
        } else {
            $_SESSION['flash_erro'] = $resultado['mensagem'];
        }
        header('Location: ' . BASE_URL . '/admin/funcionarios');
        exit;
    }

    /**
     * Action: /setup-funcionario (GET)
     * Responsabilidade: Renderizar o HTML da tela de criação de senha.
     */
    public function setupSenha() {
        // Recebe os parâmetros da URL. Usamos o operador ?? para evitar erros de 'undefined index'.
        $token = $_GET['token'] ?? '';
        $email = $_GET['email'] ?? '';

        // Validação primária da requisição
        if (empty($token) || empty($email)) {
            $_SESSION['erro_login'] = "Link de configuração inválido ou incompleto.";
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // Se estiver tudo certo, carrega o arquivo visual (View).
        // OBS: Você precisará criar este arquivo HTML/PHP na pasta views/auth
        include __DIR__ . '/../../public/views/auth/setup_funcionario.php';
    }

    /**
     * Action: /setup-funcionario/salvar (POST)
     * Responsabilidade: Receber os dados do form, chamar a lógica de negócio e redirecionar.
     */
    public function finalizarSetupSenha() {
        // Coleta os dados que vieram do formulário HTML
        $email = $_POST['email'] ?? '';
        $token = $_POST['token'] ?? '';
        $senha = $_POST['nova_senha'] ?? '';
        $confirma_senha = $_POST['confirma_senha'] ?? '';

        // Validação para evitar processamento inútil no banco
        if (empty($senha) || empty($confirma_senha)) {
            $_SESSION['flash_erro'] = "Preencha todos os campos.";
            // Retorna o usuário para o formulário mantendo o token na URL
            header('Location: ' . BASE_URL . "/setup-funcionario?token={$token}&email=" . urlencode($email));
            exit;
        }

        // Chama o UsuarioService, abstraindo a complexidade de regras de negócio
        // Para isso, precisamos instanciar o UsuarioService no construtor do FuncionarioController ou aqui
        require_once __DIR__ . '/../Services/UsuarioService.php';
        $usuarioService = new UsuarioService();
        
        $resultado = $usuarioService->finalizarCadastroEquipe($email, $token, $senha, $confirma_senha);

        // Decisão de Redirecionamento com base no retorno do Service
        if ($resultado['sucesso']) {
            // Usa a variável de sessão correta que a tela de login espera para exibir sucesso verde
            $_SESSION['sucesso_login'] = "Cadastro realizado com sucesso! Faça login para acessar o sistema.";
            header('Location: ' . BASE_URL . '/login');
            exit;
        } else {
            $_SESSION['flash_erro'] = $resultado['mensagem'];
            header('Location: ' . BASE_URL . "/setup-funcionario?token={$token}&email=" . urlencode($email));
            exit;
        }
    }

    /**
     * Action: /admin/funcionarios/status (POST)
     * Responsabilidade: Receber a requisição da tabela de listagem e alternar o status.
     */
    public function alterarStatus() {
        // Validação de segurança básica para garantir que é um POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['flash_erro'] = "Método inválido.";
            header('Location: ' . BASE_URL . '/admin/funcionarios');
            exit;
        }

        // O HTML deve enviar via POST o ID do Usuário e o Status Atual
        $id_usuario = $_POST['cod_usuario'] ?? '';
        $status_atual = $_POST['status_atual'] ?? '';

        if (empty($id_usuario) || empty($status_atual)) {
            $_SESSION['flash_erro'] = "Dados insuficientes para alterar o status.";
            header('Location: ' . BASE_URL . '/admin/funcionarios');
            exit;
        }

        // ARQUITETURA (Ternário): Se for 'ativo' vira 'inativo', se for 'inativo' vira 'ativo'
        $novo_status = ($status_atual === 'ativo') ? 'inativo' : 'ativo';

        // Aciona o serviço criado no passo anterior
        $resultado = $this->funcionarioService->alterarStatusFuncionario($id_usuario, $novo_status);

        // Feedback visual usando sessões flash
        if ($resultado['sucesso']) {
            $_SESSION['flash_sucesso'] = $resultado['mensagem'];
        } else {
            $_SESSION['flash_erro'] = $resultado['mensagem'];
        }

        header('Location: ' . BASE_URL . '/admin/funcionarios');
        exit;
    }

    public function reenviarEmail() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/funcionarios');
            exit;
        }

        $id_usuario = $_POST['cod_usuario'] ?? '';
        
        require_once __DIR__ . '/../Services/UsuarioService.php';
        $usuarioService = new UsuarioService();
        $resultado = $usuarioService->reenviarEmailSetupFuncionario($id_usuario);

        if ($resultado['sucesso']) {
            $_SESSION['flash_sucesso'] = $resultado['mensagem'];
        } else {
            $_SESSION['flash_erro'] = $resultado['mensagem'];
        }
        header('Location: ' . BASE_URL . '/admin/funcionarios');
        exit;
    }

    public function listarProfissionaisPorServicoApi() {
        header('Content-Type: application/json');
        $id_servico = $_GET['id_servico'] ?? '';

        if (empty($id_servico)) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Serviço não informado.']);
            exit;
        }

        $profissionais = $this->funcionarioModel->buscarPorServico($id_servico);
        echo json_encode(['sucesso' => true, 'profissionais' => $profissionais]);
        exit;
    }

    public function dashboard() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        
        $id_usuario = $_SESSION['usuario_id'];
        $funcionario = $this->funcionarioModel->buscarPorCodUsuario($id_usuario);
        
        if (!$funcionario) {
            $_SESSION['flash_erro'] = "Perfil de funcionário não encontrado.";
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        require_once __DIR__ . '/../Models/Agendamento.php';
        require_once __DIR__ . '/../Models/Cliente.php';
        
        $agendamentoModel = new Agendamento();
        $clienteModel = new Cliente();

        $idFuncionario = $funcionario['id_funcionario'];
        $isAdmin = ($_SESSION['usuario_tipo'] === 'admin');

        // 1. Busca as Métricas para os Cards
        // Se for admin, busca do salão todo. Se for funcionário, busca apenas os próprios.
        if ($isAdmin) {
            $totalAgendamentosHoje = $agendamentoModel->contarAgendamentosHojeGeral();
            $faturamentoMes = $agendamentoModel->calcularFaturamentoMesGeral();
            $proximosAgendamentos = $agendamentoModel->listarProximosAgendamentosResumoGeral(5);
        } else {
            $totalAgendamentosHoje = $agendamentoModel->contarAgendamentosHoje($idFuncionario);
            $faturamentoMes = $agendamentoModel->calcularFaturamentoMes($idFuncionario);
            $proximosAgendamentos = $agendamentoModel->listarProximosAgendamentosResumo($idFuncionario, 5);
        }
        
        $totalClientes = count($clienteModel->listarTodos()); 

        // Formatação do Faturamento para BRL
        $faturamentoFormatado = number_format($faturamentoMes, 2, ',', '.');

        // Saudação baseada no tempo
        date_default_timezone_set('America/Sao_Paulo');
        $horaAtual = (int) date('H');
        if ($horaAtual >= 5 && $horaAtual < 12) {
            $saudacao = "Bom dia";
        } elseif ($horaAtual >= 12 && $horaAtual < 18) {
            $saudacao = "Boa tarde";
        } else {
            $saudacao = "Boa noite";
        }

        require_once __DIR__ . '/../../public/views/funcionario/dashboard.php';
    }

    public function editarPerfil() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $idLogado = $_SESSION['usuario_id'];
        
        $funcionario = $this->funcionarioModel->buscarPorCodUsuario($idLogado);
        $usuario = $this->usuarioModel->buscarPorId($idLogado);

        if (!$funcionario || !$usuario) {
            $_SESSION['flash_erro'] = "Perfil de funcionário não encontrado.";
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // Mescla os dados do usuário com a estrutura do funcionário para a view preencher os campos corretamente
        $funcionario['email'] = $usuario['email'] ?? '';
        $funcionario['nome'] = $usuario['nome'] ?? '';
        $funcionario['telefone'] = $usuario['telefone'] ?? '';

        require_once __DIR__ . '/../../public/views/funcionario/perfil.php';
    }

    public function salvarPerfil() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/funcionario/perfil');
            exit;
        }

        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $idLogado = $_SESSION['usuario_id'];
        
        $funcionario = $this->funcionarioModel->buscarPorCodUsuario($idLogado);
        
        if (!$funcionario) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $id_funcionario = $funcionario['id_funcionario'];
        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $telefone = $_POST['telefone'] ?? null;
        $especialidade = $_POST['especialidade'] ?? '';

        // Preserva o salário original porque um funcionário comum não deve alterar o próprio salário
        $salario = $funcionario['salario_base'] ?? 0;

        $resultado = $this->funcionarioService->atualizarDadosFuncionario(
            $idLogado, $id_funcionario, $nome, $telefone, $especialidade, $salario
        );

        if ($resultado['sucesso']) {
            $_SESSION['flash_sucesso'] = "Seus dados foram atualizados com sucesso!";
            // Atualiza sessão para o menu global
            $_SESSION['usuario_nome'] = $nome;
        } else {
            $_SESSION['flash_erro'] = $resultado['mensagem'] ?? "Falha ao editar perfil.";
        }

        header('Location: ' . BASE_URL . '/funcionario/perfil');
        exit;
    }
}