<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class ProfileController extends Controller
{
    private User $users;

    public function __construct()
    {
        $this->users = new User();
    }

    public function show(): void
    {
        $this->authRequired();
        $user = $this->users->findById((int) $_SESSION['user_id']);
        $this->render('profile.show', ['user' => $user, 'csrf' => $this->csrfToken()]);
    }

    public function update(): void
    {
        $this->authRequired();
        $this->verifyCsrf();

        $nickname    = trim($_POST['nickname'] ?? '');
        $emailHidden = isset($_POST['email_hidden']);
        $userId      = (int) $_SESSION['user_id'];

        if ($nickname !== '') {
            $existing = $this->users->findByNickname($nickname);
            if ($existing && (int) $existing['id'] !== $userId) {
                $user = $this->users->findById($userId);
                $this->render('profile.show', [
                    'user'  => $user,
                    'error' => 'This nickname is already taken.',
                    'csrf'  => $this->csrfToken(),
                ]);
                return;
            }
        }

        $this->users->updateProfile($userId, $nickname, $emailHidden);
        $this->redirect('/profile');
    }

    public function uploadAvatar(): void
    {
        $this->authRequired();
        $this->verifyCsrf();

        $userId = (int) $_SESSION['user_id'];
        $file   = $_FILES['avatar'] ?? null;

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->json(['error' => 'Upload failed'], 400);
        }

        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $mime    = mime_content_type($file['tmp_name']);

        if (!in_array($mime, $allowed, true)) {
            $this->json(['error' => 'Invalid file type'], 400);
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            $this->json(['error' => 'File too large (max 2MB)'], 400);
        }

        $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
        $name = $userId . '_' . uniqid() . '.' . $ext;
        $dir  = ROOT_PATH . '/public/uploads/avatars/';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        move_uploaded_file($file['tmp_name'], $dir . $name);
        $this->users->updateAvatar($userId, '/uploads/avatars/' . $name);
        $this->json(['avatar' => '/uploads/avatars/' . $name]);
    }
}
