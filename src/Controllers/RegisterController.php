<?php
/* Controlador de Registro (alta de usuarios) */
namespace Controllers;

use Models\UserModel;
use Core\Database;

class RegisterController
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
     * @return RegisterController Nueva instancia del controlador
     */
    public static function create(Database $db): RegisterController
    {
        $userModel = new UserModel($db);
        return new self($userModel);
    }

    /**
     * Muestra el formulario de registro
     *
     * @return void
     */
    public function showRegisterForm(): void
    {
        // Obtener mensaje de error de la sesión si existe
        $errorMessage = $_SESSION['register_error'] ?? '';

        // Obtener datos del formulario de la sesión si existen
        $formData = $_SESSION['register_form_data'] ?? [];

        // Limpiar los datos de la sesión después de mostrarlos
        if (!empty($errorMessage)) {
            unset($_SESSION['register_error']);
        }
        if (!empty($formData)) {
            unset($_SESSION['register_form_data']);
        }

        // Incluir la vista del formulario de registro
        require_once __DIR__ . '/../Views/register.html';
    }

    /**
     * Procesa el formulario de registro
     *
     * @return void
     */
    public function processRegister(): void
    {
        try {
            // Verificar método de solicitud
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->redirectWithError('Método no permitido');
                return;
            }

            // Obtener y sanitizar datos del formulario
            $username = $this->sanitizeInput($_POST['username'] ?? '');
            $email = $this->sanitizeInput($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            // Validar datos de entrada básicos
            $validation = $this->validateRegisterData($username, $email, $password);
            if (!$validation['valid']) {
                $this->showFormWithError($validation['message']);
                return;
            }

            // Intentar registrar al usuario
            $registerResult = $this->userModel->register($username, $email, $password);

            if ($registerResult['success']) {
                // Log de registro exitoso
                error_log("Registro exitoso para usuario: $username ($email)", 0);

                // Redirigir al login con mensaje de éxito
                $this->redirectToLoginWithSuccess('Usuario registrado exitosamente. Por favor, inicia sesión.');
            } else {
                // Log de registro fallido
                error_log("Registro fallido para usuario: $username ($email) - " . $registerResult['message'], 0);

                $this->showFormWithError($registerResult['message']);
            }

        } catch (\Exception $e) {
            error_log("Error en procesamiento de registro: " . $e->getMessage(), 0);
            $this->redirectWithError('Error interno del servidor');
        }
    }

    /**
     * Valida los datos básicos del formulario de registro
     *
     * @param string $username Nombre de usuario
     * @param string $email Correo electrónico
     * @param string $password Contraseña
     * @return array Resultado de la validación
     */
    private function validateRegisterData(string $username, string $email, string $password): array
    {
        if (empty($username)) {
            return ['valid' => false, 'message' => 'El nombre de usuario es obligatorio'];
        }

        if (empty($email)) {
            return ['valid' => false, 'message' => 'El correo electrónico es obligatorio'];
        }

        if (empty($password)) {
            return ['valid' => false, 'message' => 'La contraseña es obligatoria'];
        }

        if (strlen($username) > 50) {
            return ['valid' => false, 'message' => 'El nombre de usuario es demasiado largo'];
        }

        if (strlen($email) > 100) {
            return ['valid' => false, 'message' => 'El correo electrónico es demasiado largo'];
        }

        // Validar formato de email básico
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'El formato del correo electrónico no es válido'];
        }

        return ['valid' => true, 'message' => 'Datos válidos'];
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
     * Redirige al formulario de login con mensaje de éxito
     *
     * @param string $message Mensaje de éxito
     * @return void
     */
    private function redirectToLoginWithSuccess(string $message): void
    {
        // Guardar el mensaje de éxito en la sesión
        $_SESSION['login_success'] = $message;

        // Redirigir al login
        header("Location: /login");
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
        $_SESSION['register_error'] = $message;

        // Guardar los datos del formulario en la sesión para mantenerlos
        $_SESSION['register_form_data'] = [
            'username' => $_POST['username'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? ''
        ];

        // Mostrar el formulario con el error
        $this->showRegisterForm();
    }

    /**
     * Redirige al formulario de registro con mensaje de error (método legacy)
     *
     * @param string $message Mensaje de error
     * @return void
     */
    private function redirectWithError(string $message): void
    {
        $encodedMessage = urlencode($message);
        header("Location: /register?error=$encodedMessage");
        exit();
    }

    /**
     * Verifica si el registro está habilitado
     * Útil para futuras implementaciones de activación por email
     *
     * @return bool True si el registro está habilitado
     */
    public function isRegistrationEnabled(): bool
    {
        // Por ahora siempre habilitado, pero puede configurarse
        return true;
    }

    /**
     * Obtiene los términos y condiciones
     * Útil para futuras implementaciones
     *
     * @return string Términos y condiciones
     */
    public function getTermsAndConditions(): string
    {
        return "Al registrarte, aceptas nuestros términos de servicio y política de privacidad.";
    }

    /**
     * Verifica si un email ya está registrado
     * Método público para validaciones del frontend (futuras implementaciones)
     *
     * @param string $email Email a verificar
     * @return bool True si el email ya existe
     */
    public function isEmailTaken(string $email): bool
    {
        // Esta funcionalidad se maneja internamente en el UserModel.register()
        // Por ahora devolver false, puede implementarse en el futuro si se necesita
        // verificación AJAX en tiempo real
        return false;
    }

    /**
     * Verifica si un nombre de usuario ya está registrado
     * Método público para validaciones del frontend (futuras implementaciones)
     *
     * @param string $username Nombre de usuario a verificar
     * @return bool True si el username ya existe
     */
    public function isUsernameTaken(string $username): bool
    {
        // Esta funcionalidad se maneja internamente en el UserModel.register()
        return false;
    }
}