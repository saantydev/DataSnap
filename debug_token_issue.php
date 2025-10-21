<?php
// Debug del problema del token
session_start();

if (!isset($_SESSION['user_id'])) {
    die('<h1>Error</h1><p>Debes estar logueado.</p>');
}

$userId = $_SESSION['user_id'];

?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Token Issue</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .debug { background: #f8f9fa; padding: 15px; margin: 10px 0; border-left: 4px solid #007bff; }
        .error { border-left-color: #dc3545; background: #f8d7da; }
        .success { border-left-color: #28a745; background: #d4edda; }
    </style>
</head>
<body>
    <h1>🔍 Debug del Problema del Token</h1>
    
<?php
require_once __DIR__ . '/src/Models/conexion.php';

echo "<div class='debug'>";
echo "<h3>1. Información de Sesión</h3>";
echo "<p><strong>User ID de sesión:</strong> $userId</p>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "</div>";

echo "<div class='debug'>";
echo "<h3>2. Consulta Directa a BD</h3>";

// Consulta 1: Igual que check_google_token.php
$stmt1 = mysqli_prepare($conn, "SELECT username, email, google_refresh_token FROM users WHERE user_id = ?");
mysqli_stmt_bind_param($stmt1, "i", $userId);
mysqli_stmt_execute($stmt1);
$result1 = mysqli_stmt_get_result($stmt1);
$user1 = mysqli_fetch_assoc($result1);
mysqli_stmt_close($stmt1);

echo "<p><strong>Consulta 1 (como check_google_token):</strong></p>";
if ($user1) {
    echo "<p>Username: {$user1['username']}</p>";
    echo "<p>Email: {$user1['email']}</p>";
    echo "<p>Token existe: " . (!empty($user1['google_refresh_token']) ? 'SÍ' : 'NO') . "</p>";
    if (!empty($user1['google_refresh_token'])) {
        echo "<p>Token preview: " . substr($user1['google_refresh_token'], 0, 20) . "...</p>";
    }
} else {
    echo "<p class='error'>❌ Usuario no encontrado</p>";
}
echo "</div>";

echo "<div class='debug'>";
echo "<h3>3. Consulta Exacta del Test</h3>";

// Consulta 2: Igual que test_render_upload.php
$stmt2 = mysqli_prepare($conn, "SELECT google_refresh_token FROM users WHERE user_id = ?");
mysqli_stmt_bind_param($stmt2, "i", $userId);
mysqli_stmt_execute($stmt2);
$result2 = mysqli_stmt_get_result($stmt2);
$user2 = mysqli_fetch_assoc($result2);
mysqli_stmt_close($stmt2);

echo "<p><strong>Consulta 2 (como test_render_upload):</strong></p>";
if ($user2) {
    echo "<p>Token existe: " . (!empty($user2['google_refresh_token']) ? 'SÍ' : 'NO') . "</p>";
    if (!empty($user2['google_refresh_token'])) {
        echo "<p>Token preview: " . substr($user2['google_refresh_token'], 0, 20) . "...</p>";
    } else {
        echo "<p class='error'>❌ Token está vacío o NULL</p>";
    }
} else {
    echo "<p class='error'>❌ Usuario no encontrado en consulta 2</p>";
}
echo "</div>";

echo "<div class='debug'>";
echo "<h3>4. Información Completa del Usuario</h3>";

$stmt3 = mysqli_prepare($conn, "SELECT * FROM users WHERE user_id = ?");
mysqli_stmt_bind_param($stmt3, "i", $userId);
mysqli_stmt_execute($stmt3);
$result3 = mysqli_stmt_get_result($stmt3);
$user3 = mysqli_fetch_assoc($result3);
mysqli_stmt_close($stmt3);

if ($user3) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    foreach ($user3 as $key => $value) {
        echo "<tr>";
        echo "<td><strong>$key</strong></td>";
        if ($key === 'google_refresh_token') {
            if (!empty($value)) {
                echo "<td>" . substr($value, 0, 30) . "... (longitud: " . strlen($value) . ")</td>";
            } else {
                echo "<td class='error'>NULL o vacío</td>";
            }
        } else {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>❌ No se pudo obtener información completa</p>";
}
echo "</div>";

echo "<div class='debug'>";
echo "<h3>5. Test de Conexión</h3>";
echo "<p>Conexión MySQL: " . (mysqli_ping($conn) ? '✅ Activa' : '❌ Inactiva') . "</p>";
echo "<p>Error MySQL: " . (mysqli_error($conn) ?: 'Ninguno') . "</p>";
echo "</div>";
?>

    <p><a href="/panel">← Volver al Panel</a></p>
</body>
</html>