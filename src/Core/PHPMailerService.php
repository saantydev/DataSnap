<?php
namespace Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class PHPMailerService
{
    private $mailer;
    
    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->configureSMTP();
    }
    
    private function configureSMTP()
    {
        $this->mailer->isSMTP();
        $this->mailer->Host = 'smtp.gmail.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = 'datasnap71@gmail.com';
        $this->mailer->Password = 'TU_CONTRASEÑA_DE_APLICACION'; // Cambiar por tu contraseña de aplicación
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = 587;
        $this->mailer->setFrom('datasnap71@gmail.com', 'DataSnap');
    }
    
    public function sendVerificationEmail($toEmail, $username, $code)
    {
        try {
            $this->mailer->addAddress($toEmail);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Código de verificación - DataSnap';
            $this->mailer->Body = $this->getVerificationTemplate($username, $code);
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Error enviando email: " . $e->getMessage());
            return false;
        }
    }
    
    public function sendPasswordResetEmail($toEmail, $username, $code)
    {
        try {
            $this->mailer->addAddress($toEmail);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Código de recuperación - DataSnap';
            $this->mailer->Body = $this->getResetTemplate($username, $code);
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Error enviando email: " . $e->getMessage());
            return false;
        }
    }
    
    private function getVerificationTemplate($username, $code)
    {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: #48A193; color: white; padding: 20px; text-align: center;'>
                <h1>DataSnap</h1>
            </div>
            <div style='padding: 30px; background: #f9f9f9;'>
                <h2>¡Bienvenido, $username!</h2>
                <p>Tu código de verificación es:</p>
                <div style='background: white; border: 2px solid #48A193; border-radius: 10px; padding: 20px; text-align: center; margin: 20px 0;'>
                    <h1 style='color: #48A193; font-size: 36px; letter-spacing: 8px; margin: 0;'>$code</h1>
                </div>
                <p><strong>Este código expira en 15 minutos.</strong></p>
            </div>
        </div>";
    }
    
    private function getResetTemplate($username, $code)
    {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: #dc3545; color: white; padding: 20px; text-align: center;'>
                <h1>DataSnap</h1>
            </div>
            <div style='padding: 30px; background: #f9f9f9;'>
                <h2>Recuperar contraseña</h2>
                <p>Tu código de recuperación es:</p>
                <div style='background: white; border: 2px solid #dc3545; border-radius: 10px; padding: 20px; text-align: center; margin: 20px 0;'>
                    <h1 style='color: #dc3545; font-size: 36px; letter-spacing: 8px; margin: 0;'>$code</h1>
                </div>
                <p><strong>Este código expira en 15 minutos.</strong></p>
            </div>
        </div>";
    }
}