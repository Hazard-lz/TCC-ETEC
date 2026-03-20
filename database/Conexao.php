<?php

// Classe responsável por gerenciar a conexão com o banco de dados
class Conexao {
    
    // Atributo estático que armazenará a única instância da conexão (Padrão Singleton)
    private static $instancia;

    // Construtor privado: impede que a classe seja instanciada com "new Conexao()" em outros arquivos
    private function __construct() { }

    // Função interna responsável por ler o arquivo .env e carregar as credenciais na memória
    private static function carregarEnv() {
        // Define o caminho do arquivo .env. __DIR__ pega a pasta atual (database/) e o '/../' sobe um nível (raiz do projeto)
        $caminhoEnv = __DIR__ . '/../.env';

        // Verifica se o arquivo .env existe antes de tentar ler, evitando que o sistema quebre
        if (!file_exists($caminhoEnv)) {
            die("Erro: Arquivo .env não encontrado na raiz do projeto.");
        }

        // Lê o arquivo inteiro e transforma em um array. As "flags"/sinalizações ignoram quebras de linha e linhas em branco
        $linhas = file($caminhoEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Percorre cada linha lida do arquivo .env
        foreach ($linhas as $linha) {
            // Se a linha começar com '#' (indicando um comentário), o sistema ignora e pula para a próxima linha
            if (strpos(trim($linha), '#') === 0) {
                continue;
            }

            // Divide a linha em duas partes (Nome e Valor) usando o sinal de igual (=) como ponto de corte. O '2' limita a divisão a no máximo duas partes.
            list($nome, $valor) = explode('=', $linha, 2);
            
            // Limpa espaços em branco extras (trim) e salva a credencial na variável superglobal nativa do PHP ($_ENV)
            $_ENV[trim($nome)] = trim($valor);
        }
    }

    // Método principal que será chamado pelos Models para obter acesso ao banco de dados
    public static function getConexao() {
        
        // Verifica se a conexão NÃO existe. Se já existir, ele pula tudo isso e apenas devolve a conexão que já está aberta
        if (!isset(self::$instancia)) {
            
            // Executa a função que lê o arquivo .env para garantir que as senhas estejam disponíveis
            self::carregarEnv();

            try {
                // Resgata as configurações de conexão que foram salvas na memória global ($_ENV)
                $host = $_ENV['DB_HOST'];
                $dbname = $_ENV['DB_NAME'];
                $user = $_ENV['DB_USER'];
                $pass = $_ENV['DB_PASS'];

                // Monta a string de conexão exigida pelo PDO (DSN - Data Source Name), definindo o servidor, o banco e a codificação de caracteres (utf8)
                $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";

                // Instancia o PDO para criar efetivamente a comunicação com o MySQL
                self::$instancia = new PDO($dsn, $user, $pass);
                
                // Configura o PDO para gerar Exceções (erros capturáveis) caso alguma query SQL esteja escrita errada
                self::$instancia->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Configura o PDO para retornar os dados das consultas formatados como arrays associativos (ex: $resultado['nome']), otimizando a memória
                self::$instancia->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
            } catch (PDOException $e) {
                // Se houver falha na conexão (ex: servidor desligado ou senha errada), interrompe a execução e mostra o erro sem vazar dados do código
                error_log("Erro de conexão: " . $e->getMessage());

                die("Erro - Falha ao conectar com o banco");
            }
        }
        
        // Retorna o objeto PDO pronto para executar comandos (SELECT, INSERT, UPDATE, DELETE)
        return self::$instancia;
    }
}
?>