<?php
// Test que simula exactamente la lógica del FileController
session_start();

if (!isset($_SESSION['user_id'])) {
    die('<h1>Error</h1><p>Debes estar logueado.</p>');
}

$userId = $_SESSION['user_id'];

?>
<!DOCTYPE html>
<html>
<head>
    <title>Test FileController Logic</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: monospace; margin: 20px; }
        .step { background: #f8f9fa; padding: 10px; margin: 10px 0; border-left: 4px solid #007bff; }
        .error { border-left-color: #dc3545; background: #f8d7da; }
        .success { border-left-color: #28a745; background: #d4edda; }
    </style>
</head>
<body>
    <h1>🧪 Test de Lógica del FileController</h1>
    <p>Simulando exactamente lo que hace uploadWithDrive()</p>
    
<?php
// Simular exactamente lo que hace el FileController
require_once __DIR__ . '/src/Models/UserModel.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Core/Database.php';

echo "<div class='step'>";
echo "<h3>Paso 1: Inicializar UserModel (como FileController)</h3>";

try {
    // Usar la misma configuración que index.php
    $dbConfig = [
        'host' => 'localhost',
        'dbname' => 'u214138677_datasnap',
        'username' => 'u214138677_datasnap',
        'password' => 'Rasa@25ChrSt',
        'charset' => 'utf8mb4'
    ];
    $db = new \Core\Database($dbConfig);
    $userModel = new \Models\UserModel($db);
    
    echo "✅ UserModel inicializado correctamente<br>";
    echo "✅ Database conectada<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
echo "</div>";

echo "<div class='step'>";
echo "<h3>Paso 2: Obtener usuario (getUserById)</h3>";

try {
    $user = $userModel->getUserById($userId);
    
    if ($user) {
        echo "✅ Usuario encontrado<br>";
        echo "Username: {$user['username']}<br>";
        echo "Email: {$user['email']}<br>";
        
        if (empty($user['google_refresh_token'])) {
            echo "<div class='error'>❌ AQUÍ ESTÁ EL PROBLEMA: Token vacío en getUserById</div>";
        } else {
            echo "✅ Token existe: " . substr($user['google_refresh_token'], 0, 20) . "...<br>";
        }
    } else {
        echo "<div class='error'>❌ Usuario no encontrado con getUserById</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error en getUserById: " . $e->getMessage() . "</div>";
}
echo "</div>";

echo "<div class='step'>";
echo "<h3>Paso 3: Comparar con consulta directa</h3>";

require_once __DIR__ . '/src/Models/conexion.php';

$stmt = mysqli_prepare($conn, "SELECT username, email, google_refresh_token FROM users WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$directUser = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($directUser) {
    echo "✅ Consulta directa exitosa<br>";
    echo "Username: {$directUser['username']}<br>";
    echo "Email: {$directUser['email']}<br>";
    
    if (empty($directUser['google_refresh_token'])) {
        echo "❌ Token vacío en consulta directa<br>";
    } else {
        echo "✅ Token existe en consulta directa: " . substr($directUser['google_refresh_token'], 0, 20) . "...<br>";
    }
    
    // Comparar resultados
    echo "<h4>Comparación:</h4>";
    if (isset($user) && $user) {
        $tokenMatch = ($user['google_refresh_token'] ?? '') === ($directUser['google_refresh_token'] ?? '');
        echo "Tokens coinciden: " . ($tokenMatch ? '✅ SÍ' : '❌ NO') . "<br>";
        
        if (!$tokenMatch) {
            echo "<div class='error'>";
            echo "UserModel token: " . ($user['google_refresh_token'] ?? 'NULL') . "<br>";
            echo "Consulta directa token: " . ($directUser['google_refresh_token'] ?? 'NULL') . "<br>";
            echo "</div>";
        }
    }
}
echo "</div>";

echo "<div class='step'>";
echo "<h3>Paso 4: Test de Render (si hay token)</h3>";

if (isset($user) && !empty($user['google_refresh_token'])) {
    echo "🔄 Probando subida a Render...<br>";
    
    // Crear archivo de prueba
    $testContent = "test,data\n1,2\n3,4";
    $tempFile = tempnam(sys_get_temp_dir(), 'datasnap_test');
    file_put_contents($tempFile, $testContent);
    
    $renderUrl = "https://datasnap-panel.onrender.com/upload_original";
    
    $ch = curl_init($renderUrl);
    $postData = [
        'file' => new \CURLFile($tempFile, 'text/csv', 'test_filecontroller.csv'),
        'google_refresh_token' => $user['google_refresh_token']
    ];
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    echo "HTTP Code: $httpCode<br>";
    echo "Curl Error: " . ($curlError ?: 'Ninguno') . "<br>";
    echo "Response: " . substr($response, 0, 200) . "...<br>";
    
    if ($httpCode == 200) {
        $renderResponse = json_decode($response, true);
        if ($renderResponse && isset($renderResponse['success']) && $renderResponse['success']) {
            echo "<div class='success'>✅ Render respondió correctamente</div>";
            echo "Drive ID: " . ($renderResponse['drive_id'] ?? 'NO_ID') . "<br>";
            echo "Drive Link: " . ($renderResponse['drive_link'] ?? 'NO_LINK') . "<br>";
        } else {
            echo "<div class='error'>❌ Render error: " . ($renderResponse['error'] ?? 'Error desconocido') . "</div>";
        }
    } else {
        echo "<div class='error'>❌ Error HTTP: $httpCode</div>";
    }
    
    unlink($tempFile);
} else {
    echo "<div class='error'>❌ No se puede probar Render: No hay token</div>";
}
echo "</div>";
?>

    <p><a href="/panel">← Volver al Panel</a></p>
</body>
</html>