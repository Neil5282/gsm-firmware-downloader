<?php
session_start();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/db.php';
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    try {
        $s = db()->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
        $s->execute([$email]);
        $u = $s->fetch();
        if ($u && password_verify($password, $u['password_hash'])) {
            $_SESSION['user'] = [
                'user_id' => $u['user_id'],
                'name' => $u['name'],
                'email' => $u['email'],
                'role' => $u['role']
            ];
            header('Location: dashboard.php');
            exit;
        }
        $error = 'Invalid email or password.';
    } catch (Throwable $e) {
        $error = 'Database is not configured yet. Import database.sql and run setup.php.';
    }
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>GSM Firmware Downloader</title><link rel="stylesheet" href="assets/style.css"></head>
<body class="auth"><div class="auth-card">
<div class="brand"><span class="brand-mark">GF</span><div><b>GSM Firmware Downloader</b><small>Firmware Management & Download System</small></div></div>
<h1>Welcome back</h1><p class="muted">Sign in to access the OTA downloader.</p>
<?php if ($error): ?><div class="alert"><?=htmlspecialchars($error)?></div><?php endif; ?>
<form method="post"><label>Email</label><input name="email" type="email" required placeholder="you@example.com">
<label>Password</label><input name="password" type="password" required placeholder="••••••••">
<button class="primary full">Sign in</button></form>
<div class="demo-note">Demo accounts: user@gsm.local / user123 &nbsp; • &nbsp; admin@gsm.local / admin123</div>
</div></body></html>
