<?php
namespace Core;

class MailtrapService
{
    // Registrate en mailtrap.io y obtén estas credenciales
    private $host = 'sandbox.smtp.mailtrap.io';
    private $port = 2525;
    private $username = 'DataSnap';
    private $password = 'Datasnaptesis71!';
    
    public function sendVerificationEmail($toEmail, $username, $code)
    {
        $subject = 'Código de verificación - DataSnap';
        $message = $this->getVerificationTemplate($username, $code);
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: DataSnap <noreply@datasnap.com>\r\n";
        
        // Para Mailtrap, usar configuración SMTP básica
        ini_set('SMTP', $this->host);
        ini_set('smtp_port', $this->port);
        
        $result = mail($toEmail, $subject, $message, $headers);
        
        error_log("Mailtrap - Enviando a: $toEmail, Código: $code");
        
        return $result;
    }
    
    public function sendPasswordResetEmail($toEmail, $username, $code)
    {
        $subject = 'Código de recuperación - DataSnap';
        $message = $this->getResetTemplate($username, $code);
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: DataSnap <noreply@datasnap.com>\r\n";
        
        return mail($toEmail, $subject, $message, $headers);
    }
    
    private function getVerificationTemplate($username, $code)
    {
        return "<h2>Código: $code</h2><p>Usuario: $username</p>";
    }
    
    private function getResetTemplate($username, $code)
    {
        return "<h2>Reset: $code</h2><p>Usuario: $username</p>";
    }
}