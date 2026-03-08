<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/db.php';

// Auto-migrate: ensure schema exists
ensure_schema();

session_start();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pw = $_POST['password'] ?? '';
    if (!empty(ADMIN_PASS_HASH) && password_verify($pw, ADMIN_PASS_HASH)) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        // CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        header('Location: ' . BASE_URL . '/admin');
        exit;
    }
    else {
        $error = 'Ungültiges Passwort.';
    }
}
?><!doctype html>
<html lang="de">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin-Anmeldung</title></head>
<body>
<h1>Admin Login</h1>
<?php if ($error): ?>
<p style="color:red"><?php echo htmlspecialchars($error); ?></p>
<?php
endif; ?>
<form method="post">
<label>Passwort: <input type="password" name="password" required></label>
<button type="submit">Anmelden</button>
</form>
</body>
</html>