<?php
// Test simple para verificar respuesta JSON
header('Content-Type: application/json');
http_response_code(200);

$response = [
    'success' => true,
    'message' => 'Test de respuesta JSON exitoso',
    'timestamp' => date('Y-m-d H:i:s'),
    'data' => [
        'test' => true,
        'server' => $_SERVER['SERVER_NAME'] ?? 'localhost'
    ]
];

echo json_encode($response);
exit();
?>