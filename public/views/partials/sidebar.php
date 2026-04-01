<?php
// 1. Pega os dados do utilizador que estão salvos na sessão do PHP
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
