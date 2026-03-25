<?php
require_once __DIR__ . '/BaseModel.php';

class Disponibilidade extends BaseModel {

    // =========================================================================
    // 1. GESTÃO DA "CAPA" (AS GRADES)
    // =========================================================================

    /**
     * Cria uma nova grade de horários para o funcionário.
     */
    public function criarNovaGrade($cod_funcionario, $nome_grade) {
        $sql = "INSERT INTO disponibilidade (cod_funcionario, nome_grade, is_ativa) 
                VALUES (:cod_funcionario, :nome_grade, 0)";
                
        return $this->executarQuery($sql, [
            ':cod_funcionario' => $cod_funcionario,
            ':nome_grade' => $nome_grade
        ], 'id');
    }

    /**
     * Atualiza apenas o nome de uma grade existente.
     */
    public function atualizarNomeGrade($id_disponibilidade, $nome_grade) {
        $sql = "UPDATE disponibilidade SET nome_grade = :nome WHERE id_disponibilidade = :id";
        return $this->executarQuery($sql, [':nome' => $nome_grade, ':id' => $id_disponibilidade]);
    }

    /**
     * ARQUITETURA: Transação de Estado (State Swap)
     * Garante que apenas UMA grade está ativa. Zera todas e ativa apenas a escolhida.
     */
    public function definirGradeAtiva($cod_funcionario, $id_disponibilidade) {
        try {
            $this->conn->beginTransaction();

            // 1. Desativa todas as grades deste funcionário
            $sqlDesativar = "UPDATE disponibilidade SET is_ativa = 0 WHERE cod_funcionario = :cod_funcionario";
            $stmtDesativar = $this->conn->prepare($sqlDesativar);
            $stmtDesativar->execute([':cod_funcionario' => $cod_funcionario]);

            // 2. Ativa a grade selecionada
            $sqlAtivar = "UPDATE disponibilidade SET is_ativa = 1 WHERE id_disponibilidade = :id_disponibilidade AND cod_funcionario = :cod_funcionario";
            $stmtAtivar = $this->conn->prepare($sqlAtivar);
            $stmtAtivar->execute([
                ':id_disponibilidade' => $id_disponibilidade,
                ':cod_funcionario' => $cod_funcionario
            ]);

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Erro ao alterar grade ativa: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca todas as grades criadas pelo funcionário (para mostrar no Dropdown/Lista da View).
     */
    public function buscarGradesFuncionario($cod_funcionario) {
        $sql = "SELECT * FROM disponibilidade WHERE cod_funcionario = :cod_funcionario ORDER BY is_ativa DESC, data_criacao DESC";
        return $this->executarQuery($sql, [':cod_funcionario' => $cod_funcionario], 'todos');
    }

    /**
     * Busca a grade que está a ditar as regras atualmente.
     */
    public function buscarGradeAtiva($cod_funcionario) {
        $sql = "SELECT id_disponibilidade, nome_grade 
                FROM disponibilidade 
                WHERE cod_funcionario = :cod_funcionario AND is_ativa = 1";
                
        return $this->executarQuery($sql, [':cod_funcionario' => $cod_funcionario], 'unico');
    }

    // =========================================================================
    // 2. GESTÃO DOS DIAS (O CONTEÚDO DA GRADE)
    // =========================================================================

    /**
     * O método de Upsert Dinâmico.
     */
    public function salvarDiaConfigurado($cod_disponibilidade, $dia_semana, $dadosDia) {
        $sqlBusca = "SELECT id_dia FROM disponibilidade_dias 
                     WHERE cod_disponibilidade = :cod AND dia_semana = :dia";
        
        $existe = $this->executarQuery($sqlBusca, [':cod' => $cod_disponibilidade, ':dia' => $dia_semana], 'todos');

        $camposUpdate = [];
        $colunasInsert = ['cod_disponibilidade', 'dia_semana'];
        $bindsInsert = [':cod', ':dia'];
        $valores = [];

        foreach ($dadosDia as $coluna => $valor) {
            $camposUpdate[] = "{$coluna} = :{$coluna}";
            $colunasInsert[] = $coluna;
            $bindsInsert[] = ":{$coluna}";
            $valores[":{$coluna}"] = $valor;
        }

        if (!empty($existe)) {
            $valores[':id_dia'] = $existe[0]['id_dia'];
            $sql = "UPDATE disponibilidade_dias SET " . implode(', ', $camposUpdate) . " WHERE id_dia = :id_dia";
            return $this->executarQuery($sql, $valores);
        } else {
            $valores[':cod'] = $cod_disponibilidade;
            $valores[':dia'] = $dia_semana;
            $sql = "INSERT INTO disponibilidade_dias (" . implode(', ', $colunasInsert) . ") 
                    VALUES (" . implode(', ', $bindsInsert) . ")";
            return $this->executarQuery($sql, $valores, 'id');
        }
    }

    /**
     * Busca os dias de uma grade ESPECÍFICA (para carregar os inputs na tela de edição).
     */
    public function buscarDiasDaGrade($id_disponibilidade) {
        $sql = "SELECT * FROM disponibilidade_dias WHERE cod_disponibilidade = :id_disponibilidade";
        return $this->executarQuery($sql, [':id_disponibilidade' => $id_disponibilidade], 'todos');
    }

    /**
     * O Motor de Busca: Agora exige o ID da Grade em vez do ID do funcionário,
     * garantindo que lê as horas corretas.
     */
    public function buscarGradePorDia($id_disponibilidade, $dia_semana) {
        $sql = "SELECT 
                    hora_inicio_trabalho AS hora_inicio, 
                    hora_fim_trabalho AS hora_fim, 
                    intervalo_inicio, 
                    intervalo_fim 
                FROM disponibilidade_dias
                WHERE cod_disponibilidade = :id_disponibilidade 
                  AND dia_semana = :dia_semana
                  AND status = 'disponivel'"; 
        
        $resultado = $this->executarQuery($sql, [
            ':id_disponibilidade' => $id_disponibilidade,
            ':dia_semana' => $dia_semana
        ], 'todos');
        
        return !empty($resultado) ? $resultado[0] : false;
    }

    public function excluirGrade($id_disponibilidade, $cod_funcionario) {
        $sql = "DELETE FROM disponibilidade 
                WHERE id_disponibilidade = :id AND cod_funcionario = :cod";
                
        return $this->executarQuery($sql, [
            ':id' => $id_disponibilidade,
            ':cod' => $cod_funcionario
        ]);
    }
}
?>