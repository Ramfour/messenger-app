<?php
$currentUserId = $currentUserId ?? (int) $_SESSION['user_id'];
$msgId         = (int) $m['id'];
$isMine        = (int) $m['sender_id'] === $currentUserId;
$senderName    = htmlspecialchars($m['nickname'] ?? $m['email'], ENT_QUOTES, 'UTF-8');
$body          = $m['body'];
$time          = date('d.m.Y H:i', strtotime($m['created_at']));
?>
<div class="message d-flex <?= $isMine ? 'justify-content-end' : 'justify-content-start' ?>"
     data-id="<?= $msgId ?>">
    <div class="message-bubble <?= $isMine ? 'bubble-mine' : 'bubble-theirs' ?>">
        <div class="message-meta d-flex gap-2 mb-1">
            <span class="fw-semibold" style="font-size:11px;color:#666"><?= $senderName ?></span>
            <span class="text-muted" style="font-size:11px"><?= $time ?></span>
            <?php if ($m['edited']): ?>
            <span class="text-muted fst-italic" style="font-size:11px">(edited)</span>
            <?php endif; ?>
        </div>
        <div class="message-body" id="msg-body-<?= $msgId ?>"><?= $body ?></div>
    </div>
</div>
