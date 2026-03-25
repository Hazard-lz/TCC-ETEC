<?php

require_once __DIR__ . '/../Models/Disponibilidade.php';
require_once __DIR__ . '/../Models/Servico.php'; 
require_once __DIR__ . '/../Models/Agendamento.php'; 

class DisponibilidadeService {

    private $disponibilidadeModel;
    private $agendamentoModel; // Adicionado

    public function __construct() {
        $this->disponibilidadeModel = new Disponibilidade();
        $this->agendamentoModel = new Agendamento(); // Injeção da dependência
    }

    // =========================================================================
    // ARQUITETURA: DRY (Don't Repeat Yourself)
    // Centraliza a validação rigorosa de tempos.
    // =========================================================================
    private function validarLogicaDeHorarios($inicio, $fim, $int_inicio, $int_fim) {
        if (empty($inicio) || empty($fim)) {
            return ['sucesso' => false, 'mensagem' => 'A hora de início e fim do expediente são obrigatórias.'];
        }

        $tempoInicio = strtotime($inicio);
        $tempoFim = strtotime($fim);

        if ($tempoFim <= $tempoInicio) {
            return ['sucesso' => false, 'mensagem' => 'A hora de saída deve ser MAIOR que a hora de entrada.'];
        }

        if ((empty($int_inicio) && !empty($int_fim)) || (!empty($int_inicio) && empty($int_fim))) {
            return ['sucesso' => false, 'mensagem' => 'Se houver intervalo, preencha o início e o fim do mesmo.'];
        }

        if (!empty($int_inicio) && !empty($int_fim)) {
            $tempoIntInicio = strtotime($int_inicio);
            $tempoIntFim = strtotime($int_fim);

            if ($tempoIntFim <= $tempoIntInicio) {
                return ['sucesso' => false, 'mensagem' => 'O fim do intervalo deve ser DEPOIS do início do intervalo.'];
            }

            if ($tempoIntInicio <= $tempoInicio || $tempoIntFim >= $tempoFim) {
                return ['sucesso' => false, 'mensagem' => 'O horário de intervalo deve estar DENTRO do horário de expediente.'];
            }
        }

        return ['sucesso' => true];
    }

    // =========================================================================
    // ARQUITETURA: UNIFICAÇÃO (INSERT E UPDATE JUNTOS)
    // =========================================================================
    public function salvarGrade($idFuncionario, $idDisponibilidade, $diasConfigurados) {
        if (empty($idFuncionario)) { return ['sucesso' => false, 'mensagem' => 'ID do funcionário não informado.']; }
        if (empty($diasConfigurados) || !is_array($diasConfigurados)) { return ['sucesso' => false, 'mensagem' => 'Nenhum dia configurado.']; }

        if (empty($idDisponibilidade)) {
            $idDisponibilidade = $this->disponibilidadeModel->cadastrar($idFuncionario);
            if (!$idDisponibilidade) {
                return ['sucesso' => false, 'mensagem' => 'Erro interno ao criar a grade do funcionário.'];
            }
        }

        foreach ($diasConfigurados as $dia => $tempos) {
            if ($tempos['status'] === 'disponivel') {
                $validacao = $this->validarLogicaDeHorarios($tempos['inicio'], $tempos['fim'], $tempos['int_inicio'], $tempos['int_fim']);
                if (!$validacao['sucesso']) {
                    return ['sucesso' => false, 'mensagem' => "Erro no dia {$dia}: " . $validacao['mensagem']];
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

        return ['sucesso' => true, 'mensagem' => 'Grade de horários salva com sucesso!'];
    }

    public function excluirGrade($id_disponibilidade) {
        if (empty($id_disponibilidade)) { return ['sucesso' => false, 'mensagem' => 'ID da grade não informado.']; }
        
        $excluiu = $this->disponibilidadeModel->inativarDias($id_disponibilidade);
        
        return $excluiu ? 
            ['sucesso' => true, 'mensagem' => 'A sua grade inteira foi marcada como indisponível.'] : 
            ['sucesso' => false, 'mensagem' => 'Erro ao tentar inativar a grade de horários.'];
    }

    // =========================================================================
    // MOTOR DE CÁLCULO DE HORÁRIOS LIVRES (INTEGRAÇÃO CONCLUÍDA)
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

        // 1. Busca os horários teóricos do funcionário (a grelha de trabalho pura)
        $gradeDoDia = $this->disponibilidadeModel->buscarGradePorDia($idFuncionario, $diaSemanaStr);

        if (!$gradeDoDia) {
            return []; // O funcionário não trabalha neste dia
        }

        // 2. Fatiamos o dia de trabalho em blocos baseados na duração do serviço
        $slotsPossiveis = $this->gerarSlotsPossiveis($gradeDoDia, $duracaoServicoMinutos);

        // 3. INTEGRAÇÃO REAL: Busca o que já está marcado na base de dados para este funcionário neste dia
        // A função 'listarAgendaFuncionario' já devolve apenas os agendamentos ativos (não cancelados)
        $agendamentosDoDia = $this->agendamentoModel->listarAgendaFuncionario($idFuncionario, $dataDesejada);
        
        // Se a query retornar falso/vazio, garantimos que é um array vazio para o foreach seguinte não quebrar
        if (!$agendamentosDoDia) {
            $agendamentosDoDia = [];
        }

        // 4. Removemos os blocos que colidem com os agendamentos reais
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

        // PORQUE: Vai saltando de bloco em bloco (Ex: 09:00, 09:30, 10:00) 
        // consoante a duração do serviço selecionado pelo cliente.
        while (($ponteiroAtual + ($duracaoMinutos * 60)) <= $fimExpediente) {
            $horaDoSlot = date('H:i', $ponteiroAtual);
            $fimDoSlot = $ponteiroAtual + ($duracaoMinutos * 60);
            $conflitoComIntervalo = false;

            if ($temIntervalo) {
                // Lógica de interseção para garantir que um serviço de 1 hora não comece 
                // 30 minutos antes do intervalo de almoço do funcionário.
                if (($ponteiroAtual >= $inicioIntervalo && $ponteiroAtual < $fimIntervalo) || 
                    ($fimDoSlot > $inicioIntervalo && $fimDoSlot <= $fimIntervalo) ||
                    ($ponteiroAtual <= $inicioIntervalo && $fimDoSlot >= $fimIntervalo)) {
                    
                    $conflitoComIntervalo = true;
                    $ponteiroAtual = $fimIntervalo; // Salta diretamente para o regresso do almoço
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
            // Regra de Negócio: Não deixar marcar para horários que já passaram no dia de hoje
            if ($isHoje && $slot <= $horaAtual) { continue; }

            $conflito = false;
            $inicioNovo = strtotime($slot);
            $fimNovo = strtotime($slot) + ($duracaoServico * 60);

            foreach ($agendamentos as $agendado) {
                $inicioAgendado = strtotime($agendado['hora_inicio']);
                $fimAgendado = strtotime($agendado['hora_fim']);

                // ARQUITETURA: Detenção de Colisão (Bounding Box)
                // A mesma fórmula matemática usada no SQL do Model, agora aplicada no PHP em memória.
                // Se (InícioA < FimB) E (FimA > InícioB) -> Ocorre uma sobreposição de tempo.
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
}
?>