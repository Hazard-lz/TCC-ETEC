<?php
require_once __DIR__ . '/BaseModel.php';

class Servico extends BaseModel {

    public function cadastrar($nome_servico, $descricao, $preco, $duracao) {
        $sql = "INSERT INTO servicos (nome_servico, descricao, preco, duracao) 
                VALUES (:nome_servico, :descricao, :preco, :duracao)";
        
        return $this->executarQuery($sql, [
            ':nome_servico' => $nome_servico, ':descricao' => $descricao, 
            ':preco' => $preco, ':duracao' => (int)$duracao
        ], 'id');
    }

    public function listarPorStatus($status) {
        $sql = "SELECT * FROM servicos WHERE status = :status ORDER BY nome_servico ASC";
        return $this->executarQuery($sql, [':status' => $status], 'todos') ?: [];
    }

    public function buscarPorNome($nome) {
        $sql = "SELECT * FROM servicos WHERE LOWER(TRIM(nome_servico)) = LOWER(TRIM(:nome)) LIMIT 1";
        return $this->executarQuery($sql, [':nome' => $nome], 'unico');
    }

    public function buscarPorId($id_servico) {
        $sql = "SELECT * FROM servicos WHERE id_servico = :id";
        return $this->executarQuery($sql, [':id' => $id_servico], 'unico');
    }

    public function atualizar($id_servico, $nome_servico, $descricao, $preco, $duracao) {
        $sql = "UPDATE servicos SET nome_servico = :nome_servico, descricao = :descricao, 
                       preco = :preco, duracao = :duracao WHERE id_servico = :id";
        
        return $this->executarQuery($sql, [
            ':nome_servico' => $nome_servico, ':descricao' => $descricao, 
            ':preco' => $preco, ':duracao' => (int)$duracao, ':id' => $id_servico
        ]);
    }

    // Mantido com try/catch pois exige controle de transação (duas tabelas)
    public function atualizarStatus($id_servico, $status) {
        try {
            $this->conn->beginTransaction();

            $sqlServico = "UPDATE servicos SET status = :status WHERE id_servico = :id";
            $stmtServico = $this->conn->prepare($sqlServico);
            $stmtServico->execute([':status' => $status, ':id' => $id_servico]);

            if ($status === 'inativo') {
                $sqlVinculo = "UPDATE funcionario_servicos SET status = 'inativo' WHERE cod_servico = :id AND status = 'ativo'";
                $stmtVinculo = $this->conn->prepare($sqlVinculo);
                $stmtVinculo->execute([':id' => $id_servico]);
            }

            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Erro na cascata lógica do serviço: " . $e->getMessage());
            return false;
        }
    }

    public function excluir($id_servico) {
        try {
            $this->conn->beginTransaction();

            // 1. Remove vínculos do serviço com funcionários
            // (Com a nova FK SET NULL, o MySQL desvincula automaticamente os itens_agendamento)
            $sql1 = "DELETE FROM funcionario_servicos WHERE cod_servico = :id";
            $stmt1 = $this->conn->prepare($sql1);
            $stmt1->execute([':id' => $id_servico]);

            // 2. Remove o serviço
            $sql2 = "DELETE FROM servicos WHERE id_servico = :id";
            $stmt2 = $this->conn->prepare($sql2);
            $stmt2->execute([':id' => $id_servico]);

            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Erro ao excluir serviço: " . $e->getMessage());
            return false;
        }
    }
}