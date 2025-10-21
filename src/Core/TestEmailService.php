<?php
namespace Core;

class TestEmailService
{
    public function sendVerificationEmail($toEmail, $username, $code)
    {
        $_SESSION['debug_code'] = $code;
        $_SESSION['debug_email'] = $toEmail;
        
        error_log("CÓDIGO DE VERIFICACIÓN: $code para $toEmail");
        
        return true;
    }
    
    public function sendPasswordResetEmail($toEmail, $username, $token)
    {
        $resetUrl = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password?token=" . $token;
        
        // Guardar enlace en sesión para mostrarlo
        $_SESSION['reset_link'] = $resetUrl;
        $_SESSION['reset_email'] = $toEmail;
        
        error_log("ENLACE DE RESET: $resetUrl para $toEmail");
        
        return true;
    }
}