<?php
/**
 * Configuración de rutas y constantes de la aplicación
 */
namespace Core;

class Config
{
    /**
     * URL base de la aplicación
     */
    public static function getBaseUrl(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host;
    }

    /**
     * Rutas de recursos estáticos
     */
    public static function getAssetUrl(string $path): string
    {
        return self::getBaseUrl() . '/config/' . ltrim($path, '/');
    }

    /**
     * Ruta de CSS
     */
    public static function getCssUrl(string $file): string
    {
        return self::getAssetUrl('css/' . $file);
    }

    /**
     * Ruta de imágenes
     */
    public static function getImageUrl(string $file): string
    {
        return self::getAssetUrl('images/' . $file);
    }

    /**
     * Ruta de iconos
     */
    public static function getIconUrl(string $file): string
    {
        return self::getAssetUrl('icons/' . $file);
    }

    /**
     * Ruta de JavaScript
     */
    public static function getJsUrl(string $file): string
    {
        return self::getAssetUrl('js/' . $file);
    }
}
?>