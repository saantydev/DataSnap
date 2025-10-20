<?php
require_once __DIR__ . '/src/Models/conexion.php';

echo "=== DIAGNÓSTICO DE BASE DE DATOS ===\n\n";

// 1. Verificar conexión
if ($conn) {
    echo "✓ Conexión a BD exitosa\n";
    echo "Base de datos: " . mysqli_get_server_info($conn) . "\n\n";
} else {
    echo "✗ Error de conexión: " . mysqli_connect_error() . "\n";
    exit;
}

// 2. Verificar tabla archivos
$result = mysqli_query($conn, "SHOW TABLES LIKE 'archivos'");
if (mysqli_num_rows($result) > 0) {
    echo "✓ Tabla 'archivos' existe\n";
} else {
    echo "✗ Tabla 'archivos' no existe\n";
    exit;
}

// 3. Verificar estructura de tabla
echo "\n--- Estructura de tabla 'archivos' ---\n";
$result = mysqli_query($conn, "DESCRIBE archivos");
while ($row = mysqli_fetch_assoc($result)) {
    echo "{$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Default']}\n";
}

// 4. Contar archivos existentes
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM archivos");
$row = mysqli_fetch_assoc($result);
echo "\n--- Total de archivos en BD ---\n";
echo "Total: {$row['total']} archivos\n";

// 5. Mostrar últimos 5 archivos
echo "\n--- Últimos 5 archivos ---\n";
$result = mysqli_query($conn, "SELECT id, nombre, tamano, estado, fecha_subida FROM archivos ORDER BY fecha_subida DESC LIMIT 5");
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $tamano = $row['tamano'] ? $row['tamano'] . ' bytes' : 'NULL';
        echo "ID: {$row['id']} | {$row['nombre']} | {$tamano} | {$row['estado']} | {$row['fecha_subida']}\n";
    }
} else {
    echo "No hay archivos en la base de datos\n";
}

// 6. Test de inserción
echo "\n--- Test de inserción ---\n";
$testNombre = 'test_' . time() . '.txt';
$testRuta = 'uploads/' . $testNombre;
$testTamano = 1024;
$testUserId = 1;

$stmt = mysqli_prepare($conn, "INSERT INTO archivos (user_id, nombre, ruta, tamano, estado, fecha_subida) VALUES (?, ?, ?, ?, 'original', NOW())");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "issi", $testUserId, $testNombre, $testRuta, $testTamano);
    if (mysqli_stmt_execute($stmt)) {
        $insertId = mysqli_insert_id($conn);
        echo "✓ Test de inserción exitoso - ID: {$insertId}\n";
        
        // Eliminar el registro de test
        mysqli_query($conn, "DELETE FROM archivos WHERE id = {$insertId}");
        echo "✓ Registro de test eliminado\n";
    } else {
        echo "✗ Error en test de inserción: " . mysqli_stmt_error($stmt) . "\n";
    }
    mysqli_stmt_close($stmt);
} else {
    echo "✗ Error preparando consulta: " . mysqli_error($conn) . "\n";
}

echo "\n=== FIN DEL DIAGNÓSTICO ===\n";
?>