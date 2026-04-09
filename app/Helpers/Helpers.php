<?php

// =========================================================================
// HELPERS — Funções e constantes utilitárias centralizadas do sistema
// =========================================================================
class Helpers {

    /**
     * Array de meses em PT-BR indexado por número (1 = Janeiro, 12 = Dezembro).
     * Uso: Helpers::MESES[(int)$data->format('m')]
     */
    const MESES = [
        '', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
        'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
    ];

    /**
     * Formata uma data para exibição amigável: "24 de Março às 10:30"
     */
    public static function dataExtenso($dataStr, $horaStr = null) {
        $dataObj = new DateTime($dataStr);
        $dia = $dataObj->format('d');
        $mesNome = self::MESES[(int)$dataObj->format('m')];
        $texto = "{$dia} de {$mesNome}";

        if ($horaStr) {
            $horaFormatada = substr($horaStr, 0, 5);
            $texto .= " às {$horaFormatada}";
        }

        return $texto;
    }
}
