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
git clone https://github.com/SEU_USUARIO/web_salao.git
```

#### Opção B (Via ZIP)

Baixe o ZIP do projeto no GitHub, extraia e coloque a pasta com o nome `web_salao` dentro de:

```
C:\xampp\htdocs\
```

---

### 3. Instalar o PHPMailer (Envio de E-mails)

O sistema utiliza a biblioteca PHPMailer para enviar códigos de verificação e redefinição de senha.

Abra o terminal dentro da pasta do projeto (`web_salao`) e rode o comando:

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
```

---

### 6. Acessar o Sistema

Com tudo configurado e o XAMPP rodando, abra o navegador e acesse:

👉 http://localhost/web_salao

---

## 📚 Observação

Projeto desenvolvido para fins acadêmicos — ETEC Bragança Paulista.