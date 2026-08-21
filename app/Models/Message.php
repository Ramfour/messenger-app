<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Message extends Model
{
    public function getByChatSince(int $chatId, int $lastId = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT m.id, m.sender_id, m.body, m.edited, m.created_at, m.updated_at,
                    u.nickname, u.email, u.avatar
             FROM messages m
             JOIN users u ON u.id = m.sender_id
             WHERE m.chat_id = :cid AND m.id > :last
             ORDER BY m.created_at ASC"
        );
        $stmt->execute(['cid' => $chatId, 'last' => $lastId]);
        return $stmt->fetchAll();
    }

    public function getByGroupSince(int $groupId, int $lastId = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT m.id, m.sender_id, m.body, m.edited, m.created_at, m.updated_at,
                    u.nickname, u.email, u.avatar
             FROM messages m
             JOIN users u ON u.id = m.sender_id
             WHERE m.group_id = :gid AND m.id > :last
             ORDER BY m.created_at ASC"
        );
        $stmt->execute(['gid' => $groupId, 'last' => $lastId]);
        return $stmt->fetchAll();
    }

    public function sendToChat(int $senderId, int $chatId, string $body): array
    {
        $stmt = $this->db->prepare(
            "INSERT INTO messages (sender_id, chat_id, body)
             VALUES (:sid, :cid, :body) RETURNING id, created_at"
        );
        $stmt->execute(['sid' => $senderId, 'cid' => $chatId, 'body' => $body]);
        return $stmt->fetch();
    }

    public function sendToGroup(int $senderId, int $groupId, string $body): array
    {
        $stmt = $this->db->prepare(
            "INSERT INTO messages (sender_id, group_id, body)
             VALUES (:sid, :gid, :body) RETURNING id, created_at"
        );
        $stmt->execute(['sid' => $senderId, 'gid' => $groupId, 'body' => $body]);
        return $stmt->fetch();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM messages WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function edit(int $id, int $senderId, string $body): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE messages SET body = :body, edited = TRUE, updated_at = NOW()
             WHERE id = :id AND sender_id = :sid"
        );
        $stmt->execute(['body' => $body, 'id' => $id, 'sid' => $senderId]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id, int $senderId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM messages WHERE id = :id AND sender_id = :sid'
        );
        $stmt->execute(['id' => $id, 'sid' => $senderId]);
        return $stmt->rowCount() > 0;
    }
}
