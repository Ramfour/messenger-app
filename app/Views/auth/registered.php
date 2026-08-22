<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Подтвердите email — Мессенджер</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
<div class="card shadow-sm" style="width:100%;max-width:400px">
    <div class="card-body p-4 text-center">
        <h4 class="mb-3">Почти готово!</h4>
        <p>Мы отправили ссылку для подтверждения на<br><strong><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></strong></p>
        <p class="text-muted small">Проверьте почту и перейдите по ссылке для активации аккаунта.</p>
    </div>
</div>
</body>
</html>
