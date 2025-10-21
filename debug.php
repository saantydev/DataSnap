<?php
echo "<h1>DEBUG - Configuración de Base de Datos</h1>";

// Verificar archivos de configuración
$configFiles = [
    'config/database.php',
    'src/config/database.php',
    'src/config/database.local.php',
    'src/config/database_alternativo.php'
];

foreach ($configFiles as $file) {
    echo "<h3>Archivo: $file</h3>";
    if (file_exists($file)) {
        echo "<p style='color: green;'>✓ Existe</p>";
        $config = require $file;
        echo "<pre>" . json_encode($config, JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "<p style='color: red;'>✗ No existe</p>";
    }
    echo "<hr>";
}

// Probar conexión directa
echo "<h3>Prueba de Conexión Directa</h3>";
try {
    $dsn = "mysql:host=82.112.247.153;dbname=u214138677_datasnap;charset=utf8mb4";
    $pdo = new PDO($dsn, 'u214138677_datasnap', 'Rasa@25ChrSt');
    echo "<p style='color: green;'>✓ Conexión exitosa con IP</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error con IP: " . $e->getMessage() . "</p>";
}

try {
    $dsn = "mysql:host=localhost;dbname=u214138677_datasnap;charset=utf8mb4";
    $pdo = new PDO($dsn, 'u214138677_datasnap', 'Rasa@25ChrSt');
    echo "<p style='color: green;'>✓ Conexión exitosa con localhost</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error con localhost: " . $e->getMessage() . "</p>";
}
?>