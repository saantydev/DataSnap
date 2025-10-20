<?php
session_start();
require_once __DIR__ . '/../Models/conexion.php';

// Verificar si el usuario está logueado
// Temporarily disabled for debugging
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit();
// }

// Función para validar tipos de archivo permitidos
function validarTipoArchivo($tipoArchivo) {
    $tiposPermitidos = [
        'application/json',
        'text/csv',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // XLSX
        'application/vnd.ms-excel', // XLS (por si acaso)
        'application/sql',
        'text/plain' // Para archivos SQL que podrían ser texto plano
    ];

    return in_array($tipoArchivo, $tiposPermitidos);
}

// Función para obtener extensión del archivo
function obtenerExtension($nombreArchivo) {
    return strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
}

// Función para validar extensión
function validarExtension($extension) {
    $extensionesPermitidas = ['json', 'csv', 'xlsx', 'xls', 'sql'];
    return in_array($extension, $extensionesPermitidas);
}

// Procesar subida de archivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo'])) {
    $archivo = $_FILES['archivo'];
    $errores = [];
    $mensaje = '';

    // Validar si hay errores en la subida
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        $errores[] = 'Error al subir el archivo.';
    } else {
        // Validar tipo de archivo
        $tipoArchivo = $archivo['type'];
        $extension = obtenerExtension($archivo['name']);

        if (!validarTipoArchivo($tipoArchivo) && !validarExtension($extension)) {
            $errores[] = 'Tipo de archivo no permitido. Solo se permiten: JSON, CSV, XLSX, XLS, SQL.';
        }

        // Validar tamaño del archivo (sin límite máximo para bases de datos extensas)
        // Removido límite de tamaño para permitir archivos grandes

        // Si no hay errores, procesar el archivo
        if (empty($errores)) {
            // Crear directorio uploads si no existe
            $directorioUploads = __DIR__ . '/../../uploads/';
            if (!is_dir($directorioUploads)) {
                mkdir($directorioUploads, 0755, true);
            }

            // Generar nombre único para el archivo
            $nombreUnico = uniqid() . '_' . basename($archivo['name']);
            $rutaDestino = $directorioUploads . $nombreUnico;

            // Mover archivo al directorio destino
            if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                // Insertar información en la base de datos
                $rutaRelativa = 'uploads/' . $nombreUnico;
                $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Usar ID de sesión o valor por defecto (para testing)

                $stmt = mysqli_prepare($conn, "INSERT INTO archivos (user_id, ruta, estado, fecha_subida) VALUES (?, ?, 'original', NOW())");
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "is", $userId, $rutaRelativa);
                    if (mysqli_stmt_execute($stmt)) {
                        $mensaje = 'Archivo subido exitosamente y listo para optimización.';
                        $archivoId = mysqli_insert_id($conn);
                    } else {
                        $errores[] = 'Error al guardar la información del archivo en la base de datos.';
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $errores[] = 'Error al preparar la consulta de base de datos.';
                }
            } else {
                $errores[] = 'Error al mover el archivo al directorio de destino.';
            }
        }
    }

    // Devolver respuesta JSON para AJAX o redirigir con mensaje
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => empty($errores),
            'message' => empty($errores) ? $mensaje : implode(' ', $errores),
            'archivo_id' => isset($archivoId) ? $archivoId : null
        ]);
        exit();
    } else {
        // Redirigir con mensaje
        $tipoMensaje = empty($errores) ? 'success' : 'error';
        $mensajeCodificado = urlencode(empty($errores) ? $mensaje : implode(' ', $errores));
        header("Location: panel.php?msg=$mensajeCodificado&type=$tipoMensaje");
        exit();
    }
}

// Check Google auth
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
$query = mysqli_query($conn, "SELECT google_refresh_token FROM users WHERE id = $userId");
$user = mysqli_fetch_assoc($query);
$hasGoogleAuth = !empty($user['google_refresh_token']);

// Mostrar la vista del panel
include __DIR__ . '/../Views/panel(prueba).html';
?>