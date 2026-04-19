<?php
date_default_timezone_set('America/Sao_Paulo');

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
