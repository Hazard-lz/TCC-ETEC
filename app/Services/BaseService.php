<?php

abstract class BaseService {
    
    // Padroniza as respostas de sucesso
    protected function sucesso($mensagem, $dadosExtras = []) {
        $respostaPadrao = ['sucesso' => true, 'mensagem' => $mensagem];
        return array_merge($respostaPadrao, $dadosExtras);
    }

    // Padroniza as respostas de erro
    protected function erro($mensagem) {
        return ['sucesso' => false, 'mensagem' => $mensagem];
    }
}
?>