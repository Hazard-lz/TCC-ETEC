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
    public function cadastrarItem($cod_agendamento, $cod_sv_func, $nome_servico_registrado, $hora_inicio, $hora_fim, $preco_cobrado, $duracao_registrada) {
        $sql = "INSERT INTO itens_agendamento 
                (cod_agendamento, cod_sv_func, nome_servico_registrado, hora_inicio, hora_fim, preco_cobrado, duracao_registrada) 
                VALUES 
                (:cod_agendamento, :cod_sv_func, :nome_servico_registrado, :hora_inicio, :hora_fim, :preco_cobrado, :duracao_registrada)";
        
        return $this->executarQuery($sql, [
            ':cod_agendamento' => $cod_agendamento,
            ':cod_sv_func' => $cod_sv_func,
            ':nome_servico_registrado' => $nome_servico_registrado,
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
                       ia.nome_servico_registrado AS nome_servico, 
                       ia.hora_inicio, ia.hora_fim, ia.preco_cobrado,
                       c.cod_usuario AS cliente_cod_usuario,
                       f.cod_usuario AS funcionario_cod_usuario
                FROM agendamentos a
                INNER JOIN clientes c ON a.cod_cliente = c.id_cliente
                INNER JOIN usuarios u_cli ON c.cod_usuario = u_cli.id_usuario
                INNER JOIN itens_agendamento ia ON a.id_agendamento = ia.cod_agendamento
                INNER JOIN funcionario_servicos fs ON ia.cod_sv_func = fs.id_sv_funcionario
                INNER JOIN funcionarios f ON fs.cod_funcionario = f.id_funcionario
                INNER JOIN usuarios u_func ON f.cod_usuario = u_func.id_usuario
                WHERE a.id_agendamento = :id_agendamento";
                
        return $this->executarQuery($sql, [':id_agendamento' => $id_agendamento], 'unico');
    }

    /**
     * Histórico para a tela do Cliente.
     */
    public function listarPorCliente($id_cliente) {
        $sql = "SELECT a.id_agendamento, a.data_agendamento, a.status, 
                       ia.nome_servico_registrado AS nome_servico, ia.hora_inicio, ia.preco_cobrado,
                       u_func.nome AS funcionario_nome
                FROM agendamentos a
                INNER JOIN itens_agendamento ia ON a.id_agendamento = ia.cod_agendamento
                INNER JOIN funcionario_servicos fs ON ia.cod_sv_func = fs.id_sv_funcionario
                INNER JOIN funcionarios f ON fs.cod_funcionario = f.id_funcionario
                INNER JOIN usuarios u_func ON f.cod_usuario = u_func.id_usuario
                WHERE a.cod_cliente = :cod_cliente
                ORDER BY a.data_agendamento DESC, ia.hora_inicio DESC";
                
        return $this->executarQuery($sql, [':cod_cliente' => $id_cliente], 'todos');    
    }

    /**
     * Histórico para a tela do Funcionário (inclui busca para Admin).
     */
    public function listarHistoricoFuncionario($id_funcionario = null) {
        $sql = "SELECT a.id_agendamento, a.data_agendamento, a.status, a.cod_cliente,
                       ia.nome_servico_registrado AS nome_servico, ia.hora_inicio, ia.preco_cobrado,
                       u_cli.nome AS cliente_nome, u_cli.telefone AS cliente_telefone,
                       u_func.nome AS funcionario_nome
                FROM agendamentos a
                INNER JOIN itens_agendamento ia ON a.id_agendamento = ia.cod_agendamento
                INNER JOIN funcionario_servicos fs ON ia.cod_sv_func = fs.id_sv_funcionario
                INNER JOIN funcionarios f ON fs.cod_funcionario = f.id_funcionario
                INNER JOIN usuarios u_func ON f.cod_usuario = u_func.id_usuario
                INNER JOIN clientes c ON a.cod_cliente = c.id_cliente
                INNER JOIN usuarios u_cli ON c.cod_usuario = u_cli.id_usuario";
        
        $params = [];
        if ($id_funcionario !== null) {
            $sql .= " WHERE fs.cod_funcionario = :cod_funcionario";
            $params[':cod_funcionario'] = $id_funcionario;
        }

        $sql .= " ORDER BY a.data_agendamento DESC, ia.hora_inicio DESC";
                
        return $this->executarQuery($sql, $params, 'todos');
    }

    /**
     * Agenda diária para a tela do Funcionário/Admin.
     */
    public function listarAgendaFuncionario($id_funcionario, $data) {
        $sql = "SELECT a.id_agendamento, a.status, 
                       u_cli.nome AS cliente_nome, 
                       ia.nome_servico_registrado AS nome_servico, 
                       ia.hora_inicio, ia.hora_fim,
                       u_func.nome AS profissional_nome
                FROM agendamentos a
                INNER JOIN clientes c ON a.cod_cliente = c.id_cliente
                INNER JOIN usuarios u_cli ON c.cod_usuario = u_cli.id_usuario
                INNER JOIN itens_agendamento ia ON a.id_agendamento = ia.cod_agendamento
                INNER JOIN funcionario_servicos fs ON ia.cod_sv_func = fs.id_sv_funcionario
                INNER JOIN funcionarios f ON fs.cod_funcionario = f.id_funcionario
                INNER JOIN usuarios u_func ON f.cod_usuario = u_func.id_usuario
                WHERE (fs.cod_funcionario = :cod_funcionario OR a.cod_funcionario_criador = :cod_funcionario_criador)
                  AND a.data_agendamento = :data
                  AND a.status != 'cancelado'
                ORDER BY ia.hora_inicio ASC";
                
        return $this->executarQuery($sql, [
            ':cod_funcionario' => $id_funcionario,
            ':cod_funcionario_criador' => $id_funcionario,
            ':data' => $data
        ], 'todos');
    }

    /**
     * Busca agendamentos de um período inteiro para carregar o calendário sob demanda.
     * Utiliza BETWEEN para trazer tudo de uma vez, evitando múltiplas queries por dia.
     */
    public function listarAgendaFuncionarioPeriodo($id_funcionario, $dataInicio, $dataFim) {
        $sql = "SELECT a.id_agendamento, a.status, a.data_agendamento,
                       u_cli.nome AS cliente_nome, 
                       ia.nome_servico_registrado AS nome_servico, 
                       ia.hora_inicio, ia.hora_fim,
                       u_func.nome AS profissional_nome
                FROM agendamentos a
                INNER JOIN clientes c ON a.cod_cliente = c.id_cliente
                INNER JOIN usuarios u_cli ON c.cod_usuario = u_cli.id_usuario
                INNER JOIN itens_agendamento ia ON a.id_agendamento = ia.cod_agendamento
                INNER JOIN funcionario_servicos fs ON ia.cod_sv_func = fs.id_sv_funcionario
                INNER JOIN funcionarios f ON fs.cod_funcionario = f.id_funcionario
                INNER JOIN usuarios u_func ON f.cod_usuario = u_func.id_usuario
                WHERE (fs.cod_funcionario = :cod_funcionario OR a.cod_funcionario_criador = :cod_funcionario_criador)
                  AND a.data_agendamento BETWEEN :data_inicio AND :data_fim
                  AND a.status != 'cancelado'
                ORDER BY a.data_agendamento ASC, ia.hora_inicio ASC";
                
        return $this->executarQuery($sql, [
            ':cod_funcionario' => $id_funcionario,
            ':cod_funcionario_criador' => $id_funcionario,
            ':data_inicio' => $dataInicio,
            ':data_fim' => $dataFim
        ], 'todos');
    }

    /**
     * Busca APENAS o agendamento futuro mais próximo do cliente.
     * ARQUITETURA: O uso do LIMIT 1 e ordenação ASC garante altíssima 
     * performance, retornando só o que a página inicial precisa.
     */
    public function buscarProximoAgendamentoCliente($id_cliente) {
        $sql = "SELECT a.id_agendamento, a.data_agendamento, a.status, 
                       ia.nome_servico_registrado AS nome_servico, ia.hora_inicio,
                       u_func.nome AS funcionario_nome
                FROM agendamentos a
                INNER JOIN itens_agendamento ia ON a.id_agendamento = ia.cod_agendamento
                INNER JOIN funcionario_servicos fs ON ia.cod_sv_func = fs.id_sv_funcionario
                INNER JOIN funcionarios f ON fs.cod_funcionario = f.id_funcionario
                INNER JOIN usuarios u_func ON f.cod_usuario = u_func.id_usuario
                WHERE a.cod_cliente = :cod_cliente
                  AND a.status IN ('pendente', 'marcado')
                  AND a.data_agendamento >= CURDATE() -- Filtra apenas datas de hoje em diante
                ORDER BY a.data_agendamento ASC, ia.hora_inicio ASC
                LIMIT 1";
                
        return $this->executarQuery($sql, [':cod_cliente' => $id_cliente], 'unico');
    }

    /**
     * Conta quantos agendamentos o funcionário tem marcados para hoje.
     * ARQUITETURA: Uso do COUNT() para não carregar dados desnecessários para a memória.
     */
    public function contarAgendamentosHoje($idFuncionario) {
        $sql = "SELECT COUNT(*) as total 
                FROM agendamentos a
                INNER JOIN itens_agendamento ia ON a.id_agendamento = ia.cod_agendamento
                INNER JOIN funcionario_servicos fs ON ia.cod_sv_func = fs.id_sv_funcionario
                WHERE fs.cod_funcionario = :id_funcionario 
                  AND a.data_agendamento = CURDATE()
                  AND a.status IN ('marcado', 'concluido')";
                  
        $resultado = $this->executarQuery($sql, [':id_funcionario' => $idFuncionario], 'unico');
        return $resultado['total'] ?? 0;
    }

    /**
     * Calcula a soma do valor dos serviços concluídos no mês atual pelo funcionário.
     */
    public function calcularFaturamentoMes($idFuncionario) {
        $sql = "SELECT SUM(ia.preco_cobrado) as total 
                FROM agendamentos a
                INNER JOIN itens_agendamento ia ON a.id_agendamento = ia.cod_agendamento
                INNER JOIN funcionario_servicos fs ON ia.cod_sv_func = fs.id_sv_funcionario
                WHERE fs.cod_funcionario = :id_funcionario 
                  AND MONTH(a.data_agendamento) = MONTH(CURDATE())
                  AND YEAR(a.data_agendamento) = YEAR(CURDATE())
                  AND a.status = 'concluido'";
                  
        $resultado = $this->executarQuery($sql, [':id_funcionario' => $idFuncionario], 'unico');
        return $resultado['total'] ?? 0.00;
    }

    /**
     * Traz os próximos agendamentos (de hoje em diante) limitados a 5 para a lista rápida.
     */
    public function listarProximosAgendamentosResumo($idFuncionario, $limite = 5) {
        $sql = "SELECT a.id_agendamento, a.data_agendamento, a.status, 
                       ia.hora_inicio, ia.nome_servico_registrado AS nome_servico,
                       u_cli.nome AS cliente_nome, u_cli.telefone AS cliente_telefone
                FROM agendamentos a
                INNER JOIN itens_agendamento ia ON a.id_agendamento = ia.cod_agendamento
                INNER JOIN funcionario_servicos fs ON ia.cod_sv_func = fs.id_sv_funcionario
                INNER JOIN clientes c ON a.cod_cliente = c.id_cliente
                INNER JOIN usuarios u_cli ON c.cod_usuario = u_cli.id_usuario
                WHERE fs.cod_funcionario = :id_funcionario
                  AND a.data_agendamento >= CURDATE()
                  AND a.status IN ('pendente', 'marcado')
                ORDER BY a.data_agendamento ASC, ia.hora_inicio ASC
                LIMIT :limite";
        
        // O (int) garante que o BaseModel faz o bindValue com PDO::PARAM_INT,
        // que é obrigatório para o LIMIT funcionar em queries preparadas.
        return $this->executarQuery($sql, [
            ':id_funcionario' => $idFuncionario,
            ':limite' => (int)$limite
        ], 'todos');
    }

    // =========================================================================
    // METODOS GERAIS (Para Dashboard ADMIN)
    // =========================================================================

    public function contarAgendamentosHojeGeral() {
        $sql = "SELECT COUNT(*) as total 
                FROM agendamentos a
                WHERE a.data_agendamento = CURDATE()
                  AND a.status IN ('marcado', 'concluido')";
        $resultado = $this->executarQuery($sql, [], 'unico');
        return $resultado['total'] ?? 0;
    }

    public function calcularFaturamentoMesGeral() {
        $sql = "SELECT SUM(ia.preco_cobrado) as total 
                FROM agendamentos a
                INNER JOIN itens_agendamento ia ON a.id_agendamento = ia.cod_agendamento
                WHERE MONTH(a.data_agendamento) = MONTH(CURDATE())
                  AND YEAR(a.data_agendamento) = YEAR(CURDATE())
                  AND a.status = 'concluido'";
        $resultado = $this->executarQuery($sql, [], 'unico');
        return $resultado['total'] ?? 0.00;
    }

    public function listarProximosAgendamentosResumoGeral($limite = 5) {
        $sql = "SELECT a.id_agendamento, a.data_agendamento, a.status, 
                       ia.hora_inicio, ia.nome_servico_registrado AS nome_servico,
                       u_cli.nome AS cliente_nome, u_cli.telefone AS cliente_telefone
                FROM agendamentos a
                INNER JOIN itens_agendamento ia ON a.id_agendamento = ia.cod_agendamento
                INNER JOIN clientes c ON a.cod_cliente = c.id_cliente
                INNER JOIN usuarios u_cli ON c.cod_usuario = u_cli.id_usuario
                WHERE a.data_agendamento >= CURDATE()
                  AND a.status IN ('pendente', 'marcado')
                ORDER BY a.data_agendamento ASC, ia.hora_inicio ASC
                LIMIT :limite";
        return $this->executarQuery($sql, [':limite' => (int)$limite], 'todos');
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

    /**
     * Cancela automaticamente agendamentos pendentes cuja data já passou.
     */
    public function cancelarPendentesExpirados() {
        $sql = "UPDATE agendamentos 
                SET status = 'cancelado' 
                WHERE status = 'pendente' 
                  AND data_agendamento < CURDATE()";
        return $this->executarQuery($sql);
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

    /**
     * Busca agendamentos planejados exatamente para amanhã.
     * Utilizado cronjob das notificações OneSignal 24h.
     */
    public function buscarAgendamentosAmanha() {
        $sql = "SELECT a.id_agendamento, a.data_agendamento, a.status, 
                       ia.nome_servico_registrado AS nome_servico, ia.hora_inicio,
                       c.cod_usuario AS cliente_cod_usuario
                FROM agendamentos a
                INNER JOIN clientes c ON a.cod_cliente = c.id_cliente
                INNER JOIN itens_agendamento ia ON a.id_agendamento = ia.cod_agendamento
                WHERE a.data_agendamento = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                  AND a.status IN ('pendente', 'marcado')";
                  
        return $this->executarQuery($sql, [], 'todos');
    }

    // =========================================================================
    // RELATÓRIO DE DESEMPENHO
    // =========================================================================

    /**
     * Retorna métricas gerais de desempenho de um funcionário num período.
     */
    public function relatorioDesempenho($idFuncionario, $dataInicio, $dataFim) {
        $sql = "SELECT 
                    SUM(CASE WHEN a.status = 'concluido' THEN 1 ELSE 0 END) AS total_concluidos,
                    SUM(CASE WHEN a.status = 'concluido' THEN ia.preco_cobrado ELSE 0 END) AS faturamento_bruto,
                    SUM(CASE WHEN a.status = 'cancelado' THEN 1 ELSE 0 END) AS total_cancelados,
                    COUNT(a.id_agendamento) AS total_geral
                FROM agendamentos a
                INNER JOIN itens_agendamento ia ON a.id_agendamento = ia.cod_agendamento
                INNER JOIN funcionario_servicos fs ON ia.cod_sv_func = fs.id_sv_funcionario
                WHERE fs.cod_funcionario = :id_func
                  AND a.data_agendamento BETWEEN :data_inicio AND :data_fim";

        return $this->executarQuery($sql, [
            ':id_func' => $idFuncionario,
            ':data_inicio' => $dataInicio,
            ':data_fim' => $dataFim
        ], 'unico');
    }

    /**
     * Retorna ranking de serviços prestados pelo funcionário no período.
     */
    public function relatorioServicosPorFuncionario($idFuncionario, $dataInicio, $dataFim) {
        $sql = "SELECT ia.nome_servico_registrado AS nome_servico,
                       COUNT(*) AS quantidade
                FROM agendamentos a
                INNER JOIN itens_agendamento ia ON a.id_agendamento = ia.cod_agendamento
                INNER JOIN funcionario_servicos fs ON ia.cod_sv_func = fs.id_sv_funcionario
                WHERE fs.cod_funcionario = :id_func
                  AND a.data_agendamento BETWEEN :data_inicio AND :data_fim
                  AND a.status = 'concluido'
                GROUP BY ia.nome_servico_registrado
                ORDER BY quantidade DESC";

        return $this->executarQuery($sql, [
            ':id_func' => $idFuncionario,
            ':data_inicio' => $dataInicio,
            ':data_fim' => $dataFim
        ], 'todos');
    }

    /**
     * Retorna lista de clientes atendidos pelo funcionário e a frequência.
     */
    public function relatorioClientesPorFuncionario($idFuncionario, $dataInicio, $dataFim) {
        $sql = "SELECT u.nome AS cliente_nome,
                       COUNT(*) AS frequencia
                FROM agendamentos a
                INNER JOIN clientes c ON a.cod_cliente = c.id_cliente
                INNER JOIN usuarios u ON c.cod_usuario = u.id_usuario
                INNER JOIN itens_agendamento ia ON a.id_agendamento = ia.cod_agendamento
                INNER JOIN funcionario_servicos fs ON ia.cod_sv_func = fs.id_sv_funcionario
                WHERE fs.cod_funcionario = :id_func
                  AND a.data_agendamento BETWEEN :data_inicio AND :data_fim
                  AND a.status = 'concluido'
                GROUP BY c.id_cliente, u.nome
                ORDER BY frequencia DESC";

        return $this->executarQuery($sql, [
            ':id_func' => $idFuncionario,
            ':data_inicio' => $dataInicio,
            ':data_fim' => $dataFim
        ], 'todos');
    }
}