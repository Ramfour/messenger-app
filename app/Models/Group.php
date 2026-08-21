<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Group extends Model
{
    public function create(string $name, int $creatorId): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO groups (name, creator_id) VALUES (:name, :cid) RETURNING id'
        );
        $stmt->execute(['name' => $name, 'cid' => $creatorId]);
        $id = (int) $stmt->fetchColumn();
        $this->addMember($id, $creatorId);
        return $id;
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM groups WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getGroupsForUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT g.id, g.name, g.creator_id,
                    (SELECT body FROM messages m WHERE m.group_id = g.id ORDER BY m.created_at DESC LIMIT 1) AS last_message,
                    (SELECT created_at FROM messages m WHERE m.group_id = g.id ORDER BY m.created_at DESC LIMIT 1) AS last_at
             FROM groups g
             JOIN group_members gm ON gm.group_id = g.id
             WHERE gm.user_id = :uid
             ORDER BY last_at DESC NULLS LAST"
        );
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function isMember(int $groupId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM group_members WHERE group_id = :gid AND user_id = :uid'
        );
        $stmt->execute(['gid' => $groupId, 'uid' => $userId]);
        return (bool) $stmt->fetchColumn();
    }

    public function addMember(int $groupId, int $userId): void
    {
        $this->db->prepare(
            'INSERT INTO group_members (group_id, user_id) VALUES (:gid, :uid) ON CONFLICT DO NOTHING'
        )->execute(['gid' => $groupId, 'uid' => $userId]);
    }

    public function getMembers(int $groupId): array
    {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.nickname, u.email, u.avatar, u.email_hidden
             FROM group_members gm
             JOIN users u ON u.id = gm.user_id
             WHERE gm.group_id = :gid"
        );
        $stmt->execute(['gid' => $groupId]);
        return $stmt->fetchAll();
    }
}
