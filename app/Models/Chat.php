<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Chat extends Model
{
    public function findOrCreate(int $a, int $b): int
    {
        [$u1, $u2] = $a < $b ? [$a, $b] : [$b, $a];

        $stmt = $this->db->prepare(
            'SELECT id FROM chats WHERE user1_id = :u1 AND user2_id = :u2'
        );
        $stmt->execute(['u1' => $u1, 'u2' => $u2]);
        $row = $stmt->fetchColumn();

        if ($row !== false) {
            return (int) $row;
        }

        $ins = $this->db->prepare(
            'INSERT INTO chats (user1_id, user2_id) VALUES (:u1, :u2) RETURNING id'
        );
        $ins->execute(['u1' => $u1, 'u2' => $u2]);
        return (int) $ins->fetchColumn();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM chats WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getChatsForUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.id,
                    u.id   AS partner_id,
                    u.nickname,
                    u.email,
                    u.avatar,
                    u.email_hidden,
                    (SELECT body FROM messages m WHERE m.chat_id = c.id ORDER BY m.created_at DESC LIMIT 1) AS last_message,
                    (SELECT created_at FROM messages m WHERE m.chat_id = c.id ORDER BY m.created_at DESC LIMIT 1) AS last_at
             FROM chats c
             JOIN users u ON u.id = CASE WHEN c.user1_id = :uid THEN c.user2_id ELSE c.user1_id END
             WHERE c.user1_id = :uid OR c.user2_id = :uid
             ORDER BY last_at DESC NULLS LAST"
        );
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function userBelongsToChat(int $userId, int $chatId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM chats WHERE id = :id AND (user1_id = :uid OR user2_id = :uid)'
        );
        $stmt->execute(['id' => $chatId, 'uid' => $userId]);
        return (bool) $stmt->fetchColumn();
    }
}
