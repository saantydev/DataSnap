<?php
// Verificar estado del token de Google
session_start();

if (!isset($_SESSION['user_id'])) {
    die('<h1>Error</h1><p>Debes estar logueado.</p><a href="/login">Ir a Login</a>');
}

$userId = $_SESSION['user_id'];

?>
<!DOCTYPE html>
<html>
<head>
    <title>Estado Google Drive - DataSnap</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .status { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; }
        .warning { background-color: #fff3cd; border: 1px solid #ffeaa7; }
        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; }
        .btn { padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>🔗 Estado de Conexión con Google Drive</h1>
    <p><strong>Usuario ID:</strong> <?php echo $userId; ?></p>
    <hr>

<?php
require_once __DIR__ . '/src/Models/conexion.php';

// Obtener datos del usuario
$stmt = mysqli_prepare($conn, "SELECT username, email, google_refresh_token FROM users WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($user) {
    echo "<p><strong>Usuario:</strong> {$user['username']}</p>";
    echo "<p><strong>Email:</strong> {$user['email']}</p>";
    
    if (!empty($user['google_refresh_token'])) {
        echo "<div class='status success'>";
        echo "<h3>✅ Google Drive Conectado</h3>";
        echo "<p>Tu cuenta está conectada correctamente con Google Drive.</p>";
        echo "<p><strong>Token:</strong> " . substr($user['google_refresh_token'], 0, 20) . "...</p>";
        echo "<p><strong>Estado:</strong> Activo y funcionando</p>";
        echo "</div>";
        
        // Test de conectividad con Render
        echo "<div class='status'>";
        echo "<h3>🔄 Probando Conectividad con Render...</h3>";
        
        $renderUrl = "https://datasnap-panel.onrender.com/upload_original";
        $ch = curl_init($renderUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 500) {
            echo "<p>✅ <strong>Render está accesible</strong> (HTTP $httpCode)</p>";
            echo "<p>Los archivos deberían subirse correctamente a Google Drive.</p>";
        } else {
            echo "<p>❌ <strong>Render no está disponible</strong> (HTTP $httpCode)</p>";
            echo "<p>Los archivos se guardarán en la base de datos pero no en Google Drive hasta que Render esté disponible.</p>";
        }
        echo "</div>";
        
    } else {
        echo "<div class='status error'>";
        echo "<h3>❌ Google Drive NO Conectado</h3>";
        echo "<p>Tu cuenta no está conectada con Google Drive.</p>";
        echo "<p><strong>Problema:</strong> No hay refresh token guardado</p>";
        echo "<p><strong>Solución:</strong> Necesitas reconectar tu cuenta</p>";
        echo "<br>";
        echo "<a href='/auth/google' class='btn'>🔗 Conectar con Google Drive</a>";
        echo "</div>";
        
        echo "<div class='status warning'>";
        echo "<h3>⚠️ ¿Por qué pasó esto?</h3>";
        echo "<ul>";
        echo "<li>El token de Google expiró</li>";
        echo "<li>Se revocó el acceso desde Google</li>";
        echo "<li>Hubo un error en la autenticación anterior</li>";
        echo "</ul>";
        echo "<p><strong>Nota:</strong> Esto es normal y se puede solucionar fácilmente reconectando.</p>";
        echo "</div>";
    }
} else {
    echo "<div class='status error'>";
    echo "<h3>❌ Usuario no encontrado</h3>";
    echo "</div>";
}
?>

    <hr>
    <p><a href="/panel">← Volver al Panel</a> | <a href="/files">Ver Mis Archivos</a></p>
</body>
</html>