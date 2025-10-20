<?php
/**
 * Configuración alternativa de base de datos
 * Prueba esta configuración si la principal no funciona
 */

return [
    'host' => 'localhost',
    'database' => 'u214138677_datasnap',
    'username' => 'u214138677_root',
    'password' => 'TIGRE.BC.9895',
    'charset' => 'utf8mb4',
    'port' => 3306,
    'options' => [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
?>