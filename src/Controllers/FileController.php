<?php
/* Controlador de Archivos (subida, listado, optimización) */
namespace Controllers;

use Models\FileModel;
use Models\UserModel;
use Core\Database;
use Core\Csrf;

class FileController
{
    /**
     * Instancia de la base de datos
     * @var Database
     */
    private $db;

    /**
     * Instancia del modelo de archivos
     * @var FileModel
     */
    private $fileModel;

    /**
     * Instancia del modelo de usuarios
     * @var UserModel
     */
    private $userModel;

    /**
     * Directorio de uploads
     * @var string
     */
    private $uploadDir;

    /**
     * Constructor - inyección de dependencias
     *
     * @param Database $db Instancia de la base de datos
     * @param FileModel $fileModel Instancia del modelo de archivos
     * @param UserModel $userModel Instancia del modelo de usuarios
     */
    public function __construct(Database $db, FileModel $fileModel, UserModel $userModel)
    {
        $this->db = $db;
        $this->fileModel = $fileModel;
        $this->userModel = $userModel;
        $this->uploadDir = __DIR__ . '/../../uploads/';
    }

    /**
     * Método factoría para crear instancia del controlador
     *
     * @param Database $db Instancia de la base de datos
     * @return FileController Nueva instancia del controlador
     */
    public static function create(Database $db): FileController
    {
        $fileModel = new FileModel($db);
        $userModel = new UserModel($db);
        return new self($db, $fileModel, $userModel);
    }

    /**
     * Muestra la lista de archivos del usuario
     *
     * Redirige automáticamente a files_new.php para evitar problemas de cache
     * Esta es una solución temporal mientras se resuelve el cache del navegador/servidor
     *
     * @return void
     */
    public function index(): void
    {
        try {
            // Verificar si el usuario está autenticado
            $userId = $this->getCurrentUserId();

            if (!$userId) {
                // Usuario no autenticado - redirigir al login
                $this->redirectToLogin();
                return;
            }

            // Renderizar la vista de listado de archivos (frontend usa /files/list vía fetch)
            require_once __DIR__ . '/../Views/archivos.html';

        } catch (\Exception $e) {
            error_log("Error en FileController::index: " . $e->getMessage(), 0);
            $this->showError('Error interno del servidor');
        }
    }

    /**
     * Devuelve la lista de archivos del usuario en formato JSON (para AJAX)
     *
     * @return void
     */
    public function list(): void
    {
        try {
            error_log("FileController::list - Starting execution");
            $userId = $this->getCurrentUserId();
            error_log("FileController::list - User ID: " . ($userId ?? 'NULL'));

            if (!$userId) {
                error_log("FileController::list - User not authenticated");
                $this->jsonResponse(['success' => false, 'message' => 'Usuario no autenticado'], 401);
                return;
            }

            // Obtener archivos del usuario
            error_log("FileController::list - Calling fileModel->getUserFiles($userId)");
            $result = $this->fileModel->getUserFiles($userId);
            error_log("FileController::list - getUserFiles result: " . json_encode($result));

            if ($result['success']) {
                $files = $result['files'];
                error_log("FileController::list - Number of files: " . count($files));
                error_log("FileController::list - Files data: " . json_encode($files));

                $response = [
                    'success' => true,
                    'archivos' => $files
                ];
                error_log("FileController::list - Response: " . json_encode($response));

                $this->jsonResponse($response);
            } else {
                error_log("FileController::list - Error getting files: " . json_encode($result));
                $this->jsonResponse(['success' => false, 'message' => 'Error al obtener archivos'], 500);
            }

        } catch (\Exception $e) {
            error_log("Error en FileController::list: " . $e->getMessage(), 0);
            $this->jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Procesa la subida de un archivo con integración opcional a Google Drive
     *
     * @return void
     */
    public function upload(): void
{
    try {
        $userId = $this->getCurrentUserId();
        if (!$userId) {
            $this->jsonResponse(['success' => false, 'message' => 'Usuario no autenticado'], 401);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
            return;
        }

        // CSRF protection
        $token = Csrf::getFromRequest();
        if (!Csrf::isValid($token)) {
            $this->jsonResponse(['success' => false, 'message' => 'CSRF token inválido'], 403);
            return;
        }

        // Verificar que se recibió el archivo con nombre 'archivo'
        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(['success' => false, 'message' => 'No se recibió ningún archivo'], 400);
            return;
        }

        $file = $_FILES['archivo'];

        $validation = $this->validateUploadedFile($file);
        if (!$validation['valid']) {
            $this->jsonResponse(['success' => false, 'message' => $validation['message']], 400);
            return;
        }

        // Guardar archivo localmente
        $this->ensureUploadDirectory();
        $uniqueName = $this->fileModel->generateUniqueFilename($file['name']);
        $filePath = $this->uploadDir . $uniqueName;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            $this->jsonResponse(['success' => false, 'message' => 'Error al guardar el archivo localmente'], 500);
            return;
        }

        // Registrar en base de datos
        $result = $this->fileModel->registerFile(
            $userId,
            'uploads/' . $uniqueName,
            $file['name'],
            $file['type'],
            $file['size']
        );

        if ($result['success']) {
            $this->jsonResponse([
                'success' => true,
                'message' => 'Archivo subido exitosamente',
                'file_id' => $result['file_id']
            ]);
        } else {
            if (file_exists($filePath)) unlink($filePath);
            $this->jsonResponse(['success' => false, 'message' => $result['message'] ?? 'Error al registrar el archivo'], 500);
        }

    } catch (\Exception $e) {
        error_log("Error en FileController::upload: " . $e->getMessage(), 0);
        $this->jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
    }
}

/**
 * Procesa la subida de un archivo con integración a Google Drive
 *
 * @return void
 */
public function uploadWithDrive(): void
{
    try {
        $userId = $this->getCurrentUserId();
        if (!$userId) {
            $this->jsonResponse(['success' => false, 'message' => 'Usuario no autenticado'], 401);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
            return;
        }

        // Verificar que se recibió el archivo
        if (!isset($_FILES['archivo'])) {
            $this->jsonResponse(['success' => false, 'message' => 'No se envió archivo'], 400);
            return;
        }

        $archivo = $_FILES['archivo'];
        $nombre = basename($archivo['name']);

        // Crear directorio uploads si no existe
        $rutaUploads = __DIR__ . '/../../uploads/';
        if (!is_dir($rutaUploads)) {
            mkdir($rutaUploads, 0777, true);
        }

        $rutaCompleta = $rutaUploads . $nombre;

        // Guardar archivo localmente
        if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            $this->jsonResponse(['success' => false, 'message' => 'Error al guardar archivo local'], 500);
            return;
        }

        // Insertar en base de datos
        $stmt = $this->db->query(
            "INSERT INTO archivos (user_id, nombre, ruta, estado, fecha_subida) VALUES (?, ?, ?, 'original', NOW())",
            [$userId, $nombre, $rutaCompleta]
        );

        if ($stmt->rowCount() > 0) {
            $idArchivo = $this->db->getLastInsertId();

            // Enviar a Render para subir a Drive
            $this->sendToDrive($rutaCompleta, $idArchivo);

            $this->jsonResponse([
                'success' => true,
                'id' => $idArchivo,
                'message' => 'Archivo subido y enviado a Render'
            ]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Error al guardar en base de datos'], 500);
        }

    } catch (\Exception $e) {
        error_log("Error en FileController::uploadWithDrive: " . $e->getMessage(), 0);
        $this->jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
    }
}

/**
 * Envía archivo a Render para subir a Google Drive
 *
 * @param string $filePath Ruta del archivo
 * @param int $fileId ID del archivo en BD
 * @return void
 */
private function sendToDrive(string $filePath, int $fileId): void
{
    try {
        // Enviar a Render para subir a Drive
        $ch = curl_init("https://datasnap-render.onrender.com/upload_original");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'file' => new \CURLFile($filePath)
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response && $httpCode == 200) {
            $data = json_decode($response, true);
            if (!empty($data['success'])) {
                // Actualizar BD con información de Drive
                $stmt = $this->db->query(
                    "UPDATE archivos SET drive_id_original = ?, drive_link_original = ? WHERE id = ?",
                    [$data['drive_id'], $data['drive_link'], $fileId]
                );
                error_log("Archivo $fileId enviado exitosamente a Google Drive");
            }
        } else {
            error_log("Error al enviar archivo $fileId a Render: HTTP $httpCode");
        }
    } catch (\Exception $e) {
        error_log("Error en sendToDrive para archivo $fileId: " . $e->getMessage());
    }
}

/**
 * Envía archivo optimizado a Render para subir a Google Drive
 *
 * @param string $filePath Ruta del archivo optimizado
 * @param int $fileId ID del archivo en BD
 * @return void
 */
private function sendOptimizedToDrive(string $filePath, int $fileId): void
{
    try {
        // Enviar a Render para subir archivo optimizado a Drive
        $ch = curl_init("https://datasnap-render.onrender.com/upload_optimizado");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'file' => new \CURLFile($filePath),
            'file_id' => $fileId
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response && $httpCode == 200) {
            $data = json_decode($response, true);
            if (!empty($data['success'])) {
                // Actualizar BD con información de Drive para archivo optimizado
                $stmt = $this->db->query(
                    "UPDATE archivos SET drive_id_optimizado = ?, drive_link_optimizado = ? WHERE id = ?",
                    [$data['drive_id'], $data['drive_link'], $fileId]
                );
                error_log("Archivo optimizado $fileId enviado exitosamente a Google Drive");
            }
        } else {
            error_log("Error al enviar archivo optimizado $fileId a Render: HTTP $httpCode");
        }
    } catch (\Exception $e) {
        error_log("Error en sendOptimizedToDrive para archivo $fileId: " . $e->getMessage());
    }
}

/**
 * Inicia el proceso de optimización de un archivo
 *
 * @return void
 */
public function optimize(): void
    {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                $this->jsonResponse(['success' => false, 'message' => 'Usuario no autenticado'], 401);
                return;
            }

            // Verificar CSRF
            $token = Csrf::getFromRequest();
            if (!Csrf::isValid($token)) {
                $this->jsonResponse(['success' => false, 'message' => 'CSRF token inválido'], 403);
                return;
            }

            // Obtener datos JSON
            $input = json_decode(file_get_contents('php://input'), true);
            $fileId = $input['id'] ?? null;

            if (!$fileId || !is_numeric($fileId)) {
                $this->jsonResponse(['success' => false, 'message' => 'ID de archivo inválido'], 400);
                return;
            }

            // Verificar propiedad del archivo
            if (!$this->fileModel->isFileOwner((int)$fileId, $userId)) {
                $this->jsonResponse(['success' => false, 'message' => 'Archivo no encontrado o no autorizado'], 403);
                return;
            }

            // Cambiar estado a pendiente
            if ($this->fileModel->updateFileState((int)$fileId, 'pendiente')) {
                // Procesar el archivo localmente
                $this->processFileLocally((int)$fileId);

                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Archivo enviado a optimización correctamente'
                ]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar el estado del archivo'], 500);
            }

        } catch (\Exception $e) {
            error_log("Error en FileController::optimize: " . $e->getMessage(), 0);
            $this->jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Descarga un archivo optimizado
     *
     * @return void
     */
    public function download(): void
    {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                $this->redirectToLogin();
                return;
            }

            $fileId = $_GET['id'] ?? null;
            if (!$fileId || !is_numeric($fileId)) {
                $this->show404();
                return;
            }

            // Obtener información del archivo
            $file = $this->fileModel->getFileById((int)$fileId);
            if (!$file || $file['user_id'] != $userId || $file['estado'] !== 'optimizado') {
                $this->show404();
                return;
            }

            // Usar la ruta optimizada para descarga
            $relativePath = $file['ruta_optimizada'] ?? null;
            if (!$relativePath) {
                $this->showError('Ruta del archivo optimizado no disponible');
                return;
            }

            // Verificar que el archivo existe
            $filePath = __DIR__ . '/../../' . $relativePath;
            if (!$this->fileModel->fileExists($filePath)) {
                $this->showError('Archivo no encontrado en el servidor');
                return;
            }

            // Enviar headers para descarga
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($relativePath) . '"');
            header('Content-Length: ' . filesize($filePath));
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Enviar archivo
            readfile($filePath);
            exit();

        } catch (\Exception $e) {
            error_log("Error en FileController::download: " . $e->getMessage(), 0);
            $this->showError('Error al descargar el archivo');
        }
    }

    /**
     * Elimina un archivo
     *
     * @return void
     */
    public function delete(): void
    {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                $this->jsonResponse(['success' => false, 'message' => 'Usuario no autenticado'], 401);
                return;
            }

            // CSRF protection
            $token = Csrf::getFromRequest();
            if (!Csrf::isValid($token)) {
                $this->jsonResponse(['success' => false, 'message' => 'CSRF token inválido'], 403);
                return;
            }
 
            $fileId = $_GET['id'] ?? null;
            if (!$fileId || !is_numeric($fileId)) {
                $this->jsonResponse(['success' => false, 'message' => 'ID de archivo inválido'], 400);
                return;
            }

            // Verificar propiedad del archivo
            if (!$this->fileModel->isFileOwner((int)$fileId, $userId)) {
                $this->jsonResponse(['success' => false, 'message' => 'Archivo no encontrado o no autorizado'], 403);
                return;
            }

            // Eliminar archivo (marcar como borrado)
            if ($this->fileModel->deleteFile((int)$fileId)) {
                $this->jsonResponse(['success' => true, 'message' => 'Archivo eliminado correctamente']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar el archivo'], 500);
            }

        } catch (\Exception $e) {
            error_log("Error en FileController::delete: " . $e->getMessage(), 0);
            $this->jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Valida un archivo subido
     *
     * @param array $file Archivo de $_FILES
     * @return array Resultado de la validación
     */
    private function validateUploadedFile(array $file): array
    {
        // Verificar errores de subida
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido',
                UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo del formulario',
                UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
                UPLOAD_ERR_NO_FILE => 'No se seleccionó ningún archivo',
                UPLOAD_ERR_NO_TMP_DIR => 'Falta el directorio temporal',
                UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo',
                UPLOAD_ERR_EXTENSION => 'Extensión de archivo no permitida'
            ];

            $message = $errorMessages[$file['error']] ?? 'Error desconocido en la subida';
            return ['valid' => false, 'message' => $message];
        }

        // Validar tipo y extensión
        if (!$this->fileModel->isValidFileType($file['type'], $file['name'])) {
            return ['valid' => false, 'message' => 'Tipo de archivo no permitido. Solo se permiten: JSON, CSV, XLSX, XLS, SQL'];
        }

        // Validar tamaño (máximo 50MB para subida)
        $maxSize = 50 * 1024 * 1024; // 50MB
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'message' => 'El archivo es demasiado grande (máximo 50MB)'];
        }

        return ['valid' => true, 'message' => 'Archivo válido'];
    }

    /**
     * Asegura que el directorio de uploads existe
     *
     * @return void
     */
    private function ensureUploadDirectory(): void
    {
        if (!is_dir($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0755, true)) {
                throw new \Exception("No se pudo crear el directorio de uploads: {$this->uploadDir}");
            }
        }
    }

    /**
     * Procesa un archivo localmente
     *
     * @param int $fileId ID del archivo
     * @return void
     */
    private function processFileLocally(int $fileId): void
    {
        try {
            // Obtener información del archivo
            $file = $this->fileModel->getFileById($fileId);
            if (!$file) {
                error_log("Archivo $fileId no encontrado para procesamiento");
                $this->fileModel->updateFileState($fileId, 'original');
                return;
            }

            // Ruta del archivo original
            $inputPath = __DIR__ . '/../../' . $file['ruta'];

            // Crear directorio processed si no existe
            $processedDir = __DIR__ . '/../../processed/';
            if (!is_dir($processedDir)) {
                mkdir($processedDir, 0755, true);
            }

            // Generar nombre para el archivo procesado
            $outputPath = $processedDir . 'optimized_' . $fileId . '_' . time() . '.' . pathinfo($file['ruta'], PATHINFO_EXTENSION);

            // Procesar el archivo (copiado simple como optimización por defecto)
            if (@copy($inputPath, $outputPath)) {
                // Marcar como optimizado con la nueva ruta
                $relativePath = 'processed/' . basename($outputPath);
                $this->fileModel->markAsOptimized($fileId, $relativePath);

                // Enviar archivo optimizado a Google Drive
                $this->sendOptimizedToDrive($outputPath, $fileId);

                error_log("Archivo $fileId procesado exitosamente");
            } else {
                // Revertir estado si hay error
                $this->fileModel->updateFileState($fileId, 'original');
                error_log("Error al procesar archivo $fileId: No se pudo copiar el archivo");
            }

        } catch (\Exception $e) {
            error_log("Error en processFileLocally para archivo $fileId: " . $e->getMessage());
            $this->fileModel->updateFileState($fileId, 'original');
        }
    }

    /**
     * Obtiene el ID del usuario actual
     *
     * @return int|null ID del usuario o null si no está logueado
     */
    private function getCurrentUserId(): ?int
    {
        // Verificar que la sesión esté iniciada
        if (session_status() !== PHP_SESSION_ACTIVE) {
            error_log("FileController::getCurrentUserId - Session not active");
            return null;
        }

        $userId = $_SESSION['user_id'] ?? null;
        error_log("FileController::getCurrentUserId - User ID from session: " . ($userId ?? 'NULL'));

        return $userId;
    }

    /**
     * Redirige al login
     *
     * @return void
     */
    private function redirectToLogin(): void
    {
        header('Location: /login');
        exit();
    }

    /**
     * Muestra página de error 404
     *
     * @return void
     */
    private function show404(): void
    {
        http_response_code(404);
        require_once __DIR__ . '/../Views/errors/404.php';
        exit();
    }

    /**
     * Muestra mensaje de error
     *
     * @param string $message Mensaje de error
     * @return void
     */
    private function showError(string $message): void
    {
        echo "<h1>Error</h1><p>{$message}</p>";
        exit();
    }

    /**
     * Envía respuesta JSON
     *
     * @param array $data Datos a enviar
     * @param int $statusCode Código HTTP
     * @return void
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}