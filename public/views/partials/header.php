<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - Belezou</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin-layout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin.css">
</head>
<body class="bg-light">
   
<?php
// Prepara os dados do usuário da sessão para o JavaScript
$userData = json_encode([
    'nome' => $_SESSION['usuario_nome'] ?? 'Usuário',
    'tipo' => $_SESSION['usuario_tipo'] ?? 'funcionario'
]);
?>

<script>
    localStorage.setItem('belezou_user', '<?= $userData ?>');
</script>

<script src="<?= BASE_URL ?>/public/resources/js/menu_global.js"></script>

<div id="conteudo-temporario" style="display: none;">