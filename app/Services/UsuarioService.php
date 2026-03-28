<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Models/Cliente.php';
require_once __DIR__ . '/EmailService.php';

class UsuarioService extends BaseService {

    private $usuarioModel;
    private $clienteModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
        $this->clienteModel = new Cliente();
    }

    public function registrarUsuario($nome, $email, $senha, $tipo = 'comum', $telefone = null) {
        
        // 1. VERIFICA SE O E-MAIL JÁ EXISTE NO BANCO
        $usuarioExistente = $this->usuarioModel->buscarPorEmail($email);
        if ($usuarioExistente) {
            return $this->erro('Este e-mail já está cadastrado. Por favor, faça login ou recupere a sua senha.');
        }

        // 2. VERIFICA O TELEFONE (Para evitar telefones duplicados)
        if ($telefone) {
            if ($this->usuarioModel->buscarPorTelefone($telefone)) {
                return $this->erro('Este telefone/WhatsApp já está cadastrado em outra conta.');
            }
        }
        

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->erro('Formato de e-mail inválido.');
        }

        if (strlen($senha) < 8) {
            return $this->erro('A senha deve ter no mínimo 8 caracteres.');
        }

        if ($this->usuarioModel->buscarPorEmail($email)) {
            return $this->erro('Este e-mail já está em uso.');
        }

        if (!empty($telefone)) {
            $telefoneExistente = $this->usuarioModel->buscarPorTelefone($telefone);
            if ($telefoneExistente) {
                return $this->erro('Este número já está cadastrado em outra conta.');
            }
        }

        $idNovoUsuario = $this->usuarioModel->cadastrar($nome, $email, $senha, $tipo, $telefone);

        if ($idNovoUsuario) {
            // Gera um código de 6 números aleatórios
            $codigo = mt_rand(100000, 999999);
            
            // ARQUITETURA: Passamos apenas o número '30'. O Banco de Dados faz a conta.
            $this->usuarioModel->salvarCodigo($idNovoUsuario, $codigo, 30);

            // Dispara o e-mail
            $emailService = new EmailService();
            $assunto = "Seu código de verificação - Belezou App";
            $html = "<div style='text-align:center; padding: 20px; font-family: sans-serif;'>
                        <h2>Olá, {$nome}!</h2>
                        <p>Falta pouco para se cadastrar no sistema.</p>
                        <p>O seu código de verificação é:</p>
                        <h1 style='color: #8b5cf6; letter-spacing: 5px; font-size: 2.5rem; background: #f8fafc; padding: 10px; border-radius: 8px; display: inline-block;'>{$codigo}</h1>
                     </div>";
                     
            $emailService->enviar($email, $nome, $assunto, $html);

            return $this->sucesso('Cadastrado com sucesso!', ['id' => $idNovoUsuario]);
        }
    }

    public function autenticar($email, $senha) {
        
        $usuario = $this->usuarioModel->buscarPorEmail($email);
        $clienteExistente = $this->clienteModel->buscarPorCodUsuario($usuario['id_usuario']);
        $expiracao = $usuario['expiracao_codigo'];
        $agora = date('Y-m-d H:i:s');

        // Verifica se existe alguma expiração no banco e apaga o código e a expiração
        if (!empty($expiracao) && $expiracao < $agora) {
                // Chama a função passando 'null' para apagar o código e a data do banco
                $this->usuarioModel->salvarCodigo($usuario['id_usuario'], null, null);
            }

        if (!$usuario) {
            return $this->erro('E-mail ou senha incorretos.');
        }

        if ($usuario['status'] !== 'ativo') {
            return $this->erro('Esta conta está desativada. Entre em contato com o administrador.');
        }

        // ARQUITETURA UX: Ao invés de gerar um erro com link, passamos uma flag de redirecionamento
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

        // ARQUITETURA: Deixamos o MySQL cuidar do fuso horário passando apenas os minutos
        $this->usuarioModel->salvarCodigo($usuario['id_usuario'], $codigo, 30);

        $emailService = new EmailService();
        $assunto = "Recuperação de Senha - Belezou App";
        $html = "<div style='text-align:center; padding: 20px; font-family: sans-serif;'>
                    <h2>Olá, {$usuario['nome']}!</h2>
                    <p>Recebemos um pedido par a redefinir a sua senha.</p>
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

        // Verifica usando a nova regra de prazo
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

        // Em vez de buscar por ID e depois por Email, use o ID para pegar os dados do banco 
        // Certifique-se que o método buscarPorEmail retorna a senha (ele já faz isso)
        // Se preferir, crie um método novo no Model 'buscarSenhaPorId' para ser mais direto.
        
        $usuario = $this->usuarioModel->buscarPorId($idUsuario); // Retorna nome, email, etc
        $usuarioCompleto = $this->usuarioModel->buscarPorEmail($usuario['email']); // Retorna TUDO, inclusive a senha

        if (!$usuarioCompleto || !password_verify($senhaAtual, $usuarioCompleto['senha'])) {
            return $this->erro('A senha atual está incorreta.');
        }

        // Criptografa e salva
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $this->usuarioModel->atualizarSenha($idUsuario, $senhaHash); // Limpa também códigos de recuperação

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

        // ARQUITETURA: Verifica se JÁ EXISTE um código válido no banco
        $codigoExistente = $usuario['codigo_verificacao'];
        $expiracaoAtual = $usuario['expiracao_codigo'];
        $agora = date('Y-m-d H:i:s');
        
        // Se tem código e ele vence no futuro...
        if (!empty($codigoExistente) && !empty($expiracaoAtual) && $expiracaoAtual > $agora) {
            // Reutiliza o mesmo código!
            $codigoParaEnviar = $codigoExistente;
            $mensagemAlerta = 'Reenviamos o código que já estava ativo para o seu e-mail.';
        } else {
            // Se expirou ou não existe, gera um NOVO com +30 min
            $codigoParaEnviar = mt_rand(100000, 999999);
            
            // ARQUITETURA: Passamos '30' minutos para o banco de dados
            $this->usuarioModel->salvarCodigo($usuario['id_usuario'], $codigoParaEnviar, 30);
            $mensagemAlerta = 'Um novo código de verificação foi gerado e enviado para o seu e-mail (válido por 30 min).';
        }

        // Dispara o e-mail
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
}
?>