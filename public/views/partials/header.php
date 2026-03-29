
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin-layout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin.css">
    
</head>
<body>
    
</body>
</html><?php
// Prevenção: caso a sessão caia ou não exista, define um padrão
$nomeCompleto = $_SESSION['usuario_nome'] ?? 'Usuário';

// Pega apenas o primeiro nome do utilizador logado para o cabeçalho não ficar muito grande
$nomeCabecalho = explode(' ', $nomeCompleto)[0];

// Pega a primeira letra do nome para fazer o Avatar redondinho
$inicial = substr($nomeCabecalho, 0, 1);
?>
<header class="topbar">
    <div style="display: flex; align-items: center;">
        <button id="menuToggle" class="menu-toggle" title="Abrir Menu">☰</button>
        <h2 style="color: var(--text-main); font-size: 1.2rem;">Painel de Controle</h2>
    </div>
    
    <div class="user-profile" style="display: flex; align-items: center; gap: 1rem;">
        
        <label class="theme-switch" title="Alternar Modo Escuro">
            <input type="checkbox" id="themeToggle">
            <span class="slider"></span>
        </label>

        <span><?= htmlspecialchars($nomeCabecalho) ?></span> 
        
        <div class="avatar" style="width: 40px; height: 40px; border-radius: 50%; background: var(--gradient-brand); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2rem;">
            <?= strtoupper(htmlspecialchars($inicial)) ?>
        </div>
    </div>
</header>