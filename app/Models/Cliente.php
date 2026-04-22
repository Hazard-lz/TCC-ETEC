<?php
require_once __DIR__ . '/BaseModel.php';

class Cliente extends BaseModel {

// CREATE/INSERT
    public function cadastrar($cod_usuario, $data_nascimento, $observacoes = null) {
        $data_nascimento = !empty(trim($data_nascimento)) ? trim($data_nascimento) : null;
        $observacoes = !empty(trim($observacoes)) ? trim($observacoes) : null;

        $sql = "INSERT INTO clientes (cod_usuario, data_nascimento, observacoes) 
                VALUES (:cod_usuario, :data_nascimento, :observacoes)";
        return $this->executarQuery($sql, [':cod_usuario' => $cod_usuario, ':data_nascimento' => $data_nascimento, ':observacoes' => $observacoes], 'id');
    }

// READ/SELECT
    public function listarTodos() {
        $sql = "SELECT c.id_cliente, c.cod_usuario, c.data_nascimento, c.observacoes, 
                       u.nome, u.email, u.telefone, u.status, u.data_criacao
                FROM clientes c
                INNER JOIN usuarios u ON c.cod_usuario = u.id_usuario
                ORDER BY u.nome ASC";
        return $this->executarQuery($sql, [], 'todos');
    }

    public function buscarPorId($id_cliente) {
        $sql = "SELECT c.*, u.nome, u.email, u.telefone, u.status 
                FROM clientes c
                INNER JOIN usuarios u ON c.cod_usuario = u.id_usuario
                WHERE c.id_cliente = :id";
        return $this->executarQuery($sql, [':id' => $id_cliente], 'unico');
    }

    public function buscarPorCodUsuario($cod_usuario) {
        $sql = "SELECT * FROM clientes WHERE cod_usuario = :cod_usuario";
        return $this->executarQuery($sql, [':cod_usuario' => $cod_usuario], 'unico');
    }

// UPDATE
    public function atualizar($id_cliente, $data_nascimento, $observacoes) {
        $data_nascimento = !empty(trim($data_nascimento)) ? trim($data_nascimento) : null;
        $observacoes = !empty(trim($observacoes)) ? trim($observacoes) : null;

        $sql = "UPDATE clientes SET data_nascimento = :data_nascimento, observacoes = :observacoes WHERE id_cliente = :id";
        return $this->executarQuery($sql, [':data_nascimento' => $data_nascimento, ':observacoes' => $observacoes, ':id' => $id_cliente]);
    }

    

    public function atualizarObservacoes($id_cliente, $observacoes) {
        $observacoes = !empty(trim($observacoes)) ? trim($observacoes) : null;

        $sql = "UPDATE clientes SET observacoes = :observacoes WHERE id_cliente = :id";
        return $this->executarQuery($sql, [
            ':observacoes' => $observacoes, 
            ':id' => $id_cliente
        ]);
    }
}