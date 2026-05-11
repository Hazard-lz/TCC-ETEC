<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - Belezou</title>

    <!-- ① SweetAlert2 — carregado PRIMEIRO para estar disponível em todo o documento -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    <!-- ② Ícones e estilos da aplicação -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin-layout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin.css">

    <?php require_once __DIR__ . '/onesignal.php'; ?>
</head>
<body class="bg-light">

<?php
// Prepara os dados do usuário da sessão para o JavaScript
$userData = json_encode([
    'cod_usuario' => $_SESSION['usuario_id'] ?? null,
    'nome'        => $_SESSION['usuario_nome'] ?? 'Usuário',
    'tipo'        => $_SESSION['usuario_tipo'] ?? 'funcionario'
]);
?>

<!-- ③ Variáveis globais + sobrescrita do alert/confirm (Swal já disponível via <head>) -->
<script>
    localStorage.setItem('belezou_user', '<?= $userData ?>');
    window.BASE_URL = '<?= BASE_URL ?>';

    // ── Configuração global padrão do SweetAlert2 (Belezou Design System) ────
    const _swalDefaults = {
        customClass: {
            popup:         'swal-belezou-popup',
            title:         'swal-belezou-title',
            htmlContainer: 'swal-belezou-text',
            confirmButton: 'swal-belezou-btn-confirm',
            cancelButton:  'swal-belezou-btn-cancel',
            icon:          'swal-belezou-icon'
        },
        buttonsStyling: false,
        showClass: { popup: 'swal-belezou-show' },
        hideClass: { popup: 'swal-belezou-hide' }
    };

    // ── Sobrescrita global do alert() nativo ──────────────────────────────────
    window.alert = function(mensagem) {
        Swal.fire({
            ..._swalDefaults,
            text:              mensagem,
            icon:              'info',
            confirmButtonText: 'Entendi'
        });
    };

    // ── Sobrescrita global do confirm() nativo (retorna Promise) ─────────────
    window.confirm = function(mensagem) {
        return Swal.fire({
            ..._swalDefaults,
            text:              mensagem,
            icon:              'question',
            showCancelButton:  true,
            confirmButtonText: 'Sim',
            cancelButtonText:  'Cancelar'
        }).then(result => result.isConfirmed);
    };
</script>

<!-- ④ Script principal da aplicação — carregado por último -->
<script src="<?= BASE_URL ?>/public/resources/js/menu_global.js"></script>

<div id="conteudo-temporario" style="display: none;">