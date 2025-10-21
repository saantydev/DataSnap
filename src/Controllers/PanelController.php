<?php
/* Controlador Panel (UI principal y endpoints del panel) */
namespace Controllers;

use Models\UserModel;
use Core\Database;

class PanelController
{
    /**
     * Instancia del modelo de usuarios
     * @var UserModel
     */
    private $userModel;

    /**
     * Instancia de la base de datos
     * @var Database
     */
    private $db;

    /**
     * Constructor - inyección de dependencias
     *
     * @param UserModel $userModel Instancia del modelo de usuarios
     * @param Database $db Instancia de la base de datos
     */
    public function __construct(UserModel $userModel, Database $db)
    {
        $this->userModel = $userModel;
        $this->db = $db;
    }

    /**
     * Método factoría para crear instancia del controlador
     *
     * @param Database $db Instancia de la base de datos
     * @return PanelController Nueva instancia del controlador
     */
    public static function create(Database $db): PanelController
    {
        $userModel = new UserModel($db);
        return new self($userModel, $db);
    }

    /**
     * Muestra el panel principal del usuario
     *
     * @return void
     */
    public function index(): void
    {
        try {
            // Verificar autenticación
            if (!$this->isUserLoggedIn()) {
                $this->redirectToLogin();
                return;
            }

            // Obtener información del usuario
            $userId = $this->getCurrentUserId();
            $user = $this->userModel->findById($userId);

            if (!$user) {
                // Usuario no encontrado, destruir sesión
                $this->logout();
                $this->redirectToLogin();
                return;
            }

            // Verificar si el usuario está activo
            if (!$this->userModel->isActive($userId)) {
                $this->logout();
                $this->redirectWithError('Cuenta suspendida. Contacte al administrador.');
                return;
            }

            // Preparar datos para la vista
            $userData = [
                'id' => $user['user_id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'created_at' => $user['created_at'],
                'last_login' => $user['last_login_at']
            ];

            // Verificar si tiene autorización de Google
            $hasGoogleAuth = !empty($user['google_refresh_token']);

            // Incluir la vista del panel con los datos del usuario
            require_once __DIR__ . '/../Views/panel(prueba).html';

        } catch (\Exception $e) {
            error_log("Error en PanelController::index: " . $e->getMessage(), 0);
            $this->showError('Error interno del servidor');
        }
    }

    /**
     * Maneja la subida de archivos (delegado al FileController)
     *
     * @return void
     */
    public function upload(): void
    {
        try {
            // Verificar autenticación
            if (!$this->isUserLoggedIn()) {
                $this->jsonResponse(['success' => false, 'message' => 'Usuario no autenticado'], 401);
                return;
            }

            // Delegar al FileController
            $fileController = FileController::create($this->db);
            $fileController->upload();

        } catch (\Exception $e) {
            error_log("Error en PanelController::upload: " . $e->getMessage(), 0);
            $this->jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Muestra estadísticas del usuario
     *
     * @return void
     */
    public function stats(): void
    {
        try {
            // Verificar autenticación
            if (!$this->isUserLoggedIn()) {
                $this->jsonResponse(['success' => false, 'message' => 'Usuario no autenticado'], 401);
                return;
            }

            $userId = $this->getCurrentUserId();

            // Obtener estadísticas usando FileModel
            $fileModel = new \Models\FileModel($this->db);
            $stats = $fileModel->getUserFileStats($userId);

            if ($stats['success']) {
                $this->jsonResponse([
                    'success' => true,
                    'stats' => $stats['stats']
                ]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Error al obtener estadísticas'], 500);
            }

        } catch (\Exception $e) {
            error_log("Error en PanelController::stats: " . $e->getMessage(), 0);
            $this->jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Verifica si un usuario está logueado
     *
     * @return bool True si el usuario está logueado
     */
    private function isUserLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Obtiene el ID del usuario actual
     *
     * @return int|null ID del usuario o null si no está logueado
     */
    private function getCurrentUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Destruye la sesión del usuario
     *
     * @return void
     */
    private function logout(): void
    {
        session_unset();
        session_destroy();
    }

    /**
     * Redirige al formulario de login
     *
     * @return void
     */
    private function redirectToLogin(): void
    {
        header('Location: /login');
        exit();
    }

    /**
     * Redirige con un mensaje de error
     *
     * @param string $message Mensaje de error
     * @return void
     */
    private function redirectWithError(string $message): void
    {
        $encodedMessage = urlencode($message);
        header("Location: /login?error=$encodedMessage");
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
        http_response_code(500);
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

    /**
     * Middleware para verificar autenticación
     *
     * @return void
     */
    public function requireAuth(): void
    {
        if (!$this->isUserLoggedIn()) {
            $this->redirectToLogin();
        }
    }

    /**
     * Obtiene información del usuario para la vista
     *
     * @return array|null Datos del usuario o null si no está logueado
     */
    public function getUserInfo(): ?array
    {
        if (!$this->isUserLoggedIn()) {
            return null;
        }

        $userId = $this->getCurrentUserId();
        return $this->userModel->findById($userId);
    }
}