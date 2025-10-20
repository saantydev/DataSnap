<?php
// Test directo a Render para ver qué está pasando
session_start();

if (!isset($_SESSION['user_id'])) {
    die('<h1>Error</h1><p>Debes estar logueado.</p>');
}

$userId = $_SESSION['user_id'];

?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Render Directo</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: monospace; margin: 20px; }
        .step { background: #f8f9fa; padding: 10px; margin: 10px 0; border-left: 4px solid #007bff; }
        .error { border-left-color: #dc3545; background: #f8d7da; }
        .success { border-left-color: #28a745; background: #d4edda; }
        pre { background: #f1f1f1; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔍 Test Directo a Render</h1>
    
<?php
// Obtener token del usuario
require_once __DIR__ . '/src/Models/conexion.php';

$stmt = mysqli_prepare($conn, "SELECT google_refresh_token FROM users WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user || empty($user['google_refresh_token'])) {
    echo "<div class='error'>❌ No hay token de Google para el usuario</div>";
    exit;
}

echo "<div class='step'>";
echo "<h3>Token encontrado</h3>";
echo "Token: " . substr($user['google_refresh_token'], 0, 30) . "...<br>";
echo "</div>";

// Crear archivo de prueba
$testContent = "nombre,edad,ciudad\nJuan,25,Madrid\nMaria,30,Barcelona\nPedro,35,Valencia";
$tempFile = tempnam(sys_get_temp_dir(), 'datasnap_render_test');
file_put_contents($tempFile, $testContent);

echo "<div class='step'>";
echo "<h3>Enviando a Render...</h3>";

$renderUrl = "https://datasnap-panel.onrender.com/upload_original";

$ch = curl_init($renderUrl);
$postData = [
    'file' => new \CURLFile($tempFile, 'text/csv', 'test_render_direct.csv'),
    'google_refresh_token' => $user['google_refresh_token']
];

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$verboseOutput = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verboseOutput);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
$curlInfo = curl_getinfo($ch);

rewind($verboseOutput);
$verboseLog = stream_get_contents($verboseOutput);
fclose($verboseOutput);

curl_close($ch);

echo "URL: $renderUrl<br>";
echo "HTTP Code: $httpCode<br>";
echo "Curl Error: " . ($curlError ?: 'Ninguno') . "<br>";
echo "Response Length: " . strlen($response) . "<br>";
echo "Content Type: " . ($curlInfo['content_type'] ?? 'N/A') . "<br>";
echo "</div>";

echo "<div class='step'>";
echo "<h3>Respuesta Completa de Render</h3>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";
echo "</div>";

if ($httpCode == 200 && !empty($response)) {
    $renderResponse = json_decode($response, true);
    
    echo "<div class='step'>";
    echo "<h3>JSON Parseado</h3>";
    
    if ($renderResponse === null) {
        echo "<div class='error'>❌ Error parseando JSON: " . json_last_error_msg() . "</div>";
    } else {
        echo "<pre>" . print_r($renderResponse, true) . "</pre>";
        
        if (isset($renderResponse['success'])) {
            if ($renderResponse['success']) {
                echo "<div class='success'>✅ Render reporta éxito</div>";
                echo "Drive ID: " . ($renderResponse['drive_id'] ?? 'NO_PRESENTE') . "<br>";
                echo "Drive Link: " . ($renderResponse['drive_link'] ?? 'NO_PRESENTE') . "<br>";
            } else {
                echo "<div class='error'>❌ Render reporta error</div>";
                echo "Error: " . ($renderResponse['error'] ?? 'Sin mensaje de error') . "<br>";
            }
        } else {
            echo "<div class='error'>❌ Respuesta sin campo 'success'</div>";
        }
    }
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<h3>Error en la comunicación</h3>";
    echo "HTTP: $httpCode<br>";
    echo "Curl Error: $curlError<br>";
    echo "</div>";
}

echo "<div class='step'>";
echo "<h3>Información de cURL</h3>";
echo "<pre>" . print_r($curlInfo, true) . "</pre>";
echo "</div>";

if (!empty($verboseLog)) {
    echo "<div class='step'>";
    echo "<h3>Log Verbose de cURL</h3>";
    echo "<pre>" . htmlspecialchars($verboseLog) . "</pre>";
    echo "</div>";
}

// Limpiar archivo temporal
unlink($tempFile);
?>

    <p><a href="/panel">← Volver al Panel</a></p>
</body>
</html>