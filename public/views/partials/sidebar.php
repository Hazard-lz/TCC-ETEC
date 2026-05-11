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
<!-- ① SweetAlert2 — carregado antes do menu_global.js para Swal estar sempre definido -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

<!-- ② Configuração global do SweetAlert2 (Belezou Design System) -->
<script>
    // Objeto de defaults reutilizável — aplica o tema Belezou em todos os Swal.fire()
    window._swalDefaults = {
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

    // Sobrescrita global do alert() nativo
    window.alert = function(mensagem) {
        Swal.fire({
            ...window._swalDefaults,
            text:              mensagem,
            icon:              'info',
            confirmButtonText: 'Entendi'
        });
    };

    // Sobrescrita global do confirm() nativo (retorna Promise)
    window.confirm = function(mensagem) {
        return Swal.fire({
            ...window._swalDefaults,
            text:              mensagem,
            icon:              'question',
            showCancelButton:  true,
            confirmButtonText: 'Sim',
            cancelButtonText:  'Cancelar'
        }).then(result => result.isConfirmed);
    };
</script>

<!-- ③ Script principal — carregado depois do Swal e dos overrides -->
<script src="<?= BASE_URL ?>/public/resources/js/menu_global.js"></script>

<div id="conteudo-temporario" style="display: none;">
