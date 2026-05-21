<?php
// O autoloader do index.php cuidaria disso, mas é bom garantir caso o arquivo seja chamado isoladamente.
require_once __DIR__ . '/../Services/UsuarioService.php';
require_once __DIR__ . '/../Models/Funcionario.php'; // Adicionado o Model de Funcionario

class AuthController
{

    private $usuarioService;
    private $funcionarioModel; // Nova variável

    public function __construct()
    {
        $this->usuarioService = new UsuarioService();
        $this->funcionarioModel = new Funcionario(); // Instanciando
    }

    public function login()
    {
        $email = trim($_POST['email'] ?? '');
        $senha = trim($_POST['senha'] ?? '');

        $resultado = $this->usuarioService->autenticar($email, $senha);

        if ($resultado['sucesso']) {
            $usuario = $resultado['dados_usuario'];

            // VERIFICA SE O USUÁRIO PERTENCE À EQUIPE DO SALÃO
            $isFuncionario = $this->funcionarioModel->buscarPorCodUsuario($usuario['id_usuario']);

            // Regenera o ID para evitar ataques de fixação
            session_regenerate_id(true);

            $_SESSION['usuario_id'] = $usuario['id_usuario'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_tipo'] = $usuario['tipo'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['usuario_telefone'] = $usuario['telefone'];

            if (in_array($usuario['tipo'], ['admin', 'subadmin']) || $isFuncionario) {
                $_SESSION['is_funcionario'] = true;
                $expira = time() + (60 * 60 * 24 * 7); // 7 dias

                // Cookie auxiliar para o index.php saber o tipo ANTES do session_start
                setcookie('belezou_tipo', 'func', [
                    'expires' => $expira,
                    'path' => '/',
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);
                // Força o cookie de sessão com a validade correta (7 dias)
                setcookie(session_name(), session_id(), [
                    'expires' => $expira,
                    'path' => '/',
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);

                header("Location: " . BASE_URL . "/funcionario/dashboard");
            } else {
                $expira = time() + (60 * 60 * 24 * 30); // 30 dias

                setcookie('belezou_tipo', 'cli', [
                    'expires' => $expira,
                    'path' => '/',
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);
                setcookie(session_name(), session_id(), [
                    'expires' => $expira,
                    'path' => '/',
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);

                header("Location: " . BASE_URL . "/");
            }
            exit();
        } else {
            // =======================================================================
            // ARQUITETURA UX: FLUXO AUTOMÁTICO DE VERIFICAÇÃO DE E-MAIL
            // =======================================================================
            if (isset($resultado['requer_verificacao']) && $resultado['requer_verificacao'] === true) {
                $emailNaoVerificado = $resultado['email'];
                $_SESSION['email_verificacao'] = $emailNaoVerificado;

                // Chamamos a função inteligente AQUI, protegida pelo POST
                // Ela verifica se já existe código, se não cria outro, e manda o e-mail.
                $resultadoReenvio = $this->usuarioService->reenviarCodigoVerificacao($emailNaoVerificado);

                // Guarda a mensagem de sucesso ("Reenviamos seu código..." ou "Um novo código...")
                $_SESSION['sucesso_verificacao'] = $resultadoReenvio['mensagem'];

                // Joga o usuário direto na tela do código!
                header("Location: " . BASE_URL . "/verificar-email");
                exit();
            }

            // Erros normais (senha errada, conta inativa, etc)
            $_SESSION['erro_login'] = $resultado['mensagem'];
            header("Location: " . BASE_URL . "/login");
            exit();
        }
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // LIMPEZA ONESIGNAL: Remove o ID do dispositivo do banco ao sair usando o Service (Arquitetura Limpa)
        // DEVE SER FEITO ANTES de limpar a variável $_SESSION
        if (isset($_SESSION['usuario_id'])) {
            $this->usuarioService->removerDispositivoOneSignal($_SESSION['usuario_id']);
        }

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Limpa o cookie auxiliar de tipo de usuário
        setcookie('belezou_tipo', '', ['expires' => time() - 42000, 'path' => '/']);

        session_destroy();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_GET['motivo']) && $_GET['motivo'] === 'inatividade') {
            $_SESSION['erro_login'] = "Sua sessão expirou por inatividade. Faça login novamente.";
        } else {
            $_SESSION['sucesso_login'] = "Você saiu com sucesso.";
        }

        header("Location: " . BASE_URL . "/login");
        exit();
    }

    public function verificarCodigoOculto()
    {
        $codigo = trim($_POST['codigo'] ?? '');
        $email = $_SESSION['email_verificacao'] ?? '';

        if (empty($email)) {
            $_SESSION['erro_login'] = "Sessão expirada. Tente fazer login para verificar o e-mail novamente.";
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        $resultado = $this->usuarioService->validarCodigo($email, $codigo);

        if ($resultado['sucesso']) {
            unset($_SESSION['email_verificacao']); // Limpa a sessão para não travar o sistema
            $_SESSION['msg_sucesso'] = $resultado['mensagem'];
            header("Location: " . BASE_URL . "/login");
            exit;
        } else {
            $_SESSION['erro_verificacao'] = $resultado['mensagem'];
            header("Location: " . BASE_URL . "/verificar-email");
            exit;
        }
    }

    public function trocarSenha()
    {
        // Medida de segurança: Só logados podem acessar essa rota
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        $idUsuario = $_SESSION['usuario_id'];
        $senhaAtual = trim($_POST['senha_atual'] ?? '');
        $novaSenha = trim($_POST['nova_senha'] ?? '');
        $confirmaSenha = trim($_POST['confirma_senha'] ?? '');

        if (empty($senhaAtual) || empty($novaSenha) || empty($confirmaSenha)) {
            $_SESSION['erro_senha'] = "Preencha todos os campos.";
            header("Location: " . BASE_URL . "/perfil");
            exit;
        }

        // Chama a inteligência do serviço
        $resultado = $this->usuarioService->trocarSenhaConhecida($idUsuario, $senhaAtual, $novaSenha, $confirmaSenha);

        if ($resultado['sucesso']) {
            $_SESSION['sucesso_senha'] = $resultado['mensagem'];
        } else {
            $_SESSION['erro_senha'] = $resultado['mensagem'];
        }

        // Devolve para o perfil (onde estão os alertas de erro/sucesso)
        header("Location: " . BASE_URL . "/perfil");
        exit;
    }

    public function esqueciSenha()
    {
        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            $_SESSION['erro_recuperacao'] = "Por favor, digite seu e-mail.";
            header("Location: " . BASE_URL . "/recuperar-senha");
            exit;
        }

        $resultado = $this->usuarioService->solicitarRecuperacaoSenha($email);

        $_SESSION['sucesso_recuperacao'] = $resultado['mensagem'];
        $_SESSION['email_recuperacao_pendente'] = $email; // Agora vai salvar corretamente
        header("Location: " . BASE_URL . "/redefinir-senha");
        exit;
    }

    public function redefinirSenha()
    {
        $email = $_SESSION['email_recuperacao_pendente'] ?? '';
        $codigo = trim($_POST['codigo'] ?? '');
        $novaSenha = trim($_POST['nova_senha'] ?? '');
        $confirmaSenha = trim($_POST['confirma_senha'] ?? '');

        // 2. VERIFICA SE A SESSÃO NÃO EXPIROU ANTES DE IR PARA O BANCO
        if (empty($email)) {
            $_SESSION['erro_recuperacao'] = "Sessão expirada. Por favor, solicite a recuperação de senha novamente.";
            header("Location: " . BASE_URL . "/recuperar-senha");
            exit;
        }

        if (empty($codigo) || empty($novaSenha)) {
            $_SESSION['erro_redefinicao'] = "Todos os campos são obrigatórios.";
            header("Location: " . BASE_URL . "/redefinir-senha");
            exit;
        }

        $resultado = $this->usuarioService->redefinirSenha($email, $codigo, $novaSenha, $confirmaSenha);

        if ($resultado['sucesso']) {
            unset($_SESSION['email_recuperacao_pendente']);
            $_SESSION['sucesso_login'] = "Senha redefinida com sucesso! Faça login.";
            header("Location: " . BASE_URL . "/login");
        } else {
            $_SESSION['erro_redefinicao'] = $resultado['mensagem'];
            header("Location: " . BASE_URL . "/redefinir-senha");
        }
        exit;
    }

    public function reenviarCodigo()
    {
        $email = $_SESSION['email_verificacao'] ?? '';

        if (empty($email)) {
            $_SESSION['erro_login'] = "Sessão expirada. Faça login novamente para gerar um novo código.";
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        $resultado = $this->usuarioService->reenviarCodigoVerificacao($email);

        if ($resultado['sucesso']) {
            $_SESSION['sucesso_verificacao'] = $resultado['mensagem'];
        } else {
            $_SESSION['erro_verificacao'] = $resultado['mensagem'];
        }

        header("Location: " . BASE_URL . "/verificar-email");
        exit;
    }

    public function reenviarCodigoRecuperacao()
    {
        // Pega o e-mail de quem pediu a recuperação lá na primeira tela
        $email = $_SESSION['email_recuperacao_pendente'] ?? '';

        if (empty($email)) {
            $_SESSION['erro_recuperacao'] = "Sessão expirada. Por favor, volte e digite seu e-mail novamente.";
            header("Location: " . BASE_URL . "/recuperar-senha");
            exit;
        }

        // Reutilizamos o seu serviço de recuperação que já gera código e envia o e-mail!
        $resultado = $this->usuarioService->solicitarRecuperacaoSenha($email);

        // Avisa a tela que deu certo
        $_SESSION['sucesso_recuperacao'] = "Um novo código de recuperação foi enviado para o seu e-mail.";

        header("Location: " . BASE_URL . "/redefinir-senha");
        exit;
    }
}