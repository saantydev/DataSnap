<?php
// Visor de logs en tiempo real
session_start();

if (!isset($_SESSION['user_id'])) {
    die('<h1>Error</h1><p>Debes estar logueado.</p>');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Logs de DataSnap</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: monospace; margin: 20px; background: #1e1e1e; color: #fff; }
        .log-container { background: #2d2d2d; padding: 15px; border-radius: 5px; max-height: 600px; overflow-y: auto; }
        .error { color: #ff6b6b; }
        .success { color: #51cf66; }
        .info { color: #74c0fc; }
        .warning { color: #ffd43b; }
        .btn { padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
    </style>
</head>
<body>
    <h1>📋 Logs de DataSnap - Tiempo Real</h1>
    <p><a href="#" onclick="location.reload()" class="btn">🔄 Actualizar</a> <a href="/panel" class="btn">← Panel</a></p>
    
    <div class="log-container">
<?php
// Intentar leer logs del servidor
$logSources = [
    '/var/log/apache2/error.log',
    '/var/log/nginx/error.log', 
    '/home/u214138677/logs/error_log',
    '/home/u214138677/public_html/tps25/datasnap/error.log',
    'error.log'
];

$found = false;
foreach ($logSources as $logFile) {
    if (file_exists($logFile) && is_readable($logFile)) {
        echo "<h3>📄 Logs desde: $logFile</h3>";
        
        // Leer las últimas 50 líneas
        $lines = file($logFile);
        $recentLines = array_slice($lines, -50);
        
        foreach ($recentLines as $line) {
            $line = htmlspecialchars(trim($line));
            
            // Colorear según el tipo de log
            if (strpos($line, 'ERROR') !== false || strpos($line, 'Fatal') !== false) {
                echo "<div class='error'>$line</div>";
            } elseif (strpos($line, 'SUCCESS') !== false || strpos($line, '✅') !== false) {
                echo "<div class='success'>$line</div>";
            } elseif (strpos($line, 'WARNING') !== false || strpos($line, '⚠️') !== false) {
                echo "<div class='warning'>$line</div>";
            } elseif (strpos($line, 'RENDER') !== false || strpos($line, 'Drive') !== false) {
                echo "<div class='info'>$line</div>";
            } else {
                echo "<div>$line</div>";
            }
        }
        $found = true;
        break;
    }
}

if (!$found) {
    echo "<h3>❌ No se pudieron encontrar logs del servidor</h3>";
    echo "<p>Ubicaciones buscadas:</p><ul>";
    foreach ($logSources as $source) {
        echo "<li>$source</li>";
    }
    echo "</ul>";
    
    // Mostrar logs de PHP si están disponibles
    if (function_exists('error_get_last')) {
        $lastError = error_get_last();
        if ($lastError) {
            echo "<h3>📋 Último Error PHP:</h3>";
            echo "<div class='error'>";
            echo "Tipo: " . $lastError['type'] . "<br>";
            echo "Mensaje: " . $lastError['message'] . "<br>";
            echo "Archivo: " . $lastError['file'] . "<br>";
            echo "Línea: " . $lastError['line'] . "<br>";
            echo "</div>";
        }
    }
}
?>
    </div>
    
    <script>
        // Auto-refresh cada 10 segundos
        setTimeout(() => {
            location.reload();
        }, 10000);
    </script>
</body>
</html>