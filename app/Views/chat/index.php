<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messenger</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="messenger-body">

<div class="messenger-layout">

    <!-- Sidebar -->
    <aside class="sidebar d-flex flex-column border-end bg-white">
        <div class="sidebar-top px-3 py-2 border-bottom d-flex align-items-center justify-content-between">
            <span class="fw-semibold small">
                <?= htmlspecialchars($me['nickname'] ?? $me['email'], ENT_QUOTES, 'UTF-8') ?>
            </span>
            <a href="/contacts" class="text-secondary" title="Contacts">&#128100;</a>
        </div>

        <div class="px-2 py-2 border-bottom position-relative">
            <input type="text" id="user-search" class="form-control form-control-sm rounded-pill"
                   placeholder="Search users..." autocomplete="off">
            <ul id="search-results" class="search-dropdown list-unstyled d-none"></ul>
        </div>

        <div class="px-3 pt-2 pb-1">
            <span class="text-uppercase text-muted" style="font-size:10px;letter-spacing:.06em">Direct</span>
        </div>
        <ul class="list-unstyled overflow-auto mb-0 flex-grow-1">
            <?php foreach ($chats as $c): ?>
            <li>
                <a href="/chats/<?= (int)$c['id'] ?>"
                   class="d-flex align-items-center gap-2 px-3 py-2 text-decoration-none sidebar-item
                          <?= isset($chat) && (int)$chat['id'] === (int)$c['id'] ? 'active' : '' ?>">
                    <img src="<?= htmlspecialchars($c['avatar'] ?? '/img/default-avatar.svg', ENT_QUOTES, 'UTF-8') ?>"
                         class="rounded-circle flex-shrink-0" width="34" height="34" style="object-fit:cover" alt="">
                    <div class="overflow-hidden">
                        <div class="small fw-medium text-truncate">
                            <?= htmlspecialchars($c['nickname'] ?? (!$c['email_hidden'] ? $c['email'] : 'User'), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <?php if ($c['last_message']): ?>
                        <div class="text-muted text-truncate" style="font-size:11px">
                            <?= htmlspecialchars(mb_strimwidth($c['last_message'], 0, 35, '…'), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>

        <div class="px-3 pt-2 pb-1 border-top">
            <span class="text-uppercase text-muted" style="font-size:10px;letter-spacing:.06em">Groups</span>
        </div>
        <ul class="list-unstyled overflow-auto mb-0">
            <?php foreach ($groups as $g): ?>
            <li>
                <a href="/groups/<?= (int)$g['id'] ?>"
                   class="d-flex align-items-center gap-2 px-3 py-2 text-decoration-none sidebar-item
                          <?= isset($group) && (int)$group['id'] === (int)$g['id'] ? 'active' : '' ?>">
                    <img src="/img/group-avatar.svg" class="rounded-circle flex-shrink-0"
                         width="34" height="34" alt="">
                    <div class="overflow-hidden">
                        <div class="small fw-medium text-truncate">
                            <?= htmlspecialchars($g['name'], ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <?php if ($g['last_message']): ?>
                        <div class="text-muted text-truncate" style="font-size:11px">
                            <?= htmlspecialchars(mb_strimwidth($g['last_message'], 0, 35, '…'), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </aside>

    <!-- Chat area -->
    <main class="chat-main d-flex flex-column">
        <?php echo $chatContent ?? '<div class="flex-grow-1 d-flex align-items-center justify-content-center text-muted">Select a chat to start messaging</div>'; ?>
    </main>

    <!-- Right nav -->
    <nav class="right-nav d-flex flex-column border-start bg-white p-3 gap-3">
        <a href="/profile" class="text-secondary small">Profile</a>
        <a href="/groups/create" class="text-secondary small">Create group</a>
        <a href="/logout" class="text-secondary small">Log out</a>
    </nav>

</div>

<script>
const CSRF = <?= json_encode($csrf) ?>;
const ME_ID = <?= (int)$_SESSION['user_id'] ?>;
</script>
<script src="/js/app.js"></script>
</body>
</html>
