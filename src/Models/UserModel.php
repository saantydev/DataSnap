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

            // Insertar usuario en la base de datos
            $result = $this->insertUser($username, $email, $hashedPassword);

            if ($result) {
                error_log("Usuario registrado exitosamente: $username ($email)", 0);
                return [
                    'success' => true,
                    'message' => 'Usuario registrado exitosamente'
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
     * @return bool True si se insertó correctamente
     */
    private function insertUser(string $username, string $email, string $hashedPassword): bool
    {
        try {
            $sql = "INSERT INTO users (username, email, password_hash, created_at) VALUES (?, ?, ?, NOW())";
            $stmt = $this->db->query($sql, [$username, $email, $hashedPassword]);

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
            $sql = "SELECT user_id, username, email, created_at, last_login_at
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