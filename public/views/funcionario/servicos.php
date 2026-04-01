<?php
// Carrega os models necessários
$servicoModel = new Servico();
$funcionarioModel = new Funcionario();

// 1. Pega TODOS os serviços ativos do salão
$todosServicos = $servicoModel->listarPorStatus('ativo');

// 2. Pega os dados do funcionário do usuário que está logado
$dadosFunc = $funcionarioModel->buscarPorCodUsuario($_SESSION['usuario_id']);

// 3. Verifica se a consulta realmente retornou dados antes de extrair o ID
if ($dadosFunc) {
    $idFuncionarioLogado = $dadosFunc['id_funcionario'];
    // Pega os IDs dos serviços que este funcionário já faz
    $meusServicos = $funcionarioModel->buscarIdsServicosPorFuncionario($idFuncionarioLogado, 'ativo');
} else {
    // Se o banco retornou false (ex: é um Admin que não corta cabelo)
    $idFuncionarioLogado = null;
    $meusServicos = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Serviços - Belezou App</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin-layout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin.css">
</head>
<body>

    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="page-header" style="margin-bottom: 2rem;">
        <div class="page-title">
            <h2 style="color: var(--text-main); margin-bottom: 0.5rem;">Meus Serviços</h2>
            <p style="color: var(--text-muted);">Selecione os serviços que você está apto a realizar no salão.</p>
        </div>
    </div>

    <div class="base-card" style="max-width: 600px; padding: 2rem;">
        
        <?php if ($idFuncionarioLogado): ?>
            <form action="<?= BASE_URL ?>/funcionario/servicos/salvar" method="POST">
                
                <div style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 2rem;">
                    <?php foreach ($todosServicos as $servico): ?>
                        <?php 
                            // Verifica se o ID deste serviço está no array de serviços do funcionário
                            $marcado = in_array($servico['id_servico'], $meusServicos) ? 'checked' : ''; 
                        ?>
                        <label style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 1px solid var(--border-color); border-radius: 8px; cursor: pointer; transition: 0.2s;">
                            <input type="checkbox" name="servicos[]" value="<?= $servico['id_servico'] ?>" <?= $marcado ?> style="width: 20px; height: 20px; accent-color: var(--color-purple);">
                            <div>
                                <strong style="display: block; color: var(--text-main);"><?= htmlspecialchars($servico['nome_servico']) ?></strong>
                                <span style="font-size: 0.85rem; color: var(--text-muted);">
                                    Duração: <?= $servico['duracao'] ?> min | Valor: R$ <?= number_format($servico['preco'], 2, ',', '.') ?>
                                </span>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>

                <button type="submit" class="btn-primary" style="margin: 0;">Salvar Minhas Especialidades</button>
            </form>
        <?php else: ?>
            <div style="text-align: center; padding: 2rem 0;">
                <p style="font-size: 3rem; margin-bottom: 1rem;">💼</p>
                <h3 style="color: var(--text-main); margin-bottom: 0.5rem;">Sem perfil de atendimento</h3>
                <p style="color: var(--text-muted);">A sua conta possui nível de Administração, mas não está cadastrada como um prestador de serviços na tabela de funcionários. Por isso, não possui uma agenda de serviços própria.</p>
            </div>
        <?php endif; ?>

    </div>

    </div>

    <script src="<?= BASE_URL ?>/public/resources/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/servico.js"></script>
</body>
</html>