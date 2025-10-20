<?php

require_once __DIR__ . '/conexion.php';
<<<<<<< HEAD
require_once __DIR__ . '/../Core/Encryption.php';

use Core\Encryption;
=======
>>>>>>> 80eb21836f8ebbf25d3d8a477426d5caea9f6925

/**
 * Función para insertar un nuevo archivo en la base de datos.
 * @param int $user_id El ID del usuario.
 * @param string $ruta La ruta del archivo.
 * @param string $estado El estado del archivo (default 'pendiente').
 * @param string $nombre El nombre del archivo.
 * @param int $tamano El tamaño del archivo en bytes.
 * @return bool True si la inserción fue exitosa, false en caso contrario.
 */
function insertarArchivo($user_id, $ruta, $estado, $nombre = null, $tamano = null) {
    global $conn;

    // Calcular tamaño si no se proporciona
    if ($tamano === null && file_exists($ruta)) {
        $tamano = filesize($ruta);
    }
    
    // Extraer nombre si no se proporciona
    if ($nombre === null) {
        $nombre = basename($ruta);
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO archivos (user_id, nombre, ruta, tamano, estado, fecha_subida) VALUES (?, ?, ?, ?, ?, NOW())");

    if ($stmt === false) {
        error_log("Error al preparar la consulta de inserción: " . mysqli_error($conn));
        return false;
    }

    mysqli_stmt_bind_param($stmt, "issis", $user_id, $nombre, $ruta, $tamano, $estado);
    $exito = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $exito;
}

/**
 * Función para actualizar el estado de un archivo.
 * @param int $id El ID del archivo.
 * @param string $estado El nuevo estado.
 * @return bool True si la actualización fue exitosa, false en caso contrario.
 */
function actualizarEstadoArchivo($id, $estado) {
    global $conn;

    $stmt = mysqli_prepare($conn, "UPDATE archivos SET estado = ? WHERE id = ?");

    if ($stmt === false) {
        error_log("Error al preparar la consulta de actualización de estado: " . mysqli_error($conn));
        return false;
    }

    mysqli_stmt_bind_param($stmt, "si", $estado, $id);
    $exito = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $exito;
}

/**
 * Función para actualizar la ruta de un archivo.
 * @param int $id El ID del archivo.
 * @param string $ruta La nueva ruta.
 * @return bool True si la actualización fue exitosa, false en caso contrario.
 */
function actualizarRutaArchivo($id, $ruta) {
    global $conn;

    $stmt = mysqli_prepare($conn, "UPDATE archivos SET ruta = ? WHERE id = ?");

    if ($stmt === false) {
        error_log("Error al preparar la consulta de actualización de ruta: " . mysqli_error($conn));
        return false;
    }

    mysqli_stmt_bind_param($stmt, "si", $ruta, $id);
    $exito = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $exito;
}

/**
 * Función para "eliminar" un archivo (cambiar estado a 'borrado').
 * @param int $id El ID del archivo.
 * @return bool True si la actualización fue exitosa, false en caso contrario.
 */
function eliminarArchivo($id) {
    return actualizarEstadoArchivo($id, 'borrado');
}

/**
 * Función para obtener un archivo por su ID.
 * @param int $id El ID del archivo.
<<<<<<< HEAD
 * @param int|null $user_id El ID del usuario (para descifrar si es propietario).
 * @return array|false El array asociativo del archivo si se encuentra, false en caso contrario.
 */
function obtenerArchivoPorId($id, $user_id = null) {
=======
 * @return array|false El array asociativo del archivo si se encuentra, false en caso contrario.
 */
function obtenerArchivoPorId($id) {
>>>>>>> 80eb21836f8ebbf25d3d8a477426d5caea9f6925
    global $conn;

    $stmt = mysqli_prepare($conn, "SELECT * FROM archivos WHERE id = ?");

    if ($stmt === false) {
        error_log("Error al preparar la consulta (obtenerArchivoPorId): " . mysqli_error($conn));
        return false;
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $archivo = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);

<<<<<<< HEAD
    // Descifrar datos si el usuario es el propietario
    if ($archivo && $user_id && $archivo['user_id'] == $user_id) {
        $archivo = Encryption::decryptFileData($archivo, $user_id);
    }

=======
>>>>>>> 80eb21836f8ebbf25d3d8a477426d5caea9f6925
    return $archivo;
}

/**
 * Función para obtener todos los archivos de un usuario.
 * @param int $user_id El ID del usuario.
 * @return array|false El array de archivos si se encuentran, false en caso contrario.
 */
function obtenerArchivosPorUsuario($user_id) {
    global $conn;

<<<<<<< HEAD
    $stmt = mysqli_prepare($conn, "SELECT id, nombre, ruta, tamano, estado, fecha_subida, drive_id_original, drive_link_original, drive_id_optimizado, drive_link_optimizado FROM archivos WHERE user_id = ? AND estado != 'borrado' AND nombre != '' AND nombre IS NOT NULL ORDER BY fecha_subida DESC");
=======
    $stmt = mysqli_prepare($conn, "SELECT id, nombre, ruta, tamano, estado, fecha_subida FROM archivos WHERE user_id = ? AND estado != 'borrado' AND nombre != '' AND nombre IS NOT NULL ORDER BY fecha_subida DESC");
>>>>>>> 80eb21836f8ebbf25d3d8a477426d5caea9f6925

    if ($stmt === false) {
        error_log("Error al preparar la consulta (obtenerArchivosPorUsuario): " . mysqli_error($conn));
        return false;
    }

    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $archivos = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    
<<<<<<< HEAD
    // Calcular tamaño si no existe en BD y descifrar datos sensibles
=======
    // Calcular tamaño si no existe en BD
>>>>>>> 80eb21836f8ebbf25d3d8a477426d5caea9f6925
    foreach ($archivos as &$archivo) {
        if (($archivo['tamano'] === null || $archivo['tamano'] == 0) && !empty($archivo['ruta'])) {
            // Construir ruta completa correctamente
            $rutaCompleta = strpos($archivo['ruta'], '/') === 0 || strpos($archivo['ruta'], 'C:') === 0 
                ? $archivo['ruta'] 
                : __DIR__ . '/../../' . $archivo['ruta'];
                
            if (file_exists($rutaCompleta)) {
                $archivo['tamano'] = filesize($rutaCompleta);
                // Actualizar en BD para próximas consultas
                actualizarTamanoArchivo($archivo['id'], $archivo['tamano']);
            } else {
                $archivo['tamano'] = 0;
            }
        }
<<<<<<< HEAD
        
        // Descifrar datos sensibles para el usuario propietario
        $archivo = Encryption::decryptFileData($archivo, $user_id);
=======
>>>>>>> 80eb21836f8ebbf25d3d8a477426d5caea9f6925
    }
    mysqli_stmt_close($stmt);

    return $archivos;
}

/**
 * Función para obtener todos los archivos.
 * @return array|false El array de todos los archivos, false en caso contrario.
 */
function obtenerTodosArchivos() {
    global $conn;

    $stmt = mysqli_prepare($conn, "SELECT * FROM archivos ORDER BY fecha_subida DESC");

    if ($stmt === false) {
        error_log("Error al preparar la consulta (obtenerTodosArchivos): " . mysqli_error($conn));
        return false;
    }

    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $archivos = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    return $archivos;
}

/**
 * Función para obtener archivos por estado.
 * @param string $estado El estado a filtrar.
 * @return array|false El array de archivos con ese estado, false en caso contrario.
 */
function obtenerArchivosPorEstado($estado) {
    global $conn;

    $stmt = mysqli_prepare($conn, "SELECT * FROM archivos WHERE estado = ? ORDER BY fecha_subida DESC");

    if ($stmt === false) {
        error_log("Error al preparar la consulta (obtenerArchivosPorEstado): " . mysqli_error($conn));
        return false;
    }

    mysqli_stmt_bind_param($stmt, "s", $estado);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $archivos = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    return $archivos;
}

/**
 * Función para actualizar el tamaño de un archivo.
 * @param int $id El ID del archivo.
 * @param int $tamano El tamaño en bytes.
 * @return bool True si la actualización fue exitosa, false en caso contrario.
 */
function actualizarTamanoArchivo($id, $tamano) {
    global $conn;

    $stmt = mysqli_prepare($conn, "UPDATE archivos SET tamano = ? WHERE id = ?");

    if ($stmt === false) {
        error_log("Error al preparar la consulta de actualización de tamaño: " . mysqli_error($conn));
        return false;
    }

    mysqli_stmt_bind_param($stmt, "ii", $tamano, $id);
    $exito = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $exito;
}

/**
 * Función para actualizar archivo con nombre y tamaño.
 * @param int $id El ID del archivo.
 * @param string $nombre El nombre del archivo.
 * @param int $tamano El tamaño en bytes.
 * @return bool True si la actualización fue exitosa, false en caso contrario.
 */
function actualizarArchivoCompleto($id, $nombre, $tamano) {
    global $conn;

    $stmt = mysqli_prepare($conn, "UPDATE archivos SET nombre = ?, tamano = ? WHERE id = ?");

    if ($stmt === false) {
        error_log("Error al preparar la consulta de actualización completa: " . mysqli_error($conn));
        return false;
    }

    mysqli_stmt_bind_param($stmt, "sii", $nombre, $tamano, $id);
    $exito = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $exito;
}

?>