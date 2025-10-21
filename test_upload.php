<?php
require_once __DIR__ . '/src/Models/conexion.php';
require_once __DIR__ . '/src/Models/archivos_model.php';

echo "=== TEST DE SUBIDA DE ARCHIVO ===\n\n";

// Simular datos de archivo
$userId = 1;
$nombre = 'test_archivo_' . time() . '.csv';
$rutaRelativa = 'uploads/' . $nombre;
$tamano = 2048; // 2KB

echo "Datos del archivo:\n";
echo "- Usuario ID: {$userId}\n";
echo "- Nombre: {$nombre}\n";
echo "- Ruta: {$rutaRelativa}\n";
echo "- Tamaño: {$tamano} bytes\n\n";

// Intentar insertar usando la función del modelo
echo "Insertando archivo...\n";
$resultado = insertarArchivo($userId, $rutaRelativa, 'original', $nombre, $tamano);

if ($resultado) {
    echo "✓ Archivo insertado correctamente\n";
    
    // Verificar que se insertó
    $stmt = mysqli_prepare($conn, "SELECT * FROM archivos WHERE nombre = ? ORDER BY id DESC LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $nombre);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $archivo = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if ($archivo) {
        echo "✓ Archivo encontrado en BD:\n";
        echo "  - ID: {$archivo['id']}\n";
        echo "  - Nombre: {$archivo['nombre']}\n";
        echo "  - Tamaño: {$archivo['tamano']} bytes\n";
        echo "  - Estado: {$archivo['estado']}\n";
        echo "  - Fecha: {$archivo['fecha_subida']}\n";
        
        // Limpiar - eliminar el archivo de test
        mysqli_query($conn, "DELETE FROM archivos WHERE id = {$archivo['id']}");
        echo "✓ Archivo de test eliminado\n";
    } else {
        echo "✗ Archivo no encontrado después de insertar\n";
    }
} else {
    echo "✗ Error al insertar archivo\n";
    echo "Error MySQL: " . mysqli_error($conn) . "\n";
}

echo "\n=== FIN DEL TEST ===\n";
?>