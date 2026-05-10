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

<!-- Anti-FOUC: Aplica o estado collapsed ANTES do layout renderizar -->
<script>
    (function() {
        if (window.innerWidth > 992 && localStorage.getItem('belezou_sidebar_collapsed') === 'true') {
            var style = document.createElement('style');
            style.id = 'sidebar-preload';
            style.textContent = '.sidebar { width: 75px !important; } .sidebar .sidebar-logo, .sidebar .nav-link span, .sidebar .nav-title, .sidebar .user-info, .sidebar .sidebar-clock { display: none !important; } .sidebar .nav-link { justify-content: center; padding: 0.85rem 0; } .sidebar .nav-link i { margin-right: 0 !important; } .sidebar .sidebar-footer { justify-content: center; padding: 1rem 0; } .main-wrapper { margin-left: 75px !important; }';
            document.head.appendChild(style);
        }
    })();
</script>

<script>
    window.BASE_URL = '<?= BASE_URL ?>';
</script>
<script src="<?= BASE_URL ?>/public/resources/js/menu_global.js"></script>

<div id="conteudo-temporario" style="display: none;">
