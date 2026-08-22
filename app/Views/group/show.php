<?php
ob_start();
$currentUserId = (int) $_SESSION['user_id'];
?>
<div class="chat-header border-bottom bg-white px-3 py-2 d-flex align-items-center gap-2">
    <img src="/img/group-avatar.svg" class="rounded-circle" width="36" height="36" alt="">
    <span class="fw-semibold flex-grow-1"><?= htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8') ?></span>
    <?php if ((int)$group['creator_id'] === $currentUserId): ?>
    <button class="btn btn-outline-secondary btn-sm" id="add-member-btn">+ Добавить участника</button>
    <?php endif; ?>
</div>

<div class="messages flex-grow-1 overflow-auto p-3 d-flex flex-column gap-2"
     id="messages" data-group-id="<?= (int)$group['id'] ?>">
    <?php foreach ($messages as $m): ?>
        <?php require VIEW_PATH . '/partials/message.php'; ?>
    <?php endforeach; ?>
</div>

<form class="message-form border-top bg-white px-3 py-2 d-flex align-items-end gap-2" id="message-form">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="group_id" value="<?= (int)$group['id'] ?>">
    <textarea name="body" id="msg-input" class="form-control rounded-pill"
              placeholder="Введите сообщение" rows="1" style="resize:none;max-height:120px"></textarea>
    <button type="submit" class="btn btn-dark rounded-circle flex-shrink-0"
            style="width:38px;height:38px;padding:0">&#9658;</button>
</form>

<!-- Add member modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Добавить участника</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="member-search" class="form-control form-control-sm mb-2"
                       placeholder="Поиск контактов...">
                <ul id="member-results" class="list-group list-group-flush"></ul>
            </div>
        </div>
    </div>
</div>
<?php
$chatContent = ob_get_clean();
require VIEW_PATH . '/chat/index.php';
