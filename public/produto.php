<?php
// Dados dos produtos
$produtos = [
    's-t8' => [
        'nome' => 'S-T8',
        'titulo' => 'Acionador de Válvula Solenoide',
        'subtitulo' => 'Controle sem fio para sistemas de irrigação',
        'descricao' => 'Solução inovadora para acionamento remoto de válvulas solenoides via rádio, ideal para culturas de café e melão. Tecnologia blindada contra raios e falhas.',
        'imagem' => './imgs/produtos/s-t8.png',
        'linha' => 'Linha S',
        'diferenciais' => [
            'Comunicação sem fio',
            'Proteção contra raios',
            'Alta durabilidade',
            'Fácil instalação',
            'Economia de energia',
            'Controle remoto preciso'
        ],
        'video' => 'https://www.youtube.com/embed/jUuqBZwwkQw?si=49ii6wYRh0BTwuHq'
    ],
    's-t16' => [
        'nome' => 'S-T16',
        'titulo' => 'Acionador de Válvula Solenoide',
        'subtitulo' => 'Versão avançada para maior capacidade',
        'descricao' => 'Controlador de válvulas solenoides com capacidade ampliada, desenvolvido para sistemas de irrigação de grande porte. Tecnologia de ponta para máxima eficiência.',
        'imagem' => './imgs/produtos/s-t16.png',
        'linha' => 'Linha S',
        'diferenciais' => [
            'Maior capacidade de controle',
            'Comunicação sem fio',
            'Proteção avançada',
            'Alta performance',
            'Economia de recursos',
            'Monitoramento em tempo real'
        ],
        'video' => 'https://www.youtube.com/embed/jUuqBZwwkQw?si=49ii6wYRh0BTwuHq'
    ],
    's-t8x' => [
        'nome' => 'S-T8X',
        'titulo' => 'Acionador de Válvula Solenoide',
        'subtitulo' => 'Versão expandida com recursos extras',
        'descricao' => 'Solução premium para acionamento de válvulas solenoides com recursos expandidos. Ideal para propriedades que exigem controle avançado e confiabilidade máxima.',
        'imagem' => './imgs/produtos/s-t8x.png',
        'linha' => 'Linha S',
        'diferenciais' => [
            'Recursos expandidos',
            'Comunicação sem fio',
            'Proteção reforçada',
            'Interface intuitiva',
            'Alta confiabilidade',
            'Suporte técnico dedicado'
        ],
        'video' => 'https://www.youtube.com/embed/jUuqBZwwkQw?si=49ii6wYRh0BTwuHq'
    ],
    's-r1e' => [
        'nome' => 'S-R1E',
        'titulo' => 'Receptor de Válvula Solenoide',
        'subtitulo' => 'Receptor robusto para ambientes externos',
        'descricao' => 'Receptor desenvolvido para operar em condições extremas, garantindo comunicação estável e duradoura em sistemas de irrigação.',
        'imagem' => './imgs/produtos/s-r1e.png',
        'linha' => 'Linha S',
        'diferenciais' => [
            'Resistente a intempéries',
            'Alcance ampliado',
            'Baixo consumo',
            'Instalação simples',
            'Sinal estável',
            'Longa durabilidade'
        ],
        'video' => 'https://www.youtube.com/embed/jUuqBZwwkQw?si=49ii6wYRh0BTwuHq'
    ],
    's-r1l' => [
        'nome' => 'S-R1L',
        'titulo' => 'Receptor de Válvula Solenoide',
        'subtitulo' => 'Versão longa distância',
        'descricao' => 'Receptor com alcance estendido, perfeito para propriedades rurais de grande extensão. Mantém comunicação estável mesmo em longas distâncias.',
        'imagem' => './imgs/produtos/s-r1l.png',
        'linha' => 'Linha S',
        'diferenciais' => [
            'Alcance estendido',
            'Sinal potente',
            'Resistente a interferências',
            'Economia de energia',
            'Fácil manutenção',
            'Alta confiabilidade'
        ],
        'video' => 'https://www.youtube.com/embed/jUuqBZwwkQw?si=49ii6wYRh0BTwuHq'
    ],
    's-r8xl' => [
        'nome' => 'S-R8XL',
        'titulo' => 'Receptor Multi-Canal',
        'subtitulo' => 'Controle múltiplas válvulas simultaneamente',
        'descricao' => 'Receptor avançado capaz de controlar múltiplas válvulas solenoides simultaneamente, otimizando o gerenciamento de sistemas de irrigação complexos.',
        'imagem' => './imgs/produtos/s-r8xl.png',
        'linha' => 'Linha S',
        'diferenciais' => [
            'Controle múltiplo',
            'Eficiência máxima',
            'Comunicação sem fio',
            'Interface avançada',
            'Escalabilidade',
            'Gestão centralizada'
        ],
        'video' => 'https://www.youtube.com/embed/jUuqBZwwkQw?si=49ii6wYRh0BTwuHq'
    ],
    'b-t1' => [
        'nome' => 'B-T1',
        'titulo' => 'Acionador de Bomba',
        'subtitulo' => 'Controle remoto para bombas de irrigação',
        'descricao' => 'Solução completa para acionamento remoto de bombas de irrigação via rádio. Elimina a necessidade de fios e microtubos, revolucionando o controle de irrigação.',
        'imagem' => './imgs/produtos/b-t1.png',
        'linha' => 'Linha B',
        'diferenciais' => [
            'Acionamento remoto',
            'Sem necessidade de fios',
            'Proteção contra sobrecarga',
            'Economia de energia',
            'Instalação simplificada',
            'Controle preciso'
        ],
        'video' => 'https://www.youtube.com/embed/jUuqBZwwkQw?si=49ii6wYRh0BTwuHq'
    ],
    'b-r1' => [
        'nome' => 'B-R1',
        'titulo' => 'Receptor de Bomba',
        'subtitulo' => 'Receptor para acionamento de bombas',
        'descricao' => 'Receptor robusto desenvolvido especificamente para o acionamento de bombas de irrigação, garantindo comunicação confiável e operação eficiente.',
        'imagem' => './imgs/produtos/b-r1.png',
        'linha' => 'Linha B',
        'diferenciais' => [
            'Alta confiabilidade',
            'Resistente a vibrações',
            'Sinal estável',
            'Baixo consumo',
            'Proteção elétrica',
            'Longa vida útil'
        ],
        'video' => 'https://www.youtube.com/embed/jUuqBZwwkQw?si=49ii6wYRh0BTwuHq'
    ],
    'i-tv16' => [
        'nome' => 'I-TV16',
        'titulo' => 'Controlador de Inversor',
        'subtitulo' => 'Controle de inversores de frequência',
        'descricao' => 'Sistema avançado para controle e acionamento de inversores de frequência de bombas de irrigação, permitindo ajuste preciso da vazão e economia de energia.',
        'imagem' => './imgs/produtos/i-tv16.png',
        'linha' => 'Linha I',
        'diferenciais' => [
            'Controle de frequência',
            'Economia de energia',
            'Ajuste de vazão',
            'Proteção do motor',
            'Comunicação sem fio',
            'Monitoramento avançado'
        ],
        'video' => 'https://www.youtube.com/embed/jUuqBZwwkQw?si=49ii6wYRh0BTwuHq'
    ],
    'i-rv16' => [
        'nome' => 'I-RV16',
        'titulo' => 'Receptor de Inversor',
        'subtitulo' => 'Receptor para controle de inversores',
        'descricao' => 'Receptor especializado para controle remoto de inversores de frequência, oferecendo máxima precisão e eficiência no gerenciamento de bombas de irrigação.',
        'imagem' => './imgs/produtos/i-rv16.png',
        'linha' => 'Linha I',
        'diferenciais' => [
            'Precisão no controle',
            'Interface intuitiva',
            'Sinal estável',
            'Proteção integrada',
            'Economia de recursos',
            'Alta performance'
        ],
        'video' => 'https://www.youtube.com/embed/jUuqBZwwkQw?si=49ii6wYRh0BTwuHq'
    ],
    'p-t1' => [
        'nome' => 'P-T1',
        'titulo' => 'Acionador de Bomba Premium',
        'subtitulo' => 'Solução profissional para bombas',
        'descricao' => 'Acionador premium desenvolvido para o controle profissional de bombas de irrigação, oferecendo recursos avançados e máxima confiabilidade.',
        'imagem' => './imgs/produtos/p-t1.png',
        'linha' => 'Linha P',
        'diferenciais' => [
            'Performance premium',
            'Recursos avançados',
            'Alta confiabilidade',
            'Interface profissional',
            'Suporte técnico',
            'Garantia estendida'
        ],
        'video' => 'https://www.youtube.com/embed/jUuqBZwwkQw?si=49ii6wYRh0BTwuHq'
    ],
    'p-r1' => [
        'nome' => 'P-R1',
        'titulo' => 'Receptor de Bomba Premium',
        'subtitulo' => 'Receptor profissional de alta performance',
        'descricao' => 'Receptor premium com tecnologia de ponta para acionamento de bombas de irrigação, garantindo operação eficiente e confiável em qualquer condição.',
        'imagem' => './imgs/produtos/p-r1.png',
        'linha' => 'Linha P',
        'diferenciais' => [
            'Alta performance',
            'Tecnologia avançada',
            'Durabilidade superior',
            'Sinal potente',
            'Proteção completa',
            'Design profissional'
        ],
        'video' => 'https://www.youtube.com/embed/jUuqBZwwkQw?si=49ii6wYRh0BTwuHq'
    ]
];

$produto_id = $_GET['produto'] ?? '';
$produto = $produtos[$produto_id] ?? null;

if (!$produto) {
    header("Location: ./index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($produto['nome']); ?> - Systech Pump</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="shortcut icon" href="./imgs/others/logo.png" type="image/png" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/index.css">
    <style>
        :root {
            --azul-principal: #0e47a1;
            --azul-escuro: #0a3570;
            --azul-claro: #1e6dd8;
            --cinza-claro: #f5f7fa;
            --cinza-escuro: #2d3748;
        }

        .produto-hero {
            background: linear-gradient(135deg, var(--azul-principal) 0%, var(--azul-escuro) 100%);
            color: white;
            padding: 4rem 0;
            margin-top: 80px;
        }

        .produto-hero-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .produto-hero-text h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .produto-hero-text .subtitulo {
            font-size: 1.5rem;
            font-weight: 400;
            margin-bottom: 1.5rem;
            opacity: 0.9;
        }

        .produto-hero-text .descricao {
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 2rem;
            opacity: 0.95;
        }

        .produto-hero-image {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .produto-hero-image img {
            max-width: 100%;
            height: auto;
            filter: drop-shadow(0 20px 40px rgba(0,0,0,0.3));
        }

        .produto-section {
            padding: 5rem 0;
        }

        .container-produto {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .diferenciais-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .diferencial-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            border-left: 4px solid var(--azul-principal);
        }

        .diferencial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(14, 71, 161, 0.2);
        }

        .diferencial-card i {
            font-size: 2.5rem;
            color: var(--azul-principal);
            margin-bottom: 1rem;
        }

        .diferencial-card h3 {
            font-size: 1.3rem;
            color: var(--cinza-escuro);
            margin-bottom: 0.5rem;
        }

        .video-section {
            background: var(--cinza-claro);
            padding: 5rem 0;
        }

        .video-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }

        .btn-manual {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--azul-principal);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
            font-weight: 600;
        }

        .btn-manual:hover {
            background: var(--azul-secundario);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(14, 71, 161, 0.3);
        }

        .video-wrapper {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .video-wrapper iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .cta-section {
            background: linear-gradient(135deg, var(--azul-principal) 0%, var(--azul-escuro) 100%);
            color: white;
            padding: 4rem 0;
            text-align: center;
        }

        .cta-section h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .cta-section p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .btn-cta {
            display: inline-block;
            background: white;
            color: var(--azul-principal);
            padding: 1rem 3rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .btn-cta:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }

        .section-title {
            font-size: 2.5rem;
            color: var(--azul-principal);
            margin-bottom: 1rem;
            text-align: center;
        }

        .section-subtitle {
            font-size: 1.2rem;
            color: #666;
            text-align: center;
            margin-bottom: 3rem;
        }

        @media (max-width: 768px) {
            .produto-hero-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .produto-hero-text h1 {
                font-size: 2.5rem;
            }

            .diferenciais-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="container navbar-content">
            <a href="./index.php" class="logo-link">
                <img src="./imgs/others/logo.png" alt="Systech Pump" />
            </a>
            <nav class="navbar-navigation">
                <ul class="navbar-list">
                    <li><a href="./index.php#quem-somos">Quem somos</a></li>
                    <li><a href="./index.php#produtos">Produtos</a></li>
                    <li><a href="./queroVender.php">Quero vender</a></li>
                    <li><a href="./faleConosco.php">Fale conosco</a></li>
                    <li><a href="./login.php">Entrar</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="produto-hero">
        <div class="produto-hero-content">
            <div class="produto-hero-text">
                <h1><?php echo htmlspecialchars($produto['nome']); ?></h1>
                <p class="subtitulo"><?php echo htmlspecialchars($produto['subtitulo']); ?></p>
                <p class="descricao"><?php echo htmlspecialchars($produto['descricao']); ?></p>
                <a href="#contato" class="btn-cta">
                    <i class="fas fa-phone"></i> Solicitar Orçamento
                </a>
            </div>
            <div class="produto-hero-image">
                <img src="<?php echo htmlspecialchars($produto['imagem']); ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>">
            </div>
        </div>
    </section>

    <section class="video-section">
        <div class="container-produto">
            <h2 class="section-title">Demonstração</h2>
            <p class="section-subtitle">Veja o produto em ação</p>
            
            <div class="video-container">
                <div class="video-wrapper">
                    <iframe 
                        src="<?php echo htmlspecialchars($produto['video']); ?>" 
                        title="Demonstração <?php echo htmlspecialchars($produto['nome']); ?>" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                    </iframe>
                </div>
            </div>
            
            <!-- Link para Baixar Manual -->
            <div style="text-align: center; margin-top: 2rem;">
                <a href="./manuais/<?php echo htmlspecialchars($produto_id); ?>.pdf" target="_blank" class="btn-manual">
                    <i class="fas fa-download"></i> Baixar Manual
                </a>
            </div>
        </div>
    </section>

    <section class="produto-section">
        <div class="container-produto">
            <h2 class="section-title">Diferenciais</h2>
            <p class="section-subtitle">Tecnologia que faz a diferença no seu sistema de irrigação</p>
            
            <div class="diferenciais-grid">
                <?php foreach ($produto['diferenciais'] as $diferencial): ?>
                <div class="diferencial-card">
                    <i class="fas fa-check-circle"></i>
                    <h3><?php echo htmlspecialchars($diferencial); ?></h3>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="cta-section" id="contato">
        <div class="container-produto">
            <h2>Interessado no <?php echo htmlspecialchars($produto['nome']); ?>?</h2>
            <p>Entre em contato conosco e solicite um orçamento personalizado</p>
            <a href="./faleConosco.php" class="btn-cta">
                <i class="fas fa-envelope"></i> Fale Conosco
            </a>
        </div>
    </section>

    <footer>
        <div class="container footer-content">
            <section class="footer-section">
                <div class="footer-main">
                    <a href="./index.php" class="logo-link">
                        <img src="./imgs/others/logo.png" alt="Systech Pump" />
                    </a>
                    <p>CISA INDUSTRIA COMERCIO E REPRESENTAÇÃO DE EQUIPAMENTOS AGRICOLAS LTDA</p>
                    <p>CNPJ: 05.920.305/0001-35</p>
                </div>
            </section>
            <section class="footer-section">
                <nav>
                    <h4>Systech Pump</h4>
                    <ul>
                        <li><a href="./index.php#quem-somos">Quem somos</a></li>
                        <li><a href="./index.php#produtos">Produtos</a></li>
                        <li><a href="./queroVender.php">Quero vender</a></li>
                        <li><a href="./faleConosco.php">Fale conosco</a></li>
                    </ul>
                </nav>
            </section>
        </div>
    </footer>

    <script src="./js/index.js"></script>
</body>
</html>




