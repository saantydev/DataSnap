<?php
// Script para verificar el estado de los archivos y la encriptación
require_once __DIR__ . '/src/Models/conexion.php';
require_once __DIR__ . '/config/encryption.php';

echo "=== VERIFICACIÓN DE ESTADO DE DRIVE ===\n\n";

// Obtener los últimos 5 archivos
$query = "SELECT id, nombre, user_id, drive_id_original, drive_link_original, fecha_subida 
          FROM archivos 
          WHERE user_id = 14 
          ORDER BY id DESC 
          LIMIT 5";

$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($archivo = mysqli_fetch_assoc($result)) {
        echo "=== ARCHIVO ID: {$archivo['id']} ===\n";
        echo "Nombre: {$archivo['nombre']}\n";
        echo "Fecha: {$archivo['fecha_subida']}\n";
        
        if (!empty($archivo['drive_id_original'])) {
            echo "Drive ID (encriptado): " . substr($archivo['drive_id_original'], 0, 30) . "...\n";
            
            if (!empty($archivo['drive_link_original'])) {
                echo "Drive Link (encriptado): " . substr($archivo['drive_link_original'], 0, 30) . "...\n";
                
                // Intentar desencriptar
                try {
                    $decryptedLink = DriveEncryption::decryptLink($archivo['drive_link_original']);
                    echo "Drive Link (desencriptado): $decryptedLink\n";
                    echo "✅ Encriptación funcionando correctamente\n";
                } catch (Exception $e) {
                    echo "❌ Error desencriptando: " . $e->getMessage() . "\n";
                }
            } else {
                echo "❌ Drive Link: NULL\n";
            }
        } else {
            echo "❌ Drive ID: NULL\n";
            echo "❌ Drive Link: NULL\n";
            echo "⏳ Archivo pendiente de subir a Drive\n";
        }
        
        echo "\n";
    }
} else {
    echo "❌ No se encontraron archivos\n";
}

echo "=== FIN VERIFICACIÓN ===\n";
?>