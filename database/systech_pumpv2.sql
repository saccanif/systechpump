-- ======================
-- Criar o novo banco
-- ======================
CREATE DATABASE systechpump_v2
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE systechpump_v2;

-- ======================
-- Tabelas
-- ======================

-- Tabela estado
CREATE TABLE estado (
    id_estado INT AUTO_INCREMENT PRIMARY KEY,
    nome_estado VARCHAR(255) NOT NULL,
    sigla_estado CHAR(2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela cidade
CREATE TABLE cidade (
    id_cidade INT AUTO_INCREMENT PRIMARY KEY,
    nome_cidade VARCHAR(255) NOT NULL,
    estado_id INT NOT NULL,
    quantidade_acessos INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cidade_estado FOREIGN KEY (estado_id) REFERENCES estado(id_estado) ON DELETE CASCADE
);

-- Tabela usuario
CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome_usuario VARCHAR(255) NOT NULL,
    email_usuario VARCHAR(255) UNIQUE NOT NULL,
    senha_hash_usuario TEXT NOT NULL,
    tipo_usuario ENUM('adm','representante','loja') NOT NULL,
    avatar_url VARCHAR(255),
    telefone VARCHAR(20),
    data_nascimento DATE,
    ultimo_login TIMESTAMP NULL,
    estado_id INT,
    cidade_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuario_estado FOREIGN KEY (estado_id) REFERENCES estado(id_estado) ON DELETE SET NULL,
    CONSTRAINT fk_usuario_cidade FOREIGN KEY (cidade_id) REFERENCES cidade(id_cidade) ON DELETE SET NULL
);

-- Tabela lojas
CREATE TABLE lojas (
    id_loja INT AUTO_INCREMENT PRIMARY KEY,
    nome_loja VARCHAR(255) NOT NULL,
    telefone_loja VARCHAR(20),
    email_loja VARCHAR(255),
    endereco VARCHAR(255),
    cnpj VARCHAR(20) UNIQUE,
    horario_funcionamento VARCHAR(100),
    usuario_id INT,
    cidade_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_loja_usuario FOREIGN KEY (usuario_id) REFERENCES usuario(id_usuario) ON DELETE SET NULL,
    CONSTRAINT fk_loja_cidade FOREIGN KEY (cidade_id) REFERENCES cidade(id_cidade) ON DELETE SET NULL
);

-- Tabela produtos
CREATE TABLE produtos (
    id_produto INT AUTO_INCREMENT PRIMARY KEY,
    nome_produto VARCHAR(255) NOT NULL,
    desc_produto TEXT,
    ativo_produto BOOLEAN DEFAULT TRUE,
    categoria ENUM('S','B','I','P'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela pedidos
CREATE TABLE pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pendente','enviado','entregue','cancelado') NOT NULL DEFAULT 'pendente',
    observacoes TEXT,
    usuario_id INT,
    loja_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pedido_usuario FOREIGN KEY (usuario_id) REFERENCES usuario(id_usuario) ON DELETE SET NULL,
    CONSTRAINT fk_pedido_loja FOREIGN KEY (loja_id) REFERENCES lojas(id_loja) ON DELETE SET NULL
);

-- Tabela pedido_itens
CREATE TABLE pedido_itens (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    quantidade_pedido_itens INT NOT NULL,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_item_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id_pedido) ON DELETE CASCADE,
    CONSTRAINT fk_item_produto FOREIGN KEY (produto_id) REFERENCES produtos(id_produto) ON DELETE CASCADE
);
