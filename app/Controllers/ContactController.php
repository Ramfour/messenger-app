<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Contact;
use App\Models\User;

class ContactController extends Controller
{
    private Contact $contacts;
    private User    $users;

    public function __construct()
    {
        $this->contacts = new Contact();
        $this->users    = new User();
    }

    public function index(): void
    {
        $this->authRequired();
        $userId   = (int) $_SESSION['user_id'];
        $contacts = $this->contacts->getContacts($userId);
        $this->render('contacts.index', ['contacts' => $contacts, 'csrf' => $this->csrfToken()]);
    }

    public function search(): void
    {
        $this->authRequired();
        $q      = trim($_GET['q'] ?? '');
        $userId = (int) $_SESSION['user_id'];

        if (strlen($q) < 2) {
            $this->json([]);
        }

        $results = $this->users->search($q, $userId);
        // mask hidden emails
        foreach ($results as &$r) {
            if ($r['email_hidden']) {
                $r['email'] = '';
            }
        }
        $this->json($results);
    }

    public function add(): void
    {
        $this->authRequired();
        $this->verifyCsrf();

        $userId    = (int) $_SESSION['user_id'];
        $contactId = (int) ($_POST['contact_id'] ?? 0);

        if ($contactId === $userId || !$this->users->findById($contactId)) {
            $this->json(['error' => 'Invalid user'], 400);
        }

        $this->contacts->add($userId, $contactId);
        $this->json(['ok' => true]);
    }

    public function remove(): void
    {
        $this->authRequired();
        $this->verifyCsrf();

        $userId    = (int) $_SESSION['user_id'];
        $contactId = (int) ($_POST['contact_id'] ?? 0);
        $this->contacts->remove($userId, $contactId);
        $this->json(['ok' => true]);
    }
}
