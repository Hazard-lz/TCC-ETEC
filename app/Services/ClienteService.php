<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../../database/Conexao.php';
require_once __DIR__ . '/UsuarioService.php';
require_once __DIR__ . '/../Models/Cliente.php';
require_once __DIR__ . '/../Models/Usuario.php'; 

class ClienteService extends BaseService {

    private $usuarioService;
    private $clienteModel;
    private $usuarioModel;
    private $conn;

    public function __construct() {
        $this->usuarioService = new UsuarioService();
        $this->clienteModel = new Cliente();
        $this->usuarioModel = new Usuario(); 
        $this->conn = Conexao::getConexao();
    }

    public function registrarCliente($nome, $email, $senha, $telefone, $data_nascimento, $observacoes = null) {
        
        if (empty($data_nascimento)) {
            return $this->erro('A data de nascimento é obrigatória para clientes.');
        }

        try {
            if (!$this->conn->inTransaction()) { $this->conn->beginTransaction(); }

            // 1. Resolve a parte do Usuário (Senha, Email, etc)
            $resultadoUsuario = $this->usuarioService->registrarUsuario($nome, $email, $senha, 'comum', $telefone);

            if ($resultadoUsuario['sucesso'] === false) {
                $this->conn->rollBack();
                return $resultadoUsuario; 
            }

            $idNovoUsuario = $resultadoUsuario['id'];

            // 2. VERIFICAÇÃO DE EVOLUÇÃO (Correção do erro de duplicação)
            // Checa se já existe um registro atrelado a esse usuário na tabela 'clientes'
            $clienteExistente = $this->clienteModel->buscarPorCodUsuario($idNovoUsuario);

            if ($clienteExistente) {
                // Como ele veio do "Cliente Rápido", já existe o vínculo. 
                // Então nós apenas ATUALIZAMOS a data de nascimento dele.
                $sucessoCliente = $this->clienteModel->atualizar($clienteExistente['id_cliente'], $data_nascimento, $observacoes);
                
                if ($sucessoCliente === false) {
                    throw new Exception("Falha ao atualizar o perfil de cliente existente no banco de dados.");
                }
                $idCliente = $clienteExistente['id_cliente'];
                
            } else {
                // Se é um cliente totalmente novo, criamos o vínculo normal
                $idCliente = $this->clienteModel->cadastrar($idNovoUsuario, $data_nascimento, $observacoes);

                if (!$idCliente) {
                    throw new Exception("Falha ao vincular o perfil de cliente no banco de dados.");
                }
            }
            
            $this->conn->commit();

            return $this->sucesso('Cadastro realizado com sucesso!', [
                'id_usuario' => $idNovoUsuario,
                'id_cliente' => $idCliente
            ]);

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Erro no registro unificado de cliente: " . $e->getMessage());
            
            return $this->erro('Ocorreu um erro interno ao realizar o cadastro. Tente novamente mais tarde.');
        }
    }

    public function atualizarDadosCliente($id_usuario, $id_cliente, $nome, $telefone, $data_nascimento, $observacoes) {
        
        if (empty($id_usuario) || empty($id_cliente)) {
            return $this->erro('Parâmetros de identificação inválidos.');
        }

        try {
            if (!$this->conn->inTransaction()) { $this->conn->beginTransaction(); }

            $resultadoUsuario = $this->usuarioService->atualizarUsuario($id_usuario, $nome, $telefone);
            
            if ($resultadoUsuario['sucesso'] === false) {
                $this->conn->rollBack();
                return $resultadoUsuario; 
            }

            $sucessoCliente = $this->clienteModel->atualizar($id_cliente, $data_nascimento, $observacoes);
            
            if (!$sucessoCliente) {
                throw new Exception("Erro ao atualizar dados específicos do cliente.");
            }

            $this->conn->commit();

            return $this->sucesso('Dados atualizados com sucesso!');

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Erro na atualização unificada de cliente: " . $e->getMessage());
            
            return $this->erro('Não foi possível atualizar os dados no momento.');
        }
    }

    public function registrarClienteRapido($nome, $telefone, $observacoes = null) {
        if (empty($nome) || empty($telefone)) {
            return $this->erro('Nome e telefone são obrigatórios para agendamentos manuais.');
        }

        try {
            if (!$this->conn->inTransaction()) { $this->conn->beginTransaction(); }

            // Verifica se o telefone já existe (pode ser que o cliente já tenha vindo)
            $usuarioExistente = $this->usuarioModel->buscarPorTelefone($telefone);
            
            if ($usuarioExistente) {
                $this->conn->rollBack();
                // Se o cliente já existe, retornamos o ID dele para o atendente prosseguir com o agendamento
                return $this->sucesso('Cliente já cadastrado.', [
                    'id_usuario' => $usuarioExistente['id_usuario']
                ]);
            }

            // Cria o usuário "Fantasma" (Sem email, sem senha, tipo comum)
            $idNovoUsuario = $this->usuarioModel->cadastrar(
                $nome, 
                null, // email nulo
                null, // senha nula (ou você pode gerar uma aleatória se quiser)
                'comum', 
                $telefone
            );

            // Cria o perfil de cliente (com observações, se houver)
            $idCliente = $this->clienteModel->cadastrar($idNovoUsuario, null, $observacoes);

            $this->conn->commit();

            return $this->sucesso('Cliente rápido registrado com sucesso!', [
                'id_usuario' => $idNovoUsuario,
                'id_cliente' => $idCliente
            ]);

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Erro no registro rápido de cliente: " . $e->getMessage());
            return $this->erro('Ocorreu um erro ao cadastrar o cliente manualmente.');
        }
    }

    public function alterarStatusCliente($id_usuario, $novo_status) {
        if (empty($id_usuario) || empty($novo_status)) {
            return $this->erro('Parâmetros inválidos para alterar o status.');
        }

        if (!in_array($novo_status, ['ativo', 'inativo'])) {
            return $this->erro('Status inválido fornecido.');
        }

        try {
            $sucesso = $this->usuarioModel->atualizarStatus($id_usuario, $novo_status);

            if ($sucesso) {
                return $this->sucesso('Status do cliente alterado com sucesso!');
            }
            
            return $this->erro('Falha ao atualizar o status no banco de dados.');
        } catch (Exception $e) {
            error_log("Erro ao alterar status do cliente: " . $e->getMessage());
            return $this->erro('Não foi possível alterar o status no momento.');
        }
    }

    public function atualizarObservacoesCliente($id_cliente, $observacoes) {
        if (empty($id_cliente)) {
            return $this->erro('Cliente não identificado.');
        }

        try {
            $sucessoCliente = $this->clienteModel->atualizarObservacoes($id_cliente, $observacoes);
            
            if (!$sucessoCliente) {
                throw new Exception("Erro ao atualizar as observações do cliente.");
            }

            return $this->sucesso('Observações atualizadas com sucesso!');

        } catch (Exception $e) {
            error_log("Erro na atualização de observações: " . $e->getMessage());
            return $this->erro('Não foi possível atualizar as observações no momento.');
        }
    }
}