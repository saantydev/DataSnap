<?php
/**
 * Sistema de encriptación para links de Google Drive
 * Protege los links para que no sean visibles en phpMyAdmin
 */

require_once __DIR__ . '/env.php';

class DriveEncryption {
    private static $key;
    
    private static function getKey() {
        if (!self::$key) {
            self::$key = getenv('ENCRYPTION_KEY') ?: 'datasnap_default_key_2024';
        }
        return self::$key;
    }
    
    /**
     * Encripta un link de Google Drive
     */
    public static function encryptLink($driveLink) {
        if (empty($driveLink)) return null;
        
        $key = self::getKey();
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($driveLink, 'AES-256-CBC', $key, 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Desencripta un link de Google Drive
     */
    public static function decryptLink($encryptedLink) {
        if (empty($encryptedLink)) return null;
        
        $key = self::getKey();
        $data = base64_decode($encryptedLink);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }
    
    /**
     * Encripta el drive_id también
     */
    public static function encryptDriveId($driveId) {
        if (empty($driveId)) return null;
        
        return hash('sha256', $driveId . self::getKey());
    }
}
?>