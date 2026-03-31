<?php
require_once __DIR__ . '/BaseModel.php';

class Usuario extends BaseModel {

    // ==============================================================
    // 1. MÉTODOS DE CADASTRO E BUSCA (CRUD BÁSICO)
    // ==============================================================

// CREATE/INSERT
    public function cadastrar($nome, $email, $senha, $tipo = 'comum', $telefone = null) {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        
        // Se vier string vazia do formulário, converte para NULL
        $telefone = !empty(trim($telefone)) ? trim($telefone) : null;
        $email = !empty(trim($email)) ? trim($email) : null;

        $sql = "INSERT INTO usuarios (nome, email, senha, tipo, telefone) 
                VALUES (:nome, :email, :senha, :tipo, :telefone)";
        
        return $this->executarQuery($sql, [
            ':nome' => $nome, ':email' => $email, ':senha' => $senhaHash, 
            ':tipo' => $tipo, ':telefone' => $telefone
        ], 'id');
    }

// SELECTS
    public function buscarPorEmail($email) {
        $sql = "SELECT * FROM usuarios WHERE email = :email";
        return $this->executarQuery($sql, [':email' => $email], 'unico');
    }
    
    public function buscarPorTelefone($telefone) {
        $sql = "SELECT id_usuario FROM usuarios WHERE telefone = :telefone";
        return $this->executarQuery($sql, [':telefone' => $telefone], 'unico');
    }

    public function buscarPorTelefoneDiferenteDe($telefone, $id_usuario) {
        $sql = "SELECT id_usuario FROM usuarios WHERE telefone = :telefone AND id_usuario != :id";
        return $this->executarQuery($sql, [':telefone' => $telefone, ':id' => $id_usuario], 'unico');
    }

    public function buscarPorId($id) {
        $sql = "SELECT id_usuario, nome, email, tipo, telefone, status, data_criacao 
                FROM usuarios WHERE id_usuario = :id";
        return $this->executarQuery($sql, [':id' => $id], 'unico');
    }

// UPDATES
    public function atualizar($id_usuario, $nome, $telefone) {
        $telefone = !empty(trim($telefone)) ? trim($telefone) : null;

        $sql = "UPDATE usuarios SET nome = :nome, telefone = :telefone WHERE id_usuario = :id";
        return $this->executarQuery($sql, [':nome' => $nome, ':telefone' => $telefone, ':id' => $id_usuario]);
    }

    public function atualizarTipo($id_usuario, $tipo) {
        $sql = "UPDATE usuarios SET tipo = :tipo WHERE id_usuario = :id";
        return $this->executarQuery($sql, [':tipo' => $tipo, ':id' => $id_usuario]);
    }

    public function atualizarStatus($id_usuario, $status) {
        $sql = "UPDATE usuarios SET status = :status WHERE id_usuario = :id";
        return $this->executarQuery($sql, [':status' => $status, ':id' => $id_usuario]);
    }

    // ==============================================================
    // 2. MÉTODOS DE SEGURANÇA
    // ==============================================================

    /**
     * GENÉRICO: Salva um código de 6 dígitos.
     */
    public function salvarCodigo($id_usuario, $codigo, $minutosExpiracao = null) {
        // Se foi passado um tempo, usa o relógio do PRÓPRIO banco de dados para somar os minutos
        if ($minutosExpiracao) {
            $sql = "UPDATE usuarios SET codigo_verificacao = :codigo, expiracao_codigo = DATE_ADD(NOW(), INTERVAL :minutos MINUTE) WHERE id_usuario = :id";
            return $this->executarQuery($sql, [
                ':codigo' => $codigo, 
                ':minutos' => $minutosExpiracao, 
                ':id' => $id_usuario
            ]);
        } else {
            // Se for nulo, grava como nulo (infinito)
            $sql = "UPDATE usuarios SET codigo_verificacao = :codigo, expiracao_codigo = NULL WHERE id_usuario = :id";
            return $this->executarQuery($sql, [
                ':codigo' => $codigo, 
                ':id' => $id_usuario
            ]);
        }
    }

    /**
     * GENÉRICO: Confere se o código pertence ao e-mail E se ainda está na validade.
     * O 'IS NULL' garante que códigos sem data de expiração também funcionem.
     */
    public function verificarCodigo($email, $codigo) {
        $sql = "SELECT id_usuario FROM usuarios 
                WHERE email = :email 
                AND codigo_verificacao = :codigo 
                AND (expiracao_codigo IS NULL OR expiracao_codigo > NOW())";
        return $this->executarQuery($sql, [':email' => $email, ':codigo' => $codigo], 'unico');
    }

    /**
     * ESPECÍFICO: Valida o e-mail e aproveita para limpar as colunas de código
     */
    public function confirmarEmail($id_usuario) {
        $sql = "UPDATE usuarios SET email_verificado = 1, codigo_verificacao = NULL, expiracao_codigo = NULL WHERE id_usuario = :id";
        return $this->executarQuery($sql, [':id' => $id_usuario]);
    }

    /**
     * ESPECÍFICO: Atualiza a senha e aproveita para limpar as colunas de código
     */
    public function atualizarSenha($id_usuario, $novaSenhaHash) {
        $sql = "UPDATE usuarios SET senha = :senha, codigo_verificacao = NULL, expiracao_codigo = NULL WHERE id_usuario = :id";
        return $this->executarQuery($sql, [':senha' => $novaSenhaHash, ':id' => $id_usuario]);
    }

    public function atualizarCadastroCompleto($id_usuario, $nome, $email, $senhaHash) {
        $email = !empty(trim($email)) ? trim($email) : null;
        
        $sql = "UPDATE usuarios 
                SET nome = :nome, 
                    email = :email, 
                    senha = :senha 
                WHERE id_usuario = :id";
                
        return $this->executarQuery($sql, [
            ':nome'  => $nome, 
            ':email' => $email, 
            ':senha' => $senhaHash, 
            ':id'    => $id_usuario
        ]);
    }
}
?>