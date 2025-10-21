<?php
require_once __DIR__ . '/src/Models/conexion.php';

echo "=== LIMPIANDO ARCHIVOS INVÁLIDOS ===<br><br>";

// Eliminar archivos con tamaño 0 o NULL (mantener los que tienen nombre válido pero tamaño 0)
$stmt = mysqli_prepare($conn, "DELETE FROM archivos WHERE (nombre = '' OR nombre IS NULL) OR (tamano = 0 OR tamano IS NULL)");

if (mysqli_stmt_execute($stmt)) {
    $eliminados = mysqli_affected_rows($conn);
    echo "✓ Eliminados {$eliminados} archivos inválidos<br>";
} else {
    echo "✗ Error al eliminar archivos: " . mysqli_error($conn) . "<br>";
}
mysqli_stmt_close($stmt);

// Mostrar archivos restantes
echo "<br>--- ARCHIVOS RESTANTES ---<br>";
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM archivos");
$row = mysqli_fetch_assoc($result);
echo "Total archivos restantes: {$row['total']}<br><br>";

$result = mysqli_query($conn, "SELECT id, nombre, tamano, estado FROM archivos ORDER BY id DESC LIMIT 10");
if (mysqli_num_rows($result) > 0) {
    echo "Últimos 10 archivos:<br>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "- ID: {$row['id']}, Nombre: {$row['nombre']}, Tamaño: {$row['tamano']}, Estado: {$row['estado']}<br>";
    }
} else {
    echo "No hay archivos en la base de datos<br>";
}

echo "<br>=== FIN ===<br>";
?>