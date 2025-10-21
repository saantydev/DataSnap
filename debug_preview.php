<?php
// Script de debug para verificar archivos optimizados
require_once __DIR__ . '/src/Core/Database.php';

try {
    $db = \Core\Database::getInstance();
    
    // Buscar archivos optimizados
    $stmt = $db->query("SELECT id, nombre, estado, ruta, ruta_optimizada FROM archivos WHERE estado = 'optimizado' LIMIT 5");
    $files = $stmt->fetchAll();
    
    echo "<h2>Archivos Optimizados en BD:</h2>\n";
    foreach ($files as $file) {
        echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>\n";
        echo "<strong>ID:</strong> " . $file['id'] . "<br>\n";
        echo "<strong>Nombre:</strong> " . $file['nombre'] . "<br>\n";
        echo "<strong>Estado:</strong> " . $file['estado'] . "<br>\n";
        echo "<strong>Ruta Original:</strong> " . $file['ruta'] . "<br>\n";
        echo "<strong>Ruta Optimizada:</strong> " . ($file['ruta_optimizada'] ?? 'NULL') . "<br>\n";
        
        // Verificar si los archivos existen
        $originalPath = __DIR__ . '/' . $file['ruta'];
        $optimizedPath = $file['ruta_optimizada'] ? __DIR__ . '/' . $file['ruta_optimizada'] : null;
        
        echo "<strong>Archivo Original Existe:</strong> " . (file_exists($originalPath) ? 'SÍ' : 'NO') . "<br>\n";
        echo "<strong>Archivo Optimizado Existe:</strong> " . ($optimizedPath && file_exists($optimizedPath) ? 'SÍ' : 'NO') . "<br>\n";
        
        if ($optimizedPath && file_exists($optimizedPath)) {
            $size = filesize($optimizedPath);
            echo "<strong>Tamaño Optimizado:</strong> " . $size . " bytes<br>\n";
        }
        
        echo "<a href='/files/preview?id=" . $file['id'] . "' target='_blank'>Ver Preview</a><br>\n";
        echo "</div>\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>