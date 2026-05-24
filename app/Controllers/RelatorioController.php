<?php

require_once __DIR__ . '/../Models/Agendamento.php';
require_once __DIR__ . '/../Models/Funcionario.php';

class RelatorioController {

    private $agendamentoModel;
    private $funcionarioModel;

    public function __construct() {
        $this->agendamentoModel = new Agendamento();
        $this->funcionarioModel = new Funcionario();
    }

    /**
     * Action: GET /admin/relatorios/desempenho
     * Gera o relatório de desempenho de um funcionário dentro de um período.
     */
    public function desempenhoFuncionario() {
        // Segurança centralizada no Middleware — /admin/relatorios é exclusivo para 'admin'

        // Lista de funcionários para o <select> do filtro
        $listaFuncionarios = $this->funcionarioModel->listarTodos();

        // Filtros vindos da URL
        $idFuncionario = $_GET['id_funcionario'] ?? '';
        $dataInicio = $_GET['data_inicio'] ?? '';
        $dataFim = $_GET['data_fim'] ?? '';

        $metricas = null;
        $rankingServicos = [];
        $retencaoClientes = [];
        $funcionarioSelecionado = null;

        $dadosDiarios = [];

        // Só executa as queries se os filtros estiverem completos
        if (!empty($idFuncionario) && !empty($dataInicio) && !empty($dataFim)) {
            
            if ($idFuncionario === 'todos') {
                $funcionarioSelecionado = [
                    'nome' => 'Visão Geral do Salão',
                    'especialidade' => 'Todos os Profissionais'
                ];
            } else {
                $funcionarioSelecionado = $this->funcionarioModel->buscarPorId($idFuncionario);
            }

            $metricas = $this->agendamentoModel->relatorioDesempenho($idFuncionario, $dataInicio, $dataFim);
            $rankingServicos = $this->agendamentoModel->relatorioServicosPorFuncionario($idFuncionario, $dataInicio, $dataFim);
            $retencaoClientes = $this->agendamentoModel->relatorioClientesPorFuncionario($idFuncionario, $dataInicio, $dataFim);
            $dadosDiarios = $this->agendamentoModel->relatorioFaturamentoDiario($idFuncionario, $dataInicio, $dataFim);

            // Cálculos derivados
            $totalConcluidos = (int) ($metricas['total_concluidos'] ?? 0);
            $faturamentoBruto = (float) ($metricas['faturamento_bruto'] ?? 0);
            $totalCancelados = (int) ($metricas['total_cancelados'] ?? 0);
            $totalGeral = (int) ($metricas['total_geral'] ?? 0);

            $ticketMedio = ($totalConcluidos > 0) ? $faturamentoBruto / $totalConcluidos : 0;
            $taxaCancelamento = ($totalGeral > 0) ? ($totalCancelados / $totalGeral) * 100 : 0;
            $taxaConversao = ($totalGeral > 0) ? ($totalConcluidos / $totalGeral) * 100 : 0;
            $taxaAbsenteismo = $taxaCancelamento;

            // Cálculo proporcional preciso dos salários contratuais baseado nos dias reais de cada mês
            $dtInicio = new DateTime($dataInicio);
            $dtFim = new DateTime($dataFim);
            
            // Loop dia a dia no período selecionado
            $interval = new DateInterval('P1D');
            $period = new DatePeriod($dtInicio, $interval, (clone $dtFim)->modify('+1 day'));

            if ($idFuncionario === 'todos') {
                $custoTotal = 0;
                $funcionariosAtivos = array_filter($listaFuncionarios, function($f) {
                    return ($f['status'] ?? '') !== 'inativo';
                });
                
                foreach ($period as $date) {
                    $diasNoMes = (int) $date->format('t'); // Retorna a quantidade de dias no mês da data (28 a 31)
                    foreach ($funcionariosAtivos as $func) {
                        $sal = (float) ($func['salario'] ?? 0);
                        $custoTotal += $sal / $diasNoMes;
                    }
                }
                $faturamentoLiquido = $faturamentoBruto - $custoTotal;
            } else {
                $sal = (float) ($funcionarioSelecionado['salario'] ?? 0);
                $custo = 0;
                foreach ($period as $date) {
                    $diasNoMes = (int) $date->format('t');
                    $custo += $sal / $diasNoMes;
                }
                $faturamentoLiquido = $faturamentoBruto - $custo;
            }
        }

        require_once __DIR__ . '/../../public/views/admin/relatorio_desempenho.php';
    }
}
