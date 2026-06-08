<?php

// =========================================================================
// MIDDLEWARE — Camada ÚNICA de proteção que verifica permissões ANTES da rota executar.
// Nenhum Controller deve repetir estas verificações de cargo/sessão.
// =========================================================================
class Middleware
{

    /**
     * Verifica as permissões de acesso com base no prefixo da URI.
     * Chamado automaticamente pelo index.php antes do Router despachar a rota.
     * 
     * HIERARQUIA DE ACESSOS:
     *   /admin/relatorios/*  → Exclusivo 'admin'
     *   /admin/*             → 'admin' e 'subadmin'
     *   /funcionario/*       → Qualquer membro da equipe (is_funcionario)
     *   /cliente/*           → Equipe do salão (para gerenciar clientes)
     *   /historico/cancelar  → Apenas clientes logados
     *   /api/*               → Qualquer usuário logado
     */
    public static function verificar($uri)
    {
        $logado = isset($_SESSION['usuario_id']);

        // ═══ SINCRONIZAÇÃO DINÂMICA DE SESSÃO  ═══
        // Se o usuário estiver logado, verificamos o banco em cada clique para garantir consistência.
        if ($logado) {
            $usuarioModel = new Usuario();
            $usuarioDB = $usuarioModel->buscarPorId($_SESSION['usuario_id']);

            // 1. Se o usuário foi desativado ou deletado, encerra o acesso imediatamente
            if (!$usuarioDB || $usuarioDB['status'] === 'inativo') {
                unset($_SESSION['usuario_id']);
                $_SESSION['erro_login'] = "Sua conta foi desativada. Entre em contato com o administrador.";
                header("Location: " . BASE_URL . "/login");
                exit;
            }

            // 2. Sincroniza dados básicos (Nome, Cargo, etc)
            // Isso permite que mudanças reflitam no próximo clique sem precisar relogar.
            $_SESSION['usuario_nome'] = $usuarioDB['nome'];
            $_SESSION['usuario_tipo'] = $usuarioDB['tipo'];
            $tipo = $usuarioDB['tipo']; // Atualiza variável local para as permissões abaixo

            // 3. Sincroniza permissão de equipe (is_funcionario)
            if (in_array($tipo, ['admin', 'subadmin'])) {
                $_SESSION['is_funcionario'] = true;
            } else {
                $funcionarioModel = new Funcionario();
                $_SESSION['is_funcionario'] = (bool) $funcionarioModel->buscarPorCodUsuario($_SESSION['usuario_id']);
            }
        }

        $tipo = $_SESSION['usuario_tipo'] ?? '';
        $metodo = $_SERVER['REQUEST_METHOD'];

        // ═══════════════════════════════════════════════════════════
        // REDIRECIONAMENTO DE USUÁRIOS LOGADOS
        // Bloqueia acesso às páginas de login/cadastro se já houver sessão
        // ═══════════════════════════════════════════════════════════
        $rotasAutenticacao = ['/login', '/cadastro', '/recuperar-senha', '/redefinir-senha', '/verificar-email'];
        if ($logado && in_array($uri, $rotasAutenticacao)) {
            $destino = (isset($_SESSION['is_funcionario']) && $_SESSION['is_funcionario'] === true)
                ? "/funcionario/dashboard"
                : "/";

            header("Location: " . BASE_URL . $destino);
            exit;
        }

        // ═══════════════════════════════════════════════════════════
        // PROTEÇÃO CSRF: Toda requisição POST deve conter um token válido
        // Exceção: Rotas de API que usam JSON (OneSignal, horários, etc)
        // ═══════════════════════════════════════════════════════════
        if ($metodo === 'POST') {
            // Rotas isentas de CSRF (APIs internas que não usam formulários HTML)
            $rotasIsentasCsrf = [
                '/api/onesignal/registrar',
                '/api/horarios-livres',
                '/api/servicos-profissional',
            ];

            $isento = false;
            foreach ($rotasIsentasCsrf as $rotaIsenta) {
                if ($uri === $rotaIsenta) {
                    $isento = true;
                    break;
                }
            }

            if (!$isento && !CsrfGuard::validar()) {
                error_log("CSRF bloqueado: $uri | IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'desconhecido'));
                $_SESSION['flash_erro'] = "Requisição inválida. Tente novamente.";

                // Redireciona para uma página segura baseada no contexto
                $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/';
                header("Location: $referer");
                exit;
            }
        }

        // --- CONTINGÊNCIA GLOBAL (FECHAMENTO DO SALÃO) ---
        if ($uri === '/agendar' || $uri === '/api/horarios-livres') {
            $isFuncionario = isset($_SESSION['is_funcionario']) && $_SESSION['is_funcionario'] === true;
            if (!$isFuncionario) {
                $configModel = new Configuracao();
                $statusFuncionamento = $configModel->obterValor('status_funcionamento', 'ativo');
                
                if ($statusFuncionamento === 'inativo') {
                    if ($uri === '/api/horarios-livres') {
                        header('Content-Type: application/json');
                        http_response_code(503);
                        echo json_encode([
                            'sucesso' => false,
                            'mensagem' => 'O salão está temporariamente fechado para novos agendamentos.'
                        ]);
                        exit;
                    } else {
                        header("Location: " . BASE_URL . "/contingencia");
                        exit;
                    }
                }
            }
        }

        // --- BLOQUEIO DE RELATÓRIOS (SÓ ADMIN PURO) ---
        // Esta regra DEVE vir antes da regra genérica /admin
        if (strpos($uri, '/admin/relatorios') === 0) {
            if (!$logado || $tipo !== 'admin') {
                $_SESSION['flash_erro'] = "Acesso restrito a administradores.";
                header("Location: " . BASE_URL . "/funcionario/dashboard");
                exit;
            }
        }

        // --- BLOQUEIO DA ÁREA ADMIN (ADMIN + SUBADMIN) ---
        if (strpos($uri, '/admin') === 0) {
            if (!$logado || !in_array($tipo, ['admin', 'subadmin'])) {
                $_SESSION['erro_login'] = "Acesso restrito à gerência.";
                header("Location: " . BASE_URL . "/login");
                exit;
            }
        }

        // --- BLOQUEIO DA ÁREA FUNCIONÁRIO (EQUIPA TODA ENTRA) ---
        if (strpos($uri, '/funcionario') === 0) {
            if (!$logado || !isset($_SESSION['is_funcionario'])) {
                $_SESSION['erro_login'] = "Acesso restrito à equipe do salão.";
                header("Location: " . BASE_URL . "/login");
                exit;
            }
        }

        // --- BLOQUEIO DA GESTÃO DE CLIENTES (EQUIPA TODA ENTRA) ---
        if (strpos($uri, '/cliente/salvar') === 0 || strpos($uri, '/cliente/alterar-status') === 0) {
            if (!$logado || !isset($_SESSION['is_funcionario'])) {
                $_SESSION['erro_login'] = "Acesso restrito à equipe do salão.";
                header("Location: " . BASE_URL . "/login");
                exit;
            }
        }

        // --- BLOQUEIO DO CANCELAMENTO PELO CLIENTE (CLIENTES LOGADOS) ---
        if ($uri === '/historico/cancelar') {
            if (!$logado || $tipo !== 'comum') {
                $_SESSION['flash_erro'] = "Acesso não autorizado.";
                header("Location: " . BASE_URL . "/login");
                exit;
            }
        }

        // --- BLOQUEIO DE APIs (QUALQUER USUÁRIO LOGADO) ---
        if (strpos($uri, '/api/') === 0) {
            if (!$logado) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['sucesso' => false, 'mensagem' => 'Não autorizado.']);
                exit;
            }
        }
    }
}
