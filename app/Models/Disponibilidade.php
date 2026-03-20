<?php
require_once __DIR__ . '/BaseModel.php';

class Disponibilidade extends BaseModel {

    // A tabela principal agora só guarda de quem é essa grade
    public function cadastrar($cod_funcionario) {
        $sql = "INSERT INTO disponibilidade (cod_funcionario) VALUES (:cod_funcionario)";
        return $this->executarQuery($sql, [':cod_funcionario' => $cod_funcionario], 'id');
    }

    // A tabela de dias recebe os horários de entrada, saída e intervalos
    public function cadastrarDias($cod_disponibilidade, $dias_semana, $inicio, $fim, $int_inicio, $int_fim) {
        try {
            $sql = "INSERT INTO disponibilidade_dias 
                    (cod_disponibilidade, dia_semana, hora_inicio_trabalho, hora_fim_trabalho, intervalo_inicio, intervalo_fim) 
                    VALUES (:cod_disponibilidade, :dia_semana, :inicio, :fim, :int_inicio, :int_fim)";
            
            $stmt = $this->conn->prepare($sql);

            foreach ($dias_semana as $dia) {
                $stmt->execute([
                    ':cod_disponibilidade' => $cod_disponibilidade,
                    ':dia_semana' => $dia,
                    ':inicio' => $inicio,
                    ':fim' => $fim,
                    ':int_inicio' => $int_inicio,
                    ':int_fim' => $int_fim
                ]);
            }
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao cadastrar dias: " . $e->getMessage());
            return false;
        }
    }

    public function buscarPorFuncionario($cod_funcionario) {
        // Traz as duas tabelas juntas para o sistema ter todas as informações da grade
        $sql = "SELECT d.id_disponibilidade, dd.* FROM disponibilidade d
                INNER JOIN disponibilidade_dias dd ON d.id_disponibilidade = dd.cod_disponibilidade
                WHERE d.cod_funcionario = :cod_funcionario";
        return $this->executarQuery($sql, [':cod_funcionario' => $cod_funcionario], 'todos');
    }

    // O Update e o Delete físico de dias
    public function excluirDias($cod_disponibilidade) {
        $sql = "DELETE FROM disponibilidade_dias WHERE cod_disponibilidade = :id";
        return $this->executarQuery($sql, [':id' => $cod_disponibilidade]);
    }

    public function excluir($id_disponibilidade) {
        $sql = "DELETE FROM disponibilidade WHERE id_disponibilidade = :id";
        return $this->executarQuery($sql, [':id' => $id_disponibilidade]);
    }
}
?>