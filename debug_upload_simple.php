<?php
// Debug simple para reemplazar temporalmente /files/upload
session_start();

// Configurar headers para JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Log todo lo que llega
error_log("=== DEBUG UPLOAD SIMPLE ===");
error_log("Method: " . $_SERVER['REQUEST_METHOD']);
error_log("URI: " . $_SERVER['REQUEST_URI']);
error_log("Session ID: " . session_id());
error_log("User ID: " . ($_SESSION['user_id'] ?? 'NO_SESSION'));
error_log("Files: " . print_r($_FILES, true));
error_log("POST: " . print_r($_POST, true));

try {
    // Verificar método
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }
    
    // Verificar sesión
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Usuario no autenticado');
    }
    
    // Verificar archivo
    if (!isset($_FILES['archivo'])) {
        throw new Exception('No se envió archivo');
    }
    
    $archivo = $_FILES['archivo'];
    $userId = $_SESSION['user_id'];
    
    // Simular guardado en BD
    require_once __DIR__ . '/src/Models/conexion.php';
    require_once __DIR__ . '/src/Models/archivos_model.php';
    
    $nombre = basename($archivo['name']);
    $rutaRelativa = 'uploads/' . $nombre;
    $tamano = $archivo['size'];
    
    error_log("Insertando archivo: User=$userId, Nombre=$nombre, Tamaño=$tamano");
    
    $resultado = insertarArchivo($userId, $rutaRelativa, 'original', $nombre, $tamano);
    
    if ($resultado) {
        $idArchivo = mysqli_insert_id($conn);
        error_log("Archivo insertado con ID: $idArchivo");
        
        // Respuesta exitosa
        $response = [
            'success' => true,
            'id' => $idArchivo,
            'message' => 'DEBUG: Archivo subido correctamente (sin Drive)',
            'debug_info' => [
                'user_id' => $userId,
                'file_name' => $nombre,
                'file_size' => $tamano,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ];
        
        error_log("Enviando respuesta exitosa: " . json_encode($response));
        
    } else {
        throw new Exception('Error al guardar en BD: ' . mysqli_error($conn));
    }
    
} catch (Exception $e) {
    error_log("Error en debug upload: " . $e->getMessage());
    
    $response = [
        'success' => false,
        'message' => 'DEBUG ERROR: ' . $e->getMessage(),
        'debug_info' => [
            'error' => $e->getMessage(),
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ];
}

// Enviar respuesta
http_response_code($response['success'] ? 200 : 500);
echo json_encode($response);
exit();
?>