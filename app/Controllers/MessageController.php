<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Chat;
use App\Models\Group;
use App\Models\Message;

class MessageController extends Controller
{
    private Message $messages;
    private Chat    $chats;
    private Group   $groups;

    public function __construct()
    {
        $this->messages = new Message();
        $this->chats    = new Chat();
        $this->groups   = new Group();
    }

    public function send(): void
    {
        $this->authRequired();
        $this->verifyCsrf();

        $userId  = (int) $_SESSION['user_id'];
        $body    = trim($_POST['body'] ?? '');
        $chatId  = isset($_POST['chat_id'])  ? (int) $_POST['chat_id']  : null;
        $groupId = isset($_POST['group_id']) ? (int) $_POST['group_id'] : null;

        if ($body === '') {
            $this->json(['error' => 'Empty message'], 400);
        }

        if ($chatId !== null) {
            if (!$this->chats->userBelongsToChat($userId, $chatId)) {
                $this->json(['error' => 'Forbidden'], 403);
            }
            $msg = $this->messages->sendToChat($userId, $chatId, htmlspecialchars($body, ENT_QUOTES, 'UTF-8'));
        } elseif ($groupId !== null) {
            if (!$this->groups->isMember($groupId, $userId)) {
                $this->json(['error' => 'Forbidden'], 403);
            }
            $msg = $this->messages->sendToGroup($userId, $groupId, htmlspecialchars($body, ENT_QUOTES, 'UTF-8'));
        } else {
            $this->json(['error' => 'No target'], 400);
        }

        $this->json(['ok' => true, 'id' => $msg['id'], 'created_at' => $msg['created_at']]);
    }

    public function edit(): void
    {
        $this->authRequired();
        $this->verifyCsrf();

        $userId = (int) $_SESSION['user_id'];
        $id     = (int) ($_POST['id'] ?? 0);
        $body   = trim($_POST['body'] ?? '');

        if ($body === '') {
            $this->json(['error' => 'Empty message'], 400);
        }

        $ok = $this->messages->edit($id, $userId, htmlspecialchars($body, ENT_QUOTES, 'UTF-8'));
        $this->json($ok ? ['ok' => true] : ['error' => 'Not found or forbidden'], $ok ? 200 : 403);
    }

    public function delete(): void
    {
        $this->authRequired();
        $this->verifyCsrf();

        $userId = (int) $_SESSION['user_id'];
        $id     = (int) ($_POST['id'] ?? 0);
        $ok     = $this->messages->delete($id, $userId);
        $this->json($ok ? ['ok' => true] : ['error' => 'Not found or forbidden'], $ok ? 200 : 403);
    }

    /**
     * Long Polling endpoint.
     * Client sends last_id; server waits up to 25s for new messages.
     */
    public function poll(): void
    {
        $this->authRequired();

        $userId  = (int) $_SESSION['user_id'];
        $lastId  = (int) ($_GET['last_id']  ?? 0);
        $chatId  = isset($_GET['chat_id'])  ? (int) $_GET['chat_id']  : null;
        $groupId = isset($_GET['group_id']) ? (int) $_GET['group_id'] : null;

        if ($chatId !== null && !$this->chats->userBelongsToChat($userId, $chatId)) {
            $this->json(['error' => 'Forbidden'], 403);
        }
        if ($groupId !== null && !$this->groups->isMember($groupId, $userId)) {
            $this->json(['error' => 'Forbidden'], 403);
        }

        $timeout = 25;
        $start   = time();

        // Close session so other requests aren't blocked during wait
        session_write_close();

        while (true) {
            if ($chatId !== null) {
                $rows = $this->messages->getByChatSince($chatId, $lastId);
            } else {
                $rows = $this->messages->getByGroupSince($groupId, $lastId);
            }

            if (!empty($rows)) {
                $this->json(['messages' => $rows]);
            }

            if ((time() - $start) >= $timeout) {
                $this->json(['messages' => []]);
            }

            sleep(1);
        }
    }
}
