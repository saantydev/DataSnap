<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DEBUG UPLOAD PROCESS ===\n\n";

// 1. Test conexión BD
echo "1. Probando conexión a BD...\n";
try {
    require_once __DIR__ . '/src/Models/conexion.php';
    echo "✅ Conexión BD exitosa\n\n";
} catch (Exception $e) {
    echo "❌ Error BD: " . $e->getMessage() . "\n\n";
}

// 2. Test encriptación
echo "2. Probando encriptación...\n";
try {
    require_once __DIR__ . '/config/encryption.php';
    
    $testLink = "https://drive.google.com/file/d/TEST123/view";
    $testId = "TEST123";
    
    $encryptedLink = DriveEncryption::encryptLink($testLink);
    $encryptedId = DriveEncryption::encryptDriveId($testId);
    $decryptedLink = DriveEncryption::decryptLink($encryptedLink);
    
    echo "Link original: $testLink\n";
    echo "Link encriptado: $encryptedLink\n";
    echo "Link desencriptado: $decryptedLink\n";
    
    if ($testLink === $decryptedLink) {
        echo "✅ Encriptación funciona\n\n";
    } else {
        echo "❌ Error en encriptación\n\n";
    }
} catch (Exception $e) {
    echo "❌ Error encriptación: " . $e->getMessage() . "\n\n";
}

// 3. Test variables de entorno
echo "3. Probando variables de entorno...\n";
require_once __DIR__ . '/config/env.php';

$vars = [
    'GOOGLE_CLIENT_ID',
    'GOOGLE_CLIENT_SECRET', 
    'GOOGLE_REDIRECT_URI',
    'ENCRYPTION_KEY'
];

foreach ($vars as $var) {
    $value = getenv($var);
    if ($value) {
        echo "✅ $var: " . substr($value, 0, 20) . "...\n";
    } else {
        echo "❌ $var: NO DEFINIDA\n";
    }
}

echo "\n4. Test inserción BD...\n";
try {
    global $conn;
    require_once __DIR__ . '/src/Models/archivos_model.php';
    
    // Test inserción
    $testUserId = 1; // Asume que existe usuario con ID 1
    $testRuta = 'uploads/test.csv';
    $testNombre = 'test.csv';
    $testTamano = 1024;
    
    $resultado = insertarArchivo($testUserId, $testRuta, 'original', $testNombre, $testTamano);
    
    if ($resultado) {
        $testId = mysqli_insert_id($conn);
        echo "✅ Inserción exitosa, ID: $testId\n";
        
        // Test update con encriptación
        $testDriveId = "TEST_DRIVE_ID_123";
        $testDriveLink = "https://drive.google.com/file/d/TEST_DRIVE_ID_123/view";
        
        $encryptedDriveId = DriveEncryption::encryptDriveId($testDriveId);
        $encryptedDriveLink = DriveEncryption::encryptLink($testDriveLink);
        
        $stmt = mysqli_prepare($conn, "UPDATE archivos SET drive_id_original = ?, drive_link_original = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssi", $encryptedDriveId, $encryptedDriveLink, $testId);
        $updateResult = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        if ($updateResult) {
            echo "✅ Update con encriptación exitoso\n";
            
            // Verificar que se guardó encriptado
            $stmt = mysqli_prepare($conn, "SELECT drive_id_original, drive_link_original FROM archivos WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $testId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            
            echo "Drive ID en BD: " . substr($row['drive_id_original'], 0, 30) . "...\n";
            echo "Drive Link en BD: " . substr($row['drive_link_original'], 0, 30) . "...\n";
            
            // Limpiar test
            mysqli_query($conn, "DELETE FROM archivos WHERE id = $testId");
            echo "✅ Test limpiado\n";
        } else {
            echo "❌ Error en update: " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "❌ Error inserción: " . mysqli_error($conn) . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error BD test: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DEBUG ===\n";
?>