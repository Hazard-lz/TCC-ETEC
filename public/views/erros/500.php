<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro no Servidor - Belezou App</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* Reutilizando as variáveis do root.css do projeto */
        :root {
            --color-pink: #f45b69;
            --color-purple: #8b5cf6;
            --gradient-brand: linear-gradient(135deg, var(--color-pink), var(--color-purple));
            --bg-color: #f0f2f5;
            --surface-color: #ffffff;
            --text-main: #2d3748;
            --text-muted: #718096;
            --border-color: #e2e8f0;
            --radius-md: 8px;
            --radius-lg: 16px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Inter", "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        /* Blob decorativo de fundo */
        body::before {
            content: '';
            position: fixed;
            top: -150px;
            right: -150px;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: var(--gradient-brand);
            opacity: 0.07;
            pointer-events: none;
            z-index: 0;
        }

        body::after {
            content: '';
            position: fixed;
            bottom: -150px;
            left: -150px;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: var(--gradient-brand);
            opacity: 0.05;
            pointer-events: none;
            z-index: 0;
        }

        /* Card principal de erro */
        .error-card {
            background-color: var(--surface-color);
            width: 100%;
            max-width: 520px;
            padding: 3rem 3.5rem;
            border-radius: var(--radius-lg);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            text-align: center;
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.5s ease forwards;
        }

        /* Ícone de erro com gradiente da marca */
        .error-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--gradient-brand);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem auto;
            font-size: 2.2rem;
            box-shadow: 0 8px 24px rgba(244, 91, 105, 0.3);
        }

        /* Badge com o código de erro */
        .error-badge {
            display: inline-block;
            background: var(--gradient-brand);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 5rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 0.5rem;
            letter-spacing: -3px;
        }

        .error-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.75rem;
        }

        .error-description {
            font-size: 0.95rem;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        /* Divider decorativo */
        .error-divider {
            width: 50px;
            height: 4px;
            background: var(--gradient-brand);
            border-radius: 2px;
            margin: 1.25rem auto;
        }

        /* Botão de retorno */
        .btn-back {
            display: inline-block;
            padding: 0.9rem 2rem;
            background: var(--gradient-brand);
            color: #ffffff;
            font-size: 1rem;
            font-weight: 700;
            text-decoration: none;
            border-radius: var(--radius-md);
            box-shadow: 0 4px 15px rgba(244, 91, 105, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 92, 246, 0.4);
        }

        /* Texto de rodapé */
        .error-footer {
            margin-top: 1.5rem;
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        /* Animação de entrada */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsividade */
        @media (max-width: 540px) {
            .error-card {
                padding: 2rem 1.5rem;
            }

            .error-badge {
                font-size: 4rem;
            }

            .error-title {
                font-size: 1.2rem;
            }
        }
    </style>
</head>

<body>

    <div class="error-card">

        <!-- Ícone com gradiente da marca -->
        <div class="error-icon">
            ⚙️
        </div>

        <!-- Código do erro -->
        <div class="error-badge">500</div>

        <div class="error-divider"></div>

        <h1 class="error-title">Ops! Algo deu errado.</h1>

        <p class="error-description">
            Tivemos um problema interno no servidor. Não se preocupe, nosso time já foi notificado e está trabalhando
            para resolver o mais rápido possível.
        </p>

        <!-- Botão de retorno -->
        <?php
        // Tenta definir o caminho base se a constante não estiver disponível
        $baseUrl = defined('BASE_URL') ? BASE_URL : '/TCC-ETEC';
        ?>
        <a href="<?= $baseUrl ?>/" class="btn-back">
            ← Voltar para a página inicial
        </a>

        <p class="error-footer">
            Belezou App &mdash; Se o problema persistir, entre em contato com o suporte.
        </p>

    </div>

</body>

</html>