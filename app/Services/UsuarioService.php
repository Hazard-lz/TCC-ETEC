<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Models/Cliente.php';
require_once __DIR__ . '/EmailService.php';
require_once __DIR__ . '/../../database/Conexao.php'; // Adicionado para uso de Transactions

class UsuarioService extends BaseService {

    private $usuarioModel;
    private $clienteModel;
    private $conn;

    public function __construct() {
        $this->usuarioModel = new Usuario();
        $this->clienteModel = new Cliente();
        $this->conn = Conexao::getConexao(); // Inicializando a conexão para as Transactions
    }

    public function registrarUsuario($nome, $email, $senha, $tipo = 'comum', $telefone = null) {
        
        // 1. Validações básicas de formato
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->erro('Formato de e-mail inválido.');
        }

        if (strlen($senha) < 8) {
            return $this->erro('A senha deve ter no mínimo 8 caracteres.');
        }

        $idNovoUsuario = null;

        // 2. VERIFICA O TELEFONE (Evolução do Cliente Rápido)
        if (!empty($telefone)) {
            $usuarioExistenteTelefone = $this->usuarioModel->buscarPorTelefone($telefone);
            
            if ($usuarioExistenteTelefone) {
                // Busca os dados para saber se é um cliente rápido (sem e-mail)
                $dadosUsuario = $this->usuarioModel->buscarPorId($usuarioExistenteTelefone['id_usuario']);
                
                if (empty($dadosUsuario['email'])) {
                    // É um usuário fantasma! Vamos ATUALIZAR para uma conta completa
                    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                    
                    // Atualiza o registro existente em vez de criar um novo
                    $sucesso = $this->usuarioModel->atualizarCadastroCompleto($dadosUsuario['id_usuario'], $nome, $email, $senhaHash);
                    
                    if ($sucesso !== false) {
                        $idNovoUsuario = $dadosUsuario['id_usuario'];
                    } else {
                        return $this->erro('Erro ao tentar evoluir o cadastro do cliente.');
                    }
                } else {
                    return $this->erro('Este telefone/WhatsApp já está cadastrado em outra conta completa.');
                }
            }
        }

        // 3. Se não for um "upgrade" de cliente rápido, fazemos a inserção normal
        if (!$idNovoUsuario) {
            // VERIFICA SE O E-MAIL JÁ EXISTE NO BANCO ANTES DO INSERT
            $usuarioExistenteEmail = $this->usuarioModel->buscarPorEmail($email);
            if ($usuarioExistenteEmail) {
                return $this->erro('Este e-mail já está cadastrado. Por favor, faça login ou recupere a sua senha.');
            }

            $idNovoUsuario = $this->usuarioModel->cadastrar($nome, $email, $senha, $tipo, $telefone);
        }

        // 4. Fluxo de envio de e-mail de verificação (Idêntico para novo cadastro ou evolução)
        if ($idNovoUsuario) {
            $codigo = mt_rand(100000, 999999);
            $this->usuarioModel->salvarCodigo($idNovoUsuario, $codigo, 30);

            $emailService = new EmailService();
            $assunto = "Seu código de verificação - Belezou App";
            $html = "<div style='text-align:center; padding: 20px; font-family: sans-serif;'>
                        <h2>Olá, {$nome}!</h2>
                        <p>Falta pouco para acessar o sistema.</p>
                        <p>O seu código de verificação é:</p>
                        <h1 style='color: #8b5cf6; letter-spacing: 5px; font-size: 2.5rem; background: #f8fafc; padding: 10px; border-radius: 8px; display: inline-block;'>{$codigo}</h1>
                     </div>";
                     
            $emailService->enviar($email, $nome, $assunto, $html);

            return $this->sucesso('Cadastrado com sucesso!', ['id' => $idNovoUsuario]);
        }

        return $this->erro('Falha inesperada ao registrar usuário.');
    }

    public function registrarUsuarioDaEquipe($nome, $email, $telefone, $tipo) {
        if ($this->usuarioModel->buscarPorEmail($email)) {
            return $this->erro('Este e-mail já está cadastrado no sistema.');
        }

        $idNovoUsuario = $this->usuarioModel->cadastrar($nome, $email, null, $tipo, $telefone);

        if ($idNovoUsuario) {
            $token = mt_rand(100000, 999999);
            $this->usuarioModel->salvarCodigo($idNovoUsuario, $token, 2880);

            require_once __DIR__ . '/EmailService.php'; 
            $emailService = new EmailService();
            $assunto = "Bem-vindo à equipe - Crie sua senha de acesso";
            
            $host = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
            $protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
            $urlCompleta = $protocolo . "://" . $host . BASE_URL;
            $link = $urlCompleta . "/setup-funcionario?token={$token}&email=" . urlencode($email);

            $html = "<div style='text-align:center; padding: 20px; font-family: sans-serif; color: #333;'>
                        <h2>Olá, {$nome}!</h2>
                        <p>Você foi adicionado à equipe do salão.</p>
                        <p>Para concluir seu cadastro e criar sua senha de acesso, clique no botão abaixo:</p>
                        <a href='{$link}' style='display:inline-block; padding: 12px 24px; background: #8b5cf6; color: #fff; text-decoration: none; border-radius: 8px; margin-top: 15px; margin-bottom: 20px;'>Criar minha senha</a>
                        <hr style='border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;'>
                        <p style='font-size: 0.85rem; color: #64748b;'>Se o botão não funcionar, copie e cole o link abaixo no seu navegador:</p>
                        <p style='font-size: 0.85rem; color: #8b5cf6; word-break: break-all;'>{$link}</p>
                     </div>";

            $textoPuro = "Olá, {$nome}!\n\nVocê foi adicionado à equipe do salão.\n\nPara concluir seu cadastro e criar sua senha de acesso, copie e cole o link abaixo no seu navegador:\n\n{$link}";

            if (!$emailService->enviar($email, $nome, $assunto, $html, $textoPuro)) {
                return $this->erro('O funcionário foi salvo, mas houve um erro ao enviar o e-mail.');
            }

            return $this->sucesso('Funcionário cadastrado com sucesso! Um e-mail foi enviado.', ['id' => $idNovoUsuario]);
        }
        
        return $this->erro('Falha ao registrar dados de acesso no banco.');
    }

    public function finalizarCadastroEquipe($email, $token, $senha, $confirmaSenha) {
        if ($senha !== $confirmaSenha) {
            return $this->erro('As senhas não coincidem.');
        }
        if (strlen($senha) < 8) {
            return $this->erro('A senha deve ter pelo menos 8 caracteres.');
        }

        $usuario = $this->usuarioModel->verificarCodigo($email, $token);
        
        if (!$usuario) {
            return $this->erro('Link inválido ou expirado. Solicite ao gerente que reenvie o acesso.');
        }

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        
        try {
            // Utilizando transação para garantir que ambas as operações sejam feitas com segurança
            if (!$this->conn->inTransaction()) { $this->conn->beginTransaction(); }

            $this->usuarioModel->atualizarSenha($usuario['id_usuario'], $senhaHash);
            $this->usuarioModel->confirmarEmail($usuario['id_usuario']);

            $this->conn->commit();

            return $this->sucesso('Cadastro realizado com sucesso!');
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Erro ao finalizar cadastro da equipe: " . $e->getMessage());
            return $this->erro('Ocorreu um erro ao salvar sua senha. Tente novamente.');
        }
    }

    public function autenticar($email, $senha) {
        $usuario = $this->usuarioModel->buscarPorEmail($email);

        // CORREÇÃO CRÍTICA: Impedir o Fatal Error validando se o usuário existe antes de acessar arrays
        if (!$usuario) {
            return $this->erro('E-mail ou senha incorretos.');
        }

        // A partir daqui, é seguro acessar os dados de $usuario
        $clienteExistente = $this->clienteModel->buscarPorCodUsuario($usuario['id_usuario']);
        $expiracao = $usuario['expiracao_codigo'];
        $agora = date('Y-m-d H:i:s');

        if (!empty($expiracao) && $expiracao < $agora) {
            $this->usuarioModel->salvarCodigo($usuario['id_usuario'], null, null);
        }

        if ($usuario['status'] !== 'ativo') {
            return $this->erro('Esta conta está desativada. Entre em contato com o administrador.');
        }

        if (isset($usuario['email_verificado']) && $usuario['email_verificado'] == 0) {
            return [
                'sucesso' => false, 
                'mensagem' => 'E-mail não verificado.',
                'requer_verificacao' => true,
                'email' => $usuario['email']
            ];
        }

        if (!password_verify($senha, $usuario['senha'])) {
            return $this->erro('E-mail ou senha incorretos.');
        }

        if ($clienteExistente && $usuario['tipo'] !== 'comum') {
            $this->usuarioModel->atualizarTipo($usuario['id_usuario'], 'comum');
            $usuario['tipo'] = 'comum';
        }

        unset($usuario['senha']);

        return $this->sucesso('Login realizado com sucesso.', ['dados_usuario' => $usuario]);
    }

    public function validarCodigo($email, $codigo) {
        $user = $this->usuarioModel->verificarCodigo($email, $codigo);
        
        if ($user) {
            $this->usuarioModel->confirmarEmail($user['id_usuario']);
            return $this->sucesso('E-mail verificado com sucesso! Pode fazer login.');
        }
        return $this->erro('Código inválido ou já utilizado. Verifique o seu e-mail.');
    }

    public function solicitarRecuperacaoSenha($email) {
        $usuario = $this->usuarioModel->buscarPorEmail($email);
        
        if (!$usuario) {
            return $this->sucesso('Se o e-mail estiver registado, receberá um código em breve.');
        }

        $codigo = mt_rand(100000, 999999); 
        $this->usuarioModel->salvarCodigo($usuario['id_usuario'], $codigo, 30);

        $emailService = new EmailService();
        $assunto = "Recuperação de Senha - Belezou App";
        $html = "<div style='text-align:center; padding: 20px; font-family: sans-serif;'>
                    <h2>Olá, {$usuario['nome']}!</h2>
                    <p>Recebemos um pedido para redefinir a sua senha.</p>
                    <p>O seu código de recuperação (válido por 30 minutos) é:</p>
                    <h1 style='color: #8b5cf6; letter-spacing: 5px; font-size: 2.5rem; background: #f8fafc; padding: 10px; border-radius: 8px; display: inline-block;'>{$codigo}</h1>
                    <p>Se não pediu esta alteração, ignore este e-mail.</p>
                 </div>";

        $emailService->enviar($email, $usuario['nome'], $assunto, $html);

        return $this->sucesso('Se o e-mail estiver registado, receberá um código em breve.');
    }

    public function redefinirSenha($email, $codigo, $novaSenha, $confirmaSenha) {
        if ($novaSenha !== $confirmaSenha) {
            return $this->erro('As senhas não coincidem.');
        }
        if (strlen($novaSenha) < 8) {
            return $this->erro('A nova senha deve ter pelo menos 8 caracteres.');
        }

        $usuario = $this->usuarioModel->verificarCodigo($email, $codigo);
        
        if (!$usuario) {
            return $this->erro('Código inválido ou expirado. Peça um novo código.');
        }

        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $this->usuarioModel->atualizarSenha($usuario['id_usuario'], $senhaHash);

        return $this->sucesso('Senha alterada com sucesso! Já pode fazer login.');
    }

    public function trocarSenhaConhecida($idUsuario, $senhaAtual, $novaSenha, $confirmaSenha) {
        if ($novaSenha !== $confirmaSenha) {
            return $this->erro('A nova senha e a confirmação não coincidem.');
        }
        if (strlen($novaSenha) < 8) {
            return $this->erro('A nova senha deve ter pelo menos 8 caracteres.');
        }
        
        $usuario = $this->usuarioModel->buscarPorId($idUsuario);
        $usuarioCompleto = $this->usuarioModel->buscarPorEmail($usuario['email']);

        if (!$usuarioCompleto || !password_verify($senhaAtual, $usuarioCompleto['senha'])) {
            return $this->erro('A senha atual está incorreta.');
        }

        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $this->usuarioModel->atualizarSenha($idUsuario, $senhaHash); 

        return $this->sucesso('Senha alterada com segurança!');
    }

    public function atualizarUsuario($id_usuario, $nome, $telefone) {
        $telefone = !empty(trim($telefone)) ? trim($telefone) : null;
        
        if ($telefone) {
            $existente = $this->usuarioModel->buscarPorTelefoneDiferenteDe($telefone, $id_usuario);
            if ($existente) {
                return $this->erro('Este telefone/WhatsApp já está cadastrado em outra conta.');
            }
        }

        $sucesso = $this->usuarioModel->atualizar($id_usuario, $nome, $telefone);
        if ($sucesso !== false) {
            return $this->sucesso('Dados básicos atualizados com sucesso!');
        }
        return $this->erro('Erro ao atualizar os dados do usuário no banco de dados.');
    }

    public function reenviarCodigoVerificacao($email) {
        $usuario = $this->usuarioModel->buscarPorEmail($email);
        
        if (!$usuario) {
            return $this->erro('Usuário não encontrado.');
        }
        
        if ($usuario['email_verificado'] == 1) {
            return $this->erro('Este e-mail já foi verificado. Você já pode fazer login.');
        }

        $codigoExistente = $usuario['codigo_verificacao'];
        $expiracaoAtual = $usuario['expiracao_codigo'];
        $agora = date('Y-m-d H:i:s');
        
        if (!empty($codigoExistente) && !empty($expiracaoAtual) && $expiracaoAtual > $agora) {
            $codigoParaEnviar = $codigoExistente;
            $mensagemAlerta = 'Reenviamos o código que já estava ativo para o seu e-mail.';
        } else {
            $codigoParaEnviar = mt_rand(100000, 999999);
            $this->usuarioModel->salvarCodigo($usuario['id_usuario'], $codigoParaEnviar, 30);
            $mensagemAlerta = 'Um novo código de verificação foi gerado e enviado para o seu e-mail (válido por 30 min).';
        }

        $emailService = new EmailService();
        $assunto = "Seu código de verificação - Belezou App";
        $html = "<div style='text-align:center; padding: 20px; font-family: sans-serif;'>
                    <h2>Olá, {$usuario['nome']}!</h2>
                    <p>Aqui está o seu código de verificação de acesso:</p>
                    <h1 style='color: #8b5cf6; letter-spacing: 5px; font-size: 2.5rem; background: #f8fafc; padding: 10px; border-radius: 8px; display: inline-block;'>{$codigoParaEnviar}</h1>
                 </div>";
                 
        $emailService->enviar($email, $usuario['nome'], $assunto, $html);

        return $this->sucesso($mensagemAlerta);
    }

    public function reenviarEmailSetupFuncionario($id_usuario) {
        $usuario = $this->usuarioModel->buscarPorId($id_usuario);
        
        if (!$usuario) return $this->erro('Usuário não encontrado.');
        if ($usuario['status'] !== 'ativo') return $this->erro('Não é possível enviar acesso para um funcionário inativo.');
        
        $usuarioCompleto = $this->usuarioModel->buscarPorEmail($usuario['email']);
        if ($usuarioCompleto['email_verificado'] == 1) return $this->erro('Este funcionário já criou a senha e verificou o e-mail.');

        $token = mt_rand(100000, 999999);
        $this->usuarioModel->salvarCodigo($id_usuario, $token, 2880);

        $host = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        $protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        $link = $protocolo . "://" . $host . BASE_URL . "/setup-funcionario?token={$token}&email=" . urlencode($usuario['email']);

        $assunto = "Reenvio: Bem-vindo à equipe - Crie sua senha de acesso";
        $html = "<div style='text-align:center; padding: 20px; font-family: sans-serif; color: #333;'>
                    <h2>Olá, {$usuario['nome']}!</h2>
                    <p>O seu link de acesso foi gerado novamente pelo administrador.</p>
                    <p>Para criar sua senha de acesso, clique no botão abaixo:</p>
                    <a href='{$link}' style='display:inline-block; padding: 12px 24px; background: #8b5cf6; color: #fff; text-decoration: none; border-radius: 8px; margin-top: 15px; margin-bottom: 20px;'>Criar minha senha</a>
                    <hr style='border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;'>
                    <p style='font-size: 0.85rem; color: #64748b;'>Se o botão não funcionar, copie e cole o link abaixo no seu navegador:</p>
                    <p style='font-size: 0.85rem; color: #8b5cf6; word-break: break-all;'>{$link}</p>
                 </div>";

        $textoPuro = "Olá, {$usuario['nome']}!\n\nO seu link de acesso foi gerado novamente pelo administrador.\n\nPara concluir seu cadastro e criar sua senha de acesso, copie e cole o link abaixo no seu navegador:\n\n{$link}";

        require_once __DIR__ . '/EmailService.php'; 
        $emailService = new EmailService();
        if ($emailService->enviar($usuario['email'], $usuario['nome'], $assunto, $html, $textoPuro)) {
            return $this->sucesso('E-mail de configuração reenviado com sucesso!');
        }
        
        return $this->erro('Falha técnica ao tentar enviar o e-mail.');
    }
}