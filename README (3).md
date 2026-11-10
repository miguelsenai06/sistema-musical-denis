# 🎵 Sistema Musical — Fase 2

**Projeto acadêmico do Curso Técnico em Desenvolvimento de Sistemas — SENAI**

Sistema web desenvolvido com **PHP e MySQL** para gerenciar artistas e músicas. A aplicação inclui autenticação de usuários, cadastro e manutenção de registros, imagens e uma biblioteca musical com pesquisa e filtros.

## 📌 Sobre o projeto

Na Fase 2, as páginas estáticas do sistema foram integradas a um banco de dados MySQL, permitindo que as informações sejam cadastradas, consultadas, atualizadas e excluídas pela interface web.

O **MySQL Workbench** é utilizado para executar o script SQL e administrar o banco de dados. O **XAMPP/Apache** executa as páginas PHP no navegador.

## ✨ Funcionalidades

- 🔐 **Login e logout** com sessões PHP e senhas armazenadas como hash bcrypt.
- 🎤 **Gerenciamento de artistas:** cadastrar, listar, editar e excluir.
- 🎵 **Gerenciamento de músicas:** cadastrar, listar, editar e excluir, vinculando cada música a um artista.
- 🖼️ **Upload de imagens** de artistas e capas de músicas (JPG ou PNG, com redimensionamento pela extensão GD).
- ▶️ **Links do YouTube** associados às músicas.
- 🔎 **Biblioteca musical** com pesquisa e filtros por artista e gênero.
- 🛡️ **Proteção das páginas** para acesso somente após o login.

## 🛠️ Tecnologias utilizadas

| Tecnologia | Utilização |
| --- | --- |
| PHP 8+ | Backend, formulários, sessões e validações |
| MySQL | Armazenamento dos dados |
| MySQL Workbench | Criação e administração do banco de dados |
| HTML5 | Estrutura das páginas |
| CSS3 | Estilização e layout responsivo |
| XAMPP (Apache) | Servidor web local |
| PHP MySQLi e GD | Acesso ao banco e tratamento de imagens |

## 📁 Estrutura do projeto

```text
sistema-musical/
├── 01-EXECUTAR-NO-WORKBENCH.sql  # Criação do banco e das tabelas
├── 02-EXEMPLOS-OPCIONAL.sql      # Dados de exemplo (opcional)
├── bd.sql                        # Script do banco
├── cadastro_artista.php          # CRUD de artistas
├── cadastro_musica.php           # CRUD de músicas
├── conexao.php                   # Conexão PHP ↔ MySQL
├── config_local.example.php      # Modelo de configuração (para o GitHub)
├── funcoes.php                   # Funções auxiliares
├── geraSenha.php                 # Geração de hash de senha
├── index.php                     # Menu principal
├── listagem_musicas.php          # Biblioteca e filtros
├── login.php                     # Autenticação
├── logout.php                    # Encerramento da sessão
├── valida_sessao.php             # Proteção de acesso
├── styles.css                    # Estilos das páginas
├── img/                          # Imagens do projeto
└── README.md                     # Documentação
```

> `config_local.php` contém as configurações **locais** de acesso ao MySQL e não deve ser publicado com uma senha real. Crie esse arquivo no seu computador usando o modelo abaixo.

## 🚀 Como executar o projeto

### 1. Preparar o ambiente

Instale ou tenha disponível:

- PHP **8.0 ou superior** com as extensões **MySQLi** e **GD** habilitadas;
- um servidor **MySQL** e o **MySQL Workbench**;
- o **XAMPP** (ou outro servidor Apache com PHP).

Coloque a pasta do projeto em:

```text
C:\xampp\htdocs\sistema-musical
```

### 2. Criar o banco de dados no MySQL Workbench

1. Abra o **MySQL Workbench** e conecte-se ao seu servidor MySQL.
2. Selecione **File → Open SQL Script**.
3. Abra `01-EXECUTAR-NO-WORKBENCH.sql`.
4. Clique no ícone de **raio ⚡** para executar o script.
5. Em **SCHEMAS**, atualize e confira o banco `sistema_musical_v2`.

O banco utiliza três tabelas:

| Tabela | Finalidade |
| --- | --- |
| `usuarios` | Usuários autorizados a entrar no sistema |
| `artistas` | Informações dos artistas cadastrados |
| `musicas` | Informações das músicas e vínculo com os artistas |

A relação entre artistas e músicas é feita por `musicas.artista_id`, que referencia `artistas.id`.

### 3. Configurar a conexão PHP com MySQL

Na pasta do projeto, faça uma cópia de `config_local.example.php` e renomeie a cópia para `config_local.php`. Edite os campos de acordo com a conexão do seu MySQL Workbench:

```php
<?php
return [
    'host' => '127.0.0.1',
    'porta' => 3306,
    'usuario' => 'root',
    'senha' => '', // Coloque aqui sua senha local, se houver
    'banco' => 'sistema_musical_v2',
];
```

> Use a **porta, o usuário e a senha do servidor MySQL** aos quais o Workbench está conectado. Não compartilhe suas credenciais no GitHub.

### 4. Iniciar e acessar

1. Inicie o **Apache** no XAMPP e confira se o servidor **MySQL** está em execução.
2. No navegador, abra: **http://localhost/sistema-musical/login.php**.
3. Faça login com o usuário de teste configurado no banco.
4. Acesse o menu para gerenciar artistas, músicas e a biblioteca.

**Credenciais de demonstração (somente para ambiente local de aula):**

```text
Usuário: admin
Senha: 123
```

O script SQL cria esse usuário apenas quando ele ainda não existe. Se o usuário já estiver cadastrado, a senha anterior será mantida. **Troque a senha de teste antes de disponibilizar o projeto em qualquer servidor público.**

## ✅ Como testar

Após o login, cadastre um artista e uma música vinculada a ele. Depois, confira se os registros aparecem nas respectivas tabelas e na Biblioteca Musical. Teste também a edição, a exclusão de registros, os filtros e o acesso ao link do YouTube.

## 🔒 Cuidados ao publicar no GitHub

**Não envie `config_local.php` com a senha do seu MySQL.** Publique apenas `config_local.example.php` e adicione `config_local.php` ao `.gitignore`. Caso esteja enviando arquivos pelo site do GitHub, confira manualmente se o arquivo privado não foi selecionado.

Este é um **projeto educacional** desenvolvido para execução local; configurações de demonstração não devem ser usadas em produção.

---

**SENAI | Curso Técnico em Desenvolvimento de Sistemas | 2026**
