<?php
namespace Core;

class DebugEmailService
{
    public function sendVerificationEmail($toEmail, $username, $code)
    {
        $subject = 'Código de verificación - DataSnap';
        $message = "
        <html>
        <body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f5f5f5;'>
            <div style='background: #ffffffff; color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <img src='http://" . $_SERVER['HTTP_HOST'] . "/config/images/datasnapLogo.png' alt='DataSnap Logo' style='width: 120px; height: auto; margin-bottom: 15px; max-width: 100%;'>
            </div>
            <div style='padding: 40px; background: white; border-radius: 0 0 10px 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>
                <h2 style='color: #333; margin-top: 0;'>¡Bienvenido a DataSnap!</h2>
                <p style='color: #666; font-size: 16px;'>Tu código de verificación es:</p>
                <div style='background: linear-gradient(135deg, #2dacb5, #2dacb5); border-radius: 15px; padding: 30px; text-align: center; margin: 30px 0; box-shadow: 0 4px 15px rgba(72, 161, 147, 0.3);'>
                    <h1 style='color: white; font-size: 42px; letter-spacing: 12px; margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.2);'>$code</h1>
                </div>
                <p style='color: #666; font-size: 16px;'>Ingresa este código en la página de verificación para activar tu cuenta.</p>
                <div style='background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 5px; padding: 15px; margin: 20px 0;'>
                    <p style='margin: 0; color: #0c5460; font-size: 14px;'><strong>⏰ Este código expira en 15 minutos.</strong></p>
                </div>
                <hr style='border: none; border-top: 1px solid #eee; margin: 30px 0;'>
                <p style='color: #999; font-size: 12px; text-align: center;'>Este email fue enviado por DataSnap - Sistema de gestión de datos</p>
            </div>
        </body>
        </html>";
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: DataSnap <datasnap71@gmail.com>\r\n";
        
        $result = mail($toEmail, $subject, $message, $headers);
        
        error_log("CÓDIGO DE VERIFICACIÓN: $code para $toEmail - Enviado: " . ($result ? 'SI' : 'NO'));
        
        return $result;
    }
    
    public function sendPasswordResetEmail($toEmail, $username, $token)
    {
        $resetUrl = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password?token=" . $token;
        
        $subject = 'Recuperar contraseña - DataSnap';
        $message = "
        <html>
        <body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f5f5f5;'>
            <div style='background: #e7f3f1ff; color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <img src='http://" . $_SERVER['HTTP_HOST'] . "/config/images/datasnapLogo.png' alt='DataSnap Logo' style='width: 120px; height: auto; margin-bottom: 15px; max-width: 100%;'>
            </div>
            <div style='padding: 40px; background: white; border-radius: 0 0 10px 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>
                <h2 style='color: #333; margin-top: 0;'>Recuperar contraseña</h2>
                <p style='color: #666; font-size: 16px;'>Hola $username,</p>
                <p style='color: #666; font-size: 16px;'>Recibimos una solicitud para restablecer tu contraseña. Haz clic en el siguiente botón:</p>
                <div style='text-align: center; margin: 40px 0;'>
                    <a href='$resetUrl' target='_blank' rel='noopener noreferrer' style='background: #2dacb5; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; display: inline-block; box-shadow: 0 2px 4px rgba(72, 161, 147, 0.3);'>Restablecer Contraseña</a>
                </div>
                <p style='color: #666; font-size: 14px;'>O copia y pega este enlace en tu navegador:</p>
                <p style='word-break: break-all; background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #2dacb5; font-family: monospace; font-size: 12px;'>$resetUrl</p>
                <div style='background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px; margin: 20px 0;'>
                    <p style='margin: 0; color: #856404; font-size: 14px;'><strong>⚠️ Este enlace expira en 1 hora.</strong></p>
                </div>
                <p style='color: #999; font-size: 14px;'>Si no solicitaste esto, puedes ignorar este email de forma segura.</p>
                <hr style='border: none; border-top: 1px solid #eee; margin: 30px 0;'>
                <p style='color: #999; font-size: 12px; text-align: center;'>Este email fue enviado por DataSnap - Sistema de gestión de datos</p>
            </div>
        </body>
        </html>";
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: DataSnap <datasnap71@gmail.com>\r\n";
        
        $result = mail($toEmail, $subject, $message, $headers);
        
        error_log("ENLACE DE RESET: $resetUrl para $toEmail - Enviado: " . ($result ? 'SI' : 'NO'));
        
        return $result;
    }
}