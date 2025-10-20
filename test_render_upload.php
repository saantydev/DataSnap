<?php
// Test manual de subida a Render
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TEST RENDER UPLOAD ===\n\n";

// Obtener datos del usuario
require_once __DIR__ . '/src/Models/conexion.php';
require_once __DIR__ . '/src/Models/UserModel.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Core/Database.php';

// Usar conexión directa
global $conn;
$userModel = null;

$userId = 14; // Tu user ID

// Obtener refresh token directamente de BD
$stmt = mysqli_prepare($conn, "SELECT google_refresh_token FROM users WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user || empty($user['google_refresh_token'])) {
    echo "❌ Usuario no tiene refresh token\n";
    echo "Token actual: " . ($user['google_refresh_token'] ?? 'NULL') . "\n";
    die();
}

echo "✅ Usuario tiene refresh token\n";
echo "Refresh token: " . substr($user['google_refresh_token'], 0, 20) . "...\n\n";

// Crear archivo de prueba
$testContent = "test,data,column\n1,2,3\n4,5,6\n7,8,9";
$tempFile = tempnam(sys_get_temp_dir(), 'datasnap_test');
file_put_contents($tempFile, $testContent);

echo "Archivo de prueba creado: $tempFile\n";
echo "Contenido: " . strlen($testContent) . " bytes\n\n";

// Test 1: Verificar conectividad con Render
echo "=== TEST 1: Conectividad con Render ===\n";
$renderUrl = "https://datasnap-panel.onrender.com/upload_original";

$ch = curl_init($renderUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_NOBODY, true); // Solo HEAD request
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Curl Error: " . ($curlError ?: 'Ninguno') . "\n";

if ($httpCode >= 200 && $httpCode < 500) {
    echo "✅ Render está accesible\n\n";
} else {
    echo "❌ Render no está accesible\n\n";
}

// Test 2: Subida real
echo "=== TEST 2: Subida Real ===\n";

$ch = curl_init($renderUrl);
$postData = [
    'file' => new \CURLFile($tempFile, 'text/csv', 'test_manual.csv'),
    'google_refresh_token' => $user['google_refresh_token']
];

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_VERBOSE, true);

// Capturar verbose output
$verboseFile = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verboseFile);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

// Leer verbose output
rewind($verboseFile);
$verboseLog = stream_get_contents($verboseFile);
fclose($verboseFile);

curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Curl Error: " . ($curlError ?: 'Ninguno') . "\n";
echo "Response Length: " . strlen($response) . " bytes\n";
echo "Response: " . substr($response, 0, 500) . (strlen($response) > 500 ? '...' : '') . "\n\n";

if ($httpCode == 200) {
    $renderResponse = json_decode($response, true);
    if ($renderResponse) {
        echo "✅ JSON válido recibido\n";
        echo "Success: " . ($renderResponse['success'] ? 'true' : 'false') . "\n";
        
        if (isset($renderResponse['success']) && $renderResponse['success']) {
            echo "Drive ID: " . ($renderResponse['drive_id'] ?? 'NO_ID') . "\n";
            echo "Drive Link: " . ($renderResponse['drive_link'] ?? 'NO_LINK') . "\n";
            echo "✅ Subida exitosa\n";
            
            // Test encriptación
            if (isset($renderResponse['drive_id']) && isset($renderResponse['drive_link'])) {
                echo "\n=== TEST 3: Encriptación ===\n";
                require_once __DIR__ . '/config/encryption.php';
                
                try {
                    $encryptedId = DriveEncryption::encryptDriveId($renderResponse['drive_id']);
                    $encryptedLink = DriveEncryption::encryptLink($renderResponse['drive_link']);
                    
                    echo "ID encriptado: " . substr($encryptedId, 0, 30) . "...\n";
                    echo "Link encriptado: " . substr($encryptedLink, 0, 30) . "...\n";
                    
                    $decryptedLink = DriveEncryption::decryptLink($encryptedLink);
                    echo "Link desencriptado: $decryptedLink\n";
                    
                    if ($decryptedLink === $renderResponse['drive_link']) {
                        echo "✅ Encriptación funcionando correctamente\n";
                    } else {
                        echo "❌ Error en encriptación\n";
                    }
                    
                } catch (Exception $e) {
                    echo "❌ Error encriptación: " . $e->getMessage() . "\n";
                }
            }
            
        } else {
            echo "❌ Render reportó error: " . ($renderResponse['error'] ?? 'Error desconocido') . "\n";
        }
    } else {
        echo "❌ Respuesta no es JSON válido\n";
    }
} else {
    echo "❌ Error HTTP: $httpCode\n";
}

echo "\n=== VERBOSE LOG ===\n";
echo $verboseLog;

// Limpiar
unlink($tempFile);

echo "\n=== FIN TEST ===\n";
?>