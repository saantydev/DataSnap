<?php
/** Controlador de Login (autenticación y sesión) */
namespace Controllers;

use Models\UserModel;
use Core\Database;

class LoginController
{
    /**
     * Instancia del modelo de usuario
     * @var UserModel
     */
    private $userModel;

    /**
     * Constructor - inyección de dependencias
     *
     * @param UserModel $userModel Instancia del modelo de usuario
     */
    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * Método factoría para crear instancia del controlador
     *
     * @param Database $db Instancia de la base de datos
     * @return LoginController Nueva instancia del controlador
     */
    public static function create(Database $db): LoginController
    {
        $userModel = new UserModel($db);
        return new self($userModel);
    }

    /**
     * Muestra el formulario de login
     *
     * @return void
     */
    public function showLoginForm(): void
    {
        // Verificar si el usuario ya está logueado
        if ($this->isUserLoggedIn()) {
            $this->redirectToPanel();
            return;
        }

        // Obtener mensaje de error de la sesión si existe
        $errorMessage = $_SESSION['login_error'] ?? '';

        // Obtener mensaje de éxito de la sesión si existe
        $successMessage = $_SESSION['login_success'] ?? '';

        // Obtener datos del formulario de la sesión si existen
        $formData = $_SESSION['login_form_data'] ?? [];

        // Limpiar los datos de la sesión después de mostrarlos
        if (!empty($errorMessage)) {
            unset($_SESSION['login_error']);
        }
        if (!empty($successMessage)) {
            unset($_SESSION['login_success']);
        }
        if (!empty($formData)) {
            unset($_SESSION['login_form_data']);
        }

        // Incluir la vista del formulario de login
        require_once __DIR__ . '/../Views/login.html';
    }

    /**
     * Procesa el formulario de login
     *
     * @return void
     */
    public function processLogin(): void
    {
        try {
            // Verificar método de solicitud
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->redirectWithError('Método no permitido');
                return;
            }

            // Obtener y sanitizar datos del formulario
            $username = $this->sanitizeInput($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            // Validar datos de entrada
            $validation = $this->validateLoginData($username, $password);
            if (!$validation['valid']) {
                $this->showFormWithError($validation['message']);
                return;
            }

            // Intentar autenticar al usuario
            $authResult = $this->userModel->authenticate($username, $password);

            if ($authResult['success']) {
                // Crear sesión
                $this->createUserSession($authResult['user']);

                // Log de login exitoso
                error_log("Login exitoso para usuario: {$authResult['user']['username']}", 0);

                // Redirigir al panel
                $this->redirectToPanel();
            } else {
                // Log de login fallido
                error_log("Login fallido para usuario: $username", 0);

                $this->showFormWithError('Nombre de usuario o contraseña incorrectos');
            }

        } catch (\Exception $e) {
            error_log("Error en procesamiento de login: " . $e->getMessage(), 0);
            $this->redirectWithError('Error interno del servidor');
        }
    }

    /**
     * Procesa el logout del usuario
     *
     * @return void
     */
    public function logout(): void
    {
        try {
            // Obtener información del usuario antes de destruir la sesión
            $username = $_SESSION['username'] ?? 'desconocido';

            // Destruir la sesión
            session_unset();
            session_destroy();

            // Log de logout
            error_log("Logout exitoso para usuario: $username", 0);

            // Redirigir al formulario de login
            $this->redirectToLogin();

        } catch (\Exception $e) {
            error_log("Error en logout: " . $e->getMessage(), 0);
            // Aún así redirigir al login
            $this->redirectToLogin();
        }
    }

    /**
     * Verifica si un usuario está logueado
     *
     * @return bool True si el usuario está logueado
     */
    public function isUserLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Obtiene el ID del usuario logueado
     *
     * @return int|null ID del usuario o null si no está logueado
     */
    public function getCurrentUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Verifica si el usuario actual tiene acceso a un recurso
     *
     * @param int $resourceUserId ID del usuario propietario del recurso
     * @return bool True si tiene acceso
     */
    public function hasAccess(int $resourceUserId): bool
    {
        $currentUserId = $this->getCurrentUserId();
        return $currentUserId !== null && $currentUserId === $resourceUserId;
    }

    /**
     * Valida los datos del formulario de login
     *
     * @param string $username Nombre de usuario
     * @param string $password Contraseña
     * @return array Resultado de la validación
     */
    private function validateLoginData(string $username, string $password): array
    {
        if (empty($username)) {
            return ['valid' => false, 'message' => 'El nombre de usuario es obligatorio'];
        }

        if (empty($password)) {
            return ['valid' => false, 'message' => 'La contraseña es obligatoria'];
        }

        if (strlen($username) > 100) {
            return ['valid' => false, 'message' => 'El nombre de usuario es demasiado largo'];
        }

        return ['valid' => true, 'message' => 'Datos válidos'];
    }

    /**
     * Crea la sesión del usuario después del login exitoso
     *
     * @param array $user Datos del usuario
     * @return void
     */
    private function createUserSession(array $user): void
    {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['login_time'] = time();

        // Regenerar ID de sesión por seguridad
        session_regenerate_id(true);
    }

    /**
     * Sanitiza la entrada del usuario
     *
     * @param string $input Entrada a sanitizar
     * @return string Entrada sanitizada
     */
    private function sanitizeInput(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Redirige al panel del usuario
     *
     * @return void
     */
    private function redirectToPanel(): void
    {
        header('Location: /panel');
        exit();
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
     * Muestra el formulario con un mensaje de error
     *
     * @param string $message Mensaje de error
     * @return void
     */
    private function showFormWithError(string $message): void
    {
        // Guardar el mensaje de error en la sesión
        $_SESSION['login_error'] = $message;

        // Guardar los datos del formulario en la sesión para mantenerlos
        $_SESSION['login_form_data'] = [
            'username' => $_POST['username'] ?? ''
        ];

        // Mostrar el formulario con el error
        $this->showLoginForm();
    }

    /**
     * Redirige con un mensaje de error (método legacy)
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
     * Middleware para verificar autenticación
     * Redirige al login si el usuario no está autenticado
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
     * Middleware para verificar que el usuario NO esté autenticado
     * Redirige al panel si el usuario ya está logueado
     *
     * @return void
     */
    public function requireGuest(): void
    {
        if ($this->isUserLoggedIn()) {
            $this->redirectToPanel();
        }
    }

    /**
     * Verifica si la sesión ha expirado
     *
     * @param int $maxLifetime Tiempo máximo de vida de la sesión en segundos (default 2 horas)
     * @return bool True si la sesión ha expirado
     */
    public function isSessionExpired(int $maxLifetime = 7200): bool
    {
        $loginTime = $_SESSION['login_time'] ?? 0;
        return (time() - $loginTime) > $maxLifetime;
    }

    /**
     * Renueva la sesión si está próxima a expirar
     *
     * @param int $renewThreshold Umbral para renovar en segundos (default 30 minutos)
     * @return void
     */
    public function renewSessionIfNeeded(int $renewThreshold = 1800): void
    {
        if ($this->isSessionExpired() || (time() - ($_SESSION['login_time'] ?? 0)) > $renewThreshold) {
            $_SESSION['login_time'] = time();
            session_regenerate_id(true);
        }
    }
}