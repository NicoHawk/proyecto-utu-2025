<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

if (!isset($_GET['q']) || empty($_GET['q'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetro q requerido']);
    exit;
}

$query = urlencode($_GET['q']);
$url = "https://nominatim.openstreetmap.org/search?format=json&q={$query}&limit=1";

// Configurar contexto con User-Agent (requerido por Nominatim)
$options = [
    'http' => [
        'method' => 'GET',
        'header' => [
            'User-Agent: SistemaGestionAutosElectricos/1.0',
            'Accept: application/json'
        ]
    ]
];

$context = stream_context_create($options);
$response = @file_get_contents($url, false, $context);

if ($response === false) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo conectar al servicio de geocodificación']);
    exit;
}

echo $response;
?>
