<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Check your email — Messenger</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
<div class="card shadow-sm" style="width:100%;max-width:400px">
    <div class="card-body p-4 text-center">
        <h4 class="mb-3">Almost there!</h4>
        <p>We sent a confirmation link to<br><strong><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></strong></p>
        <p class="text-muted small">Check your inbox and click the link to activate your account.</p>
    </div>
</div>
</body>
</html>
