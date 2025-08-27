CREATE DATABASE IF NOT EXISTS systechpump;
USE systechpump;

CREATE TABLE estado (
    idEstado INT PRIMARY KEY,
    nomeEstado VARCHAR(100),
    siglaEstado CHAR(2)
);

CREATE TABLE cidade (
    idCidade INT PRIMARY KEY,
    nomeCidade VARCHAR(100),
    estado_idEstado INT,
    qtdaAcesso INT,
    FOREIGN KEY (estado_idEstado) REFERENCES estado(idEstado)
);

CREATE TABLE Usuario (
    idUsuario INT PRIMARY KEY,
    nomeUsuario VARCHAR(100),
    emailUsuario VARCHAR(100),
    senhaHASH_Usuario TEXT,
    tipoUsuario ENUM('admin', 'representante', 'lojista'),
    avatar_url TEXT,
    UsuCriadoEm TIMESTAMP,
    cidade_idCidade INT,
    idEstado INT,
    FOREIGN KEY (cidade_idCidade) REFERENCES cidade(idCidade),
    FOREIGN KEY (idEstado) REFERENCES estado(idEstado)
);

CREATE TABLE lojas (
    idlojas INT PRIMARY KEY,
    nomeLoja VARCHAR(100),
    telefoneLoja VARCHAR(20),
    emailLoja VARCHAR(100),
    lojaCriadoEm TIMESTAMP,
    cidade_idCidade INT,
    Usuario_idUsuario INT,
    fotoLoja VARCHAR(255),
    FOREIGN KEY (cidade_idCidade) REFERENCES cidade(idCidade),
    FOREIGN KEY (Usuario_idUsuario) REFERENCES Usuario(idUsuario)
);

CREATE TABLE produtos (
    idprodutos INT PRIMARY KEY,
    nomeProduto VARCHAR(100),
    descProduto TEXT,
    precoProduto DECIMAL(10, 2),
    ativoProduto TINYINT(1)
);

CREATE TABLE pedidos (
    idPedidos INT PRIMARY KEY,
    dataPedido TIMESTAMP,
    status ENUM('pendente', 'enviado', 'entregue', 'cancelado'),
    valorTotal FLOAT,
    lojas_idlojas INT,
    FOREIGN KEY (lojas_idlojas) REFERENCES lojas(idlojas)
);

CREATE TABLE Pedido_Itens (
    idPedItens INT PRIMARY KEY,
    quantPedItens INT,
    pedidos_idPedidos INT,
    produtos_idProdutos INT,
    FOREIGN KEY (pedidos_idPedidos) REFERENCES pedidos(idPedidos),
    FOREIGN KEY (produtos_idProdutos) REFERENCES produtos(idprodutos)
);
