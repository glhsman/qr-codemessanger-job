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
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Anmeldung – QR Admin</title>
    <?php
    foreach (['ico', 'png', 'jpg', 'svg'] as $ext) {
        if (file_exists(__DIR__ . '/favicon/favicon.' . $ext)) {
            $mtime = filemtime(__DIR__ . '/favicon/favicon.' . $ext);
            $type = ($ext === 'ico') ? 'x-icon' : $ext;
            echo '<link rel="icon" type="image/' . $type . '" href="favicon/favicon.' . $ext . '?v=' . $mtime . '">';
            break;
        }
    }
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --bg-body: #f3f4f6;
            --text-main: #1f2937;
            --border: #e5e7eb;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--bg-body);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
        }
        .login-card {
            background: #fff;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .logo {
            font-size: 2rem;
            margin-bottom: 2rem;
            display: inline-block;
        }
        h1 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        p.subtitle {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 2rem;
        }
        form { text-align: left; }
        label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        input[type=password] {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 1rem;
            margin-bottom: 1.5rem;
            background: #f9fafb;
            transition: all 0.2s;
        }
        input:focus {
            outline: none;
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        button:hover { opacity: 0.9; }
        .error {
            background: #fef2f2;
            color: #dc2626;
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            border: 1px solid #fee2e2;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">📣</div>
        <h1>Willkommen zurück</h1>
        <p class="subtitle">Bitte melde dich am Admin-Bereich an.</p>
        
        <?php if ($error): ?>
            <div class="error">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="post">
            <label for="password">Passwort</label>
            <input type="password" name="password" id="password" required autofocus>
            <button type="submit">Anmelden</button>
        </form>
    </div>
</body>
</html>