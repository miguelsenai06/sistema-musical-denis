-- Criar banco de dados com nome modificado
CREATE DATABASE IF NOT EXISTS sistema_musical_v2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Usar o banco de dados
USE sistema_musical_v2;

-- Criar tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL,
    senha VARCHAR(255) NOT NULL
);

-- Criar tabela de artistas (removido biografia, email e website)
CREATE TABLE IF NOT EXISTS artistas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    genero VARCHAR(50),
    pais VARCHAR(50),
    imagem VARCHAR(255)
);

-- Criar tabela de músicas (modificada para incluir link do YouTube e remover campos)
CREATE TABLE IF NOT EXISTS musicas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    artista_id INT,
    titulo VARCHAR(100) NOT NULL,
    album VARCHAR(100),
    genero VARCHAR(50),
    youtube_link VARCHAR(255), -- Novo campo para link do YouTube
    letra TEXT,
    imagem_capa VARCHAR(255),
    FOREIGN KEY (artista_id) REFERENCES artistas(id)
);

-- Instalar.php cria o usuário admin com hash gerado pelo PHP.
