<?php

// =========================================================================
// CSRFGUARD — Proteção contra ataques de Cross-Site Request Forgery (CSRF)
// =========================================================================
// Um token único é gerado por sessão e embutido em todos os formulários.
// Cada requisição POST deve enviar esse token, que é validado antes de
// qualquer ação ser executada. Isso impede que sites externos enviem 
// comandos usando os cookies do usuário logado.
// =========================================================================
class CsrfGuard {

    /**
     * Gera ou reutiliza o token CSRF da sessão atual.
     * Retorna o token como string para uso em formulários e requisições AJAX.
     */
    public static function gerarToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Retorna um campo <input type="hidden"> pronto para ser inserido em formulários HTML.
     * Uso na View: <?= CsrfGuard::campoHidden() ?>
     */
    public static function campoHidden(): string {
        $token = self::gerarToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Retorna uma <meta> tag com o token para uso em requisições JavaScript (fetch/AJAX).
     * Uso no <head>: <?= CsrfGuard::metaTag() ?>
     */
    public static function metaTag(): string {
        $token = self::gerarToken();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token) . '">';
    }

    /**
     * Valida o token enviado na requisição contra o token da sessão.
     * Aceita o token tanto via POST (formulários) quanto via Header (AJAX).
     * Retorna true se o token for válido, false caso contrário.
     */
    public static function validar(): bool {
        $tokenSessao = $_SESSION['csrf_token'] ?? '';

        // Tenta pegar do corpo do POST (formulários HTML)
        $tokenRecebido = $_POST['csrf_token'] ?? '';

        // Se não veio no POST, tenta pegar do Header (requisições AJAX/fetch)
        if (empty($tokenRecebido)) {
            $tokenRecebido = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        }

        // Se não veio no header customizado, tenta no corpo JSON (fetch com body JSON)
        if (empty($tokenRecebido)) {
            $input = file_get_contents('php://input');
            $dados = json_decode($input, true);
            $tokenRecebido = $dados['csrf_token'] ?? '';
        }

        if (empty($tokenSessao) || empty($tokenRecebido)) {
            return false;
        }

        return hash_equals($tokenSessao, $tokenRecebido);
    }
}
