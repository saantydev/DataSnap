<?php
require_once __DIR__ . '/src/Models/conexion.php';
require_once __DIR__ . '/src/Models/archivos_model.php';

echo "Actualizando tamaños de archivos...\n";

// Obtener todos los archivos con tamaño null o 0
$stmt = mysqli_prepare($conn, "SELECT id, nombre, ruta FROM archivos WHERE tamano IS NULL OR tamano = 0");
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$archivos = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$actualizados = 0;
$errores = 0;

foreach ($archivos as $archivo) {
    echo "Procesando archivo ID {$archivo['id']}: {$archivo['nombre']}\n";
    
    // Construir ruta completa
    $rutaCompleta = $archivo['ruta'];
    
    // Si es ruta relativa, convertir a absoluta
    if (strpos($rutaCompleta, '/') !== 0 && strpos($rutaCompleta, 'C:') !== 0) {
        $rutaCompleta = __DIR__ . '/' . $archivo['ruta'];
    }
    
    if (file_exists($rutaCompleta)) {
        $tamano = filesize($rutaCompleta);
        if (actualizarTamanoArchivo($archivo['id'], $tamano)) {
            echo "  ✓ Actualizado: {$tamano} bytes\n";
            $actualizados++;
        } else {
            echo "  ✗ Error al actualizar en BD\n";
            $errores++;
        }
    } else {
        echo "  ✗ Archivo no encontrado: {$rutaCompleta}\n";
        $errores++;
    }
}

echo "\nResumen:\n";
echo "Archivos actualizados: {$actualizados}\n";
echo "Errores: {$errores}\n";
echo "Total procesados: " . count($archivos) . "\n";
?>