<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create group — Messenger</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-4" style="max-width:480px">
    <div class="d-flex align-items-center mb-3 gap-3">
        <a href="/chats" class="btn btn-outline-secondary btn-sm">&larr; Cancel</a>
        <h5 class="mb-0">Create group chat</h5>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="/groups/create">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                <div class="mb-3">
                    <label class="form-label">Group name</label>
                    <input type="text" name="name" class="form-control" required maxlength="100" autofocus>
                </div>
                <?php if (!empty($contacts)): ?>
                <div class="mb-3">
                    <label class="form-label">Add members from contacts</label>
                    <?php foreach ($contacts as $c): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="members[]"
                               value="<?= (int)$c['id'] ?>" id="m<?= (int)$c['id'] ?>">
                        <label class="form-check-label" for="m<?= (int)$c['id'] ?>">
                            <?= htmlspecialchars($c['nickname'] ?? (!$c['email_hidden'] ? $c['email'] : 'User'), ENT_QUOTES, 'UTF-8') ?>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted small">Add contacts first to include them in the group.</p>
                <?php endif; ?>
                <button type="submit" class="btn btn-dark w-100">Create group</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
