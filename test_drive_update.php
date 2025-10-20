<?php
// Test específico para el problema de NULL en campos drive
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TEST DRIVE UPDATE ===\n\n";

// Incluir archivos necesarios
require_once __DIR__ . '/config/encryption.php';
require_once __DIR__ . '/src/Models/conexion.php';

// Datos de prueba
$testDriveId = "1JQfOGrQQKfZUDgWmiTjStu6bleASohWA";
$testDriveLink = "https://drive.google.com/file/d/1JQfOGrQQKfZUDgWmiTjStu6bleASohWA/view";

echo "1. Datos originales:\n";
echo "Drive ID: $testDriveId\n";
echo "Drive Link: $testDriveLink\n\n";

// Test encriptación
echo "2. Probando encriptación...\n";
try {
    $encryptedDriveId = DriveEncryption::encryptDriveId($testDriveId);
    $encryptedDriveLink = DriveEncryption::encryptLink($testDriveLink);
    
    echo "Drive ID encriptado: $encryptedDriveId\n";
    echo "Drive Link encriptado: " . substr($encryptedDriveLink, 0, 50) . "...\n\n";
    
    // Test desencriptación
    $decryptedLink = DriveEncryption::decryptLink($encryptedDriveLink);
    echo "Link desencriptado: $decryptedLink\n";
    echo "Encriptación OK: " . ($testDriveLink === $decryptedLink ? "✅ SÍ" : "❌ NO") . "\n\n";
    
} catch (Exception $e) {
    echo "❌ Error en encriptación: " . $e->getMessage() . "\n\n";
    exit;
}

// Test BD - buscar el último archivo subido
echo "3. Buscando último archivo en BD...\n";
$query = "SELECT id, nombre, user_id, drive_id_original, drive_link_original FROM archivos ORDER BY id DESC LIMIT 1";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $archivo = mysqli_fetch_assoc($result);
    $archivoId = $archivo['id'];
    
    echo "Último archivo ID: $archivoId\n";
    echo "Nombre: {$archivo['nombre']}\n";
    echo "User ID: {$archivo['user_id']}\n";
    echo "Drive ID actual: " . ($archivo['drive_id_original'] ?: 'NULL') . "\n";
    echo "Drive Link actual: " . ($archivo['drive_link_original'] ?: 'NULL') . "\n\n";
    
    // Test UPDATE
    echo "4. Probando UPDATE con encriptación...\n";
    $stmt = mysqli_prepare($conn, "UPDATE archivos SET drive_id_original = ?, drive_link_original = ? WHERE id = ?");
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssi", $encryptedDriveId, $encryptedDriveLink, $archivoId);
        $updateResult = mysqli_stmt_execute($stmt);
        
        if ($updateResult) {
            echo "✅ UPDATE ejecutado correctamente\n";
            
            // Verificar el resultado
            $checkQuery = "SELECT drive_id_original, drive_link_original FROM archivos WHERE id = $archivoId";
            $checkResult = mysqli_query($conn, $checkQuery);
            $checkData = mysqli_fetch_assoc($checkResult);
            
            echo "Drive ID después del UPDATE: " . substr($checkData['drive_id_original'], 0, 30) . "...\n";
            echo "Drive Link después del UPDATE: " . substr($checkData['drive_link_original'], 0, 30) . "...\n";
            
            // Test desencriptación desde BD
            $decryptedFromDB = DriveEncryption::decryptLink($checkData['drive_link_original']);
            echo "Link desencriptado desde BD: $decryptedFromDB\n";
            echo "Coincide con original: " . ($testDriveLink === $decryptedFromDB ? "✅ SÍ" : "❌ NO") . "\n";
            
        } else {
            echo "❌ Error en UPDATE: " . mysqli_error($conn) . "\n";
        }
        
        mysqli_stmt_close($stmt);
    } else {
        echo "❌ Error preparando statement: " . mysqli_error($conn) . "\n";
    }
    
} else {
    echo "❌ No se encontraron archivos en la BD\n";
}

echo "\n=== FIN TEST ===\n";
?>