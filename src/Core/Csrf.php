<?php
namespace Core;

/**
 * CSRF helper: generación y verificación de token anti-CSRF
 *
 * Uso:
 * - Generar token por sesión (una vez): Csrf::generate();
 * - Obtener token actual para inyectar en vistas: Csrf::token();
 * - En endpoints POST: if (!Csrf::isValid(Csrf::getFromRequest())) { ... 403 ... }
 */
class Csrf
{
    private const SESSION_KEY = 'csrf_token';
    private const TOKEN_BYTES = 32;

    /**
     * Genera un token y lo guarda en la sesión si no existe.
     * Devuelve el token actual (existente o nuevo).
     */
    public static function generate(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            // No forzamos session_start aquí para no romper flujos;
            // Router debe haber iniciado la sesión previamente.
            return '';
        }

        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(self::TOKEN_BYTES));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Devuelve el token actual de la sesión o cadena vacía si no hay sesión/token.
     */
    public static function token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return '';
        }
        return (string)($_SESSION[self::SESSION_KEY] ?? '');
    }

    /**
     * Obtiene el token enviado por el cliente.
     * - Prioriza header: X-CSRF-Token (HTTP_X_CSRF_TOKEN)
     * - Como alternativa, campo de formulario: csrf_token (POST)
     */
    public static function getFromRequest(): ?string
    {
        // Header estándar para fetch/XHR
        $header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (is_string($header) && $header !== '') {
            return $header;
        }

        // Campo oculto en formularios
        if (isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) && $_POST['csrf_token'] !== '') {
            return $_POST['csrf_token'];
        }

        return null;
    }

    /**
     * Verifica que el token proporcionado coincide con el token de sesión.
     */
    public static function isValid(?string $providedToken): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }
        $sessionToken = $_SESSION[self::SESSION_KEY] ?? null;
        if (!is_string($sessionToken) || !is_string($providedToken)) {
            return false;
        }
        // Comparación segura contra timing attacks
        return hash_equals($sessionToken, $providedToken);
    }
}