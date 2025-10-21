<?php
/**
 * Clase Encryption - Maneja el cifrado/descifrado de datos sensibles
 * 
 * Esta clase proporciona métodos para cifrar y descifrar información sensible
 * como drive_id y drive_link usando AES-256-CBC con una clave específica por usuario.
 * 
 * @package Core
 * @author Sistema Datasnap
 * @version 1.0
 */
namespace Core;

class Encryption
{
    /**
     * Método de cifrado utilizado
     */
    private const CIPHER_METHOD = 'AES-256-CBC';
    
    /**
     * Clave base para el cifrado (debe ser cambiada en producción)
     */
    private const BASE_KEY = 'DataSnap2024SecureKey!@#$%^&*()';
    
    /**
     * Genera una clave única para cada usuario
     * 
     * @param int $userId ID del usuario
     * @return string Clave de cifrado única para el usuario
     */
    private static function getUserKey(int $userId): string
    {
        return hash('sha256', self::BASE_KEY . $userId . 'salt_unique_per_user');
    }
    
    /**
     * Cifra un valor usando la clave del usuario
     * 
     * @param string $value Valor a cifrar
     * @param int $userId ID del usuario
     * @return string|null Valor cifrado en base64 o null si hay error
     */
    public static function encrypt(string $value, int $userId): ?string
    {
        if (empty($value)) {
            return null;
        }
        
        try {
            $key = self::getUserKey($userId);
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::CIPHER_METHOD));
            $encrypted = openssl_encrypt($value, self::CIPHER_METHOD, $key, 0, $iv);
            
            if ($encrypted === false) {
                error_log("Error al cifrar valor para usuario $userId");
                return null;
            }
            
            // Combinar IV y datos cifrados, luego codificar en base64
            return base64_encode($iv . $encrypted);
            
        } catch (\Exception $e) {
            error_log("Excepción al cifrar: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Descifra un valor usando la clave del usuario
     * 
     * @param string $encryptedValue Valor cifrado en base64
     * @param int $userId ID del usuario
     * @return string|null Valor descifrado o null si hay error
     */
    public static function decrypt(string $encryptedValue, int $userId): ?string
    {
        if (empty($encryptedValue)) {
            return null;
        }
        
        try {
            $key = self::getUserKey($userId);
            $data = base64_decode($encryptedValue);
            
            if ($data === false) {
                error_log("Error al decodificar base64 para usuario $userId");
                return null;
            }
            
            $ivLength = openssl_cipher_iv_length(self::CIPHER_METHOD);
            $iv = substr($data, 0, $ivLength);
            $encrypted = substr($data, $ivLength);
            
            $decrypted = openssl_decrypt($encrypted, self::CIPHER_METHOD, $key, 0, $iv);
            
            if ($decrypted === false) {
                error_log("Error al descifrar valor para usuario $userId");
                return null;
            }
            
            return $decrypted;
            
        } catch (\Exception $e) {
            error_log("Excepción al descifrar: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Cifra múltiples campos de un archivo
     * 
     * @param array $fileData Datos del archivo
     * @param int $userId ID del usuario
     * @return array Datos del archivo con campos cifrados
     */
    public static function encryptFileData(array $fileData, int $userId): array
    {
        $fieldsToEncrypt = [
            'drive_id_original',
            'drive_link_original', 
            'drive_id_optimizado',
            'drive_link_optimizado'
        ];
        
        foreach ($fieldsToEncrypt as $field) {
            if (isset($fileData[$field]) && !empty($fileData[$field])) {
                $fileData[$field] = self::encrypt($fileData[$field], $userId);
            }
        }
        
        return $fileData;
    }
    
    /**
     * Descifra múltiples campos de un archivo
     * 
     * @param array $fileData Datos del archivo
     * @param int $userId ID del usuario
     * @return array Datos del archivo con campos descifrados
     */
    public static function decryptFileData(array $fileData, int $userId): array
    {
        $fieldsToDecrypt = [
            'drive_id_original',
            'drive_link_original',
            'drive_id_optimizado', 
            'drive_link_optimizado'
        ];
        
        foreach ($fieldsToDecrypt as $field) {
            if (isset($fileData[$field]) && !empty($fileData[$field])) {
                $fileData[$field] = self::decrypt($fileData[$field], $userId);
            }
        }
        
        return $fileData;
    }
}