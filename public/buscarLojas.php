<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/connection.php';

$conexao = conectarBD();
$cidade_info = null;
$cidade_id = null;
$buscar_raio = isset($_GET['raio']) && $_GET['raio'] === '1';

// Verificar se foi passado cidade_id ou cidade_nome
if (isset($_GET['cidade_id']) && !empty($_GET['cidade_id']) && $_GET['cidade_id'] !== 'outra') {
    $cidade_id = intval($_GET['cidade_id']);
    
    // Buscar informações da cidade selecionada do banco
    $sql_cidade = "SELECT c.idCidade, c.nomeCidade, e.idEstado, e.nomeEstado, e.siglaEstado 
                   FROM cidade c 
                   INNER JOIN estado e ON c.estado_idEstado = e.idEstado 
                   WHERE c.idCidade = ?";
    $stmt_cidade = mysqli_prepare($conexao, $sql_cidade);
    mysqli_stmt_bind_param($stmt_cidade, "i", $cidade_id);
    mysqli_stmt_execute($stmt_cidade);
    $result_cidade = mysqli_stmt_get_result($stmt_cidade);
    $cidade_info = mysqli_fetch_assoc($result_cidade);
} else if (isset($_GET['cidade_nome']) && !empty($_GET['cidade_nome']) && isset($_GET['estado_id'])) {
    // Cidade manual - buscar informações do estado
    $estado_id = intval($_GET['estado_id']);
    $cidade_nome = trim($_GET['cidade_nome']);
    
    $sql_estado = "SELECT idEstado, nomeEstado, siglaEstado FROM estado WHERE idEstado = ?";
    $stmt_estado = mysqli_prepare($conexao, $sql_estado);
    mysqli_stmt_bind_param($stmt_estado, "i", $estado_id);
    mysqli_stmt_execute($stmt_estado);
    $result_estado = mysqli_stmt_get_result($stmt_estado);
    $estado_info = mysqli_fetch_assoc($result_estado);
    
    if ($estado_info) {
        // Criar objeto cidade_info fictício para cidade manual
        $cidade_info = [
            'idCidade' => null,
            'nomeCidade' => $cidade_nome,
            'idEstado' => $estado_info['idEstado'],
            'nomeEstado' => $estado_info['nomeEstado'],
            'siglaEstado' => $estado_info['siglaEstado']
        ];
    }
}

if (!$cidade_info) {
    echo json_encode(['error' => 'Cidade não especificada ou estado inválido']);
    mysqli_close($conexao);
    exit;
}

// Buscar lojas na cidade (apenas se a cidade estiver no banco)
$lojas = [];
if ($cidade_id) {
    $sql_lojas = "SELECT l.idlojas, l.nomeLoja, l.telefoneLoja, l.emailLoja, l.fotoLoja,
                         l.cep, l.logradouro, l.numero, l.bairro,
                         c.nomeCidade, e.siglaEstado
                  FROM lojas l
                  INNER JOIN cidade c ON l.cidade_idCidade = c.idCidade
                  INNER JOIN estado e ON c.estado_idEstado = e.idEstado
                  WHERE l.cidade_idCidade = ?";
    $stmt_lojas = mysqli_prepare($conexao, $sql_lojas);
    mysqli_stmt_bind_param($stmt_lojas, "i", $cidade_id);
    mysqli_stmt_execute($stmt_lojas);
    $result_lojas = mysqli_stmt_get_result($stmt_lojas);

    while ($loja = mysqli_fetch_assoc($result_lojas)) {
        $lojas[] = $loja;
    }
}
// Se cidade_id for null, significa que é cidade manual e não há lojas no banco

// Se não encontrou lojas e deve buscar em raio de 40km (apenas se cidade estiver no banco)
if (empty($lojas) && $buscar_raio && $cidade_id) {
    // Função para buscar coordenadas de uma cidade usando Nominatim
    function buscarCoordenadas($cidade, $estado) {
        $query = urlencode("$cidade, $estado, Brasil");
        $url = "https://nominatim.openstreetmap.org/search?format=json&q=$query&limit=1";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'SystechPump/1.0');
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        if ($data && count($data) > 0) {
            return [
                'lat' => floatval($data[0]['lat']),
                'lng' => floatval($data[0]['lon'])
            ];
        }
        return null;
    }
    
    // Função para calcular distância entre duas coordenadas (Haversine)
    function calcularDistancia($lat1, $lng1, $lat2, $lng2) {
        $raioTerra = 6371; // Raio da Terra em km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng/2) * sin($dLng/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distancia = $raioTerra * $c;
        
        return $distancia;
    }
    
    // Buscar coordenadas da cidade selecionada
    $coords_cidade = buscarCoordenadas($cidade_info['nomeCidade'], $cidade_info['siglaEstado']);
    
    if ($coords_cidade) {
        // Buscar todas as cidades do mesmo estado que têm lojas
        $sql_cidades_estado = "SELECT DISTINCT c.idCidade, c.nomeCidade, e.siglaEstado 
                              FROM cidade c 
                              INNER JOIN estado e ON c.estado_idEstado = e.idEstado 
                              INNER JOIN lojas l ON l.cidade_idCidade = c.idCidade
                              WHERE c.estado_idEstado = ? AND c.idCidade != ?";
        $stmt_cidades = mysqli_prepare($conexao, $sql_cidades_estado);
        mysqli_stmt_bind_param($stmt_cidades, "ii", $cidade_info['idEstado'], $cidade_id);
        mysqli_stmt_execute($stmt_cidades);
        $result_cidades = mysqli_stmt_get_result($stmt_cidades);
        
        $cidades_proximas = [];
        while ($cidade = mysqli_fetch_assoc($result_cidades)) {
            $coords_outra = buscarCoordenadas($cidade['nomeCidade'], $cidade['siglaEstado']);
            if ($coords_outra) {
                $distancia = calcularDistancia(
                    $coords_cidade['lat'], $coords_cidade['lng'],
                    $coords_outra['lat'], $coords_outra['lng']
                );
                
                // Se está dentro de 40km, adicionar à lista
                if ($distancia <= 40) {
                    $cidade['distancia'] = round($distancia, 1);
                    $cidades_proximas[] = $cidade;
                }
            }
        }
        
        // Ordenar por distância
        usort($cidades_proximas, function($a, $b) {
            return $a['distancia'] <=> $b['distancia'];
        });
        
        // Buscar lojas nas cidades próximas (limitar a 5 cidades mais próximas)
        $cidades_proximas = array_slice($cidades_proximas, 0, 5);
        
        if (!empty($cidades_proximas)) {
            $ids_cidades = array_column($cidades_proximas, 'idCidade');
            $placeholders = implode(',', array_fill(0, count($ids_cidades), '?'));
            
            $sql_lojas_raio = "SELECT l.idlojas, l.nomeLoja, l.telefoneLoja, l.emailLoja, l.fotoLoja,
                                      l.cep, l.logradouro, l.numero, l.bairro,
                                      c.nomeCidade, e.siglaEstado, c.idCidade
                               FROM lojas l
                               INNER JOIN cidade c ON l.cidade_idCidade = c.idCidade
                               INNER JOIN estado e ON c.estado_idEstado = e.idEstado
                               WHERE l.cidade_idCidade IN ($placeholders)
                               LIMIT 10";
            $stmt_raio = mysqli_prepare($conexao, $sql_lojas_raio);
            mysqli_stmt_bind_param($stmt_raio, str_repeat('i', count($ids_cidades)), ...$ids_cidades);
            mysqli_stmt_execute($stmt_raio);
            $result_raio = mysqli_stmt_get_result($stmt_raio);
            
            while ($loja = mysqli_fetch_assoc($result_raio)) {
                // Adicionar informação de distância
                foreach ($cidades_proximas as $cidade_prox) {
                    if ($cidade_prox['idCidade'] == $loja['idCidade']) {
                        $loja['distancia'] = $cidade_prox['distancia'];
                        break;
                    }
                }
                $lojas[] = $loja;
            }
        }
    }
}

// Informações da sede (Colatina - ES)
$sede_info = [
    'nome' => 'Systech Pump - Sede',
    'telefone' => '(27) 99999-9999', // Substitua pelo telefone real
    'endereco' => 'Endereço da Sede', // Substitua pelo endereço real
    'cidade' => 'Colatina',
    'estado' => 'ES',
    'email' => 'contato@systechpump.com.br' // Substitua pelo email real
];

// Se não encontrou lojas e cidade está no banco, buscar em raio automaticamente
if (empty($lojas) && $cidade_id) {
    $buscar_raio = true;
    // Re-executar busca em raio (código já está acima)
}

$response = [
    'cidade' => $cidade_info,
    'lojas' => $lojas,
    'sede' => $sede_info,
    'tem_lojas' => !empty($lojas),
    'busca_raio' => $buscar_raio && empty($lojas)
];

echo json_encode($response);
mysqli_close($conexao);
?>

