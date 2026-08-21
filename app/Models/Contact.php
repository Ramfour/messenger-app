<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Contact extends Model
{
    public function getContacts(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.email, u.nickname, u.avatar, u.email_hidden
             FROM contacts c
             JOIN users u ON u.id = c.contact_id
             WHERE c.user_id = :uid
             ORDER BY u.nickname, u.email"
        );
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function exists(int $userId, int $contactId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM contacts WHERE user_id = :uid AND contact_id = :cid'
        );
        $stmt->execute(['uid' => $userId, 'cid' => $contactId]);
        return (bool) $stmt->fetchColumn();
    }

    public function add(int $userId, int $contactId): void
    {
        $this->db->prepare(
            'INSERT INTO contacts (user_id, contact_id) VALUES (:uid, :cid) ON CONFLICT DO NOTHING'
        )->execute(['uid' => $userId, 'cid' => $contactId]);
    }

    public function remove(int $userId, int $contactId): void
    {
        $this->db->prepare(
            'DELETE FROM contacts WHERE user_id = :uid AND contact_id = :cid'
        )->execute(['uid' => $userId, 'cid' => $contactId]);
    }
}
