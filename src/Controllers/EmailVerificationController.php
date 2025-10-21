<?php
namespace Controllers;

use Models\UserModel;
use Core\Database;

class EmailVerificationController
{
    private $userModel;

    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    public static function create(Database $db): EmailVerificationController
    {
        $userModel = new UserModel($db);
        return new self($userModel);
    }

    public function verifyEmail(): void
    {
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            $this->showError('Token de verificación no válido');
            return;
        }

        $result = $this->userModel->verifyEmail($token);
        
        if ($result['success']) {
            $_SESSION['login_success'] = 'Email verificado correctamente. Ya puedes iniciar sesión.';
            header('Location: /login');
        } else {
            $this->showError($result['message']);
        }
        exit();
    }

    private function showError(string $message): void
    {
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Error de Verificación</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                .error { color: #dc3545; }
            </style>
        </head>
        <body>
            <h1 class='error'>Error de Verificación</h1>
            <p>$message</p>
            <a href='/login'>Volver al Login</a>
        </body>
        </html>";
    }
}