<?php
namespace Controllers;

use Core\Database;
use Models\PasswordResetModel;

class PasswordResetController
{
    private $db;
    private $passwordResetModel;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->passwordResetModel = PasswordResetModel::create($db);
    }

    public static function create(Database $db): PasswordResetController
    {
        return new self($db);
    }

    public function showForgotPasswordForm(): void
    {
        require_once __DIR__ . '/../Views/forgot-password.php';
    }

    public function processForgotPassword(): void
    {
        $email = $_POST['email'] ?? '';
        $errorMessage = '';
        $successMessage = '';

        if (empty($email)) {
            $errorMessage = 'Por favor ingresa tu email';
        } else {
            $user = $this->passwordResetModel->getUserByEmail($email);
            
            if ($user) {
                $token = $this->passwordResetModel->createResetToken($user['id']);
                $resetLink = "https://datasnap.escuelarobertoarlt.com/reset-password?token=" . $token;
                
                // Aquí deberías enviar el email (por ahora solo mostramos el link)
                $successMessage = "Link de recuperación: " . $resetLink;
            } else {
                $successMessage = "Si el email existe, recibirás un link de recuperación";
            }
        }

        require_once __DIR__ . '/../Views/forgot-password.php';
    }

    public function showResetPasswordForm(): void
    {
        $token = $_GET['token'] ?? '';
        $errorMessage = '';

        if (empty($token)) {
            $errorMessage = 'Token inválido';
        } else {
            $tokenData = $this->passwordResetModel->validateToken($token);
            if (!$tokenData) {
                $errorMessage = 'Token inválido o expirado';
            }
        }

        require_once __DIR__ . '/../Views/reset-password.php';
    }

    public function processResetPassword(): void
    {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $errorMessage = '';
        $successMessage = '';

        if (empty($token) || empty($password) || empty($confirmPassword)) {
            $errorMessage = 'Todos los campos son requeridos';
        } elseif ($password !== $confirmPassword) {
            $errorMessage = 'Las contraseñas no coinciden';
        } elseif (strlen($password) < 6) {
            $errorMessage = 'La contraseña debe tener al menos 6 caracteres';
        } else {
            $tokenData = $this->passwordResetModel->validateToken($token);
            
            if (!$tokenData) {
                $errorMessage = 'Token inválido o expirado';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                if ($this->passwordResetModel->updatePassword($tokenData['user_id'], $hashedPassword)) {
                    $this->passwordResetModel->markTokenAsUsed($token);
                    $successMessage = 'Contraseña actualizada correctamente';
                } else {
                    $errorMessage = 'Error al actualizar la contraseña';
                }
            }
        }

        require_once __DIR__ . '/../Views/reset-password.php';
    }
}