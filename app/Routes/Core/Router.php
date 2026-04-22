<?php

// =========================================================================
// ROUTER — Classe responsável por mapear e executar as rotas do sistema
// =========================================================================
class Router {
    
    // Array que armazena todas as rotas registradas, organizadas por método HTTP
    // Estrutura: $rotas['GET']['/login'] = 'AuthController@exibirLogin'
    private $rotas = [];

    /**
     * Registra uma rota do tipo GET (Exibição de página)
     */
    public function get($uri, $acao) {
        $this->rotas['GET'][$uri] = $acao;
    }

    /**
     * Registra uma rota do tipo POST (Envio de formulário / Ação)
     */
    public function post($uri, $acao) {
        $this->rotas['POST'][$uri] = $acao;
    }

    /**
     * Atalho: Registra uma rota GET que apenas inclui uma View (sem precisar de Controller)
     * Exemplo: $router->view('/login', 'auth/login') → inclui public/views/auth/login.php
     */
    public function view($uri, $caminhoView) {
        $this->rotas['GET'][$uri] = ['__view' => $caminhoView];
    }

    /**
     * Executa (despacha) a rota que corresponde à URI e ao método HTTP da requisição atual
     */
    public function executar($uri) {
        $metodo = $_SERVER['REQUEST_METHOD'];

        // 1. Procura uma rota registrada para o método HTTP + URI atuais
        if (isset($this->rotas[$metodo][$uri])) {
            $this->despachar($this->rotas[$metodo][$uri]);
            return;
        }

        // 2. Se não encontrou nenhuma rota, retorna 404
        http_response_code(404);
        echo "<div style='text-align: center; margin-top: 50px; font-family: sans-serif;'>";
        echo "<h1>Página não encontrada!</h1>";
        echo "<p>A rota (<strong>" . htmlspecialchars($uri) . "</strong>) não existe no sistema.</p>";
        echo "<a href='" . BASE_URL . "/'>Voltar para a página inicial</a>";
        echo "</div>";
    }

    /**
     * Interpreta o tipo da ação registrada e executa de acordo:
     *   - Array com '__view' → inclui o arquivo PHP da view
     *   - Closure (função anônima) → executa diretamente
     *   - String 'Controller@metodo' → instancia o controller e chama o método
     */
    private function despachar($acao) {

        // Tipo 1: View direta (veio do método $router->view())
        if (is_array($acao) && isset($acao['__view'])) {
            $caminhoCompleto = __DIR__ . '/../../../public/views/' . $acao['__view'] . '.php';
            include $caminhoCompleto;
            return;
        }

        // Tipo 2: Closure / Função anônima (para rotas com lógica especial)
        if (is_callable($acao)) {
            call_user_func($acao);
            return;
        }

        // Tipo 3: String 'NomeController@metodo' → instancia e chama
        if (is_string($acao) && strpos($acao, '@') !== false) {
            list($nomeController, $metodo) = explode('@', $acao);
            $controller = new $nomeController();
            $controller->$metodo();
            return;
        }
    }
}
