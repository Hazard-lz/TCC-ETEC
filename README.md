# Belezou App ✂️💈

Sistema web para gestão de salões de beleza e barbearias, focado em conectar clientes, funcionários e a administração em uma única plataforma intuitiva.

> ⚠️ **Status do Projeto:** Em desenvolvimento 🚧

## 🎓 Sobre o Projeto (Acadêmico)

Este projeto está sendo desenvolvido como **Trabalho de Conclusão de Curso (TCC)** para a **ETEC de Bragança Paulista**.

**Integrantes do Grupo:**
- Leonardo 
- Moisés  
- Vinícius  
- Cássia  

---

## 🛠️ Tecnologias Utilizadas

- **Backend:** PHP (Arquitetura MVC nativa)  
- **Frontend:** HTML5, CSS3, JavaScript Vanilla  
- **Banco de Dados:** MySQL (PDO com transações seguras)  
- **Dependências:** Composer, PHPMailer  

---

## ⚙️ Como baixar e rodar o projeto localmente

Siga o passo a passo abaixo para rodar o Belezou App no seu computador.

### 1. Pré-requisitos

Certifique-se de ter instalado em sua máquina:

- **XAMPP** (ou WAMP/MAMP) para o servidor Apache e banco de dados MySQL  
- **Composer** (gerenciador de dependências do PHP)  
- **Git** (opcional, para clonar o repositório)  

---

### 2. Baixar o Projeto

Você precisa colocar o projeto dentro da pasta pública do seu servidor local (no XAMPP, é a pasta `htdocs`).

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

---

### 3. Instalar o PHPMailer (Envio de E-mails)

O sistema utiliza a biblioteca PHPMailer para enviar códigos de verificação e redefinição de senha.

Abra o terminal dentro da pasta do projeto (`TCC-ETEC`) e rode o comando:

```bash
composer require phpmailer/phpmailer
```

---

### 4. Configurar o Banco de Dados

1. Abra o painel do XAMPP e inicie o Apache e o MySQL  
2. Acesse o phpMyAdmin no navegador:  
   ```
   http://localhost/phpmyadmin
   ```
3. Crie um banco de dados vazio chamado `salao_db` (com colação `utf8mb4_unicode_ci`)  
4. Selecione o banco recém-criado  
5. Vá na aba **Importar**  
6. Importe o arquivo `schema.sql` (localizado na pasta `database/` do projeto)  

---

### 5. Configurar o Arquivo `.env`

O sistema precisa de credenciais para conectar ao banco de dados e ao servidor de e-mail.

1. Na raiz do projeto, encontre o arquivo `.env.example`  
2. Faça uma cópia deste arquivo  
3. Renomeie para `.env`  
4. Abra o `.env` e preencha com suas informações (exemplo padrão do XAMPP):

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

---

### 6. Acessar o Sistema

Com tudo configurado e o XAMPP rodando, abra o navegador e acesse:

👉 http://localhost/TCC-ETEC

---

### 7. Configuração de Notificações Push (OneSignal & Cron)

O App Belezou possui alertas nativos gerenciados via OneSignal para avisar funcionários e clientes de marcações, bem como enviar lembretes 24h antes.

> **Obs:** É obrigatório criar uma conta gratuita no [OneSignal](https://onesignal.com/) para obter as chaves de integração e configurá-las no seu arquivo `.env` para que todo o sistema funcione.

1. **Posicionamento do Service Worker (Importante para testes locais!)**
   Para rodar localmente no XAMPP, copie o arquivo `OneSignalSDKWorker.js` da sua pasta e cole-o **livre, direto na pasta htdocs** raiz (`C:\xampp\htdocs\OneSignalSDKWorker.js`).
   
2. **Lembretes de 24 horas (Cronjob)**
   O arquivo `cron/notificar_24h_agendamentos.php` é feito para disparar notificações pontualmente 24 horas antes do agendamento.
   - **Para testar no Windows:** Abra o **Agendador de Tarefas**, adicione um gatilho Diário, e na Ação aponte para executar seu PHP.
   - Script de Ação Recomendado: `"C:\xampp\php\php.exe" -f "C:\xampp\htdocs\TCC-ETEC\cron\notificar_24h_agendamentos.php"`

---

## 📚 Observação

Projeto desenvolvido para fins acadêmicos — ETEC Bragança Paulista.
