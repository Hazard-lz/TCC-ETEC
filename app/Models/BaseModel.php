<?php

require_once __DIR__ . '/../../database/Conexao.php';

abstract class BaseModel {
    
    // Protected permite que as classes filhas acedam à conexão quando precisarem de transações complexas
    protected $conn;

    public function __construct() {
        $this->conn = Conexao::getConexao();
    }

    /**
     * Função centralizada para executar as queries e tratar erros.
     * @param string $sql A query SQL
     * @param array $parametros Array associativo com os valores (ex: [':id' => 1])
     * @param string $acao O que retornar: 'id', 'todos', 'unico', 'coluna' ou 'nada'
     */
    protected function executarQuery($sql, $parametros = [], $acao = 'nada') {
        try {
            $stmt = $this->conn->prepare($sql);
            
            foreach ($parametros as $chave => $valor) {
                // Se for número inteiro, avisa o PDO para não tratar como string
                $tipo = is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($chave, $valor, $tipo);
            }
            
            $stmt->execute();

            if ($acao === 'id') return $this->conn->lastInsertId();
            if ($acao === 'todos') return $stmt->fetchAll();
            if ($acao === 'unico') return $stmt->fetch();
            if ($acao === 'coluna') return $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            return true; // Retorna true para INSERT simples, UPDATE ou DELETE
            
        } catch (PDOException $e) {
            error_log("Erro no BD: " . $e->getMessage() . " | SQL: " . $sql);
            return false;
        }
    }
}
?>