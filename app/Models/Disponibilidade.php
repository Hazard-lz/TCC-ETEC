<?php
require_once __DIR__ . '/BaseModel.php';

class Disponibilidade extends BaseModel {

    // Cria a ligação inicial do funcionário com a tabela de disponibilidade
    public function cadastrar($cod_funcionario) {
        $sql = "INSERT INTO disponibilidade (cod_funcionario) VALUES (:cod_funcionario)";
        return $this->executarQuery($sql, [':cod_funcionario' => $cod_funcionario], 'id');
    }

    // =========================================================================
    // ARQUITETURA: UPSERT DINÂMICO (UPDATE OR INSERT)
    // Este método recebe apenas os dados que precisam de ser guardados e monta a query
    // dinamicamente. Se o dia já existe, faz UPDATE. Se não, faz INSERT.
    // =========================================================================
    public function salvarDiaConfigurado($cod_disponibilidade, $dia_semana, $dadosDia) {
        // 1. Verifica se esse dia já está registado para esta grelha
        $sqlBusca = "SELECT id_dia FROM disponibilidade_dias 
                     WHERE cod_disponibilidade = :cod AND dia_semana = :dia";
        
        $existe = $this->executarQuery($sqlBusca, [
            ':cod' => $cod_disponibilidade, 
            ':dia' => $dia_semana
        ], 'todos');

        // 2. Montagem dinâmica dos campos para a Query
        $camposUpdate = [];
        $colunasInsert = ['cod_disponibilidade', 'dia_semana'];
        $bindsInsert = [':cod', ':dia'];
        $valores = [];

        foreach ($dadosDia as $coluna => $valor) {
            // Prepara para o UPDATE
            $camposUpdate[] = "{$coluna} = :{$coluna}";
            
            // Prepara para o INSERT
            $colunasInsert[] = $coluna;
            $bindsInsert[] = ":{$coluna}";
            
            // Alimenta o array de valores
            $valores[":{$coluna}"] = $valor;
        }

        if (!empty($existe)) {
            // Se o dia já existe, faz o UPDATE dinâmico mantendo o ID original intacto
            $valores[':id_dia'] = $existe[0]['id_dia'];
            $sql = "UPDATE disponibilidade_dias SET " . implode(', ', $camposUpdate) . " WHERE id_dia = :id_dia";
            
            return $this->executarQuery($sql, $valores);
        } else {
            // Se o dia não existe, faz o INSERT dinâmico
            $valores[':cod'] = $cod_disponibilidade;
            $valores[':dia'] = $dia_semana;
            
            $sql = "INSERT INTO disponibilidade_dias (" . implode(', ', $colunasInsert) . ") 
                    VALUES (" . implode(', ', $bindsInsert) . ")";
            
            return $this->executarQuery($sql, $valores, 'id');
        }
    }

    // Busca TODOS os dias (ativos e inativos) para preencher o formulário na View de edição
    public function buscarPorFuncionario($cod_funcionario) {
        $sql = "SELECT d.id_disponibilidade, dd.* FROM disponibilidade d
                INNER JOIN disponibilidade_dias dd ON d.id_disponibilidade = dd.cod_disponibilidade
                WHERE d.cod_funcionario = :cod_funcionario";
        return $this->executarQuery($sql, [':cod_funcionario' => $cod_funcionario], 'todos');
    }

    // =========================================================================
    // O MOTOR DE BUSCA COM EXCLUSÃO LÓGICA
    // Utilizado pelo sistema de agendamentos para saber os horários livres.
    // Aqui nós filtramos e SÓ retornamos se o status for 'disponivel'.
    // =========================================================================
    public function buscarGradePorDia($cod_funcionario, $dia_semana) {
        $sql = "SELECT 
                    dd.hora_inicio_trabalho AS hora_inicio, 
                    dd.hora_fim_trabalho AS hora_fim, 
                    dd.intervalo_inicio, 
                    dd.intervalo_fim 
                FROM disponibilidade d
                INNER JOIN disponibilidade_dias dd ON d.id_disponibilidade = dd.cod_disponibilidade
                WHERE d.cod_funcionario = :cod_funcionario 
                  AND dd.dia_semana = :dia_semana
                  AND dd.status = 'disponivel'"; 
        
        $parametros = [
            ':cod_funcionario' => $cod_funcionario,
            ':dia_semana' => $dia_semana
        ];

        $resultado = $this->executarQuery($sql, $parametros, 'todos');
        return !empty($resultado) ? $resultado[0] : false;
    }

    // Exclusão Lógica da grelha inteira (Inativação em vez de DELETE físico)
    public function inativarDias($cod_disponibilidade) {
        $sql = "UPDATE disponibilidade_dias SET status = 'indisponivel' WHERE cod_disponibilidade = :id";
        return $this->executarQuery($sql, [':id' => $cod_disponibilidade]);
    }
}
?>