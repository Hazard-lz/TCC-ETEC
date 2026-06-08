<?php
// =========================================================================
// FRONT CONTROLLER CENTRAL (index.php)
// =========================================================================
// Este arquivo é o ponto de entrada único para todas as requisições HTTP do
// sistema. Ele gerencia a inicialização, configurações de sessão, segurança
// CSRF, injeção de estilo White-Label, e despacha a rota correta.
// =========================================================================

// Define o fuso horário padrão do sistema para garantir sincronia nas datas e horários dos agendamentos
date_default_timezone_set('America/Sao_Paulo');

// 1. Configura o tempo máximo de vida da sessão no servidor para 30 dias (em segundos)
// Isso avisa o PHP para não expirar ou apagar fisicamente a sessão antes do tempo.
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);

// 2. CONFIGURAÇÃO DE COOKIE DE SESSÃO COM LIFETIME DINÂMICO
// Lê o cookie auxiliar setado durante o login para determinar o tipo de usuário conectado
// Isso precisa ser executado ANTES de iniciar a sessão com session_start()
$tipoLogado = $_COOKIE['belezou_tipo'] ?? '';

if ($tipoLogado === 'func') {
    $cookieLifetime = 60 * 60 * 24 * 7;   // Funcionário: Sessão dura 7 dias
} elseif ($tipoLogado === 'cli') {
    $cookieLifetime = 60 * 60 * 24 * 30;  // Cliente: Sessão dura 30 dias
} else {
    $cookieLifetime = 0;                   // Visitante anônimo: Sessão expira ao fechar o navegador
}

// Detecta automaticamente se a requisição está trafegando sobre conexão segura HTTPS
$isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

// Define parâmetros de segurança nos cookies de sessão (impossibilita leitura por Javascript de terceiros)
session_set_cookie_params([
    'lifetime' => $cookieLifetime,
    'path' => '/',
    'domain' => '',
    'secure' => $isHttps,        // Habilitado automaticamente quando em HTTPS
    'httponly' => true,          // Proteção contra ataques XSS (impede acesso do document.cookie no JS)
    'samesite' => 'Strict'       // Impede envio de cookies em requisições de sites externos (proteção CSRF)
]);

// Inicia a sessão PHP para armazenar estados do usuário conectado
session_start();

// Inicia o buffer de saída (output buffering). Isso permite que o PHP capture toda a resposta
// HTML gerada pelo sistema para poder injetar tags globais de estilo e segurança no final.
ob_start();

// Carrega o mecanismo de proteção contra falsificação de requisições Cross-Site (CSRF)
require_once __DIR__ . '/app/Helpers/CsrfGuard.php';
CsrfGuard::gerarToken(); // Garante a criação de um token único de sessão

// Carrega dependências externas instaladas via Composer (como PHPMailer)
require_once __DIR__ . '/vendor/autoload.php';

// Detecta o BASE_URL da aplicação dinamicamente
// Útil para garantir que os caminhos funcionem tanto rodando na raiz do domínio quanto em subpastas (ex: localhost/TCC-ETEC)
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
define('BASE_URL', rtrim($scriptDir, '/'));

// =========================================================================
// AUTO-CONCLUSÃO AUTOMÁTICA DE AGENDAMENTOS ANTIGOS
// =========================================================================
// Se um agendamento foi "marcado" e já se passaram mais de 7 dias da sua data, 
// o sistema assume que o serviço foi executado e altera o status para "concluido".
// Roda no máximo uma vez a cada 10 minutos para preservar a performance.
// =========================================================================
if (!isset($_SESSION['ultima_autoconclusao']) || (time() - $_SESSION['ultima_autoconclusao']) > 600) {
    try {
        require_once __DIR__ . '/database/Conexao.php';
        $conn = Conexao::getConexao();
        $conn->exec("UPDATE agendamentos SET status = 'concluido' WHERE status = 'marcado' AND data_agendamento < DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
        $_SESSION['ultima_autoconclusao'] = time();
    } catch (Exception $e) {
        // Falha silenciosa para não travar a navegação do usuário caso ocorra problema temporário no banco
    }
}

// =========================================================================
// AUTOLOADER CUSTOMIZADO (Carregamento Automático de Classes)
// =========================================================================
// Evita a necessidade de usar 'require_once' repetitivos para cada Controller,
// Model ou Service. O PHP chama esta função sempre que tentamos instanciar
// uma classe que ainda não foi incluída na memória.
// =========================================================================
spl_autoload_register(function ($nome_da_classe) {
    // Lista das pastas onde as classes do sistema ficam armazenadas
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
// TRATAMENTO DA URI DE ENTRADA
// =========================================================================
// Limpa e normaliza a URL acessada pelo usuário para casar com as rotas.
// Exemplo: 'http://localhost/TCC-ETEC/login' se transforma apenas em '/login'
// =========================================================================
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (strpos($uri, BASE_URL) === 0) {
    $uri = substr($uri, strlen(BASE_URL));
}

if ($uri == '') {
    $uri = '/';
}

// =========================================================================
// MIDDLEWARE DE ROTAS (Níveis de Acesso)
// =========================================================================
// Verifica se o usuário tem permissão para acessar a rota requisitada.
// Exemplo: impede que um cliente acesse '/admin/funcionarios' ou que 
// um visitante deslogado acesse o dashboard.
// =========================================================================
Middleware::verificar($uri);

// =========================================================================
// EXECUÇÃO DO ROTEADOR CENTRAL
// =========================================================================
// Instancia o roteador, inclui as definições do arquivo routes.php e despacha
// o controle para a classe/método mapeado para a URI atual.
// =========================================================================
$router = new Router();
require_once __DIR__ . '/app/Routes/routes.php';
$router->executar($uri);

// =========================================================================
// INJEÇÃO GLOBAL DE ATIVOS (Favicon, CSRF e Identidade Visual White-Label)
// =========================================================================
// Captura o HTML final que está guardado no buffer de saída, analisa e 
// injeta tags fundamentais no <head> antes de enviar para o navegador.
// =========================================================================
$html = ob_get_clean();

if (strpos($html, '<head>') !== false) {
    $tagsParaInjetar = "";

    // 1. Injeção automática do Favicon padrão se a view não especificou nenhum
    if (strpos($html, 'rel="icon"') === false) {
        $tagsParaInjetar .= "\n    <link rel=\"icon\" type=\"image/png\" href=\"" . BASE_URL . "/public/resources/images/favicon.png\">";
    }

    // 2. Injeção automática da Meta Tag CSRF (usada pelos scripts JavaScript para chamadas AJAX/fetch seguros)
    if (strpos($html, '<meta name="csrf-token"') === false) {
        $tagsParaInjetar .= "\n    " . CsrfGuard::metaTag();
    }

    // 3. Injeção Dinâmica de Cores e Identidade Visual (Customização de Marca - White-Label)
    try {
        $configModel = new Configuracao();
        $corPrimaria = $configModel->obterValor('cor_primaria');
        $corSecundaria = $configModel->obterValor('cor_secundaria');
        $logoUrl = $configModel->obterValor('logo_url');

        $tagsBranding = "";
        // Se houver cores personalizadas salvas no banco, sobrescreve as variáveis CSS `:root` globais do sistema
        if (!empty($corPrimaria) || !empty($corSecundaria)) {
            $p = !empty($corPrimaria) ? htmlspecialchars($corPrimaria) : '#f45b69';
            $s = !empty($corSecundaria) ? htmlspecialchars($corSecundaria) : '#8b5cf6';

            $tagsBranding .= "\n    <style id=\"white-label-colors\">";
            $tagsBranding .= "\n        :root {";
            $tagsBranding .= "\n            --color-pink: {$p} !important;";
            $tagsBranding .= "\n            --color-purple: {$s} !important;";
            $tagsBranding .= "\n            --gradient-brand: linear-gradient(135deg, {$s} 0%, {$p} 100%) !important;";
            $tagsBranding .= "\n        }";
            $tagsBranding .= "\n        .sidebar {";
            $tagsBranding .= "\n            background: linear-gradient(135deg, {$s} 0%, {$p} 100%) !important;";
            $tagsBranding .= "\n        }";
            $tagsBranding .= "\n    </style>";
        }

        // Se houver URL do logotipo personalizada no banco, injeta script para substituir as imagens com classe de logo
        if (!empty($logoUrl)) {
            $tagsBranding .= "\n    <script id=\"white-label-logo-script\">";
            $tagsBranding .= "\n        window.LOGO_URL = '" . htmlspecialchars($logoUrl) . "';";
            $tagsBranding .= "\n        document.addEventListener('DOMContentLoaded', () => {";
            $tagsBranding .= "\n            const atualizarLogos = () => {";
            $tagsBranding .= "\n                document.querySelectorAll('.login-logo, .sidebar-logo').forEach(img => {";
            $tagsBranding .= "\n                    img.src = window.LOGO_URL;";
            $tagsBranding .= "\n                });";
            $tagsBranding .= "\n            };";
            $tagsBranding .= "\n            atualizarLogos();";
            $tagsBranding .= "\n            // Executa com pequenos atrasos para garantir a alteração em telas dinâmicas/SPA";
            $tagsBranding .= "\n            setTimeout(atualizarLogos, 100);";
            $tagsBranding .= "\n            setTimeout(atualizarLogos, 500);";
            $tagsBranding .= "\n        });";
            $tagsBranding .= "\n    </script>";
        }

        if (!empty($tagsBranding)) {
            $tagsParaInjetar .= $tagsBranding;
        }
    } catch (Exception $e) {
        // Falha silenciosa para evitar travar o sistema caso o banco de dados ainda não esteja instalado/disponível
    }

    // Insere as tags preparadas logo antes do fechamento da tag </head> para prioridade de renderização correta
    if (!empty($tagsParaInjetar)) {
        if (strpos($html, '</head>') !== false) {
            $html = str_replace('</head>', $tagsParaInjetar . "\n</head>", $html);
        } else {
            $html = str_replace('<head>', '<head>' . $tagsParaInjetar, $html);
        }
    }
}

// Cospe o HTML tratado para o navegador do cliente
echo $html;

