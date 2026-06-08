# Belezou App ✂️💈

Sistema web premium para gestão de salões de beleza e barbearias, focado em conectar clientes, funcionários e a administração em uma única plataforma intuitiva, moderna e responsiva.

> ✅ **Status do Projeto:** Concluído

---

## 🎓 Sobre o Projeto (Acadêmico)

Este projeto foi desenvolvido como **Trabalho de Conclusão de Curso (TCC)** para a **ETEC de Bragança Paulista**.

**Integrantes do Grupo:**
- Cássia  
- Leonardo 
- Moisés  
- Vinícius  

---

## ⚡ Principais Recursos do Sistema

O **Belezou App** é uma solução completa em modelo *White-Label* que oferece:

### 📱 Para os Clientes
*   **Agendamento em 1-Clique ("Agendar Novamente")**: Atalho inteligente na home que identifica o último serviço e profissional realizados e carrega diretamente na etapa do calendário.
*   **Wizard de Agendamento Inteligente**: Assistente em passos para seleção de serviço, profissional e horários disponíveis.
*   **Skeleton Loaders Premium**: Efeitos de carregamento animados que suavizam a transição entre as etapas de agendamento.
*   **Histórico e Remarcação Direta**: Visualização de compromissos anteriores e futuros com a possibilidade de cancelar e remarcar horários instantaneamente.
*   **Central de Ajuda Dinâmica**: Informações do salão, FAQ contextual e mapa interativo integrado ao Google Maps com botão "Como Chegar" (GPS).

### 💈 Para os Profissionais (Funcionários)
*   **Calendário Interativo (FullCalendar)**: Gestão de compromissos em grade visual com suporte a agendamento manual direto pelo balcão.
*   **Atualização em Tempo Real (Server-Sent Events - SSE)**: Atualizações instantâneas de novas solicitações de agendamento no painel sem necessidade de recarregar a página.
*   **Gestão de Grade e Bloqueios**: Definição dinâmica de horários de expediente, dias de atendimento e criação de bloqueios de agenda para férias e folgas.
*   **Notificações Push**: Recebimento de alertas em tempo real no celular sobre novos agendamentos e cancelamentos.

### 💼 Para os Administradores (Gerência)
*   **Painel White-Label**: Customização dinâmica da identidade visual (logotipo e cores primária/secundária do sistema) que reflete em tempo real em todas as telas.
*   **Localização Dinâmica**: Configuração do endereço escrito, link do iframe de mapa e link do GPS que atualiza a tela do cliente dinamicamente.
*   **Relatórios Financeiros e Desempenho**:
    *   **Faturamento Bruto**: Soma de todos os agendamentos concluídos no período.
    *   **Custo Proporcional de Folha**: Cálculo automático do salário base dos funcionários ativos baseado no número exato de dias do período.
    *   **Faturamento Líquido (Lucro/Prejuízo)**: Lucro consolidado no período com suporte visual a déficits financeiros.
    *   **Ranking de Serviços e Retenção de Clientes**: Tabelas com dados consolidados dos serviços mais executados e clientes frequentes.
*   **Gestão de Cadastros**: Cadastro e controle de status de serviços, preços, durações e equipe de funcionários.
*   **Comunicado Oficial em Lote**: Envio de e-mail e push notification simultâneo para todos os clientes cadastrados comunicando o fechamento do salão.

---

## 🛠️ Tecnologias Utilizadas

*   **Backend:** PHP (Arquitetura MVC nativa reforçada com Camada de Serviços)
*   **Frontend:** HTML5, Vanilla CSS3 (Design moderno, flexbox/grid e animações suaves), JavaScript Vanilla
*   **Banco de Dados:** MySQL (PDO com transações robustas)
*   **Segurança:** Guarda CSRF global (`CsrfGuard.php`), criptografia de senhas (bcrypt), cookies seguros.
*   **Notificações & E-mail:** OneSignal REST API, PHPMailer
*   **Gráficos & Calendários:** Chart.js, FullCalendar.js

---

## 📐 Estrutura de Arquitetura (MVC + Services)

O sistema adota uma estrutura limpa e de baixo acoplamento:

```
├── app/
│   ├── Controllers/       # Recebimento de requisições HTTP e controle de telas
│   ├── Models/            # Consultas SQL e interações diretas com o banco
│   ├── Services/          # Regras de negócio complexas, disparos de e-mail/push e transações
│   ├── Helpers/           # Utilitários de segurança (CSRF) e formatação
│   └── Routes/            # Mapeador amigável de URLs do sistema
├── cron/                  # Rotinas em segundo plano (notificações 24h)
├── database/              # Singleton de conexão (PDO) e schema do banco
├── public/
│   ├── resources/         # Arquivos de estilização (CSS) e lógica do navegador (JS)
│   └── views/             # Telas PHP estruturadas por módulo
└── index.php              # Ponto de entrada (Front Controller) e injeção White-Label/CSRF
```

---

## ⚙️ Como baixar e rodar o projeto localmente

Siga o passo a passo abaixo para rodar o Belezou App no seu computador.

### 1. Pré-requisitos
Certifique-se de ter instalado em sua máquina:
*   **XAMPP** (ou WAMP/MAMP) para o servidor Apache e banco de dados MySQL
*   **Composer** (gerenciador de dependências do PHP)
*   **Git** (opcional, para clonar o repositório)

### 2. Baixar o Projeto
Coloque o projeto dentro da pasta pública do seu servidor local (no XAMPP, a pasta `htdocs`).

#### Opção A (Via Git)
Abra o terminal dentro da pasta `htdocs` e rode o comando:
```bash
git clone https://github.com/Hazard-lz/TCC-ETEC.git
```

#### Opção B (Via ZIP)
Baixe o ZIP do projeto no GitHub, extraia e coloque a pasta com o nome `TCC-ETEC` dentro de:
```
C:\xampp\htdocs\
```

### 3. Instalar Dependências do PHP
Abra o terminal dentro da pasta do projeto (`TCC-ETEC`) e rode:
```bash
composer install
```

### 4. Configurar o Banco de Dados
1. Abra o painel do XAMPP e inicie o Apache e o MySQL.
2. Acesse o phpMyAdmin no navegador: `http://localhost/phpmyadmin`
3. Crie um banco de dados vazio chamado `salao_db` (colação `utf8mb4_unicode_ci`).
4. Selecione o banco recém-criado, vá na aba **Importar** e selecione o arquivo `database/schema.sql`.

### 5. Configurar o Arquivo `.env`
1. Na raiz do projeto, copie o arquivo `.env.example` e renomeie para `.env`.
2. Abra o `.env` e preencha com as suas informações:
```env
DB_HOST=localhost
DB_NAME=salao_db
DB_USER=root
DB_PASS=

MAIL_HOST=smtp.dominio.com
MAIL_USER=contato@seudominio.com.br
MAIL_PASS=SuaSenha
MAIL_PORT=587

ONESIGNAL_APP_ID=seu_app_id_aqui
ONESIGNAL_REST_API_KEY=sua_rest_api_key_aqui
```

### 6. Acessar o Sistema
Abra o navegador e acesse:
👉 [http://localhost/TCC-ETEC](http://localhost/TCC-ETEC)

---

## 🔔 Configuração de Notificações Push & Automações

### 1. Posicionamento do Service Worker (Essencial para testes locais)
Para rodar localmente no XAMPP, copie o arquivo `OneSignalSDKWorker.js` da pasta do projeto e cole-o **diretamente na raiz da pasta htdocs** do XAMPP (`C:\xampp\htdocs\OneSignalSDKWorker.js`).

### 2. Lembretes de 24 horas (Cronjob)
O arquivo `cron/notificar_24h_agendamentos.php` deve ser programado para rodar a cada hora no servidor de hospedagem para disparar lembretes automáticos.
*   **No Windows (Agendador de Tarefas)**: Aponte a ação para o seu PHP.
    *   *Comando:* `"C:\xampp\php\php.exe" -f "C:\xampp\htdocs\TCC-ETEC\cron\notificar_24h_agendamentos.php"`

---

## 📚 Licença e Observações
Projeto acadêmico desenvolvido como Trabalho de Conclusão de Curso da ETEC Bragança Paulista. Uso livre para fins de estudo e aprendizagem.
