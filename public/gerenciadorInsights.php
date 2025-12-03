<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php?erro=Faça login para acessar");
    exit();
}

$usuario = $_SESSION['usuario'];
$tipoUsuario = $usuario['tipoUsuario'];
$idUsuario = $usuario['idUsuario'];

require_once "../config/connection.php";
$conexao = conectarBD();

// Verificar se a tabela existe
$sql_check = "SHOW TABLES LIKE 'acessos_cidade'";
$result_check = mysqli_query($conexao, $sql_check);
if (mysqli_num_rows($result_check) == 0) {
    // Criar tabela se não existir
    $sql_create = "CREATE TABLE IF NOT EXISTS acessos_cidade (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cidade_idCidade INT NOT NULL,
        data_acesso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (cidade_idCidade) REFERENCES cidade(idCidade) ON DELETE CASCADE,
        INDEX idx_cidade_data (cidade_idCidade, data_acesso)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    mysqli_query($conexao, $sql_create);
}

// Limpar acessos antigos
$sql_cleanup = "DELETE FROM acessos_cidade WHERE data_acesso < DATE_SUB(NOW(), INTERVAL 30 DAY)";
mysqli_query($conexao, $sql_cleanup);

$dados_grafico = [];
$filtro_estado = $_GET['estado'] ?? '';
$periodo = $_GET['periodo'] ?? '30'; // Padrão: último mês

if ($tipoUsuario === 'admin') {
    // Admin vê todas as cidades com filtro por estado
    $where = "WHERE a.data_acesso >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    if ($filtro_estado) {
        $where .= " AND e.idEstado = " . intval($filtro_estado);
    }
    
    $sql = "SELECT c.idCidade, c.nomeCidade, e.siglaEstado, COUNT(a.id) as total_acessos
            FROM cidade c
            INNER JOIN estado e ON c.estado_idEstado = e.idEstado
            LEFT JOIN acessos_cidade a ON c.idCidade = a.cidade_idCidade $where
            GROUP BY c.idCidade, c.nomeCidade, e.siglaEstado
            HAVING total_acessos > 0
            ORDER BY total_acessos DESC
            LIMIT 50";
    
    $result = mysqli_query($conexao, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $dados_grafico[] = $row;
    }
    
    // Buscar estados para filtro
    $sql_estados = "SELECT DISTINCT e.idEstado, e.nomeEstado, e.siglaEstado 
                    FROM estado e 
                    INNER JOIN cidade c ON e.idEstado = c.estado_idEstado
                    INNER JOIN acessos_cidade a ON c.idCidade = a.cidade_idCidade
                    WHERE a.data_acesso >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    ORDER BY e.nomeEstado";
    $result_estados = mysqli_query($conexao, $sql_estados);
    
} else if ($tipoUsuario === 'representante') {
    // Representante vê apenas suas cidades
    $sql = "SELECT c.idCidade, c.nomeCidade, e.siglaEstado, COUNT(a.id) as total_acessos
            FROM cidade c
            INNER JOIN estado e ON c.estado_idEstado = e.idEstado
            LEFT JOIN acessos_cidade a ON c.idCidade = a.cidade_idCidade 
                AND a.data_acesso >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            WHERE c.representante_id = ?
            GROUP BY c.idCidade, c.nomeCidade, e.siglaEstado
            ORDER BY total_acessos DESC";
    
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "i", $idUsuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $dados_grafico[] = $row;
    }
    
} else if ($tipoUsuario === 'lojista') {
    // Lojista vê acessos ao longo do tempo (gráfico de linhas)
    $periodo_dias = intval($periodo);
    
    // Buscar cidade da loja
    $sql_cidade = "SELECT c.idCidade, c.nomeCidade, e.siglaEstado
                   FROM usuario u
                   INNER JOIN lojas l ON u.loja_id = l.idlojas
                   INNER JOIN cidade c ON l.cidade_idCidade = c.idCidade
                   INNER JOIN estado e ON c.estado_idEstado = e.idEstado
                   WHERE u.idUsuario = ?";
    $stmt_cidade = mysqli_prepare($conexao, $sql_cidade);
    mysqli_stmt_bind_param($stmt_cidade, "i", $idUsuario);
    mysqli_stmt_execute($stmt_cidade);
    $result_cidade = mysqli_stmt_get_result($stmt_cidade);
    $cidade_info = mysqli_fetch_assoc($result_cidade);
    
    if ($cidade_info) {
        $idCidade = $cidade_info['idCidade'];
        
        // Buscar acessos agrupados por dia
        $sql = "SELECT DATE(a.data_acesso) as data_acesso, COUNT(a.id) as total_acessos
                FROM acessos_cidade a
                WHERE a.cidade_idCidade = ?
                  AND a.data_acesso >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(a.data_acesso)
                ORDER BY data_acesso ASC";
        
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $idCidade, $periodo_dias);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $dados_grafico[] = $row;
        }
    }
}

// Definir página de voltar
if ($tipoUsuario === 'admin') {
    $voltar = "adm.php";
} else if ($tipoUsuario === 'representante') {
    $voltar = "representante.php";
} else {
    $voltar = "loja.php";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insights - System Pump</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/adm.css">
    <link rel="stylesheet" href="./css/insights.css">
    <link rel="shortcut icon" href="./imgs/others/logo.png" type="image/png" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-container">
        <main class="main-content">
            <header class="header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <a href="<?php echo $voltar; ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                    <h1 class="mb-0"><i class="fas fa-chart-line"></i> Insights</h1>
                </div>
            </header>

            <div class="page-content">
                <div class="insights-container">
                    <div class="insights-header">
                        <?php if ($tipoUsuario === 'lojista'): ?>
                            <h2>Acessos ao Longo do Tempo</h2>
                            <div class="filtro-periodo">
                                <label for="filtroPeriodo">Período:</label>
                                <select id="filtroPeriodo" class="form-control">
                                    <option value="7" <?php echo $periodo == '7' ? 'selected' : ''; ?>>Últimos 7 dias</option>
                                    <option value="15" <?php echo $periodo == '15' ? 'selected' : ''; ?>>Últimos 15 dias</option>
                                    <option value="30" <?php echo $periodo == '30' ? 'selected' : ''; ?>>Último mês</option>
                                    <option value="60" <?php echo $periodo == '60' ? 'selected' : ''; ?>>Últimos 2 meses</option>
                                    <option value="90" <?php echo $periodo == '90' ? 'selected' : ''; ?>>Últimos 3 meses</option>
                                </select>
                            </div>
                        <?php else: ?>
                            <h2>Acessos por Cidade (Últimos 30 dias)</h2>
                            <?php if ($tipoUsuario === 'admin'): ?>
                            <div class="filtro-estado">
                                <label for="filtroEstado">Filtrar por Estado:</label>
                                <select id="filtroEstado" class="form-control">
                                    <option value="">Todos os estados</option>
                                    <?php 
                                    if (isset($result_estados)):
                                        mysqli_data_seek($result_estados, 0);
                                        while ($estado = mysqli_fetch_assoc($result_estados)): 
                                    ?>
                                        <option value="<?php echo $estado['idEstado']; ?>" 
                                            <?php echo ($filtro_estado == $estado['idEstado']) ? 'selected' : ''; ?>>
                                            <?php echo $estado['nomeEstado']; ?>
                                        </option>
                                    <?php 
                                        endwhile;
                                    endif; 
                                    ?>
                                </select>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($dados_grafico)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <?php if ($tipoUsuario === 'lojista'): ?>
                                Nenhum acesso registrado no período selecionado.
                            <?php else: ?>
                                Nenhum acesso registrado nos últimos 30 dias.
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="chart-wrapper">
                            <canvas id="graficoAcessos"></canvas>
                        </div>

                        <?php if ($tipoUsuario !== 'lojista'): ?>
                        <div class="tabela-acessos">
                            <h3>Detalhamento</h3>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Cidade</th>
                                        <th>Estado</th>
                                        <th>Total de Acessos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dados_grafico as $dado): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($dado['nomeCidade']); ?></td>
                                        <td><?php echo htmlspecialchars($dado['siglaEstado']); ?></td>
                                        <td><strong><?php echo $dado['total_acessos']; ?></strong></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        <?php if (!empty($dados_grafico)): ?>
        const ctx = document.getElementById('graficoAcessos').getContext('2d');
        const dados = <?php echo json_encode($dados_grafico); ?>;
        
        <?php if ($tipoUsuario === 'lojista'): ?>
        // Gráfico de linhas para lojista
        const labels = dados.map(item => {
            const date = new Date(item.data_acesso);
            return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
        });
        const valores = dados.map(item => parseInt(item.total_acessos));

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Acessos',
                    data: valores,
                    backgroundColor: 'rgba(14, 70, 161, 0.1)',
                    borderColor: 'rgba(14, 70, 161, 1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: 'rgba(14, 70, 161, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
        <?php else: ?>
        // Gráfico de barras para admin/representante
        const labels = dados.map(item => `${item.nomeCidade} - ${item.siglaEstado}`);
        const valores = dados.map(item => parseInt(item.total_acessos));

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Acessos nos últimos 30 dias',
                    data: valores,
                    backgroundColor: 'rgba(14, 70, 161, 0.8)',
                    borderColor: 'rgba(14, 70, 161, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });
        <?php endif; ?>
        <?php endif; ?>

        <?php if ($tipoUsuario === 'admin'): ?>
        document.getElementById('filtroEstado').addEventListener('change', function() {
            const estado = this.value;
            const url = new URL(window.location);
            if (estado) {
                url.searchParams.set('estado', estado);
            } else {
                url.searchParams.delete('estado');
            }
            window.location.href = url.toString();
        });
        <?php endif; ?>

        <?php if ($tipoUsuario === 'lojista'): ?>
        document.getElementById('filtroPeriodo').addEventListener('change', function() {
            const periodo = this.value;
            const url = new URL(window.location);
            url.searchParams.set('periodo', periodo);
            window.location.href = url.toString();
        });
        <?php endif; ?>
    </script>
</body>
</html>

