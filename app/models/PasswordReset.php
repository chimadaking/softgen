<?php
namespace App\Models;

class PasswordReset extends BaseModel {
    public function createToken(int $userId): string {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);

        $this->query(
            "INSERT INTO password_resets (user_id, token_hash, expires_at, created_at)
             VALUES (?, ?, ?, NOW())",
            [$userId, $tokenHash, $expiresAt]
        );

        return $token;
    }

    public function findValidToken(string $token): ?object {
        $tokenHash = hash('sha256', $token);
        return $this->fetch(
            "SELECT * FROM password_resets
             WHERE token_hash = ?
               AND used_at IS NULL
               AND expires_at > NOW()
             LIMIT 1",
            [$tokenHash]
        ) ?: null;
    }

    public function markUsed(int $id): bool {
        return $this->query("UPDATE password_resets SET used_at = NOW() WHERE id = ?", [$id]) !== false;
    }
}
