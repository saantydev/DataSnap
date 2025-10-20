<?php
session_start();
require_once __DIR__ . '/src/Models/conexion.php';
require_once __DIR__ . '/src/Models/archivos_model.php';

echo "=== DEBUG API ARCHIVOS ===\n\n";

// Simular usuario logueado
$_SESSION['user_id'] = 1;
$userId = $_SESSION['user_id'];

echo "Usuario ID: {$userId}\n\n";

// Test directo del modelo
echo "--- Test modelo obtenerArchivosPorUsuario ---\n";
$archivos = obtenerArchivosPorUsuario($userId);

if ($archivos !== false) {
    echo "✓ Función del modelo funciona\n";
    echo "Total archivos encontrados: " . count($archivos) . "\n\n";
    
    if (count($archivos) > 0) {
        echo "Primeros 3 archivos:\n";
        for ($i = 0; $i < min(3, count($archivos)); $i++) {
            $archivo = $archivos[$i];
            echo "- ID: {$archivo['id']}, Nombre: {$archivo['nombre']}, Tamaño: {$archivo['tamano']}\n";
        }
    }
} else {
    echo "✗ Error en función del modelo\n";
    echo "Error MySQL: " . mysqli_error($conn) . "\n";
}

echo "\n--- Test respuesta JSON ---\n";
header('Content-Type: application/json');
if ($archivos !== false) {
    $response = [
        'success' => true,
        'archivos' => $archivos
    ];
    echo json_encode($response, JSON_PRETTY_PRINT);
} else {
    $response = [
        'success' => false,
        'message' => 'Error al obtener los archivos'
    ];
    echo json_encode($response, JSON_PRETTY_PRINT);
}
?>