<?php
// =========================================================================
// MODEL DE CONFIGURAÇÃO (Configuracao.php)
// =========================================================================
// Este modelo gerencia a tabela 'configuracoes_sistema', que funciona como
// um repositório chave-valor para armazenar as configurações gerais do app
// (como cores do tema, logotipo, regras de cancelamento e localização).
// =========================================================================

require_once __DIR__ . '/BaseModel.php';

class Configuracao extends BaseModel {

    /**
     * Busca uma linha de configuração completa no banco pelo nome da chave.
     * 
     * @param string $chave Nome identificador da configuração (ex: 'cor_primaria')
     * @return array|null Retorna o registro do banco de dados ou null se não encontrado
     */
    public function buscarPorChave($chave) {
        $sql = "SELECT * FROM configuracoes_sistema WHERE chave = :chave";
        // Executa a query utilizando o helper herdado do BaseModel para retornar um único registro
        return $this->executarQuery($sql, [':chave' => $chave], 'unico');
    }

    /**
     * Salva ou atualiza uma configuração.
     * Se a chave já existir no banco de dados, executa um UPDATE.
     * Caso contrário, insere um novo registro com a chave fornecida via INSERT.
     * 
     * @param string $chave Nome identificador da configuração
     * @param string $valor Conteúdo a ser armazenado
     * @return bool|int Retorna o sucesso da operação (ou o ID inserido se for novo registro)
     */
    public function salvar($chave, $valor) {
        // Verifica primeiro se a configuração já está cadastrada
        $existente = $this->buscarPorChave($chave);
        
        if ($existente) {
            // Se já existe, atualiza o valor da chave correspondente
            $sql = "UPDATE configuracoes_sistema SET valor = :valor WHERE chave = :chave";
            return $this->executarQuery($sql, [':valor' => $valor, ':chave' => $chave]);
        } else {
            // Se não existe, cria um novo registro
            $sql = "INSERT INTO configuracoes_sistema (chave, valor) VALUES (:chave, :valor)";
            return $this->executarQuery($sql, [':chave' => $chave, ':valor' => $valor], 'id');
        }
    }

    /**
     * Obtém diretamente o valor string de uma configuração.
     * Se a configuração não for localizada no banco, retorna o valor padrão especificado.
     * 
     * @param string $chave Nome identificador da configuração
     * @param mixed $padrao Valor opcional de retorno caso a chave não exista no banco
     * @return string|null O valor armazenado no banco ou o valor padrão definido
     */
    public function obterValor($chave, $padrao = null) {
        $conf = $this->buscarPorChave($chave);
        // Retorna apenas a coluna 'valor' se o registro existir, senão retorna o valor padrão
        return $conf ? $conf['valor'] : $padrao;
    }
}

