<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../Models/Disponibilidade.php';
require_once __DIR__ . '/../Models/Servico.php'; 
require_once __DIR__ . '/../Models/Agendamento.php'; 

class DisponibilidadeService extends BaseService {

    private $disponibilidadeModel;
    private $agendamentoModel;

    public function __construct() {
        $this->disponibilidadeModel = new Disponibilidade();
        $this->agendamentoModel = new Agendamento(); 
    }

    private function validarLogicaDeHorarios($inicio, $fim, $int_inicio, $int_fim) {
        if (empty($inicio) || empty($fim)) {
            return $this->erro('A hora de início e fim do expediente são obrigatórias.');
        }

        $tempoInicio = strtotime($inicio);
        $tempoFim = strtotime($fim);

        if ($tempoFim <= $tempoInicio) {
            return $this->erro('A hora de saída deve ser MAIOR que a hora de entrada.');
        }

        if ((empty($int_inicio) && !empty($int_fim)) || (!empty($int_inicio) && empty($int_fim))) {
            return $this->erro('Se houver intervalo, preencha o início e o fim do mesmo.');
        }

        if (!empty($int_inicio) && !empty($int_fim)) {
            $tempoIntInicio = strtotime($int_inicio);
            $tempoIntFim = strtotime($int_fim);

            if ($tempoIntFim <= $tempoIntInicio) {
                return $this->erro('O fim do intervalo deve ser DEPOIS do início do intervalo.');
            }

            if ($tempoIntInicio <= $tempoInicio || $tempoIntFim >= $tempoFim) {
                return $this->erro('O horário de intervalo deve estar DENTRO do horário de expediente.');
            }
        }

        return ['sucesso' => true];
    }

    // =========================================================================
    // ARQUITETURA: Orquestração de Grades (Criação/Atualização)
    // =========================================================================
    public function salvarGrade($idFuncionario, $idDisponibilidade, $nomeGrade, $isAtiva, $diasConfigurados) {
        if (empty($idFuncionario)) { return $this->erro('ID do funcionário não informado.'); }
        if (empty($nomeGrade)) { return $this->erro('O nome da grade é obrigatório.'); }
        if (empty($diasConfigurados) || !is_array($diasConfigurados)) { return $this->erro('Nenhum dia configurado.'); }

        // ---------------------------------------------------------------------
        // VALIDAÇÕES DEFENSIVAS (PREVENÇÃO DE PAYLOAD INJECTION)
        // ---------------------------------------------------------------------
        
        // 1. Limite de Dias: Uma semana só tem 7 dias. Impede que arrays gigantes sobrecarreguem o loop.
        if (count($diasConfigurados) > 7) {
            return $this->erro('Tentativa inválida. Uma grade não pode conter mais de 7 dias.');
        }

        // 2. Whitelist de Dias: Garante que as chaves do array batem exatamente com o ENUM do MySQL.
        $diasPermitidos = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'];
        foreach (array_keys($diasConfigurados) as $diaRecebido) {
            if (!in_array($diaRecebido, $diasPermitidos)) {
                return $this->erro("O dia '{$diaRecebido}' não é válido para a grade de horários.");
            }
        }
        // ---------------------------------------------------------------------

        // 1. Se não houver ID, é uma grade nova. Criamos a "Capa".
        if (empty($idDisponibilidade)) {
            $idDisponibilidade = $this->disponibilidadeModel->criarNovaGrade($idFuncionario, $nomeGrade);
            if (!$idDisponibilidade) {
                return $this->erro('Erro interno ao criar a nova grade.');
            }
        } else {
            // Se já existe, apenas atualizamos o nome por precaução
            $this->disponibilidadeModel->atualizarNomeGrade($idDisponibilidade, $nomeGrade);
        }

        // 2. Se o utilizador marcou a checkbox para ativar esta grade agora
        if ($isAtiva) {
            $this->disponibilidadeModel->definirGradeAtiva($idFuncionario, $idDisponibilidade);
        }

        // 3. Salva os dias da semana associados a esta Grade
        foreach ($diasConfigurados as $dia => $tempos) {
            if ($tempos['status'] === 'disponivel') {
                $validacao = $this->validarLogicaDeHorarios($tempos['inicio'], $tempos['fim'], $tempos['int_inicio'], $tempos['int_fim']);
                if (!$validacao['sucesso']) {
                    return $this->erro("Erro na {$dia}: " . $validacao['mensagem']);
                }
            } else {
                if (empty($tempos['inicio'])) { $tempos['inicio'] = '00:00'; }
                if (empty($tempos['fim'])) { $tempos['fim'] = '00:00'; }
            }

            $int_inicio = empty(trim($tempos['int_inicio'])) ? null : $tempos['int_inicio'];
            $int_fim = empty(trim($tempos['int_fim'])) ? null : $tempos['int_fim'];

            $dadosDia = [
                'hora_inicio_trabalho' => $tempos['inicio'],
                'hora_fim_trabalho' => $tempos['fim'],
                'intervalo_inicio' => $int_inicio,
                'intervalo_fim' => $int_fim,
                'status' => $tempos['status']
            ];

            $this->disponibilidadeModel->salvarDiaConfigurado($idDisponibilidade, $dia, $dadosDia);
        }

        return $this->sucesso('Grade de horários salva com sucesso!');
    }

    /**
     * Alterna a grade ativa via clique num botão da View.
     */
    public function ativarGrade($idFuncionario, $idDisponibilidade) {
        $ativou = $this->disponibilidadeModel->definirGradeAtiva($idFuncionario, $idDisponibilidade);
        return $ativou ? 
            $this->sucesso('Grade ativada com sucesso.') : 
            $this->erro('Falha ao ativar a grade.');
    }


    // =========================================================================
    // MOTOR DE CÁLCULO DE HORÁRIOS LIVRES (ATUALIZADO PARA LER APENAS A ATIVA)
    // =========================================================================
    public function calcularHorariosLivres($idFuncionario, $dataDesejada, $idServico) {
        
        $servicoModel = new Servico();
        $dadosServico = $servicoModel->buscarPorId($idServico);

        if (!$dadosServico) {
            throw new Exception("Serviço selecionado não é válido ou não existe.");
        }

        $duracaoServicoMinutos = (int) $dadosServico['duracao'];

        $data = new DateTime($dataDesejada);
        $mapaDias = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'];
        $diaSemanaStr = $mapaDias[$data->format('w')];

        // 1. O NÚCLEO DA ATUALIZAÇÃO: Descobrimos qual a grade que manda hoje
        $gradeAtiva = $this->disponibilidadeModel->buscarGradeAtiva($idFuncionario);
        
        if (!$gradeAtiva) {
            return []; // O funcionário não tem nenhuma grade ativa, logo não trabalha.
        }

        // 2. Busca os horários teóricos DENTRO DESSA GRADE ESPECÍFICA
        $gradeDoDia = $this->disponibilidadeModel->buscarGradePorDia($gradeAtiva['id_disponibilidade'], $diaSemanaStr);

        if (!$gradeDoDia) {
            return []; // Folga
        }

        // 3. Fatiamento em slots e verificação de conflitos reais
        $slotsPossiveis = $this->gerarSlotsPossiveis($gradeDoDia, $duracaoServicoMinutos);

        $agendamentosDoDia = $this->agendamentoModel->listarAgendaFuncionario($idFuncionario, $dataDesejada);
        
        if (!is_array($agendamentosDoDia)) {
            $agendamentosDoDia = [];
        }

        $horariosLivres = $this->filtrarHorariosValidos($slotsPossiveis, $agendamentosDoDia, $dataDesejada, $duracaoServicoMinutos);

        return array_values($horariosLivres);
    }

    private function gerarSlotsPossiveis($grade, $duracaoMinutos) {
        $slots = [];
        $inicioExpediente = strtotime($grade['hora_inicio']);
        $fimExpediente = strtotime($grade['hora_fim']);
        
        $temIntervalo = !empty($grade['intervalo_inicio']) && !empty($grade['intervalo_fim']);
        $inicioIntervalo = $temIntervalo ? strtotime($grade['intervalo_inicio']) : null;
        $fimIntervalo = $temIntervalo ? strtotime($grade['intervalo_fim']) : null;

        $ponteiroAtual = $inicioExpediente;

        while (($ponteiroAtual + ($duracaoMinutos * 60)) <= $fimExpediente) {
            $horaDoSlot = date('H:i', $ponteiroAtual);
            $fimDoSlot = $ponteiroAtual + ($duracaoMinutos * 60);
            $conflitoComIntervalo = false;

            if ($temIntervalo) {
                if (($ponteiroAtual >= $inicioIntervalo && $ponteiroAtual < $fimIntervalo) || 
                    ($fimDoSlot > $inicioIntervalo && $fimDoSlot <= $fimIntervalo) ||
                    ($ponteiroAtual <= $inicioIntervalo && $fimDoSlot >= $fimIntervalo)) {
                    
                    $conflitoComIntervalo = true;
                    $ponteiroAtual = $fimIntervalo; 
                }
            }

            if (!$conflitoComIntervalo) {
                $slots[] = $horaDoSlot;
                $ponteiroAtual += ($duracaoMinutos * 60); 
            }
        }
        return $slots;
    }

    private function filtrarHorariosValidos($slots, $agendamentos, $dataDesejada, $duracaoServico) {
        $hoje = date('Y-m-d');
        $horaAtual = date('H:i');
        $isHoje = ($dataDesejada === $hoje);
        $horariosFiltrados = [];

        foreach ($slots as $slot) {
            if ($isHoje && $slot <= $horaAtual) { continue; }

            $conflito = false;
            $inicioNovo = strtotime($slot);
            $fimNovo = strtotime($slot) + ($duracaoServico * 60);

            foreach ($agendamentos as $agendado) {
                $inicioAgendado = strtotime($agendado['hora_inicio']);
                $fimAgendado = strtotime($agendado['hora_fim']);

                if ($inicioNovo < $fimAgendado && $fimNovo > $inicioAgendado) {
                    $conflito = true;
                    break; 
                }
            }

            if (!$conflito) {
                $horariosFiltrados[] = $slot;
            }
        }
        return $horariosFiltrados;
    }

    /**
     * ARQUITETURA: Remoção de Grade com Trava de Segurança
     */
    public function excluirGrade($idDisponibilidade, $idFuncionario) {
        if (empty($idDisponibilidade)) { 
            return $this->erro('ID da grade não informado.'); 
        }
        
        // 1. TRAVA DE SEGURANÇA: Verifica se a grade que ele quer apagar é a ativa
        $gradeAtual = $this->disponibilidadeModel->buscarGradeAtiva($idFuncionario);
        
        if ($gradeAtual && $gradeAtual['id_disponibilidade'] == $idDisponibilidade) {
            return $this->erro('Você não pode excluir a grade que está ativa no momento. Ative outra grade de horários primeiro para poder excluir esta.');
        }

        // 2. Se não for a ativa, prossegue com o Hard Delete
        $excluiu = $this->disponibilidadeModel->excluirGrade($idDisponibilidade, $idFuncionario);
        
        return $excluiu ? 
            $this->sucesso('A grade foi permanentemente excluída.') : 
            $this->erro('Erro ao tentar excluir a grade de horários.');
    }
}