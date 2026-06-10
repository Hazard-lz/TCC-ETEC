<?php

// =========================================================================
// HELPERS — Funções e constantes utilitárias centralizadas do sistema
// =========================================================================
class Helpers {

    /**
     * Array de meses em PT-BR indexado por número (1 = Janeiro, 12 = Dezembro).
     * Uso: Helpers::MESES[(int)$data->format('m')]
     */
    const MESES = [
        '', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
        'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
    ];

    /**
     * Formata uma data para exibição amigável: "24 de Março às 10:30"
     */
    public static function dataExtenso($dataStr, $horaStr = null) {
        $dataObj = new DateTime($dataStr);
        $dia = $dataObj->format('d');
        $mesNome = self::MESES[(int)$dataObj->format('m')];
        $texto = "{$dia} de {$mesNome}";

        if ($horaStr) {
            $horaFormatada = substr($horaStr, 0, 5);
            $texto .= " às {$horaFormatada}";
        }

        return $texto;
    }

    /**
     * Envia o cabeçalho de redirecionamento para o navegador, grava a sessão e fecha a
     * conexão HTTP de imediato. Isso permite que o PHP continue executando em segundo plano
     * (por exemplo, disparando e-mails ou push notifications nas shutdown functions)
     * enquanto o navegador do usuário é redirecionado instantaneamente.
     * 
     * @param string $url URL de redirecionamento
     */
    public static function responderERedirecionar($url) {
        // Garante que o PHP continue rodando mesmo após a desconexão do cliente
        ignore_user_abort(true);

        // Define o cabeçalho de redirecionamento
        header("Location: $url");

        // IMPORTANTE: Grava e libera o arquivo de sessão imediatamente.
        // Sem isso, a requisição redirecionada do navegador ficaria travada no servidor
        // esperando o término da primeira requisição (bloqueio de arquivo de sessão do PHP).
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        // Se estiver rodando sob FastCGI (FPM), finaliza a requisição de forma nativa e limpa
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
            return;
        }

        // Para Apache (mod_php) ou outros ambientes sem FastCGI, fechamos a conexão via HTTP headers
        header("Connection: close");
        header("Content-Length: 0");

        // Esvazia e desativa todos os buffers de saída ativos do PHP
        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        // Envia todos os cabeçalhos e buffers restantes para o navegador
        flush();
    }

    /**
     * Envia uma resposta JSON para o navegador, grava a sessão e fecha a conexão HTTP
     * de imediato, permitindo que tarefas subsequentes rodem em segundo plano.
     * 
     * @param array $dados Dados a serem convertidos para JSON
     */
    public static function responderJson($dados) {
        ignore_user_abort(true);

        $json = json_encode($dados);
        $tamanho = strlen($json);

        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        if (function_exists('fastcgi_finish_request')) {
            echo $json;
            fastcgi_finish_request();
            return;
        }

        header("Connection: close");
        header("Content-Length: $tamanho");
        echo $json;

        while (ob_get_level() > 0) {
            ob_end_flush();
        }
        flush();
    }
}
