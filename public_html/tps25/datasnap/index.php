<?php
/**
 * Punto de entrada principal de la aplicación DataSnap
 *
 * Este archivo inicializa la aplicación PHP MVC y maneja todas las rutas.
 * Todas las peticiones HTTP son redirigidas aquí por el archivo .htaccess
 */

// Incluir clases necesarias (sin autoloader de Composer)
require_once __DIR__ . '/src/Core/Database.php';
require_once __DIR__ . '/src/Core/Router.php';
require_once __DIR__ . '/src/Core/Csrf.php';
require_once __DIR__ . '/src/Core/ErrorHandler.php';
require_once __DIR__ . '/src/Core/Migrations.php';
require_once __DIR__ . '/src/Core/Config.php';

// Incluir controladores
require_once __DIR__ . '/src/Controllers/HomeController.php';
require_once __DIR__ . '/src/Controllers/LoginController.php';
require_once __DIR__ . '/src/Controllers/RegisterController.php';
require_once __DIR__ . '/src/Controllers/PasswordResetController.php';
require_once __DIR__ . '/src/Controllers/FileController.php';
require_once __DIR__ . '/src/Controllers/PanelController.php';

// Incluir modelos
require_once __DIR__ . '/src/Models/UserModel.php';
require_once __DIR__ . '/src/Models/FileModel.php';
require_once __DIR__ . '/src/Models/PasswordResetModel.php';

use Controllers\FileController;

// Inicializar la base de datos con configuración forzada
$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'u214138677_datasnap',
    'username' => 'u214138677_datasnap',
    'password' => 'Rasa@25ChrSt',
    'charset' => 'utf8mb4'
];
$db = \Core\Database::getInstance($dbConfig);

// Ejecutar migraciones para asegurar que el esquema esté actualizado
\Core\Migrations::run($db);

if (strpos($_SERVER['REQUEST_URI'], '/auth/google/callback') === 0) {
    $controller = FileController::create($db);
    $controller->googleCallback();
    exit;
}
try {

    // Inicializar el router con la conexión a la base de datos
    $router = \Core\Router::init($db);

    // Ejecutar el enrutamiento
    $router->dispatch();

} catch (\Exception $e) {
    // Manejar errores críticos de inicialización
    error_log("Error crítico en index.php: " . $e->getMessage(), 0);

    // Mostrar página de error 500
    http_response_code(500);
    if (file_exists(__DIR__ . '/src/Views/errors/500.php')) {
        require_once __DIR__ . '/src/Views/errors/500.php';
    } else {
        echo "<h1>Error Interno del Servidor</h1>";
        echo "<p>Ha ocurrido un error interno. Por favor, inténtelo más tarde.</p>";
    }
    exit();
}