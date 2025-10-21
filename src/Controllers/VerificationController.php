<?php
namespace Controllers;

use Models\UserModel;
use Core\Database;
use Core\DebugEmailService;

class VerificationController
{
    private $userModel;

    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    public static function create(Database $db): VerificationController
    {
        $userModel = new UserModel($db);
        return new self($userModel);
    }

    public function showVerificationForm(): void
    {
        $email = $_SESSION['pending_verification_email'] ?? '';
        if (empty($email)) {
            header('Location: /register');
            exit();
        }

        $message = $_SESSION['verification_message'] ?? '';
        unset($_SESSION['verification_message']);

        require_once __DIR__ . '/../Views/verify-code.html';
    }

    public function processVerification(): void
    {
        $email = $_SESSION['pending_verification_email'] ?? '';
        $code = $_POST['code'] ?? '';

        if (empty($email) || empty($code)) {
            $_SESSION['verification_message'] = 'Código requerido';
            header('Location: /verify-code');
            exit();
        }

        $result = $this->userModel->verifyEmailWithCode($email, $code);

        if ($result['success']) {
            unset($_SESSION['pending_verification_email']);
            $_SESSION['login_success'] = 'Cuenta verificada correctamente. Ya puedes iniciar sesión.';
            header('Location: /login');
        } else {
            $_SESSION['verification_message'] = $result['message'];
            header('Location: /verify-code');
        }
        exit();
    }

    public function resendCode(): void
    {
        $email = $_SESSION['pending_verification_email'] ?? '';
        if (empty($email)) {
            header('Location: /register');
            exit();
        }

        // Generar nuevo código
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Usar el método del UserModel para actualizar
        $result = $this->updateVerificationCode($email, $code);
        
        if ($result) {
            // Enviar email
            $emailService = new DebugEmailService();
            $emailService->sendVerificationEmail($email, '', $code);
            $_SESSION['verification_message'] = 'Código reenviado correctamente';
        } else {
            $_SESSION['verification_message'] = 'Error al reenviar el código';
        }

        header('Location: /verify-code');
        exit();
    }
    
    private function updateVerificationCode(string $email, string $code): bool
    {
        try {
            // Crear una nueva instancia de Database para hacer la consulta
            $dbConfig = [
                'host' => 'localhost',
                'dbname' => 'u214138677_datasnap',
                'username' => 'u214138677_datasnap',
                'password' => 'Rasa@25ChrSt',
                'charset' => 'utf8mb4'
            ];
            $db = \Core\Database::getInstance($dbConfig);
            
            $sql = "UPDATE users SET verification_code = ?, verification_code_expires = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE email = ?";
            $stmt = $db->query($sql, [$code, $email]);
            
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Error actualizando código de verificación: " . $e->getMessage(), 0);
            return false;
        }
    }
}