<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contacts — Messenger</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-4" style="max-width:540px">
    <div class="d-flex align-items-center mb-3 gap-3">
        <a href="/chats" class="btn btn-outline-secondary btn-sm">&larr; Chats</a>
        <h5 class="mb-0">Contacts</h5>
    </div>
    <ul class="list-group">
        <?php foreach ($contacts as $c): ?>
        <li class="list-group-item d-flex align-items-center gap-3">
            <img src="<?= htmlspecialchars($c['avatar'] ?? '/img/default-avatar.svg', ENT_QUOTES, 'UTF-8') ?>"
                 class="rounded-circle" width="36" height="36" style="object-fit:cover" alt="">
            <span class="flex-grow-1">
                <?= htmlspecialchars($c['nickname'] ?? (!$c['email_hidden'] ? $c['email'] : 'User'), ENT_QUOTES, 'UTF-8') ?>
            </span>
            <form method="POST" action="/contacts/remove">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="contact_id" value="<?= (int)$c['id'] ?>">
                <button type="submit" class="btn btn-outline-danger btn-sm">Remove</button>
            </form>
        </li>
        <?php endforeach; ?>
        <?php if (empty($contacts)): ?>
        <li class="list-group-item text-muted">No contacts yet. Search for users from the chat screen.</li>
        <?php endif; ?>
    </ul>
</div>
</body>
</html>
