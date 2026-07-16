<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/db.php';

// Auto-migrate: ensure schema exists
ensure_schema();

session_start();
$error = '';
$setupMode = empty(ADMIN_PASS_HASH);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($setupMode) {
        // Ersten Passwort setzen
        $pw = $_POST['password'] ?? '';
        $pwConfirm = $_POST['password_confirm'] ?? '';

        if (empty($pw)) {
            $error = 'Passwort darf nicht leer sein.';
        } elseif (strlen($pw) < 8) {
            $error = 'Passwort muss mindestens 8 Zeichen lang sein.';
        } elseif ($pw !== $pwConfirm) {
            $error = 'Passwörter stimmen nicht überein.';
        } else {
            $creds = json_decode(file_get_contents(SECRETS_PATH), true);
            $creds['ADMIN_PASS_HASH'] = password_hash($pw, PASSWORD_DEFAULT);
            file_put_contents(SECRETS_PATH, json_encode($creds, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            session_regenerate_id(true);
            $_SESSION['admin'] = true;
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }
    } else {
        // Normale Anmeldung
        $pw = $_POST['password'] ?? '';
        if (!empty(ADMIN_PASS_HASH) && password_verify($pw, ADMIN_PASS_HASH)) {
            session_regenerate_id(true);
            $_SESSION['admin'] = true;
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
            header('Location: ' . BASE_URL . '/admin');
            exit;
        } else {
            $error = 'Ungültiges Passwort.';
        }
    }
}
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= $setupMode ? 'Erstes Passwort setzen' : 'Anmeldung' ?> – QR Admin</title>
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
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
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
        .pw-field-wrapper { position: relative; margin-bottom: 1.5rem; }
        .pw-field-wrapper input {
            width: 100%;
            padding: 0.75rem 2.5rem 0.75rem 0.75rem;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 1rem;
            background: #f9fafb;
            transition: all 0.2s;
        }
        .pw-field-wrapper input:focus {
            outline: none;
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        .pw-toggle {
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #6b7280;
            padding: 0.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
            z-index: 1;
        }
        .pw-toggle:hover { color: var(--primary); }
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
        <?php if ($setupMode): ?>
            <h1>Erste Einrichtung</h1>
            <p class="subtitle">Bitte setze ein Passwort für den Admin-Bereich (mind. 8 Zeichen).</p>
        <?php else: ?>
            <h1>Willkommen zurück</h1>
            <p class="subtitle">Bitte melde dich am Admin-Bereich an.</p>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" id="login-form">
            <?php if ($setupMode): ?>
                <label for="password">Passwort</label>
                <div class="pw-field-wrapper">
                    <input type="password" name="password" id="password" required minlength="8" autofocus>
                    <button type="button" class="pw-toggle" onclick="togglePassword('password', this)" aria-label="Passwort anzeigen"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
                </div>

                <label for="password_confirm">Passwort bestätigen</label>
                <div class="pw-field-wrapper">
                    <input type="password" name="password_confirm" id="password_confirm" required minlength="8">
                    <button type="button" class="pw-toggle" onclick="togglePassword('password_confirm', this)" aria-label="Passwort anzeigen"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
                </div>
            <?php else: ?>
                <label for="password">Passwort</label>
                <div class="pw-field-wrapper">
                    <input type="password" name="password" id="password" required autofocus>
                    <button type="button" class="pw-toggle" onclick="togglePassword('password', this)" aria-label="Passwort anzeigen"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
                </div>
            <?php endif; ?>
            <button type="submit"><?= $setupMode ? 'Passwort setzen' : 'Anmelden' ?></button>
        </form>
    </div>

    <script>
    function togglePassword(inputId, btn) {
        var input = document.getElementById(inputId);
        if (input.type === 'password') {
            input.type = 'text';
            btn.classList.add('visible');
        } else {
            input.type = 'password';
            btn.classList.remove('visible');
        }
    }
    </script>
</body>
</html>
