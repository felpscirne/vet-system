# Sistema de Agendamento Veterinário

Aplicação web simples para gerenciamento de consultas veterinárias, para a disciplina Desenvolvimento de Aplicativo Web I.

## Funcionalidades

- Cadastro e autenticação de usuários
- Agendamento de consultas para pets
- Visualizar, editar e excluir consultas
- Design responsivo
- Senhas seguras com hash

## Stack Tecnológica

- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5.3
- **Backend:** PHP 7.4+, PDO
- **Banco de Dados:** MySQL 8.0+
- **Segurança:** Prepared statements, hash de senhas

## Estrutura do Banco

```sql
CREATE DATABASE veterinary_system;
USE veterinary_system;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE appointments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    animal_name VARCHAR(100) NOT NULL,
    animal_age INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    reason TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

## Instalação

1. **Requisitos:**
   - PHP 7.4+
   - MySQL 8.0+
   - Servidor web (Apache/Nginx)

2. **Configuração:**
   ```bash
   git clone [url-do-repositorio]
   cd veterinary_system
   ```

3. **Banco de Dados:**
   - Criar banco usando o SQL acima
   - Atualizar credenciais em `config/database.php`

4. **Execução:**
   - Colocar arquivos no diretório do servidor web
   - Acessar via navegador

## Regras de Negócio

- Consultas apenas segunda-feira a sexta-feira, 08:00-18:00
- Apenas datas futuras
- Usuários podem editar/excluir apenas suas próprias consultas
- Mínimo 10 caracteres para motivo da consulta

## Estrutura do Projeto

```
veterinary_system/
├── config/database.php
├── includes/auth.php, header.php, footer.php
├── css/style.css
├── js/scripts.js
├── pages/login.php, register.php, dashboard.php, etc.
├── index.php
└── README.md
```