<?php
require_once __DIR__ . '/BaseModel.php';

class Configuracao extends BaseModel {

    /**
     * Busca uma configuração completa pelo nome da chave.
     * @param string $chave
     * @return array|null
     */
    public function buscarPorChave($chave) {
        $sql = "SELECT * FROM configuracoes_sistema WHERE chave = :chave";
        return $this->executarQuery($sql, [':chave' => $chave], 'unico');
    }

    /**
     * Salva ou atualiza uma configuração.
     * @param string $chave
     * @param string $valor
     * @return bool|int
     */
    public function salvar($chave, $valor) {
        $existente = $this->buscarPorChave($chave);
        if ($existente) {
            $sql = "UPDATE configuracoes_sistema SET valor = :valor WHERE chave = :chave";
            return $this->executarQuery($sql, [':valor' => $valor, ':chave' => $chave]);
        } else {
            $sql = "INSERT INTO configuracoes_sistema (chave, valor) VALUES (:chave, :valor)";
            return $this->executarQuery($sql, [':chave' => $chave, ':valor' => $valor], 'id');
        }
    }

    /**
     * Obtém diretamente o valor de uma chave.
     * @param string $chave
     * @param mixed $padrao
     * @return string|null
     */
    public function obterValor($chave, $padrao = null) {
        $conf = $this->buscarPorChave($chave);
        return $conf ? $conf['valor'] : $padrao;
    }
}
