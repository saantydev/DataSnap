<?php
namespace Controllers;

use Models\UserModel;
use Core\Database;
use Core\DebugEmailService;

class PasswordResetController
{
    private $userModel;

    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    public static function create(Database $db): PasswordResetController
    {
        $userModel = new UserModel($db);
        return new self($userModel);
    }

    public function showForgotPasswordForm(): void
    {
        $message = $_SESSION['reset_message'] ?? '';
        unset($_SESSION['reset_message']);
        
        require_once __DIR__ . '/../Views/forgot-password.html';
    }

    public function processForgotPassword(): void
    {
        $email = $_POST['email'] ?? '';
        
        if (empty($email)) {
            $_SESSION['reset_message'] = 'Email es requerido';
            header('Location: /forgot-password');
            exit();
        }

        $result = $this->userModel->generatePasswordResetToken($email);
        
        if ($result['success']) {
            $emailService = new DebugEmailService();
            $emailService->sendPasswordResetEmail($email, $result['username'], $result['token']);
            $_SESSION['reset_message'] = 'Si el email existe y está verificado, recibirás un enlace de recuperación.';
        } else {
            $_SESSION['reset_message'] = 'Si el email existe y está verificado, recibirás un enlace de recuperación.';
        }
        
        header('Location: /forgot-password');
        exit();
    }

    public function showResetPasswordForm(): void
    {
        $token = $_GET['token'] ?? '';
        $errorMessage = $_SESSION['reset_error'] ?? '';
        unset($_SESSION['reset_error']);
        
        if (empty($token)) {
            $_SESSION['login_error'] = 'Token inválido';
            header('Location: /login');
            exit();
        }
        
        // Validar token y obtener email del usuario
        $validation = $this->userModel->validateResetToken($token);
        
        if (!$validation['success']) {
            $_SESSION['login_error'] = $validation['message'];
            header('Location: /login');
            exit();
        }
        
        $userEmail = $validation['email'];
        $username = $validation['username'];

        require_once __DIR__ . '/../Views/reset-password.html';
    }

    public function processResetPassword(): void
    {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if ($password !== $confirmPassword) {
            $_SESSION['reset_error'] = 'Las contraseñas no coinciden';
            header('Location: /reset-password?token=' . $token);
            return;
        }
        
        // Validar token y obtener email del usuario
        $validation = $this->userModel->validateResetToken($token);
        if (!$validation['success']) {
            $_SESSION['login_error'] = 'Token inválido o expirado';
            header('Location: /login');
            exit();
        }

        $result = $this->userModel->resetPasswordWithToken($token, $validation['email'], $password);
        
        if ($result['success']) {
            $_SESSION['login_success'] = 'Contraseña actualizada correctamente';
            header('Location: /login');
        } else {
            $_SESSION['reset_error'] = $result['message'];
            header('Location: /reset-password?token=' . $token);
        }
        exit();
    }
}