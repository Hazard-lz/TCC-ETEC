<?php
date_default_timezone_set('America/Sao_Paulo');

// 1. Avisa o servidor para NÃO apagar os ficheiros físicos de sessão antes de 30 dias
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);

// 2. COOKIE DE SESSÃO COM LIFETIME DINÂMICO
// Lê um cookie auxiliar definido durante o login para saber o tipo de utilizador
// ANTES de abrir a sessão (pois session_set_cookie_params só funciona antes do session_start)
$tipoLogado = $_COOKIE['belezou_tipo'] ?? '';

if ($tipoLogado === 'func') {
    $cookieLifetime = 60 * 60 * 24 * 7;   // Funcionário: 7 dias
} elseif ($tipoLogado === 'cli') {
    $cookieLifetime = 60 * 60 * 24 * 30;  // Cliente: 30 dias
} else {
    $cookieLifetime = 0;                   // Visitante: morre ao fechar o navegador
}

// Detecta automaticamente se está rodando em HTTPS (Hostinger, Vercel, etc)
$isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

session_set_cookie_params([
    'lifetime' => $cookieLifetime,
    'path' => '/',
    'domain' => '',
    'secure' => $isHttps,        // Ativado automaticamente em HTTPS
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

// Inicia o buffer de saída para permitir a injeção global de metatags e favicon no <head>
ob_start();

// Gera o token CSRF logo na inicialização da sessão
require_once __DIR__ . '/app/Helpers/CsrfGuard.php';
CsrfGuard::gerarToken();

require_once __DIR__ . '/vendor/autoload.php';

// Detecta o BASE_URL dinamicamente (útil para raiz vs subpasta)
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
define('BASE_URL', rtrim($scriptDir, '/'));

// =========================================================================
// AUTO-CONCLUSÃO: Agendamentos "marcados" há mais de 7 dias viram "concluido"
// Roda no máximo 1x a cada 10 minutos para não pesar
// =========================================================================
if (!isset($_SESSION['ultima_autoconclusao']) || (time() - $_SESSION['ultima_autoconclusao']) > 600) {
    try {
        require_once __DIR__ . '/database/Conexao.php';
        $conn = Conexao::getConexao();
        $conn->exec("UPDATE agendamentos SET status = 'concluido' WHERE status = 'marcado' AND data_agendamento < DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
        $_SESSION['ultima_autoconclusao'] = time();
    } catch (Exception $e) {
        // Silencioso — não deve travar a navegação
    }
}

// =========================================================================
// 1. ARQUITETURA: AUTOLOADER (Carregamento Dinâmico de Classes)
// =========================================================================
spl_autoload_register(function ($nome_da_classe) {
    // Lista das pastas onde as classes ficam guardadas
    $pastas = [
        'app/Controllers/',
        'app/Models/',
        'app/Services/',
        'app/Helpers/',
        'app/Routes/Core/'
    ];

    foreach ($pastas as $pasta) {
        $arquivo = __DIR__ . '/' . $pasta . $nome_da_classe . '.php';
        if (file_exists($arquivo)) {
            require_once $arquivo;
            return;
        }
    }
});

// =========================================================================
// 2. PREPARAÇÃO DA URI
// =========================================================================
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (strpos($uri, BASE_URL) === 0) {
    $uri = substr($uri, strlen(BASE_URL));
}

if ($uri == '') {
    $uri = '/';
}

// =========================================================================
// 3. MIDDLEWARE DE PROTEÇÃO DE ROTAS (Níveis de Acesso)
// =========================================================================
Middleware::verificar($uri);

// =========================================================================
// 4. ROTEAMENTO CENTRALIZADO
// =========================================================================
$router = new Router();
require_once __DIR__ . '/app/Routes/routes.php';
$router->executar($uri);

// =========================================================================
// 5. INJEÇÃO GLOBAL DE ATIVOS (Favicon e Segurança)
// Captura o HTML gerado e garante que tags essenciais estejam presentes no <head>
// =========================================================================
$html = ob_get_clean();

if (strpos($html, '<head>') !== false) {
    $tagsParaInjetar = "";

    // Injeta o Favicon se não houver um definido na View
    if (strpos($html, 'rel="icon"') === false) {
        $tagsParaInjetar .= "\n    <link rel=\"icon\" type=\"image/png\" href=\"" . BASE_URL . "/public/resources/images/favicon.png\">";
    }

    // Injeta a Meta Tag de CSRF se não houver (essencial para segurança em requisições AJAX)
    if (strpos($html, 'name="csrf-token"') === false) {
        $tagsParaInjetar .= "\n    " . CsrfGuard::metaTag();
    }

    if (!empty($tagsParaInjetar)) {
        // Insere logo após a abertura da tag <head>
        $html = str_replace('<head>', '<head>' . $tagsParaInjetar, $html);
    }
}

echo $html;
