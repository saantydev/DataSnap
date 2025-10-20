<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/../Controllers/FileController.php';
require_once __DIR__ . '/../Core/Database.php';

// Crear instancia de la base de datos
$db = new \Core\Database();
$fileController = \Controllers\FileController::create($db);

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Procesar el archivo localmente usando el controlador
    $fileController->processFileLocally($id);

    // Redirigir de vuelta al panel
    header("Location: panel.php");
    exit();
}
?>
