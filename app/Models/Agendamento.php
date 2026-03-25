<?php
require_once __DIR__ . '/BaseModel.php';

class Agendamento extends BaseModel {

    // =========================================================================
    // 1. CREATE
    // =========================================================================

    /**
     * Regista a capa do agendamento.
     * Mapeamento exato do Schema: id_agendamento, cod_cliente, cod_funcionario_criador, 
     * data_agendamento, data_criacao (automático) e status.
     */
    public function cadastrarAgendamento($cod_cliente, $cod_funcionario_criador, $data_agendamento, $status = 'pendente') {
        $sql = "INSERT INTO agendamentos (cod_cliente, cod_funcionario_criador, data_agendamento, status) 
                VALUES (:cod_cliente, :cod_funcionario_criador, :data_agendamento, :status)";
        
        return $this->executarQuery($sql, [
            ':cod_cliente' => $cod_cliente,
            ':cod_funcionario_criador' => $cod_funcionario_criador,
            ':data_agendamento' => $data_agendamento,
            ':status' => $status
        ], 'id');
    }

    /**
     * Adiciona o item ao agendamento.
     * Mapeamento exato do Schema: itens_agendamento.
     */
    public function cadastrarItem($cod_agendamento, $cod_sv_func, $hora_inicio, $hora_fim, $preco_cobrado, $duracao_registrada) {
        $sql = "INSERT INTO itens_agendamento 
                (cod_agendamento, cod_sv_func, hora_inicio, hora_fim, preco_cobrado, duracao_registrada) 
                VALUES 
                (:cod_agendamento, :cod_sv_func, :hora_inicio, :hora_fim, :preco_cobrado, :duracao_registrada)";
        
        return $this->executarQuery($sql, [
            ':cod_agendamento' => $cod_agendamento,
            ':cod_sv_func' => $cod_sv_func,
            ':hora_inicio' => $hora_inicio,
            ':hora_fim' => $hora_fim,
            ':preco_cobrado' => $preco_cobrado,
            ':duracao_registrada' => $duracao_registrada
        ]);
    }

    // =========================================================================
    // 2. READ (Listagens Complexas com JOINs)
    // =========================================================================

    /**
     * Traz os detalhes completos de um agendamento específico.
     * ARQUITETURA: Aqui fazemos a "ponte" entre as tabelas normalizadas.
     * Como o agendamento aponta para 'clientes', precisamos ir de clientes até 'usuarios' para pegar o nome.
     */
    public function buscarPorId($id_agendamento) {
        $sql = "SELECT a.*, 
                       u_cli.nome AS cliente_nome, 
                       u_func.nome AS funcionario_nome, 
                       s.nome_servico, 
                       ia.hora_inicio, ia.hora_fim, ia.preco_cobrado
                FROM agendamentos a
                INNER JOIN clientes c ON a.cod_cliente = c.id_cliente
                INNER JOIN usuarios u_cli ON c.cod_usuario = u_cli.id_usuario
                INNER JOIN itens_agendamento ia ON a.id_agendamento = ia.cod_agendamento
                INNER JOIN funcionario_servicos fs ON ia.cod_sv_func = fs.id_sv_funcionario
                INNER JOIN funcionarios f ON fs.cod_funcionario = f.id_funcionario
                INNER JOIN usuarios u_func ON f.cod_usuario = u_func.id_usuario
                INNER JOIN servicos s ON fs.cod_servico = s.id_servico
                WHERE a.id_agendamento = :id_agendamento";
                
        return $this->executarQuery($sql, [':id_agendamento' => $id_agendamento], 'unico');
    }

    /**
     * Histórico para a tela do Cliente.
     */
    public function listarPorCliente($id_cliente) {
        $sql = "SELECT a.id_agendamento, a.data_agendamento, a.status, 
                       s.nome_servico, ia.hora_inicio, ia.preco_cobrado
                FROM agendamentos a
                INNER JOIN itens_agendamento ia ON a.id_agendamento = ia.cod_agendamento
                INNER JOIN funcionario_servicos fs ON ia.cod_sv_func = fs.id_sv_funcionario
                INNER JOIN servicos s ON fs.cod_servico = s.id_servico
                WHERE a.cod_cliente = :cod_cliente
                ORDER BY a.data_agendamento DESC, ia.hora_inicio DESC";
                
        return $this->executarQuery($sql, [':cod_cliente' => $id_cliente]);
    }

    /**
     * Agenda diária para a tela do Funcionário/Admin.
     */
    public function listarAgendaFuncionario($id_funcionario, $data) {
        $sql = "SELECT a.id_agendamento, a.status, 
                       u_cli.nome AS cliente_nome, 
                       s.nome_servico, 
                       ia.hora_inicio, ia.hora_fim
                FROM agendamentos a
                INNER JOIN clientes c ON a.cod_cliente = c.id_cliente
                INNER JOIN usuarios u_cli ON c.cod_usuario = u_cli.id_usuario
                INNER JOIN itens_agendamento ia ON a.id_agendamento = ia.cod_agendamento
                INNER JOIN funcionario_servicos fs ON ia.cod_sv_func = fs.id_sv_funcionario
                INNER JOIN servicos s ON fs.cod_servico = s.id_servico
                WHERE fs.cod_funcionario = :cod_funcionario 
                  AND a.data_agendamento = :data
                  AND a.status != 'cancelado'
                ORDER BY ia.hora_inicio ASC";
                
        return $this->executarQuery($sql, [
            ':cod_funcionario' => $id_funcionario,
            ':data' => $data
        ]);
    }

    // =========================================================================
    // 3. UPDATE
    // =========================================================================

    public function atualizarStatus($id_agendamento, $novo_status) {
        $sql = "UPDATE agendamentos SET status = :status WHERE id_agendamento = :id";
        return $this->executarQuery($sql, [
            ':status' => $novo_status,
            ':id' => $id_agendamento
        ]);
    }

    // =========================================================================
    // UTILITÁRIOS (Validações de Regra via BD)
    // =========================================================================

    /**
     * Matemética de detecção de colisão de tempo para evitar overbooking.
     */
    public function verificarConflitoHorario($id_funcionario, $data, $hora_inicio, $hora_fim) {
        $sql = "SELECT a.id_agendamento 
                FROM agendamentos a
                INNER JOIN itens_agendamento ia ON a.id_agendamento = ia.cod_agendamento
                INNER JOIN funcionario_servicos fs ON ia.cod_sv_func = fs.id_sv_funcionario
                WHERE fs.cod_funcionario = :cod_funcionario
                  AND a.data_agendamento = :data
                  AND a.status NOT IN ('cancelado')
                  AND (ia.hora_inicio < :hora_fim AND ia.hora_fim > :hora_inicio)";
        
        return $this->executarQuery($sql, [
            ':cod_funcionario' => $id_funcionario,
            ':data' => $data,
            ':hora_inicio' => $hora_inicio,
            ':hora_fim' => $hora_fim
        ], 'unico');
    }

    /**
     * Garante que o ID retornado é válido e que o status na tabela associativa é 'ativo'.
     */
    public function buscarVinculoFuncionarioServico($id_funcionario, $id_servico) {
        $sql = "SELECT id_sv_funcionario 
                FROM funcionario_servicos 
                WHERE cod_funcionario = :cod_funcionario 
                  AND cod_servico = :cod_servico 
                  AND status = 'ativo'";
                  
        return $this->executarQuery($sql, [
            ':cod_funcionario' => $id_funcionario,
            ':cod_servico' => $id_servico
        ], 'unico');
    }
}
?>