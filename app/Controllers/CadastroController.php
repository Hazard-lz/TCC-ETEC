<?php

require_once __DIR__ . '/../Services/ClienteService.php';
require_once __DIR__ . '/../Services/EmailService.php';

class CadastroController
{

    private $clienteService;

    public function __construct()
    {
        $this->clienteService = new ClienteService();
    }

    public function registrar()
    {
        $nome       = trim($_POST['nome'] ?? '');
        $email      = trim($_POST['email'] ?? '');
        $senha      = $_POST['senha'] ?? '';
        $telefone   = trim($_POST['telefone'] ?? '');
        $nascimento = $_POST['data_nascimento'] ?? '';

        $resultado = $this->clienteService->registrarCliente($nome, $email, $senha, $telefone, $nascimento);
        $emailService = new EmailService();

        $assunto = "Bem-vindo ao Belezou App!";
        $html = "
            <h2>Olá, {$nome}!</h2>
            <p>A sua conta foi criada com sucesso.</p>
            <p>Estamos muito felizes em ter você no nosso salão.</p>
        ";

        $emailService->enviar($email, $nome, $assunto, $html);

        if ($resultado['sucesso']) {
            $_SESSION['email_verificacao'] = $email; // Lembra quem é o utilizador
            header("Location: " . BASE_URL . "/verificar-email"); 
            exit; 
        } else {
            $_SESSION['erro_cadastro'] = $resultado['mensagem'];
            header("Location: " . BASE_URL . "/cadastro");
            exit;
        }
    }
}
