<?php
namespace Core;

class EmailService
{
    private $fromEmail = 'datasnap71@gmail.com';
    private $fromName = 'DataSnap';
    
    public function sendVerificationEmail($toEmail, $username, $code)
    {
        $subject = 'Código de verificación - DataSnap';
        
        $message = "
        <html>
        <body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: #48A193; color: white; padding: 20px; text-align: center;'>
                <h1>DataSnap</h1>
            </div>
            <div style='padding: 30px; background: #f9f9f9;'>
                <h2>¡Bienvenido, $username!</h2>
                <p>Tu código de verificación es:</p>
                <div style='background: white; border: 2px solid #48A193; border-radius: 10px; padding: 20px; text-align: center; margin: 20px 0;'>
                    <h1 style='color: #48A193; font-size: 36px; letter-spacing: 8px; margin: 0;'>$code</h1>
                </div>
                <p>Ingresa este código en la página de verificación para activar tu cuenta.</p>
                <p><strong>Este código expira en 15 minutos.</strong></p>
            </div>
        </body>
        </html>";
        
        return $this->sendEmail($toEmail, $subject, $message);
    }
    
    public function sendPasswordResetEmail($toEmail, $username, $code)
    {
        $subject = 'Código de recuperación - DataSnap';
        
        $message = "
        <html>
        <body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: #dc3545; color: white; padding: 20px; text-align: center;'>
                <h1>DataSnap</h1>
            </div>
            <div style='padding: 30px; background: #f9f9f9;'>
                <h2>Recuperar contraseña</h2>
                <p>Hola $username,</p>
                <p>Tu código de recuperación es:</p>
                <div style='background: white; border: 2px solid #dc3545; border-radius: 10px; padding: 20px; text-align: center; margin: 20px 0;'>
                    <h1 style='color: #dc3545; font-size: 36px; letter-spacing: 8px; margin: 0;'>$code</h1>
                </div>
                <p>Ingresa este código para restablecer tu contraseña.</p>
                <p><strong>Este código expira en 15 minutos.</strong></p>
                <p>Si no solicitaste esto, ignora este email.</p>
            </div>
        </body>
        </html>";
        
        return $this->sendEmail($toEmail, $subject, $message);
    }
    
    private function sendEmail($to, $subject, $message)
    {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $this->fromName . ' <' . $this->fromEmail . '>',
            'Reply-To: ' . $this->fromEmail,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        return mail($to, $subject, $message, implode("\r\n", $headers));
    }
}