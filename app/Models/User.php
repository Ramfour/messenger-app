<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function findByNickname(string $nickname): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE nickname = :nickname LIMIT 1');
        $stmt->execute(['nickname' => $nickname]);
        return $stmt->fetch();
    }

    public function findByVerifyToken(string $token): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE verify_token = :token LIMIT 1');
        $stmt->execute(['token' => $token]);
        return $stmt->fetch();
    }

    public function create(string $email, string $passwordHash, string $verifyToken): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (email, password_hash, verify_token) VALUES (:email, :hash, :token) RETURNING id'
        );
        $stmt->execute(['email' => $email, 'hash' => $passwordHash, 'token' => $verifyToken]);
        return (int) $stmt->fetchColumn();
    }

    public function verify(int $id): void
    {
        $this->db->prepare('UPDATE users SET email_verified = TRUE, verify_token = NULL WHERE id = :id')
            ->execute(['id' => $id]);
    }

    public function updateProfile(int $id, string $nickname, bool $emailHidden): void
    {
        $this->db->prepare(
            'UPDATE users SET nickname = :nickname, email_hidden = :hidden WHERE id = :id'
        )->execute(['nickname' => $nickname ?: null, 'hidden' => $emailHidden ? 'true' : 'false', 'id' => $id]);
    }

    public function updateAvatar(int $id, string $avatar): void
    {
        $this->db->prepare('UPDATE users SET avatar = :avatar WHERE id = :id')
            ->execute(['avatar' => $avatar, 'id' => $id]);
    }

    public function search(string $query, int $excludeId): array
    {
        $like = '%' . $query . '%';
        $stmt = $this->db->prepare(
            "SELECT id, email, nickname, avatar, email_hidden FROM users
             WHERE id <> :exclude
               AND email_verified = TRUE
               AND (
                   nickname ILIKE :q
                   OR (email_hidden = FALSE AND email ILIKE :q)
               )
             LIMIT 20"
        );
        $stmt->execute(['exclude' => $excludeId, 'q' => $like]);
        return $stmt->fetchAll();
    }
}
