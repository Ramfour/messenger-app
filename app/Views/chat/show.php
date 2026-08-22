<?php
ob_start();
$currentUserId = (int) $_SESSION['user_id'];
?>
<div class="chat-header border-bottom bg-white px-3 py-2 d-flex align-items-center gap-2">
    <img src="<?= htmlspecialchars($partner['avatar'] ?? '/img/default-avatar.svg', ENT_QUOTES, 'UTF-8') ?>"
         class="rounded-circle" width="36" height="36" style="object-fit:cover" alt="">
    <span class="fw-semibold">
        <?= htmlspecialchars($partner['nickname'] ?? (!$partner['email_hidden'] ? $partner['email'] : 'User'), ENT_QUOTES, 'UTF-8') ?>
    </span>
</div>

<div class="messages flex-grow-1 overflow-auto p-3 d-flex flex-column gap-2"
     id="messages" data-chat-id="<?= (int)$chat['id'] ?>">
    <?php foreach ($messages as $m): ?>
        <?php require VIEW_PATH . '/partials/message.php'; ?>
    <?php endforeach; ?>
</div>

<form class="message-form border-top bg-white px-3 py-2 d-flex align-items-end gap-2" id="message-form">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="chat_id" value="<?= (int)$chat['id'] ?>">
    <textarea name="body" id="msg-input" class="form-control rounded-pill"
              placeholder="Type your message here" rows="1" style="resize:none;max-height:120px"></textarea>
    <button type="submit" class="btn btn-dark rounded-circle flex-shrink-0"
            style="width:38px;height:38px;padding:0">&#9658;</button>
</form>
<?php
$chatContent = ob_get_clean();
require VIEW_PATH . '/chat/index.php';
