<?php
// Endpoint web para verificar estado de Drive
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    die('<h1>Error</h1><p>Debes estar logueado para ver esta página.</p><a href="/login">Ir a Login</a>');
}

$userId = $_SESSION['user_id'];

?>
<!DOCTYPE html>
<html>
<head>
    <title>Estado de Drive - Debug</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .archivo { border: 1px solid #ccc; margin: 10px 0; padding: 15px; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .warning { background-color: #fff3cd; border-color: #ffeaa7; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
        .encrypted { font-family: monospace; background: #f8f9fa; padding: 5px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>🔍 Estado de Archivos y Encriptación</h1>
    <p><strong>Usuario ID:</strong> <?php echo $userId; ?></p>
    <hr>

<?php
require_once __DIR__ . '/src/Models/conexion.php';
require_once __DIR__ . '/config/encryption.php';

// Obtener los últimos 10 archivos del usuario
$query = "SELECT id, nombre, user_id, drive_id_original, drive_link_original, fecha_subida, estado 
          FROM archivos 
          WHERE user_id = ? 
          ORDER BY id DESC 
          LIMIT 10";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    while ($archivo = mysqli_fetch_assoc($result)) {
        $hasEncryption = !empty($archivo['drive_id_original']) && !empty($archivo['drive_link_original']);
        $cssClass = $hasEncryption ? 'success' : 'warning';
        
        echo "<div class='archivo $cssClass'>";
        echo "<h3>📁 {$archivo['nombre']} (ID: {$archivo['id']})</h3>";
        echo "<p><strong>Estado:</strong> {$archivo['estado']}</p>";
        echo "<p><strong>Fecha:</strong> {$archivo['fecha_subida']}</p>";
        
        if ($hasEncryption) {
            echo "<p><strong>✅ Estado Drive:</strong> Datos encriptados guardados</p>";
            echo "<p><strong>Drive ID (encriptado):</strong><br><span class='encrypted'>" . substr($archivo['drive_id_original'], 0, 50) . "...</span></p>";
            echo "<p><strong>Drive Link (encriptado):</strong><br><span class='encrypted'>" . substr($archivo['drive_link_original'], 0, 50) . "...</span></p>";
            
            // Intentar desencriptar para verificar
            try {
                $decryptedLink = DriveEncryption::decryptLink($archivo['drive_link_original']);
                if (strpos($decryptedLink, 'drive.google.com') !== false) {
                    echo "<p><strong>🔓 Link desencriptado:</strong> <a href='$decryptedLink' target='_blank'>Ver en Google Drive</a></p>";
                    echo "<p><strong>✅ Encriptación:</strong> Funcionando correctamente</p>";
                } else {
                    echo "<p><strong>❌ Error:</strong> Link desencriptado no válido</p>";
                }
            } catch (Exception $e) {
                echo "<p><strong>❌ Error desencriptando:</strong> " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p><strong>⏳ Estado Drive:</strong> Pendiente de subir</p>";
            echo "<p><strong>Drive ID:</strong> NULL</p>";
            echo "<p><strong>Drive Link:</strong> NULL</p>";
            echo "<p><em>El archivo se está procesando en segundo plano. Recarga la página en unos minutos.</em></p>";
        }
        
        echo "</div>";
    }
} else {
    echo "<div class='archivo error'>";
    echo "<h3>❌ No se encontraron archivos</h3>";
    echo "<p>No tienes archivos subidos aún.</p>";
    echo "</div>";
}

mysqli_stmt_close($stmt);
?>

    <hr>
    <p><a href="/panel">← Volver al Panel</a> | <a href="/files">Ver Mis Archivos</a></p>
    
    <script>
        // Auto-refresh cada 30 segundos para ver cambios
        setTimeout(() => {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>