<?php
/* Controlador de Archivos (subida, listado, optimización) */
namespace Controllers;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/env.php';

use Models\FileModel;
use Models\UserModel;
use Core\Database;
use Google\Client;
use Google\Service\Drive;

class FileController
{
    private $db;
    private $fileModel;
    private $userModel;
    private $uploadDir;

    public function __construct(Database $db, FileModel $fileModel, UserModel $userModel)
    {
        $this->db = $db;
        $this->fileModel = $fileModel;
        $this->userModel = $userModel;
        $this->uploadDir = __DIR__ . '/../../uploads/';
    }

    public static function create(Database $db): FileController
    {
        $fileModel = new FileModel($db);
        $userModel = new UserModel($db);
        return new self($db, $fileModel, $userModel);
    }

    /** ==================== LISTAR ==================== */
    public function index(): void
    {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                $this->redirectToLogin();
                return;
            }
            
            // Verificar si es una solicitud de previsualización
            if (isset($_GET['action']) && $_GET['action'] === 'preview') {
                $this->preview();
                return;
            }
            
            // Verificar si es una solicitud de datos de previsualización
            if (isset($_GET['action']) && $_GET['action'] === 'preview-data') {
                $this->previewData();
                return;
            }
            
            // Obtener datos del usuario para el sidebar
            $user = $this->userModel->findById($userId);
            if (!$user) {
                $this->redirectToLogin();
                return;
            }
            
            $userData = [
                'id' => $user['user_id'],
                'username' => $user['username'],
                'email' => $user['email']
            ];
            
            require_once __DIR__ . '/../Views/archivos.html';
        } catch (\Exception $e) {
            error_log("Error en FileController::index: " . $e->getMessage(), 0);
            $this->showError('Error interno del servidor');
        }
    }

    public function list(): void
    {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                $this->jsonResponse(['success' => false, 'message' => 'Usuario no autenticado'], 401);
                return;
            }

            // Usar mysqli directamente
            global $conn;
            require_once __DIR__ . '/../Models/conexion.php';
            require_once __DIR__ . '/../Models/archivos_model.php';
            
            $archivos = obtenerArchivosPorUsuario($userId);
            
            if ($archivos !== false) {
                $this->jsonResponse([
                    'success' => true,
                    'archivos' => $archivos
                ]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Error al obtener archivos'], 500);
            }
        } catch (\Exception $e) {
            error_log("Error en FileController::list: " . $e->getMessage(), 0);
            $this->jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }

    /** ==================== SUBIDA ==================== */
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

            if (!isset($_FILES['archivo'])) {
                $this->jsonResponse(['success' => false, 'message' => 'No se envió archivo'], 400);
                return;
            }

            $archivo = $_FILES['archivo'];
            $nombre = basename($archivo['name']);

            $this->ensureUploadDirectory();
            $rutaCompleta = $this->uploadDir . $nombre;

            if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
                $this->jsonResponse(['success' => false, 'message' => 'Error al guardar archivo local'], 500);
                return;
            }

            // Obtener tamaño del archivo
            $tamanoArchivo = filesize($rutaCompleta);
            
            // Usar ruta relativa para BD
            $rutaRelativa = 'uploads/' . $nombre;
            
            error_log("ANTES DE INSERTAR - Usuario: {$userId}, Nombre: {$nombre}, Ruta: {$rutaRelativa}, Tamaño: {$tamanoArchivo}");
            
            // Usar mysqli directamente para consistencia
            global $conn;
            require_once __DIR__ . '/../Models/conexion.php';
            require_once __DIR__ . '/../Models/archivos_model.php';
            
            $resultado = insertarArchivo($userId, $rutaRelativa, 'original', $nombre, $tamanoArchivo);
            
            error_log("RESULTADO INSERCIÓN: " . ($resultado ? 'EXITOSO' : 'FALLIDO'));
            
            if (!$resultado) {
                error_log("Error MySQL: " . mysqli_error($conn));
            }

            if ($resultado) {
                // Obtener el ID del archivo insertado
                $idArchivo = mysqli_insert_id($conn);
                $message = 'Archivo subido correctamente';
                $driveSuccess = false;

                // Check if user has Google auth
                $user = $this->userModel->getUserById($userId);
                if (empty($user['google_refresh_token'])) {
                    // Redirect to auth
                    header('Location: /auth/google');
                    exit();
                }

                // Usar API de Render para subir a Google Drive
                try {
                    error_log("Uploading file to Render API: $nombre, user: $userId");

                    $renderUrl = "https://datasnap-panel.onrender.com/upload_original";
                    
                    $ch = curl_init($renderUrl);
                    $postData = [
                        'file' => new \CURLFile($rutaCompleta, mime_content_type($rutaCompleta), $nombre),
                        'google_refresh_token' => $user['google_refresh_token']
                    ];
                    
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                    
                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $curlError = curl_error($ch);
                    curl_close($ch);
                    
                    error_log("Render API response: HTTP $httpCode, Response: $response, CurlError: $curlError");
                    
                    if ($httpCode == 200) {
                        $renderResponse = json_decode($response, true);
                        if ($renderResponse && isset($renderResponse['success']) && $renderResponse['success']) {
                            $driveId = $renderResponse['drive_id'];
                            $driveLink = $renderResponse['drive_link'];
                            
                            $this->db->query(
                                "UPDATE archivos SET drive_id_original = ?, drive_link_original = ? WHERE id = ?",
                                [$driveId, $driveLink, $idArchivo]
                            );
                            
                            error_log("File uploaded via Render API successfully. ID: $driveId");
                        } else {
                            $errorMsg = $renderResponse['error'] ?? 'Error desconocido';
                            // Detectar si es un error de token expirado
                            if (strpos($errorMsg, 'invalid_grant') !== false || strpos($errorMsg, 'expired') !== false) {
                                // Limpiar el token expirado
                                $this->db->query("UPDATE users SET google_refresh_token = NULL WHERE user_id = ?", [$userId]);
                                $this->jsonResponse(['success' => false, 'message' => 'Token de Google expirado', 'redirect' => '/auth/google'], 401);
                                return;
                            }
                            throw new \Exception('Error en respuesta de Render: ' . $errorMsg);
                        }
                    } else {
                        throw new \Exception("Error HTTP $httpCode: $response");
                    }

                } catch (\Google\Service\Exception $e) {
                    error_log("Google Drive API error: " . $e->getMessage());
                    $this->jsonResponse(['success' => false, 'message' => 'Error en Google Drive API: ' . $e->getMessage()], 500);
                    return;
                } catch (\Exception $e) {
                    error_log("Error during Drive upload: " . $e->getMessage());
                    $this->jsonResponse(['success' => false, 'message' => 'Error al subir a Google Drive: ' . $e->getMessage()], 500);
                    return;
                }

                // No enviar automáticamente a Render, solo subir a Drive
                $message .= ' y subido a Google Drive';

                $this->jsonResponse([
                    'success' => true,
                    'id' => $idArchivo,
                    'message' => $message
                ]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Error al guardar en base de datos: ' . mysqli_error($conn)], 500);
            }
        } catch (\Exception $e) {
            error_log("Error en FileController::uploadWithDrive: " . $e->getMessage(), 0);
            $this->jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }

    /** ==================== OPTIMIZAR ==================== */
    public function optimize(): void
    {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                $this->jsonResponse(['success' => false, 'message' => 'Usuario no autenticado'], 401);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $fileId = $input['id'] ?? null;

            if (!$fileId || !is_numeric($fileId)) {
                $this->jsonResponse(['success' => false, 'message' => 'ID de archivo inválido'], 400);
                return;
            }

            $file = $this->fileModel->getFileById((int)$fileId);
            if (!$file || $file['user_id'] != $userId || $file['estado'] == 'borrado') {
                $this->jsonResponse(['success' => false, 'message' => 'Archivo no encontrado, no autorizado o eliminado'], 403);
                return;
            }
            
            // Verificar que el archivo físico existe
            if (!file_exists($file['ruta'])) {
                error_log("Archivo físico no encontrado: {$file['ruta']}");
                $this->jsonResponse(['success' => false, 'message' => 'Archivo físico no encontrado en el servidor'], 404);
                return;
            }

            $this->fileModel->updateFileState((int)$fileId, 'pendiente');

            // Obtener refresh token del usuario
            $user = $this->userModel->findById($userId);
            if (!$user || empty($user['google_refresh_token'])) {
                $this->jsonResponse(['success' => false, 'message' => 'Usuario no tiene autorización de Google Drive'], 403);
                return;
            }

            // Leer contenido del archivo para enviar a Render
            $fileContent = file_get_contents($file['ruta']);
            
            // Enviar a Render para procesar
            $url = "https://datasnap-panel.onrender.com/procesar";
            $payload = json_encode([
                "id" => (int)$fileId,
                "file_content" => $fileContent,
                "file_name" => $file['nombre'],
                "google_refresh_token" => $user['google_refresh_token']
            ]);
            error_log("Sending to Render: URL=$url, File: {$file['nombre']}");

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120); // Aumentar timeout para IA Universal
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            error_log("Render response: HTTP $httpCode, Response: $response, CurlError: $curlError");

            if ($httpCode == 200) {
                $renderResponse = json_decode($response, true);
                if ($renderResponse && isset($renderResponse['success']) && $renderResponse['success']) {
                    // Guardar el archivo optimizado localmente
                    if (isset($renderResponse['archivo_optimizado']) && isset($renderResponse['nombre_archivo'])) {
                        // Crear carpeta de optimizados si no existe
                        $optimizedDir = $this->uploadDir . '../processed/';
                        if (!is_dir($optimizedDir)) {
                            mkdir($optimizedDir, 0755, true);
                        }
                        
                        $optimizedPath = $optimizedDir . $renderResponse['nombre_archivo'];
                        file_put_contents($optimizedPath, $renderResponse['archivo_optimizado']);
                        
                        // Actualizar BD con estado optimizado y ruta
                        $this->db->query(
                            "UPDATE archivos SET estado = 'optimizado', ruta_optimizada = ?, fecha_optimizacion = NOW() WHERE id = ?",
                            [$optimizedPath, $fileId]
                        );
                        
                        error_log("Archivo optimizado guardado: $optimizedPath");
                    } else {
                        // Solo actualizar estado si no hay archivo
                        $this->db->query(
                            "UPDATE archivos SET estado = 'optimizado', fecha_optimizacion = NOW() WHERE id = ?",
                            [$fileId]
                        );
                    }
                    
                    $message = 'Archivo optimizado con IA Global Universal';
                    if (isset($renderResponse['estadisticas'])) {
                        $stats = $renderResponse['estadisticas'];
                        $message .= " - {$stats['filas_optimizadas']} filas procesadas";
                    }
                    
                    $this->jsonResponse([
                        'success' => true,
                        'message' => $message,
                        'estadisticas' => $renderResponse['estadisticas'] ?? null
                    ]);
                } else {
                    $this->jsonResponse([
                        'success' => false,
                        'message' => 'Error en el procesamiento: ' . ($renderResponse['error'] ?? 'Error desconocido')
                    ], 500);
                }
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Error al conectar con Render (HTTP ' . $httpCode . '): ' . $response
                ], 500);
            }
        } catch (\Exception $e) {
            error_log("Error en FileController::optimize: " . $e->getMessage(), 0);
            $this->jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }

    /** ==================== DESCARGA ==================== */
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

            $file = $this->fileModel->getFileById((int)$fileId);
            if (!$file || $file['user_id'] != $userId || $file['estado'] !== 'optimizado') {
                $this->show404();
                return;
            }

            // Descargar archivo optimizado local
            if (!empty($file['ruta_optimizada']) && file_exists($file['ruta_optimizada'])) {
                // Determinar tipo de contenido basado en la extensión
                $extension = strtolower(pathinfo($file['ruta_optimizada'], PATHINFO_EXTENSION));
                
                if ($extension === 'sql') {
                    header('Content-Type: text/plain');
                    header('Content-Disposition: attachment; filename="optimizado_' . $file['nombre'] . '"');
                } else {
                    header('Content-Type: text/csv');
                    header('Content-Disposition: attachment; filename="optimizado_' . $file['nombre'] . '"');
                }
                
                readfile($file['ruta_optimizada']);
                exit();
            } else {
                $this->showError("Archivo optimizado no disponible");
            }
        } catch (\Exception $e) {
            error_log("Error en FileController::download: " . $e->getMessage(), 0);
            $this->showError('Error al descargar el archivo');
        }
    }

    /** ==================== ELIMINAR ==================== */
    public function delete(): void
    {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                $this->jsonResponse(['success' => false, 'message' => 'Usuario no autenticado'], 401);
                return;
            }

            $fileId = $_GET['id'] ?? null;
            if (!$fileId || !is_numeric($fileId)) {
                $this->jsonResponse(['success' => false, 'message' => 'ID de archivo inválido'], 400);
                return;
            }

            if (!$this->fileModel->isFileOwner((int)$fileId, $userId)) {
                $this->jsonResponse(['success' => false, 'message' => 'Archivo no encontrado o no autorizado'], 403);
                return;
            }

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

    /** ==================== HELPERS ==================== */
    private function validateUploadedFile(array $file): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'message' => 'Error en la subida'];
        }
        if (!$this->fileModel->isValidFileType($file['type'], $file['name'])) {
            return ['valid' => false, 'message' => 'Tipo de archivo no permitido'];
        }
        $maxSize = 50 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'message' => 'El archivo es demasiado grande (máximo 50MB)'];
        }
        return ['valid' => true, 'message' => 'Archivo válido'];
    }

    private function ensureUploadDirectory(): void
    {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    private function getCurrentUserId(): ?int
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return null;
        }
        return $_SESSION['user_id'] ?? null;
    }

    private function redirectToLogin(): void
    {
        header('Location: /login');
        exit();
    }

    private function show404(): void
    {
        http_response_code(404);
        require_once __DIR__ . '/../Views/errors/404.php';
        exit();
    }

    private function showError(string $message): void
    {
        echo "<h1>Error</h1><p>{$message}</p>";
        exit();
    }

    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    public function googleAuth(): void
    {
        $client = new Client();
        $client->setClientId(getenv('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(getenv('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(getenv('GOOGLE_REDIRECT_URI'));
        $client->addScope(Drive::DRIVE);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        $authUrl = $client->createAuthUrl();
        header('Location: ' . $authUrl);
        exit();
    }

    public function googleCallback(): void
    {
        // Ensure session is started
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $client = new Client();
        $client->setClientId(getenv('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(getenv('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(getenv('GOOGLE_REDIRECT_URI'));

        if (isset($_GET['code'])) {
            $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
            if (isset($token['refresh_token'])) {
                $userId = $this->getCurrentUserId();
                if ($userId) {
                    $this->db->query("UPDATE users SET google_refresh_token = ? WHERE user_id = ?", [$token['refresh_token'], $userId]);
                    error_log("Google refresh token saved for user ID: $userId");
                } else {
                    error_log("No user ID in session during Google callback");
                    // Redirect to login if no session
                    header('Location: /login?error=' . urlencode('Sesión expirada. Inicia sesión nuevamente.'));
                    exit();
                }
            } else {
                error_log("No refresh token in Google response");
            }
            header('Location: /panel');
            exit();
        }
    }

    /** ==================== PREVISUALIZACIÓN ==================== */
    public function preview(): void
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

            $file = $this->fileModel->getFileById((int)$fileId);
            if (!$file || $file['user_id'] != $userId || $file['estado'] == 'borrado') {
                $this->show404();
                return;
            }

            // Obtener datos del usuario para el sidebar
            $user = $this->userModel->findById($userId);
            $userData = [
                'id' => $user['user_id'],
                'username' => $user['username'],
                'email' => $user['email']
            ];

            require_once __DIR__ . '/../Views/preview.html';
        } catch (\Exception $e) {
            error_log("Error en FileController::preview: " . $e->getMessage(), 0);
            $this->showError('Error interno del servidor');
        }
    }

    public function previewData(): void
    {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                $this->jsonResponse(['success' => false, 'message' => 'Usuario no autenticado'], 401);
                return;
            }

            $fileId = $_GET['id'] ?? null;
            if (!$fileId || !is_numeric($fileId)) {
                $this->jsonResponse(['success' => false, 'message' => 'ID inválido'], 400);
                return;
            }

            $file = $this->fileModel->getFileById((int)$fileId);
            if (!$file || $file['user_id'] != $userId || $file['estado'] == 'borrado') {
                $this->jsonResponse(['success' => false, 'message' => 'Archivo no encontrado'], 404);
                return;
            }

            // Leer contenido original
            $originalContent = null;
            if (file_exists($file['ruta'])) {
                $originalContent = file_get_contents($file['ruta']);
                // Escapar HTML para evitar problemas de renderizado
                $originalContent = htmlspecialchars($originalContent, ENT_QUOTES, 'UTF-8');
            }

            // Leer contenido optimizado si existe
            $optimizedContent = null;
            if (!empty($file['ruta_optimizada']) && file_exists($file['ruta_optimizada'])) {
                $optimizedContent = file_get_contents($file['ruta_optimizada']);
                // Escapar HTML para evitar problemas de renderizado
                $optimizedContent = htmlspecialchars($optimizedContent, ENT_QUOTES, 'UTF-8');
            }

            $this->jsonResponse([
                'success' => true,
                'file' => [
                    'id' => $file['id'],
                    'nombre' => $file['nombre'],
                    'tamano' => $file['tamano'] ?? 0,
                    'estado' => $file['estado'],
                    'fecha_subida' => $file['fecha_subida']
                ],
                'original_content' => $originalContent,
                'optimized_content' => $optimizedContent
            ]);
        } catch (\Exception $e) {
            error_log("Error en FileController::previewData: " . $e->getMessage(), 0);
            $this->jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }

}
