<?php
require_once __DIR__ . '/config/encryption.php';

// Test de encriptación
$originalLink = "https://drive.google.com/file/d/1JQfOGrQQKfZUDgWmiTjStu6bleASohWA/view";
$originalId = "1JQfOGrQQKfZUDgWmiTjStu6bleASohWA";

echo "=== TEST DE ENCRIPTACIÓN ===\n\n";

echo "Link original: $originalLink\n";
echo "ID original: $originalId\n\n";

// Encriptar
$encryptedLink = DriveEncryption::encryptLink($originalLink);
$encryptedId = DriveEncryption::encryptDriveId($originalId);

echo "Link encriptado: $encryptedLink\n";
echo "ID encriptado: $encryptedId\n\n";

// Desencriptar
$decryptedLink = DriveEncryption::decryptLink($encryptedLink);

echo "Link desencriptado: $decryptedLink\n\n";

// Verificar
if ($originalLink === $decryptedLink) {
    echo "✅ ENCRIPTACIÓN FUNCIONA CORRECTAMENTE\n";
} else {
    echo "❌ ERROR EN LA ENCRIPTACIÓN\n";
}

echo "\n=== RESULTADO EN PHPMYADMIN ===\n";
echo "En phpMyAdmin verás:\n";
echo "drive_link_original: $encryptedLink\n";
echo "drive_id_original: $encryptedId\n";
echo "\nEn lugar de los links reales de Google Drive.\n";
?>