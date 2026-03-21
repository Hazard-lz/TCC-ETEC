<?php

require_once __DIR__ . '/../Models/Disponibilidade.php';
require_once __DIR__ . '/../Models/Servico.php'; 
// require_once __DIR__ . '/../Models/Agendamento.php'; // Remove este comentário quando criares o Model de Agendamentos

class DisponibilidadeService {

    private $disponibilidadeModel;

    public function __construct() {
        $this->disponibilidadeModel = new Disponibilidade();
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
            return ['sucesso' => false, 'mensagem' => 'Se houver intervalo, preenche o início e o fim do mesmo.'];
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
    // O Model agora sabe lidar com a inserção ou atualização sozinho (Upsert).
    // O Service apenas orquestra, valida os dias e envia para o Model.
    // =========================================================================
    public function salvarGrade($idFuncionario, $idDisponibilidade, $diasConfigurados) {
        if (empty($idFuncionario)) { return ['sucesso' => false, 'mensagem' => 'ID do funcionário não informado.']; }
        if (empty($diasConfigurados) || !is_array($diasConfigurados)) { return ['sucesso' => false, 'mensagem' => 'Nenhum dia configurado.']; }

        // Se não existir a "Capa" da disponibilidade (primeiro acesso do funcionário), nós a criamos.
        if (empty($idDisponibilidade)) {
            $idDisponibilidade = $this->disponibilidadeModel->cadastrar($idFuncionario);
            if (!$idDisponibilidade) {
                return ['sucesso' => false, 'mensagem' => 'Erro interno ao criar a grade do funcionário.'];
            }
        }

        foreach ($diasConfigurados as $dia => $tempos) {
            
            // Só fazemos a validação rigorosa das horas se o dia for marcado como 'disponivel'
            if ($tempos['status'] === 'disponivel') {
                $validacao = $this->validarLogicaDeHorarios($tempos['inicio'], $tempos['fim'], $tempos['int_inicio'], $tempos['int_fim']);
                if (!$validacao['sucesso']) {
                    return ['sucesso' => false, 'mensagem' => "Erro no dia {$dia}: " . $validacao['mensagem']];
                }
            } else {
                // Se for indisponível e as horas estiverem vazias, injetamos 00:00 para não quebrar o NOT NULL da base de dados
                if (empty($tempos['inicio'])) { $tempos['inicio'] = '00:00'; }
                if (empty($tempos['fim'])) { $tempos['fim'] = '00:00'; }
            }

            // Tratamento de valores vazios para a base de dados (NULL)
            $int_inicio = empty(trim($tempos['int_inicio'])) ? null : $tempos['int_inicio'];
            $int_fim = empty(trim($tempos['int_fim'])) ? null : $tempos['int_fim'];

            // Array limpo para o Upsert do Model
            $dadosDia = [
                'hora_inicio_trabalho' => $tempos['inicio'],
                'hora_fim_trabalho' => $tempos['fim'],
                'intervalo_inicio' => $int_inicio,
                'intervalo_fim' => $int_fim,
                'status' => $tempos['status']
            ];

            // Guarda ou atualiza este dia específico
            $this->disponibilidadeModel->salvarDiaConfigurado($idDisponibilidade, $dia, $dadosDia);
        }

        return ['sucesso' => true, 'mensagem' => 'Grade de horários salva com sucesso!'];
    }

    public function excluirGrade($id_disponibilidade) {
        if (empty($id_disponibilidade)) { return ['sucesso' => false, 'mensagem' => 'ID da grade não informado.']; }
        
        // Agora chamamos o inativarDias (exclusão lógica)
        $excluiu = $this->disponibilidadeModel->inativarDias($id_disponibilidade);
        
        return $excluiu ? 
            ['sucesso' => true, 'mensagem' => 'A sua grade inteira foi marcada como indisponível.'] : 
            ['sucesso' => false, 'mensagem' => 'Erro ao tentar inativar a grade de horários.'];
    }

    // =========================================================================
    // MOTOR DE CÁLCULO DE HORÁRIOS LIVRES (O NÚCLEO DO AGENDAMENTO)
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

        // O Model já faz o filtro para só devolver se o status for 'disponivel'
        $gradeDoDia = $this->disponibilidadeModel->buscarGradePorDia($idFuncionario, $diaSemanaStr);

        if (!$gradeDoDia) {
            return []; // O funcionário está de folga ou inativo neste dia da semana
        }

        $slotsPossiveis = $this->gerarSlotsPossiveis($gradeDoDia, $duracaoServicoMinutos);

        // TODO: Substituir por consulta real no Model de Agendamentos para trazer os ocupados
        $agendamentosDoDia = []; 

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
}
?>