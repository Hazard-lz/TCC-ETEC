<?php

// =========================================================================
// MIDDLEWARE — Camada de proteção que verifica permissões ANTES da rota executar
// =========================================================================
class Middleware {
    
    /**
     * Verifica as permissões de acesso com base no prefixo da URI.
     * Chamado automaticamente pelo index.php antes do Router despachar a rota.
     */
    public static function verificar($uri) {

        // --- BLOQUEIO DA PASTA ADMIN (SÓ ADMIN ENTRA) ---
        if (strpos($uri, '/admin') === 0) {
            if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
                $_SESSION['erro_login'] = "Acesso restrito a administradores.";
                header("Location: " . BASE_URL . "/login");
                exit;
            }
        }

        // --- BLOQUEIO DA PASTA FUNCIONÁRIO (EQUIPA TODA ENTRA) ---
        if (strpos($uri, '/funcionario') === 0) {
            if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['is_funcionario'])) {
                $_SESSION['erro_login'] = "Acesso restrito à equipe do salão.";
                header("Location: " . BASE_URL . "/login");
                exit;
            }
        }
    }
}
