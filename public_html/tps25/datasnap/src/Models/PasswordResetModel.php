<?php
namespace Models;

use Core\Database;

class PasswordResetModel
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public static function create(Database $db): PasswordResetModel
    {
        return new self($db);
    }

    public function createResetToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $this->db->query(
            "INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)",
            [$userId, $token, $expiresAt]
        );

        return $token;
    }

    public function validateToken(string $token): ?array
    {
        $stmt = $this->db->query(
            "SELECT pr.*, u.email FROM password_reset_tokens pr 
             JOIN users u ON pr.user_id = u.id 
             WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.used = FALSE",
            [$token]
        );

        return $stmt->fetch() ?: null;
    }

    public function markTokenAsUsed(string $token): bool
    {
        $stmt = $this->db->query(
            "UPDATE password_reset_tokens SET used = TRUE WHERE token = ?",
            [$token]
        );

        return $stmt->rowCount() > 0;
    }

    public function getUserByEmail(string $email): ?array
    {
        $stmt = $this->db->query(
            "SELECT id, username, email FROM users WHERE email = ?",
            [$email]
        );

        return $stmt->fetch() ?: null;
    }

    public function updatePassword(int $userId, string $hashedPassword): bool
    {
        $stmt = $this->db->query(
            "UPDATE users SET password = ? WHERE id = ?",
            [$hashedPassword, $userId]
        );

        return $stmt->rowCount() > 0;
    }
}