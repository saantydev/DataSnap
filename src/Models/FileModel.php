<?php
/**
 * Modelo FileModel - Maneja operaciones relacionadas con archivos
 *
 * Esta clase encapsula toda la lógica de negocio relacionada con archivos,
 * incluyendo subida, listado, optimización, descarga y gestión de estados.
 * Implementa validaciones robustas y manejo de errores con códigos estándar.
 *
 * @package Models
 * @author Sistema Datasnap
 * @version 1.0
 */
namespace Models;

use Core\Database;
<<<<<<< HEAD
use Core\Encryption;
=======
>>>>>>> 80eb21836f8ebbf25d3d8a477426d5caea9f6925

class FileModel
{
    /**
     * Instancia de la base de datos
     * @var Database
     */
    private $db;

    /**
     * Tipos MIME permitidos para archivos
     * @var array
     */
    private const ALLOWED_MIME_TYPES = [
        'application/json',
        'text/csv',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel',
        'application/sql',
        'text/plain'
    ];

    /**
     * Extensiones de archivo permitidas
     * @var array
     */
    private const ALLOWED_EXTENSIONS = ['json', 'csv', 'xlsx', 'xls', 'sql', 'txt'];

    /**
     * Estados posibles de un archivo
     * @var array
     */
    private const FILE_STATES = ['original', 'pendiente', 'optimizado', 'borrado'];

    /**
     * Constructor - inyección de dependencias
     *
     * @param Database $db Instancia de la base de datos
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Registra un nuevo archivo en el sistema
     *
     * @param int $userId ID del usuario
     * @param string $filePath Ruta del archivo
     * @param string $originalName Nombre original del archivo
     * @param string $mimeType Tipo MIME del archivo
     * @param int $fileSize Tamaño del archivo en bytes
     * @return array Resultado del registro con 'success', 'message' y 'file_id'
     */
    public function registerFile(int $userId, string $filePath, string $originalName, string $mimeType, int $fileSize): array
    {
        try {
            // Validar datos de entrada
            $validation = $this->validateFileData($filePath, $originalName, $mimeType, $fileSize);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message']
                ];
            }

            // Insertar archivo en la base de datos con tamaño
            $result = $this->insertFile($userId, $filePath, $originalName, $fileSize);

            if ($result['success']) {
                error_log("Archivo registrado exitosamente: $originalName (ID: {$result['file_id']})", 0);
                return [
                    'success' => true,
                    'message' => 'Archivo registrado exitosamente',
                    'file_id' => $result['file_id']
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al registrar el archivo'
                ];
            }

        } catch (\Exception $e) {
            error_log("Error al registrar archivo: " . $e->getMessage(), 0);
            return [
                'success' => false,
                'message' => 'Error interno del servidor'
            ];
        }
    }

    /**
     * Valida los datos de un archivo
     *
     * @param string $filePath Ruta del archivo
     * @param string $originalName Nombre original
     * @param string $mimeType Tipo MIME
     * @param int $fileSize Tamaño en bytes
     * @return array Resultado de la validación
     */
    private function validateFileData(string $filePath, string $originalName, string $mimeType, int $fileSize): array
    {
        // Validar tipo MIME
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
            error_log("FileModel::validateFileData - MIME type not allowed: $mimeType");
            return ['valid' => false, 'message' => 'Tipo de archivo no permitido'];
        }

        // Validar extensión
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            error_log("FileModel::validateFileData - Extension not allowed: $extension");
            return ['valid' => false, 'message' => 'Extensión de archivo no permitida'];
        }

        // Validar tamaño (máximo 100MB)
        $maxSize = 100 * 1024 * 1024; // 100MB
        if ($fileSize > $maxSize) {
            error_log("FileModel::validateFileData - File too large: $fileSize bytes");
            return ['valid' => false, 'message' => 'El archivo es demasiado grande (máximo 100MB)'];
        }

        // Validar nombre del archivo
        if (empty($originalName) || strlen($originalName) > 255) {
            error_log("FileModel::validateFileData - Invalid filename: $originalName");
            return ['valid' => false, 'message' => 'Nombre de archivo inválido'];
        }

        // Nota: No validamos existencia del archivo aquí porque se valida antes de mover
        error_log("FileModel::validateFileData - File validation passed for: $originalName");
        return ['valid' => true, 'message' => 'Archivo válido'];
    }

    /**
     * Inserta un archivo en la base de datos
     *
     * @param int $userId ID del usuario
     * @param string $filePath Ruta del archivo
     * @param string $originalName Nombre original
     * @param string $mimeType Tipo MIME
     * @param int $fileSize Tamaño en bytes
     * @return array Resultado de la inserción
     */
    private function insertFile(int $userId, string $filePath, string $originalName, int $fileSize = 0): array
    {
        try {
            // Obtener tamaño del archivo si no se proporciona
            if ($fileSize === 0 && file_exists($filePath)) {
                $fileSize = filesize($filePath);
            }
            
            $sql = "INSERT INTO archivos (user_id, ruta, nombre, tamano, estado, fecha_subida)
                    VALUES (?, ?, ?, ?, 'original', NOW())";
            $stmt = $this->db->query($sql, [$userId, $filePath, $originalName, $fileSize]);

            if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'file_id' => $this->db->getLastInsertId()
                ];
            } else {
                return ['success' => false];
            }
        } catch (\Exception $e) {
            error_log("Error al insertar archivo en BD: " . $e->getMessage(), 0);
            return ['success' => false];
        }
    }

    /**
     * Obtiene todos los archivos de un usuario
     *
     * @param int $userId ID del usuario
     * @return array Lista de archivos del usuario
     */
    public function getUserFiles(int $userId): array
    {
        try {
            // First, reset any pending files that have been pending for more than 5 seconds
            $this->resetExpiredPendingFiles($userId);

<<<<<<< HEAD
            $sql = "SELECT id, ruta, nombre, tamano, estado, fecha_subida, drive_link_optimizado, drive_link_original, drive_id_original, drive_id_optimizado
=======
            $sql = "SELECT id, ruta, nombre, tamano, estado, fecha_subida, drive_link_optimizado, drive_link_original
>>>>>>> 80eb21836f8ebbf25d3d8a477426d5caea9f6925
                    FROM archivos
                    WHERE user_id = ? AND estado != 'borrado'
                    ORDER BY fecha_subida DESC";
            $stmt = $this->db->query($sql, [$userId]);
            $files = $stmt->fetchAll();

<<<<<<< HEAD
            // Descifrar los campos sensibles para el usuario propietario
            foreach ($files as &$file) {
                $file = Encryption::decryptFileData($file, $userId);
            }

=======
>>>>>>> 80eb21836f8ebbf25d3d8a477426d5caea9f6925
            return [
                'success' => true,
                'files' => $files
            ];
        } catch (\Exception $e) {
            error_log("Error al obtener archivos del usuario: " . $e->getMessage(), 0);
            return [
                'success' => false,
                'message' => 'Error al obtener archivos',
                'files' => []
            ];
        }
    }

    /**
     * Resetea archivos pendientes que han estado en ese estado por más de 5 segundos
     *
     * @param int $userId ID del usuario
     */
    private function resetExpiredPendingFiles(int $userId): void
    {
        try {
            // Reset pending files with timestamp older than 5 seconds
            $sql1 = "UPDATE archivos
                     SET estado = 'original', pending_timestamp = NULL
                     WHERE user_id = ? AND estado = 'pendiente'
                     AND pending_timestamp IS NOT NULL
                     AND (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(pending_timestamp)) > 5";
            $stmt1 = $this->db->query($sql1, [$userId]);
            $affected1 = $stmt1->rowCount();

            // Reset old pending files without timestamp (stuck from before the change)
            $sql2 = "UPDATE archivos
                     SET estado = 'original'
                     WHERE user_id = ? AND estado = 'pendiente'
                     AND pending_timestamp IS NULL
                     AND fecha_subida < DATE_SUB(NOW(), INTERVAL 1 HOUR)";
            $stmt2 = $this->db->query($sql2, [$userId]);
            $affected2 = $stmt2->rowCount();

            $totalAffected = $affected1 + $affected2;
            if ($totalAffected > 0) {
                error_log("Reseteados $totalAffected archivos pendientes expirados para usuario $userId", 0);
            }
        } catch (\Exception $e) {
            error_log("Error al resetear archivos pendientes expirados: " . $e->getMessage(), 0);
        }
    }

    /**
     * Obtiene un archivo por su ID
     *
     * @param int $fileId ID del archivo
<<<<<<< HEAD
     * @param int|null $userId ID del usuario (para descifrar datos si es propietario)
     * @return array|null Datos del archivo o null si no existe
     */
    public function getFileById(int $fileId, ?int $userId = null): ?array
=======
     * @return array|null Datos del archivo o null si no existe
     */
    public function getFileById(int $fileId): ?array
>>>>>>> 80eb21836f8ebbf25d3d8a477426d5caea9f6925
    {
        try {
            $sql = "SELECT * FROM archivos WHERE id = ?";
            $stmt = $this->db->query($sql, [$fileId]);
            $file = $stmt->fetch();

<<<<<<< HEAD
            if ($file && $userId && $file['user_id'] == $userId) {
                // Solo descifrar si el usuario es el propietario
                $file = Encryption::decryptFileData($file, $userId);
            }

=======
>>>>>>> 80eb21836f8ebbf25d3d8a477426d5caea9f6925
            return $file ?: null;
        } catch (\Exception $e) {
            error_log("Error al obtener archivo por ID: " . $e->getMessage(), 0);
            return null;
        }
    }

    /**
     * Verifica si un usuario es propietario de un archivo
     *
     * @param int $fileId ID del archivo
     * @param int $userId ID del usuario
     * @return bool True si el usuario es propietario
     */
    public function isFileOwner(int $fileId, int $userId): bool
    {
<<<<<<< HEAD
        $file = $this->getFileById($fileId); // Sin descifrar para verificación
=======
        $file = $this->getFileById($fileId);
>>>>>>> 80eb21836f8ebbf25d3d8a477426d5caea9f6925
        return $file && $file['user_id'] == $userId;
    }

    /**
     * Actualiza el estado de un archivo
     *
     * @param int $fileId ID del archivo
     * @param string $newState Nuevo estado
     * @return bool True si se actualizó correctamente
     */
    public function updateFileState(int $fileId, string $newState): bool
    {
        if (!in_array($newState, self::FILE_STATES)) {
            trigger_error("Estado de archivo inválido: $newState", E_USER_WARNING);
            return false;
        }

        try {
            $pendingTimestamp = ($newState === 'pendiente') ? 'NOW()' : 'NULL';
            $sql = "UPDATE archivos SET estado = ?, pending_timestamp = $pendingTimestamp WHERE id = ?";
            $stmt = $this->db->query($sql, [$newState, $fileId]);

            if ($stmt->rowCount() > 0) {
                error_log("Estado del archivo $fileId actualizado a: $newState", 0);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            error_log("Error al actualizar estado del archivo: " . $e->getMessage(), 0);
            return false;
        }
    }

    /**
     * Marca un archivo como optimizado y actualiza la ruta
     *
     * @param int $fileId ID del archivo
     * @param string $optimizedPath Ruta del archivo optimizado
     * @return bool True si se actualizó correctamente
     */
    public function markAsOptimized(int $fileId, string $optimizedPath): bool
    {
        try {
            $sql = "UPDATE archivos SET estado = 'optimizado', ruta_optimizada = ?, fecha_optimizacion = NOW() WHERE id = ?";
            $stmt = $this->db->query($sql, [$optimizedPath, $fileId]);

            if ($stmt->rowCount() > 0) {
                error_log("Archivo $fileId marcado como optimizado", 0);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            error_log("Error al marcar archivo como optimizado: " . $e->getMessage(), 0);
            return false;
        }
    }

    /**
     * Elimina un archivo (lo marca como borrado)
     *
     * @param int $fileId ID del archivo
     * @return bool True si se eliminó correctamente
     */
    public function deleteFile(int $fileId): bool
    {
        return $this->updateFileState($fileId, 'borrado');
    }

    /**
     * Obtiene estadísticas de archivos de un usuario
     *
     * @param int $userId ID del usuario
     * @return array Estadísticas de archivos
     */
    public function getUserFileStats(int $userId): array
    {
        try {
            $sql = "SELECT
                        COUNT(*) as total_files,
                        SUM(CASE WHEN estado = 'original' THEN 1 ELSE 0 END) as original_files,
                        SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pending_files,
                        SUM(CASE WHEN estado = 'optimizado' THEN 1 ELSE 0 END) as optimized_files,
                        SUM(tamano) as total_size
                    FROM archivos
                    WHERE user_id = ? AND estado != 'borrado'";
            $stmt = $this->db->query($sql, [$userId]);
            $stats = $stmt->fetch();

            return [
                'success' => true,
                'stats' => $stats
            ];
        } catch (\Exception $e) {
            error_log("Error al obtener estadísticas de archivos: " . $e->getMessage(), 0);
            return [
                'success' => false,
                'message' => 'Error al obtener estadísticas',
                'stats' => null
            ];
        }
    }

    /**
     * Valida el tipo de archivo basado en MIME type y extensión
     *
     * @param string $mimeType Tipo MIME del archivo
     * @param string $filename Nombre del archivo
     * @return bool True si el tipo es válido
     */
    public function isValidFileType(string $mimeType, string $filename): bool
    {
        // Verificar MIME type
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
            return false;
        }

        // Verificar extensión
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            return false;
        }

        return true;
    }

    /**
<<<<<<< HEAD
     * Actualiza los datos de Google Drive de un archivo (cifrados)
     *
     * @param int $fileId ID del archivo
     * @param int $userId ID del usuario propietario
     * @param string|null $driveId ID de Google Drive
     * @param string|null $driveLink Link de Google Drive
     * @param string $type Tipo: 'original' o 'optimizado'
     * @return bool True si se actualizó correctamente
     */
    public function updateDriveData(int $fileId, int $userId, ?string $driveId, ?string $driveLink, string $type = 'original'): bool
    {
        try {
            // Cifrar los datos antes de guardar
            $encryptedId = $driveId ? Encryption::encrypt($driveId, $userId) : null;
            $encryptedLink = $driveLink ? Encryption::encrypt($driveLink, $userId) : null;
            
            if ($type === 'optimizado') {
                $sql = "UPDATE archivos SET drive_id_optimizado = ?, drive_link_optimizado = ? WHERE id = ? AND user_id = ?";
            } else {
                $sql = "UPDATE archivos SET drive_id_original = ?, drive_link_original = ? WHERE id = ? AND user_id = ?";
            }
            
            $stmt = $this->db->query($sql, [$encryptedId, $encryptedLink, $fileId, $userId]);
            
            if ($stmt->rowCount() > 0) {
                error_log("Datos de Google Drive actualizados para archivo $fileId (tipo: $type)");
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            error_log("Error al actualizar datos de Google Drive: " . $e->getMessage(), 0);
            return false;
        }
    }

    /**
=======
>>>>>>> 80eb21836f8ebbf25d3d8a477426d5caea9f6925
     * Genera un nombre único para un archivo
     *
     * @param string $originalName Nombre original del archivo
     * @return string Nombre único generado
     */
    public function generateUniqueFilename(string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $timestamp = time();
        $random = bin2hex(random_bytes(8));

        return "{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Verifica si un archivo existe físicamente
     *
     * @param string $filePath Ruta del archivo
     * @return bool True si el archivo existe
     */
    public function fileExists(string $filePath): bool
    {
        return file_exists($filePath) && is_readable($filePath);
    }

    /**
     * Obtiene el tamaño de un archivo
     *
     * @param string $filePath Ruta del archivo
     * @return int Tamaño en bytes o 0 si hay error
     */
    public function getFileSize(string $filePath): int
    {
        if ($this->fileExists($filePath)) {
            return filesize($filePath);
        }
        return 0;
    }
}