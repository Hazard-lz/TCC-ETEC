<?php
$qntd_dias = 30;
// 30 dias em segundos
$segundosLimite = 60 * 60 * 24 * $qntd_dias;

// 1. Avisa o servidor para NÃO apagar os ficheiros físicos de sessão antes de 30 dias
ini_set('session.gc_maxlifetime', $segundosLimite);

// 2. BLINDAGEM DO COOKIE DE SESSÃO (Padrão: Morre ao fechar o navegador)
session_set_cookie_params([
    'lifetime' => 0,             // 0 = Seguro para a equipa (morre ao fechar a aba)
    'path' => '/',
    'domain' => '',
    'secure' => false,           // Mude para 'true' quando tiver HTTPS em produção
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

require_once __DIR__ . '/vendor/autoload.php';

define('BASE_URL', '/TCC-ETEC');

// =========================================================================
// 1. ARQUITETURA: AUTOLOADER (Carregamento Dinâmico de Classes)
// =========================================================================
spl_autoload_register(function ($nome_da_classe) {
    // Lista das pastas onde as classes ficam guardadas
    $pastas = [
        'app/Controllers/',
        'app/Models/',
        'app/Services/'
    ];

    foreach ($pastas as $pasta) {
        $arquivo = __DIR__ . '/' . $pasta . $nome_da_classe . '.php';
        if (file_exists($arquivo)) {
            require_once $arquivo;
            return;
        }
    }
});


$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (strpos($uri, BASE_URL) === 0) {
    $uri = substr($uri, strlen(BASE_URL));
}

if ($uri == '') {
    $uri = '/';
}

// =========================================================================
// 2. ARQUITETURA: MIDDLEWARE DE PROTEÇÃO DE ROTAS (Níveis de Acesso)
// =========================================================================

// --- BLOQUEIO DA PASTA ADMIN (SÓ ADMIN ENTRA) ---
if (strpos($uri, '/admin') === 0) {
    if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
        $_SESSION['erro_login'] = "Acesso restrito a administradores.";
        header("Location: " . BASE_URL . "/login");
        exit;
    }
    
    // Controle de inatividade de 30 minutos
    if (isset($_SESSION['ultimo_acesso']) && (time() - $_SESSION['ultimo_acesso'] > 1800)) {
        header("Location: " . BASE_URL . "/login/sair?motivo=inatividade"); 
        exit;
    }
    $_SESSION['ultimo_acesso'] = time();
}

// --- BLOQUEIO DA PASTA FUNCIONÁRIO (EQUIPA TODA ENTRA) ---
if (strpos($uri, '/funcionario') === 0) {
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['is_funcionario'])) {
        $_SESSION['erro_login'] = "Acesso restrito à equipe do salão.";
        header("Location: " . BASE_URL . "/login");
        exit;
    }
    
    // Controle de inatividade de 30 minutos
    if (isset($_SESSION['ultimo_acesso']) && (time() - $_SESSION['ultimo_acesso'] > 1800)) {
        header("Location: " . BASE_URL . "/login/sair?motivo=inatividade"); 
        exit;
    }
    $_SESSION['ultimo_acesso'] = time();
}

// =========================================================================
// 3. ROTEAMENTO CENTRALIZADO (O SWITCH)
// =========================================================================
switch ($uri) {

    // ------------------------------------------
    // ROTAS DE CLIENTES
    // ------------------------------------------
    case '/':
        // 1. Verifica se existe um utilizador logado e se ele possui a flag de funcionário/admin na sessão.
        // O uso do isset() previne erros de "Undefined array key" caso o visitante não esteja logado.
        if (isset($_SESSION['usuario_id']) && isset($_SESSION['is_funcionario']) && $_SESSION['is_funcionario'] === true) {
            
            // 2. Sendo da equipe, forçamos o redirecionamento para o dashboard correto.
            header("Location: " . BASE_URL . "/funcionario/dashboard");
            
            // Isso garante que o servidor pare de processar o resto do ficheiro index.php imediatamente,
            exit; 
        }

        // 4. Se o fluxo passou pelo 'if' sem entrar, significa que é um visitante anônimo ou um cliente comum.
        // Portanto, carregamos a página principal pública.
        include __DIR__ . '/public/views/cliente/main.php';
        break;
    case '/perfil':
        include __DIR__ . '/public/views/cliente/perfil.php';
        break;
    case '/historico':
        include __DIR__ . '/public/views/cliente/historico.php';
        break;
    case '/agendar':
        include __DIR__ . '/public/views/cliente/agendar.php';
        break;

    // ------------------------------------------
    // ROTAS DE AUTENTICAÇÃO E REGISTO
    // ------------------------------------------
    case '/cadastro':
        include __DIR__ . '/public/views/auth/cadastro.php'; 
        break;
    case '/cadastro/salvar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller = new CadastroController();
            $controller->registrar();
        } else {
            header("Location: " . BASE_URL . "/cadastro");
            exit;
        }
        break;
    case '/login':
        include __DIR__ . '/public/views/auth/login.php';
        break;
    case '/login/autenticar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authController = new AuthController();
            $authController->login(); 
        } else {
            header("Location: " . BASE_URL . "/login");
            exit;
        }
        break;
    case '/login/sair':
        $authController = new AuthController();
        $authController->logout();
        break;
   // ROTA PARA O USUÁRIO LOGADO ALTERAR A SENHA NO PERFIL
    case '/auth/trocarSenha':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authController = new AuthController();
            $authController->trocarSenha();
        } else {
            // Se alguém tentar acessar a URL diretamente sem formulário, manda pro perfil
            header("Location: " . BASE_URL . "/perfil");
            exit;
        }
        break;

    // ------------------------------------------
    // ROTAS: ESQUECI MINHA SENHA
    // ------------------------------------------
    
    // 1. Tela para o usuário digitar o e-mail
    case '/recuperar-senha':
        include __DIR__ . '/public/views/auth/recuperarSenha.php'; 
        break;

    // 2. Ação que envia o código para o e-mail
    case '/auth/esqueciSenha':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authController = new AuthController();
            $authController->esqueciSenha();
        } else {
            header("Location: " . BASE_URL . "/login");
            exit;
        }
        break;

    // 3. Tela para o usuário digitar o código que recebeu e a nova senha
    case '/redefinir-senha':
        include __DIR__ . '/public/views/auth/redefinirSenha.php'; 
        break;

    // 4. Ação que valida o código e salva a nova senha no banco
    case '/auth/redefinirSenha':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authController = new AuthController();
            $authController->redefinirSenha();
        } else {
            header("Location: " . BASE_URL . "/login");
            exit;
        }
        break;
    // ------------------------------------------
    // ROTAS EXCLUSIVAS DO ADMIN (GERÊNCIA)
    // ------------------------------------------
    case '/admin/funcionarios':
        include __DIR__ . '/public/views/admin/funcionarios.php';
        break;
    case '/admin/servicos':
        include __DIR__ . '/public/views/admin/servicos.php';
        break;
    case '/admin/servicos/salvar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller = new ServicoController();
            $dados = json_decode(file_get_contents("php://input"), true) ?? $_POST;
            
            // Se vier um ID, é edição. Se não vier, é cadastro novo.
            if (!empty($dados['id_servico'])) {
                $controller->editar();
            } else {
                $controller->cadastrar();
            }
            exit;
        }   
        break;

    // Rota para ativar/inativar o serviço
    case '/admin/servicos/status':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller = new ServicoController();
            $controller->alterarStatus();
        }
        break;
    // ------------------------------------------
    // ROTAS DA EQUIPA (FUNCIONÁRIOS E ADMIN)
    // ------------------------------------------
    case '/funcionario/dashboard':
        include __DIR__ . '/public/views/funcionario/dashboard.php';
        break;
    case '/funcionario/agendamentos':
        include __DIR__ . '/public/views/funcionario/agendamentos.php';
        break;
    case '/funcionario/clientes':
        include __DIR__ . '/public/views/funcionario/clientes.php';
        break;
    case '/funcionario/servicos':
        include __DIR__ . '/public/views/funcionario/servicos.php';
        break;
    // ------------------------------------------
    // ROTAS DE DISPONIBILIDADE
    // ------------------------------------------
    case '/funcionario/disponibilidade':
        include __DIR__ . '/public/views/funcionario/disponibilidade.php';
        break;

    case '/funcionario/disponibilidade/salvar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Para não esquecer: O arquivo do Controller já deve ser carregado pelo seu spl_autoload_register
            $controller = new DisponibilidadeController();
            $controller->salvar();
        } else {
            header("Location: " . BASE_URL . "/funcionario/disponibilidade");
            exit;
        }
        break;

    case '/funcionario/disponibilidade/excluir':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller = new DisponibilidadeController();
            $controller->excluir();
        } else {
            header("Location: " . BASE_URL . "/funcionario/disponibilidade");
            exit;
        }
        break; 
    // Rota lógica para salvar as especialidades do funcionário
    case '/funcionario/servicos/salvar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $funcionarioModel = new Funcionario();
            $dadosFunc = $funcionarioModel->buscarPorCodUsuario($_SESSION['usuario_id']);
            $servicosSelecionados = $_POST['servicos'] ?? []; 
            
            $funcionarioModel->atualizarServicos($dadosFunc['id_funcionario'], $servicosSelecionados);
            
            header("Location: " . BASE_URL . "/funcionario/servicos");
            exit;
        }
        break;
    
    // --- ROTAS DE VERIFICAÇÃO DE E-MAIL ---
    case '/verificar-email':
        include __DIR__ . '/public/views/auth/verificar_email.php';
        break;
    case '/verificar-email/validar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authController = new AuthController();
            $authController->verificarCodigoOculto();
        } else {
            header("Location: " . BASE_URL . "/login");
            exit;
        }
        break;
    // ------------------------------------------
    // ROTA PADRÃO (ERRO 404)
    // ------------------------------------------
    default:
        http_response_code(404);
        echo "<div style='text-align: center; margin-top: 50px; font-family: sans-serif;'>";
        echo "<h1>Página não encontrada!</h1>";
        echo "<p>A rota (<strong>" . htmlspecialchars($uri) . "</strong>) não existe no sistema.</p>";
        echo "<a href='" . BASE_URL . "/'>Voltar para a página inicial</a>";
        echo "</div>";
        break;
}