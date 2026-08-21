<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile — Messenger</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
<div class="card shadow-sm" style="width:100%;max-width:480px">
    <div class="card-body p-4">
        <h4 class="mb-4">Profile</h4>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="d-flex align-items-center gap-3 mb-4">
            <img src="<?= htmlspecialchars($user['avatar'] ?? '/img/default-avatar.svg', ENT_QUOTES, 'UTF-8') ?>"
                 class="rounded-circle" width="72" height="72" style="object-fit:cover" alt="Avatar">
            <form id="avatar-form" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                <label class="btn btn-outline-secondary btn-sm">
                    Change photo
                    <input type="file" name="avatar" accept="image/*" id="avatar-input" hidden>
                </label>
            </form>
        </div>

        <form method="POST" action="/profile">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
            <div class="mb-3">
                <label class="form-label">Nickname</label>
                <input type="text" name="nickname" class="form-control" maxlength="50"
                       value="<?= htmlspecialchars($user['nickname'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" name="email_hidden" id="emailHidden"
                       <?= $user['email_hidden'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="emailHidden">Hide email from search</label>
            </div>
            <button type="submit" class="btn btn-dark w-100">Save</button>
        </form>
        <div class="mt-3 text-center">
            <a href="/chats" class="small">&larr; Back to chats</a>
        </div>
    </div>
</div>
<script>
document.getElementById('avatar-input').addEventListener('change', function () {
    const form = document.getElementById('avatar-form');
    const data = new FormData(form);
    data.append('avatar', this.files[0]);
    fetch('/profile/avatar', { method: 'POST', body: data })
        .then(r => r.json())
        .then(d => { if (d.avatar) location.reload(); });
});
</script>
</body>
</html>
