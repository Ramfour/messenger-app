<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Contact;
use App\Models\Group;
use App\Models\Message;
use App\Models\User;
use App\Models\Chat;

class GroupController extends Controller
{
    private Group   $groups;
    private Contact $contacts;
    private Message $messages;
    private User    $users;
    private Chat    $chats;

    public function __construct()
    {
        $this->groups   = new Group();
        $this->contacts = new Contact();
        $this->messages = new Message();
        $this->users    = new User();
        $this->chats    = new Chat();
    }

    public function showCreate(): void
    {
        $this->authRequired();
        $userId   = (int) $_SESSION['user_id'];
        $contacts = $this->contacts->getContacts($userId);
        $this->render('group.create', ['contacts' => $contacts, 'csrf' => $this->csrfToken()]);
    }

    public function create(): void
    {
        $this->authRequired();
        $this->verifyCsrf();

        $userId  = (int) $_SESSION['user_id'];
        $name    = trim($_POST['name'] ?? '');
        $members = $_POST['members'] ?? [];

        if ($name === '') {
            $this->json(['error' => 'Group name required'], 400);
        }

        $groupId = $this->groups->create($name, $userId);

        foreach ($members as $memberId) {
            $memberId = (int) $memberId;
            if ($memberId !== $userId && $this->contacts->exists($userId, $memberId)) {
                $this->groups->addMember($groupId, $memberId);
            }
        }

        $this->redirect('/groups/' . $groupId);
    }

    public function show(string $id): void
    {
        $this->authRequired();
        $userId  = (int) $_SESSION['user_id'];
        $groupId = (int) $id;

        if (!$this->groups->isMember($groupId, $userId)) {
            $this->redirect('/chats');
        }

        $group    = $this->groups->findById($groupId);
        $messages = $this->messages->getByGroupSince($groupId);
        $members  = $this->groups->getMembers($groupId);
        $chats    = $this->chats->getChatsForUser($userId);
        $groups   = $this->groups->getGroupsForUser($userId);
        $me       = $this->users->findById($userId);

        $this->render('group.show', [
            'group'    => $group,
            'messages' => $messages,
            'members'  => $members,
            'chats'    => $chats,
            'groups'   => $groups,
            'me'       => $me,
            'csrf'     => $this->csrfToken(),
        ]);
    }

    public function addMember(string $id): void
    {
        $this->authRequired();
        $this->verifyCsrf();

        $userId   = (int) $_SESSION['user_id'];
        $groupId  = (int) $id;
        $newMember = (int) ($_POST['user_id'] ?? 0);

        $group = $this->groups->findById($groupId);
        if (!$group || (int) $group['creator_id'] !== $userId) {
            $this->json(['error' => 'Forbidden'], 403);
        }

        if (!$this->contacts->exists($userId, $newMember)) {
            $this->json(['error' => 'User not in your contacts'], 403);
        }

        $this->groups->addMember($groupId, $newMember);
        $this->json(['ok' => true]);
    }
}
