<?php
/**
 * Script de prueba para verificar rutas
 */

require_once __DIR__ . '/src/Core/Database.php';
require_once __DIR__ . '/src/Core/Router.php';

// Configuración de base de datos
$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'u214138677_datasnap',
    'username' => 'u214138677_datasnap',
    'password' => 'Rasa@25ChrSt',
    'charset' => 'utf8mb4'
];

$db = \Core\Database::getInstance($dbConfig);
$router = \Core\Router::init($db);

echo "<h1>Rutas Registradas</h1>";
echo "<pre>";
print_r($router->getRoutes());
echo "</pre>";

echo "<h2>Rutas de Previsualización:</h2>";
echo "<ul>";
echo "<li>GET /files/preview - Vista de previsualización</li>";
echo "<li>GET /api/files/preview/{id} - Datos de previsualización</li>";
echo "</ul>";

echo "<h2>Ejemplo de URLs:</h2>";
echo "<ul>";
echo "<li><a href='/files/preview?id=1'>/files/preview?id=1</a></li>";
echo "<li><a href='/api/files/preview/1'>/api/files/preview/1</a></li>";
echo "</ul>";
?>