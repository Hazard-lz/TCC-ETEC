g
<?php

class AjudaController
{

    // Método para renderizar a página de Ajuda do Cliente
    public function indexCliente()
    {
        require_once __DIR__ . '/../../public/views/cliente/ajuda.php';
    }

    // Método para renderizar a página de Ajuda do Funcionário
    public function indexFuncionario()
    {
        require_once __DIR__ . '/../../public/views/funcionario/ajuda.php';
    }

    // Método para renderizar a página de Ajuda do Administrador
    public function indexAdmin()
    {
        require_once __DIR__ . '/../../public/views/admin/ajuda.php';
    }
}
