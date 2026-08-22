<?php if (!empty($_SESSION['flash'])): ?>
<div class="flash flash--<?= htmlspecialchars($_SESSION['flash']['type'], ENT_QUOTES, 'UTF-8') ?>">
    <?= htmlspecialchars($_SESSION['flash']['msg'], ENT_QUOTES, 'UTF-8') ?>
</div>
<?php unset($_SESSION['flash']); endif; ?>
