<?php
namespace Core;

class SimpleEmailService
{
    private $fromEmail = 'datasnap71@gmail.com';
    private $fromName = 'DataSnap';
    
    public function sendVerificationEmail($toEmail, $username, $code)
    {
        $subject = 'Código de verificación - DataSnap';
        $message = $this->getVerificationTemplate($username, $code);
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        
        $result = mail($toEmail, $subject, $message, $headers);
        
        // Log para debug
        error_log("Enviando email a: $toEmail, Código: $code, Resultado: " . ($result ? 'OK' : 'FAIL'));
        
        return $result;
    }
    
    public function sendPasswordResetEmail($toEmail, $username, $code)
    {
        $subject = 'Código de recuperación - DataSnap';
        $message = $this->getResetTemplate($username, $code);
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        
        $result = mail($toEmail, $subject, $message, $headers);
        
        error_log("Enviando reset a: $toEmail, Código: $code, Resultado: " . ($result ? 'OK' : 'FAIL'));
        
        return $result;
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