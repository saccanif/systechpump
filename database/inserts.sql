INSERT INTO estado (idEstado, nomeEstado, siglaEstado) VALUES
(1, 'Espírito Santo', 'ES'),
(2, 'Minas Gerais', 'MG'),
(3, 'Bahia', 'BA'),
(4, 'Rondônia', 'RO');

INSERT INTO cidade (idCidade, nomeCidade, estado_idEstado, qtdaAcesso) VALUES
(1, 'Vila Valério', 1, 0),
(2, 'Marilândia', 1, 0),
(3, 'São Gabriel da Palha', 1, 0),
(4, 'Resplendor', 2, 0),  -- Cidade de Minas
(5, 'Governador Lindenberg', 1, 0),
(6, 'Linhares', 1, 0),
(7, 'Montanha', 1, 0),
(8, 'Cacoal', 4, 0),  -- Cidade de Rondônia
(9, 'Colatina', 1, 0);

INSERT INTO Usuario (
    idUsuario,
    nomeUsuario,
    emailUsuario,
    senhaHASH_Usuario,
    tipoUsuario,
    avatar_url,
    UsuCriadoEm,
    cidade_idCidade,
    idEstado
) VALUES (
    1,
    'Administrador Geral',
    'admin@systechpump.com',
    SHA2('admin123', 256),  
    'admin',
    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcREOpm6KixFFhes1cw4usIlw1qMVORY4FN11Q&s',
    NOW(),
    9,  -- Colatina
    1   -- Espírito Santo
);
