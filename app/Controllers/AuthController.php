<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthController extends Controller
{
    private User $users;

    public function __construct()
    {
        $this->users = new User();
    }

    public function showRegister(): void
    {
        $this->guestOnly();
        $this->render('auth.register', ['csrf' => $this->csrfToken()]);
    }

    public function register(): void
    {
        $this->guestOnly();
        $this->verifyCsrf();

        $email    = trim(strtolower($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->render('auth.register', ['error' => 'Invalid email address.', 'csrf' => $this->csrfToken()]);
            return;
        }

        if (strlen($password) < 6) {
            $this->render('auth.register', ['error' => 'Password must be at least 6 characters.', 'csrf' => $this->csrfToken()]);
            return;
        }

        if ($this->users->findByEmail($email)) {
            $this->render('auth.register', ['error' => 'Email already registered.', 'csrf' => $this->csrfToken()]);
            return;
        }

        $hash  = password_hash($password, PASSWORD_BCRYPT);
        $token = bin2hex(random_bytes(32));
        $id    = $this->users->create($email, $hash, $token);

        $this->sendVerificationEmail($email, $token);

        $this->render('auth.registered', ['email' => $email]);
    }

    public function verify(string $token): void
    {
        $user = $this->users->findByVerifyToken($token);

        if (!$user) {
            $this->render('auth.verify_fail');
            return;
        }

        $this->users->verify((int) $user['id']);
        $this->render('auth.verify_ok');
    }

    public function showLogin(): void
    {
        $this->guestOnly();
        $this->render('auth.login', ['csrf' => $this->csrfToken()]);
    }

    public function login(): void
    {
        $this->guestOnly();
        $this->verifyCsrf();

        $email    = trim(strtolower($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $user     = $this->users->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->render('auth.login', ['error' => 'Invalid email or password.', 'csrf' => $this->csrfToken()]);
            return;
        }

        if (!$user['email_verified']) {
            $this->render('auth.login', ['error' => 'Please verify your email first.', 'csrf' => $this->csrfToken()]);
            return;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $this->redirect('/chats');
    }

    public function logout(): void
    {
        session_destroy();
        $this->redirect('/login');
    }

    private function sendVerificationEmail(string $to, string $token): void
    {
        $url = ($_ENV['APP_URL'] ?? 'http://localhost') . '/verify/' . $token;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST']     ?? '';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USERNAME'] ?? '';
            $mail->Password   = $_ENV['MAIL_PASSWORD'] ?? '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int) ($_ENV['MAIL_PORT'] ?? 587);
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($_ENV['MAIL_FROM'] ?? '', $_ENV['MAIL_FROM_NAME'] ?? 'Messenger');
            $mail->addAddress($to);
            $mail->Subject = 'Confirm your registration';
            $mail->Body    = "Click the link to verify your account:\n\n{$url}";
            $mail->send();
        } catch (Exception $e) {
            error_log('Mailer error: ' . $e->getMessage());
        }
    }
}
