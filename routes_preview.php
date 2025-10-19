<?php
/**
 * Rutas adicionales para previsualización de archivos
 * Agregar estas rutas a tu archivo de rutas principal
 */

// Rutas de previsualización
$router->get('/files/preview', function() {
    $fileController = FileController::create($db);
    $fileController->preview();
});

$router->get('/files/preview/{id}', function($id) {
    $_GET['id'] = $id;
    $fileController = FileController::create($db);
    $fileController->previewData();
});

// Ruta alternativa para datos de previsualización
$router->get('/api/files/preview/{id}', function($id) {
    $_GET['id'] = $id;
    $fileController = FileController::create($db);
    $fileController->previewData();
});
?>