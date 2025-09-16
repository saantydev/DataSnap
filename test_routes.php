<?php
/**
 * Script de prueba para verificar las rutas del sistema DataSnap
 *
 * Este script verifica que todas las rutas principales estén funcionando correctamente
 * después de la implementación del sistema de enrutamiento.
 */

// Incluir las clases necesarias
require_once __DIR__ . '/src/Core/Database.php';
require_once __DIR__ . '/src/Core/Router.php';
require_once __DIR__ . '/src/Core/Csrf.php';
require_once __DIR__ . '/src/Core/ErrorHandler.php';
require_once __DIR__ . '/src/Core/Migrations.php';

// Incluir controladores
require_once __DIR__ . '/src/Controllers/HomeController.php';
require_once __DIR__ . '/src/Controllers/LoginController.php';
require_once __DIR__ . '/src/Controllers/RegisterController.php';
require_once __DIR__ . '/src/Controllers/FileController.php';
require_once __DIR__ . '/src/Controllers/PanelController.php';

// Incluir modelos
require_once __DIR__ . '/src/Models/UserModel.php';
require_once __DIR__ . '/src/Models/FileModel.php';

echo "<h1>🧪 Prueba de Rutas - DataSnap</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;}</style>";

// Función para probar una ruta
function testRoute($method, $uri, $expectedController, $expectedMethod) {
    echo "<h3>Probando: $method $uri</h3>";

    // Simular variables globales
    $_SERVER['REQUEST_METHOD'] = $method;
    $_SERVER['REQUEST_URI'] = $uri;

    try {
        // Inicializar base de datos
        $db = \Core\Database::getInstance();

        // Inicializar router
        $router = \Core\Router::init($db);

        // Obtener rutas definidas
        $routes = $router->getRoutes();

        if (isset($routes[$method][$uri])) {
            $route = $routes[$method][$uri];
            if ($route['controller'] === $expectedController && $route['method'] === $expectedMethod) {
                echo "<p class='success'>✅ Ruta correcta: {$route['controller']}::{$route['method']}</p>";
                return true;
            } else {
                echo "<p class='error'>❌ Ruta incorrecta. Esperado: $expectedController::$expectedMethod, Encontrado: {$route['controller']}::{$route['method']}</p>";
                return false;
            }
        } else {
            echo "<p class='error'>❌ Ruta no encontrada en el sistema de enrutamiento</p>";
            return false;
        }

    } catch (\Exception $e) {
        echo "<p class='error'>❌ Error al probar ruta: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Probar rutas principales
$tests = [
    ['GET', '/', 'HomeController', 'index'],
    ['GET', '/login', 'LoginController', 'showLoginForm'],
    ['GET', '/register', 'RegisterController', 'showRegisterForm'],
    ['GET', '/panel', 'PanelController', 'index'],
    ['GET', '/files', 'FileController', 'index'],
    ['POST', '/login', 'LoginController', 'processLogin'],
    ['POST', '/register', 'RegisterController', 'processRegister'],
    ['POST', '/files/upload', 'FileController', 'upload'],
    ['GET', '/logout', 'LoginController', 'logout'],
];

$passed = 0;
$total = count($tests);

echo "<h2>📋 Resultados de las pruebas:</h2>";

foreach ($tests as $test) {
    if (testRoute($test[0], $test[1], $test[2], $test[3])) {
        $passed++;
    }
    echo "<hr>";
}

echo "<h2>📊 Resumen:</h2>";
echo "<p>Total de pruebas: $total</p>";
echo "<p>Pasaron: <span class='success'>$passed</span></p>";
echo "<p>Fallaron: <span class='error'>" . ($total - $passed) . "</span></p>";

if ($passed === $total) {
    echo "<p class='success'>🎉 ¡Todas las rutas están funcionando correctamente!</p>";
} else {
    echo "<p class='warning'>⚠️ Algunas rutas necesitan revisión.</p>";
}

// Verificar archivos importantes
echo "<h2>📁 Verificación de archivos:</h2>";

$filesToCheck = [
    'index.php' => 'Punto de entrada principal',
    '.htaccess' => 'Configuración de Apache',
    'src/Core/Router.php' => 'Sistema de enrutamiento',
    'src/Core/Database.php' => 'Conexión a base de datos',
    'config/database.php' => 'Configuración de BD',
];

foreach ($filesToCheck as $file => $description) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "<p class='success'>✅ $description: $file</p>";
    } else {
        echo "<p class='error'>❌ Archivo faltante: $file ($description)</p>";
    }
}

echo "<br><p><strong>💡 Próximos pasos:</strong></p>";
echo "<ul>";
echo "<li>Sube estos archivos al servidor usando SFTP</li>";
echo "<li>Verifica que el servidor tenga mod_rewrite habilitado</li>";
echo "<li>Prueba las rutas en el navegador: /register, /login, /panel, etc.</li>";
echo "<li>Si hay errores, revisa los logs del servidor</li>";
echo "</ul>";