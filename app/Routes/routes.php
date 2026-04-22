<?php
// =========================================================================
// ARQUIVO CENTRAL DE ROTAS — Todas as rotas do sistema estão aqui
// =========================================================================
// Métodos disponíveis:
//   $router->get('/uri', 'Controller@metodo')   → Rota GET com Controller
//   $router->post('/uri', 'Controller@metodo')  → Rota POST com Controller
//   $router->view('/uri', 'pasta/arquivo')      → Rota GET que inclui uma View diretamente
//   $router->get('/uri', function() { ... })    → Rota GET com lógica personalizada (Closure)
//   $router->post('/uri', function() { ... })   → Rota POST com lógica personalizada (Closure)
// =========================================================================


// ===========================================================
// ROTAS DE CLIENTES
// ===========================================================

$router->get('/', function () {
    // Se o usuário já logado for funcionário, redireciona direto para o dashboard
    if (isset($_SESSION['usuario_id']) && isset($_SESSION['is_funcionario']) && $_SESSION['is_funcionario'] === true) {
        header("Location: " . BASE_URL . "/funcionario/dashboard");
        exit;
    }
    $controller = new ClienteController();
    $controller->home();
});

$router->view('/perfil', 'cliente/perfil');

$router->post('/perfil/salvar/dados', 'ClienteController@salvarDadosPerfil');
$router->get('/perfil/salvar/dados', function () {
    header("Location: " . BASE_URL . "/perfil");
    exit;
});

$router->get('/agendar', 'AgendamentoController@carregarTelaCliente');
$router->post('/agendar', 'AgendamentoController@salvar');

$router->get('/historico', 'AgendamentoController@historicoCliente');


// ===========================================================
// ROTAS DE AUTENTICAÇÃO E REGISTO
// ===========================================================

$router->view('/cadastro', 'auth/cadastro');
$router->post('/cadastro/salvar', 'CadastroController@registrar');
$router->get('/cadastro/salvar', function () {
    header("Location: " . BASE_URL . "/cadastro");
    exit;
});

$router->view('/login', 'auth/login');
$router->post('/login/autenticar', 'AuthController@login');
$router->get('/login/autenticar', function () {
    header("Location: " . BASE_URL . "/login");
    exit;
});

$router->get('/login/sair', 'AuthController@logout');

$router->post('/auth/trocarSenha', 'AuthController@trocarSenha');
$router->get('/auth/trocarSenha', function () {
    header("Location: " . BASE_URL . "/perfil");
    exit;
});


// ===========================================================
// ROTAS: ESQUECI MINHA SENHA
// ===========================================================

$router->view('/recuperar-senha', 'auth/recuperarSenha');

$router->post('/auth/esqueciSenha', 'AuthController@esqueciSenha');
$router->get('/auth/esqueciSenha', function () {
    header("Location: " . BASE_URL . "/login");
    exit;
});

$router->view('/redefinir-senha', 'auth/redefinirSenha');

$router->post('/auth/redefinirSenha', 'AuthController@redefinirSenha');
$router->get('/auth/redefinirSenha', function () {
    header("Location: " . BASE_URL . "/login");
    exit;
});

$router->post('/auth/reenviar-codigo-recuperacao', 'AuthController@reenviarCodigoRecuperacao');
$router->get('/auth/reenviar-codigo-recuperacao', function () {
    header("Location: " . BASE_URL . "/login");
    exit;
});


// ===========================================================
// ROTAS EXCLUSIVAS DO ADMIN (GERÊNCIA)
// ===========================================================

$router->view('/admin/funcionarios', 'admin/funcionarios');

$router->post('/admin/funcionarios/salvar', 'FuncionarioController@salvar');
$router->get('/admin/funcionarios/salvar', function () {
    header("Location: " . BASE_URL . "/admin/funcionarios");
    exit;
});

$router->view('/admin/servicos', 'admin/servicos');

$router->post('/admin/servicos/salvar', function () {
    $controller = new ServicoController();
    $dados = json_decode(file_get_contents("php://input"), true) ?? $_POST;

    if (!empty($dados['id_servico'])) {
        $controller->editar();
    } else {
        $controller->cadastrar();
    }
});

$router->post('/admin/servicos/status', 'ServicoController@alterarStatus');

$router->post('/admin/funcionarios/status', 'FuncionarioController@alterarStatus');
$router->get('/admin/funcionarios/status', function () {
    header("Location: " . BASE_URL . "/admin/funcionarios");
    exit;
});

$router->post('/admin/funcionarios/reenviar-email', 'FuncionarioController@reenviarEmail');
$router->get('/admin/funcionarios/reenviar-email', function () {
    header("Location: " . BASE_URL . "/admin/funcionarios");
    exit;
});


// ===========================================================
// ROTAS DA EQUIPE (FUNCIONÁRIOS E ADMIN)
// ===========================================================

$router->get('/funcionario/dashboard', 'FuncionarioController@dashboard');

$router->get('/funcionario/agenda', 'AgendamentoController@agendaFuncionario');
$router->post('/funcionario/agenda', 'AgendamentoController@salvar');

$router->post('/funcionario/agenda/status', 'AgendamentoController@alterarStatus');

$router->view('/funcionario/clientes', 'funcionario/clientes');

$router->post('/cliente/salvar', 'ClienteController@salvar');
$router->get('/cliente/salvar', function () {
    header("Location: " . BASE_URL . "/funcionario/clientes");
    exit;
});

$router->post('/cliente/alterar-status', 'ClienteController@alterarStatus');
$router->get('/cliente/alterar-status', function () {
    header("Location: " . BASE_URL . "/funcionario/clientes");
    exit;
});

$router->get('/funcionario/perfil', 'FuncionarioController@editarPerfil');
$router->post('/funcionario/perfil/salvar', 'FuncionarioController@salvarPerfil');

$router->view('/funcionario/servicos', 'funcionario/servicos');


// ===========================================================
// ROTAS DE DISPONIBILIDADE
// ===========================================================

$router->view('/funcionario/disponibilidade', 'funcionario/disponibilidade');

$router->post('/api/horarios-livres', 'DisponibilidadeController@buscarHorariosLivres');

$router->get('/api/profissionais-por-servico', 'FuncionarioController@listarProfissionaisPorServicoApi');

$router->post('/funcionario/disponibilidade/salvar', 'DisponibilidadeController@salvar');
$router->get('/funcionario/disponibilidade/salvar', function () {
    header("Location: " . BASE_URL . "/funcionario/disponibilidade");
    exit;
});

$router->post('/funcionario/disponibilidade/selecionar', 'DisponibilidadeController@selecionarGradeVisao');
$router->get('/funcionario/disponibilidade/selecionar', function () {
    header("Location: " . BASE_URL . "/funcionario/disponibilidade");
    exit;
});

$router->post('/funcionario/disponibilidade/salvar', 'DisponibilidadeController@salvar');
$router->post('/funcionario/disponibilidade/salvar_antecedencia', 'DisponibilidadeController@salvarAntecedencia');
$router->post('/funcionario/disponibilidade/ativar', 'DisponibilidadeController@ativar');
$router->get('/funcionario/disponibilidade/ativar', function () {
    header("Location: " . BASE_URL . "/funcionario/disponibilidade");
    exit;
});

$router->post('/funcionario/disponibilidade/excluir', 'DisponibilidadeController@excluir');
$router->get('/funcionario/disponibilidade/excluir', function () {
    header("Location: " . BASE_URL . "/funcionario/disponibilidade");
    exit;
});

$router->post('/funcionario/servicos/salvar', function () {
    $funcionarioModel = new Funcionario();
    $dadosFunc = $funcionarioModel->buscarPorCodUsuario($_SESSION['usuario_id']);
    $servicosSelecionados = $_POST['servicos'] ?? [];

    $funcionarioModel->atualizarServicos($dadosFunc['id_funcionario'], $servicosSelecionados);

    $_SESSION['flash_sucesso'] = "Especialidades salvas com sucesso!";

    header("Location: " . BASE_URL . "/funcionario/servicos");
    exit;
});


// ===========================================================
// ROTAS DE SETUP DE FUNCIONÁRIO (VIA E-MAIL)
// ===========================================================

$router->get('/setup-funcionario', 'FuncionarioController@setupSenha');

$router->post('/setup-funcionario/salvar', 'FuncionarioController@finalizarSetupSenha');
$router->get('/setup-funcionario/salvar', function () {
    header("Location: " . BASE_URL . "/login");
    exit;
});


// ===========================================================
// ROTAS DE VERIFICAÇÃO DE E-MAIL
// ===========================================================

$router->view('/verificar-email', 'auth/verificar_email');

$router->post('/verificar-email/validar', 'AuthController@verificarCodigoOculto');
$router->get('/verificar-email/validar', function () {
    header("Location: " . BASE_URL . "/login");
    exit;
});

$router->post('/verificar-email/reenviar', 'AuthController@reenviarCodigo');
$router->get('/verificar-email/reenviar', function () {
    header("Location: " . BASE_URL . "/verificar-email");
    exit;
});
