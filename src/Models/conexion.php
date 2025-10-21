<?php

$config = require __DIR__ . '/../../config/database.php';

$servername = $config['host'];
$database = $config['dbname'];
$username = $config['username'];
$password = $config['password'];

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Error de conexión a la base de datos: " . mysqli_connect_error());
}

mysqli_set_charset($conn, $config['charset']);


?>