<?php
session_start();
require_once __DIR__ . '/../Models/conexion.php';
require_once __DIR__ . '/../Models/archivos_model.php';
require 'vendor/autoload.php';

use Google\Client;
use Google\Service\Drive;


// Verificar si el usuario está logueado
// Temporarily disabled for debugging
// if (!isset($_SESSION['user_id'])) {
//     if (isset($_GET['action']) && $_GET['action'] !== '') {
//         // Si es una petición AJAX, devolver error JSON
//         header('Content-Type: application/json');
//         echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
//         exit();
//     } else {
//         // Si es una petición normal, redirigir al login
//         header("Location: login.php");
//         exit();
//     }
// }


// Obtener la acción solicitada
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'listar':
        // Listar archivos del usuario
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Default to user 1 for testing
        $archivos = obtenerArchivosPorUsuario($userId);

        header('Content-Type: application/json');
        if ($archivos !== false) {
            echo json_encode([
                'success' => true,
                'archivos' => $archivos
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener los archivos'
            ]);
        }
        break;

    case 'optimizar':
        // Optimizar un archivo
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Default to user 1 for testing

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit();
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $archivoId = isset($input['id']) ? intval($input['id']) : 0;

        if (!$archivoId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID de archivo no válido']);
            exit();
        }

        // Verificar que el archivo pertenece al usuario
        $archivo = obtenerArchivoPorId($archivoId);
        if (!$archivo || $archivo['user_id'] != $userId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Archivo no encontrado o no autorizado']);
            exit();
        }

        // Cambiar estado a pendiente
        if (actualizarEstadoArchivo($archivoId, 'pendiente')) {
            // Procesar el archivo localmente
            require_once __DIR__ . '/../Controllers/FileController.php';
            require_once __DIR__ . '/../Core/Database.php';

            try {
                $db = new \Core\Database();
                $fileController = \Controllers\FileController::create($db);
                $fileController->processFileLocally($archivoId);

                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Archivo enviado a optimización correctamente'
                ]);
            } catch (\Exception $e) {
                // Revertir estado si hay error
                actualizarEstadoArchivo($archivoId, 'original');
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al procesar el archivo: ' . $e->getMessage()
                ]);
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar el estado del archivo'
            ]);
        }
        break;

    case 'descargar':
        // Descargar archivo optimizado
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Default to user 1 for testing
        $archivoId = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if (!$archivoId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID de archivo no válido']);
            exit();
        }

        // Verificar que el archivo pertenece al usuario y está optimizado
        $archivo = obtenerArchivoPorId($archivoId);
        if (!$archivo || $archivo['user_id'] != $userId || $archivo['estado'] !== 'optimizado') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Archivo no encontrado, no autorizado o no optimizado']);
            exit();
        }

        // Verificar que el archivo existe
        $rutaCompleta = __DIR__ . '/../../' . $archivo['ruta'];
        if (!file_exists($rutaCompleta)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Archivo no encontrado en el servidor']);
            exit();
        }

        // Obtener información del archivo
        $nombreArchivo = basename($archivo['ruta']);
        $tamanoArchivo = filesize($rutaCompleta);

        // Enviar headers para descarga
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Content-Length: ' . $tamanoArchivo);
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Leer y enviar el archivo
        readfile($rutaCompleta);
        exit();
        break;

    default:
        // Mostrar la vista de archivos
        include __DIR__ . '/../Views/archivos.html';
        break;
}
?>