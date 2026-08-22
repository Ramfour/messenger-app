<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Chat;
use App\Models\Contact;
use App\Models\Group;
use App\Models\Message;
use App\Models\User;

class ChatController extends Controller
{
    private Chat    $chats;
    private Group   $groups;
    private Message $messages;
    private Contact $contacts;
    private User    $users;

    public function __construct()
    {
        $this->chats    = new Chat();
        $this->groups   = new Group();
        $this->messages = new Message();
        $this->contacts = new Contact();
        $this->users    = new User();
    }

    public function index(): void
    {
        $this->authRequired();
        $userId = (int) $_SESSION['user_id'];
        $chats  = $this->chats->getChatsForUser($userId);
        $groups = $this->groups->getGroupsForUser($userId);
        $me     = $this->users->findById($userId);
        $this->render('chat.index', [
            'chats'  => $chats,
            'groups' => $groups,
            'me'     => $me,
            'csrf'   => $this->csrfToken(),
        ]);
    }

    public function show(string $id): void
    {
        $this->authRequired();
        $userId = (int) $_SESSION['user_id'];
        $chatId = (int) $id;

        if (!$this->chats->userBelongsToChat($userId, $chatId)) {
            $this->redirect('/chats');
        }

        $chat     = $this->chats->findById($chatId);
        $messages = $this->messages->getByChatSince($chatId);
        $chats    = $this->chats->getChatsForUser($userId);
        $groups   = $this->groups->getGroupsForUser($userId);
        $me       = $this->users->findById($userId);

        $partnerId = (int) ($chat['user1_id'] === $userId ? $chat['user2_id'] : $chat['user1_id']);
        $partner   = $this->users->findById($partnerId);

        $this->render('chat.show', [
            'chat'     => $chat,
            'messages' => $messages,
            'partner'  => $partner,
            'chats'    => $chats,
            'groups'   => $groups,
            'me'       => $me,
            'csrf'     => $this->csrfToken(),
        ]);
    }

    public function create(): void
    {
        $this->authRequired();
        $this->verifyCsrf();

        $userId    = (int) $_SESSION['user_id'];
        $contactId = (int) ($_POST['contact_id'] ?? 0);

        if (!$this->contacts->exists($userId, $contactId)) {
            $this->json(['error' => 'Not in contacts'], 403);
        }

        $chatId = $this->chats->findOrCreate($userId, $contactId);
        $this->json(['chat_id' => $chatId]);
    }
}
