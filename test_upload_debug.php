<?php
// Test para simular exactamente lo que hace el upload
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TEST UPLOAD DEBUG ===\n\n";

// Simular datos de upload
$userId = 14; // Tu user ID
$nombre = "test_debug.csv";
$rutaRelativa = "uploads/test_debug.csv";
$tamanoArchivo = 1024;

// 1. Test inserción en BD
echo "1. Insertando archivo en BD...\n";
require_once __DIR__ . '/src/Models/conexion.php';
require_once __DIR__ . '/src/Models/archivos_model.php';

$resultado = insertarArchivo($userId, $rutaRelativa, 'original', $nombre, $tamanoArchivo);

if ($resultado) {
    $idArchivo = mysqli_insert_id($conn);
    echo "✅ Archivo insertado con ID: $idArchivo\n\n";
    
    // 2. Test obtener usuario
    echo "2. Obteniendo datos del usuario...\n";
    require_once __DIR__ . '/src/Models/UserModel.php';
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/src/Core/Database.php';
    
    $config = require __DIR__ . '/config/database.php';
    $db = new \Core\Database($config);
    $userModel = new \Models\UserModel($db);
    
    $user = $userModel->getUserById($userId);
    
    if ($user && !empty($user['google_refresh_token'])) {
        echo "✅ Usuario tiene refresh token\n\n";
        
        // 3. Test llamada a Render (simulada)
        echo "3. Probando llamada a Render...\n";
        
        $renderUrl = "https://datasnap-panel.onrender.com/upload_original";
        
        // Crear archivo temporal para test
        $tempFile = tempnam(sys_get_temp_dir(), 'test_upload');
        file_put_contents($tempFile, "test,data\n1,2\n3,4");
        
        $ch = curl_init($renderUrl);
        $postData = [
            'file' => new \CURLFile($tempFile, 'text/csv', $nombre),
            'google_refresh_token' => $user['google_refresh_token']
        ];
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        echo "HTTP Code: $httpCode\n";
        echo "Curl Error: " . ($curlError ?: 'Ninguno') . "\n";
        echo "Response: " . substr($response, 0, 200) . "...\n\n";
        
        if ($httpCode == 200) {
            $renderResponse = json_decode($response, true);
            if ($renderResponse && isset($renderResponse['success']) && $renderResponse['success']) {
                echo "✅ Render respondió correctamente\n";
                
                $driveId = $renderResponse['drive_id'];
                $driveLink = $renderResponse['drive_link'];
                
                echo "Drive ID: $driveId\n";
                echo "Drive Link: $driveLink\n\n";
                
                // 4. Test encriptación y guardado
                echo "4. Probando encriptación y guardado...\n";
                require_once __DIR__ . '/config/encryption.php';
                
                try {
                    $encryptedDriveId = DriveEncryption::encryptDriveId($driveId);
                    $encryptedDriveLink = DriveEncryption::encryptLink($driveLink);
                    
                    $stmt = mysqli_prepare($conn, "UPDATE archivos SET drive_id_original = ?, drive_link_original = ? WHERE id = ?");
                    mysqli_stmt_bind_param($stmt, "ssi", $encryptedDriveId, $encryptedDriveLink, $idArchivo);
                    $updateResult = mysqli_stmt_execute($stmt);
                    
                    if ($updateResult) {
                        echo "✅ UPDATE exitoso con encriptación\n";
                        
                        // Verificar que se guardó
                        $checkStmt = mysqli_prepare($conn, "SELECT drive_id_original, drive_link_original FROM archivos WHERE id = ?");
                        mysqli_stmt_bind_param($checkStmt, "i", $idArchivo);
                        mysqli_stmt_execute($checkStmt);
                        $result = mysqli_stmt_get_result($checkStmt);
                        $row = mysqli_fetch_assoc($result);
                        
                        echo "Drive ID en BD: " . substr($row['drive_id_original'], 0, 30) . "...\n";
                        echo "Drive Link en BD: " . substr($row['drive_link_original'], 0, 30) . "...\n";
                        
                        mysqli_stmt_close($checkStmt);
                    } else {
                        echo "❌ Error en UPDATE: " . mysqli_error($conn) . "\n";
                    }
                    
                    mysqli_stmt_close($stmt);
                    
                } catch (Exception $e) {
                    echo "❌ Error en encriptación: " . $e->getMessage() . "\n";
                }
                
            } else {
                echo "❌ Render error: " . ($renderResponse['error'] ?? 'Respuesta inválida') . "\n";
            }
        } else {
            echo "❌ Render no disponible (HTTP $httpCode)\n";
        }
        
        // Limpiar archivo temporal
        unlink($tempFile);
        
    } else {
        echo "❌ Usuario no tiene refresh token\n";
    }
    
    // Limpiar test
    mysqli_query($conn, "DELETE FROM archivos WHERE id = $idArchivo");
    echo "\n✅ Test limpiado\n";
    
} else {
    echo "❌ Error insertando archivo: " . mysqli_error($conn) . "\n";
}

echo "\n=== FIN TEST ===\n";
?>