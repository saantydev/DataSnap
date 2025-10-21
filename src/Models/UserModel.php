<?php
/**
 * Modelo UserModel - Maneja operaciones relacionadas con usuarios
 *
 * Esta clase encapsula toda la lógica de negocio relacionada con usuarios,
 * incluyendo registro, autenticación, validación y gestión de datos de usuario.
 * Implementa validaciones robustas y manejo de errores con códigos estándar.
 *
 * @package Models
 * @author Sistema Datasnap
 * @version 1.0
 */
namespace Models;

use Core\Database;

class UserModel
{
    /**
     * Instancia de la base de datos
     * @var Database
     */
    private $db;

    /**
     * Constructor - inyección de dependencias
     *
     * @param Database $db Instancia de la base de datos
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Registra un nuevo usuario en el sistema
     *
     * @param string $username Nombre de usuario
     * @param string $email Correo electrónico
     * @param string $password Contraseña en texto plano
     * @return array Resultado del registro con 'success' y 'message'
     */
    public function register(string $username, string $email, string $password): array
    {
        try {
            // Validar datos de entrada
            $validation = $this->validateRegistrationData($username, $email, $password);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message']
                ];
            }

            // Verificar si el usuario ya existe
            if ($this->userExists($username, $email)) {
                return [
                    'success' => false,
                    'message' => 'El nombre de usuario o correo electrónico ya están registrados'
                ];
            }

            // Hashear la contraseña
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            if ($hashedPassword === false) {
                trigger_error("Error al hashear la contraseña", E_USER_WARNING);
                return [
                    'success' => false,
                    'message' => 'Error interno del servidor'
                ];
            }

            // Generar código de verificación de 6 dígitos
            $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Insertar usuario en la base de datos
            $result = $this->insertUser($username, $email, $hashedPassword, $verificationCode);

            if ($result) {
                error_log("Usuario registrado exitosamente: $username ($email)", 0);
                return [
                    'success' => true,
                    'message' => 'Usuario registrado. Revisa tu email para el código de verificación.',
                    'verification_code' => $verificationCode,
                    'email' => $email,
                    'username' => $username
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al registrar el usuario'
                ];
            }

        } catch (\Exception $e) {
            error_log("Error en registro de usuario: " . $e->getMessage(), 0);
            return [
                'success' => false,
                'message' => 'Error interno del servidor'
            ];
        }
    }

    /**
     * Autentica un usuario en el sistema
     *
     * @param string $username Nombre de usuario o email
     * @param string $password Contraseña
     * @return array Resultado de la autenticación con 'success', 'message' y datos del usuario
     */
    public function authenticate(string $username, string $password): array
    {
        try {
            // Validar datos de entrada
            if (empty($username) || empty($password)) {
                return [
                    'success' => false,
                    'message' => 'Nombre de usuario y contraseña son obligatorios'
                ];
            }

            // Buscar usuario por username o email
            $user = $this->findUserByUsernameOrEmail($username);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Credenciales incorrectas'
                ];
            }

            // Verificar contraseña
            if (!password_verify($password, $user['password_hash'])) {
                error_log("Intento de login fallido para usuario: $username", 0);
                return [
                    'success' => false,
                    'message' => 'Credenciales incorrectas'
                ];
            }

            // Actualizar último login
            $this->updateLastLogin($user['user_id']);

            // Remover hash de contraseña de la respuesta
            unset($user['password_hash']);

            error_log("Login exitoso para usuario: $username", 0);

            return [
                'success' => true,
                'message' => 'Autenticación exitosa',
                'user' => $user
            ];

        } catch (\Exception $e) {
            error_log("Error en autenticación: " . $e->getMessage(), 0);
            return [
                'success' => false,
                'message' => 'Error interno del servidor'
            ];
        }
    }

    /**
     * Valida los datos de registro
     *
     * @param string $username Nombre de usuario
     * @param string $email Correo electrónico
     * @param string $password Contraseña
     * @return array Resultado de la validación
     */
    private function validateRegistrationData(string $username, string $email, string $password): array
    {
        // Validar nombre de usuario
        if (empty($username)) {
            return ['valid' => false, 'message' => 'El nombre de usuario es obligatorio'];
        }

        if (strlen($username) < 3 || strlen($username) > 50) {
            return ['valid' => false, 'message' => 'El nombre de usuario debe tener entre 3 y 50 caracteres'];
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return ['valid' => false, 'message' => 'El nombre de usuario solo puede contener letras, números y guiones bajos'];
        }

        // Validar email
        if (empty($email)) {
            return ['valid' => false, 'message' => 'El correo electrónico es obligatorio'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'El formato del correo electrónico no es válido'];
        }

        if (strlen($email) > 100) {
            return ['valid' => false, 'message' => 'El correo electrónico es demasiado largo'];
        }

        // Validar contraseña
        if (empty($password)) {
            return ['valid' => false, 'message' => 'La contraseña es obligatoria'];
        }

        if (strlen($password) < 8) {
            return ['valid' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres'];
        }

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
            return ['valid' => false, 'message' => 'La contraseña debe contener al menos una letra minúscula, una mayúscula y un número'];
        }

        return ['valid' => true, 'message' => 'Datos válidos'];
    }

    /**
     * Verifica si un usuario ya existe
     *
     * @param string $username Nombre de usuario
     * @param string $email Correo electrónico
     * @return bool True si el usuario existe
     */
    private function userExists(string $username, string $email): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM users WHERE username = ? OR email = ?";
            $stmt = $this->db->query($sql, [$username, $email]);
            $result = $stmt->fetch();

            return $result['count'] > 0;
        } catch (\Exception $e) {
            error_log("Error al verificar existencia de usuario: " . $e->getMessage(), 0);
            return true; // Asumir que existe para prevenir registro
        }
    }

    /**
     * Inserta un nuevo usuario en la base de datos
     *
     * @param string $username Nombre de usuario
     * @param string $email Correo electrónico
     * @param string $hashedPassword Contraseña hasheada
     * @param string $verificationToken Token de verificación
     * @return bool True si se insertó correctamente
     */
    private function insertUser(string $username, string $email, string $hashedPassword, string $verificationCode): bool
    {
        try {
            $sql = "INSERT INTO users (username, email, password_hash, verification_code, verification_code_expires, created_at) 
                    VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE), NOW())";
            $stmt = $this->db->query($sql, [$username, $email, $hashedPassword, $verificationCode]);

            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Error al insertar usuario: " . $e->getMessage(), 0);
            return false;
        }
    }

    /**
     * Busca un usuario por nombre de usuario o email
     *
     * @param string $usernameOrEmail Nombre de usuario o email
     * @return array|null Datos del usuario o null si no existe
     */
    private function findUserByUsernameOrEmail(string $usernameOrEmail): ?array
    {
        try {
            $sql = "SELECT user_id, username, email, password_hash, created_at, last_login_at
                    FROM users
                    WHERE username = ? OR email = ?";
            $stmt = $this->db->query($sql, [$usernameOrEmail, $usernameOrEmail]);
            $user = $stmt->fetch();

            return $user ?: null;
        } catch (\Exception $e) {
            error_log("Error al buscar usuario: " . $e->getMessage(), 0);
            return null;
        }
    }

    /**
     * Actualiza la fecha del último login
     *
     * @param int $userId ID del usuario
     * @return bool True si se actualizó correctamente
     */
    private function updateLastLogin(int $userId): bool
    {
        try {
            $sql = "UPDATE users SET last_login_at = NOW() WHERE user_id = ?";
            $stmt = $this->db->query($sql, [$userId]);

            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Error al actualizar último login: " . $e->getMessage(), 0);
            return false;
        }
    }

    /**
     * Obtiene un usuario por su ID
     *
     * @param int $userId ID del usuario
     * @return array|null Datos del usuario o null si no existe
     */
    public function findById(int $userId): ?array
    {
        try {
            $sql = "SELECT user_id, username, email, created_at, last_login_at, google_refresh_token
                    FROM users
                    WHERE user_id = ?";
            $stmt = $this->db->query($sql, [$userId]);
            $user = $stmt->fetch();

            return $user ?: null;
        } catch (\Exception $e) {
            error_log("Error al obtener usuario por ID: " . $e->getMessage(), 0);
            return null;
        }
    }

    /**
     * Alias para findById para compatibilidad
     *
     * @param int $userId ID del usuario
     * @return array|null Datos del usuario o null si no existe
     */
    public function getUserById(int $userId): ?array
    {
        return $this->findById($userId);
    }

    /**
     * Verifica si un usuario tiene autorización de Google
     *
     * @param int $userId ID del usuario
     * @return bool True si tiene refresh token
     */
    public function hasGoogleAuth(int $userId): bool
    {
        $user = $this->findById($userId);
        return $user && !empty($user['google_refresh_token']);
    }

    /**
     * Verifica si un usuario está activo/suspendido
     *
     * @param int $userId ID del usuario
     * @return bool True si el usuario está activo
     */
    public function isActive(int $userId): bool
    {
        $user = $this->findById($userId);
        return $user !== null;
    }

    /**
     * Verifica email con código
     */
    public function verifyEmailWithCode(string $email, string $code): array
    {
        try {
            $sql = "UPDATE users SET email_verified = TRUE, verification_code = NULL, verification_code_expires = NULL 
                    WHERE email = ? AND verification_code = ? AND verification_code_expires > NOW()";
            $stmt = $this->db->query($sql, [$email, $code]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Email verificado correctamente'];
            }
            return ['success' => false, 'message' => 'Código inválido o expirado'];
        } catch (\Exception $e) {
            error_log("Error verificando email: " . $e->getMessage(), 0);
            return ['success' => false, 'message' => 'Error interno'];
        }
    }
    
    /**
     * Genera token para recuperar contraseña
     */
    public function generatePasswordResetToken(string $email): array
    {
        try {
            // Verificar que el email existe y está verificado
            $checkSql = "SELECT user_id, username FROM users WHERE email = ? AND email_verified = TRUE";
            $checkStmt = $this->db->query($checkSql, [$email]);
            $user = $checkStmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'message' => 'Email no encontrado o no verificado'];
            }
            
            $token = bin2hex(random_bytes(32));
            $sql = "UPDATE users SET reset_code = ?, reset_code_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) 
                    WHERE email = ?";
            $stmt = $this->db->query($sql, [$token, $email]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'token' => $token, 'username' => $user['username']];
            }
            return ['success' => false, 'message' => 'Error al generar token'];
        } catch (\Exception $e) {
            error_log("Error generando token reset: " . $e->getMessage(), 0);
            return ['success' => false, 'message' => 'Error interno'];
        }
    }
    
    /**
     * Valida token de reset y obtiene email del usuario
     */
    public function validateResetToken(string $token): array
    {
        try {
            $sql = "SELECT email, username FROM users WHERE reset_code = ? AND reset_code_expires > NOW()";
            $stmt = $this->db->query($sql, [$token]);
            $user = $stmt->fetch();
            
            if ($user) {
                return ['success' => true, 'email' => $user['email'], 'username' => $user['username']];
            }
            return ['success' => false, 'message' => 'Token inválido o expirado'];
        } catch (\Exception $e) {
            error_log("Error validando token: " . $e->getMessage(), 0);
            return ['success' => false, 'message' => 'Error interno'];
        }
    }
    
    /**
     * Restablece contraseña con token y validación de email
     */
    public function resetPasswordWithToken(string $token, string $email, string $newPassword): array
    {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password_hash = ?, reset_code = NULL, reset_code_expires = NULL 
                    WHERE reset_code = ? AND email = ? AND reset_code_expires > NOW()";
            $stmt = $this->db->query($sql, [$hashedPassword, $token, $email]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Contraseña actualizada'];
            }
            return ['success' => false, 'message' => 'Token inválido, expirado o email incorrecto'];
        } catch (\Exception $e) {
            error_log("Error reseteando contraseña: " . $e->getMessage(), 0);
            return ['success' => false, 'message' => 'Error interno'];
        }
    }

    /**
     * Sanitiza datos de entrada para prevenir XSS
     *
     * @param string $data Datos a sanitizar
     * @return string Datos sanitizados
     */
    private function sanitize(string $data): string
    {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}