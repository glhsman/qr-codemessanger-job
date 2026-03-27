<?php
// favicon_upload.php
// Ermöglicht das Hochladen eines Fav-Icons (nur .ico, .png, .jpg, .svg)

session_start();
if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
    http_response_code(403);
    echo "Nicht autorisiert.";
    exit;
}

$uploadDir = __DIR__ . '/favicon/';
$allowedTypes = ['image/x-icon' => 'ico', 'image/vnd.microsoft.icon' => 'ico', 'image/png' => 'png', 'image/jpeg' => 'jpg', 'image/svg+xml' => 'svg'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['favicon'])) {
    $file = $_FILES['favicon'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Fehler beim Upload.';
    } elseif (!array_key_exists($file['type'], $allowedTypes)) {
        $error = 'Ungültiger Dateityp.';
    } else {
        $ext = $allowedTypes[$file['type']];
        $target = $uploadDir . 'favicon.' . $ext;
        foreach (glob($uploadDir . 'favicon.*') as $old) {
            unlink($old);
        }
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $success = 'Fav-Icon erfolgreich hochgeladen!';
        } else {
            $error = 'Fehler beim Speichern.';
        }
    }
}
$currentFavicon = null;
foreach (['ico', 'png', 'jpg', 'svg'] as $ext) {
    if (file_exists($uploadDir . 'favicon.' . $ext)) {
        $currentFavicon = 'favicon/favicon.' . $ext;
        break;
    }
}
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Fav-Icon – QR Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --sidebar-bg: #1f2937;
            --sidebar-hover: #374151;
            --bg-body: #f3f4f6;
            --bg-card: #ffffff;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --border: #e5e7eb;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; background: var(--bg-body); color: var(--text-main); min-height: 100vh; display: flex; overflow-x: hidden; }
        
        .admin-wrapper { display: flex; width: 100%; min-height: 100vh; }
        
        aside {
            width: 260px; background: var(--sidebar-bg); color: #fff; padding: 2rem 1.5rem; flex-shrink: 0; display: flex; flex-direction: column; position: fixed; top: 0; bottom: 0; left: 0; z-index: 100;
        }
        .logo { font-size: 1.25rem; font-weight: 800; margin-bottom: 2.5rem; display: flex; align-items: center; gap: 0.75rem; color: #fff; text-decoration: none; }
        .nav-links { list-style: none; flex: 1; }
        .nav-links li { margin-bottom: 0.5rem; }
        .nav-links a {
            display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: rgba(255,255,255,0.7); text-decoration: none; border-radius: 8px; font-size: 0.9rem; font-weight: 500; transition: all 0.2s;
        }
        .nav-links a:hover, .nav-links a.active { background: var(--sidebar-hover); color: #fff; }
        .logout-link { margin-top: auto; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1); color: #fca5a5; text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 0.75rem; }

        main { flex: 1; margin-left: 260px; padding: 2rem 2.5rem; max-width: 1200px; width: 100%; }
        header { margin-bottom: 2.5rem; }
        header h1 { font-size: 1.5rem; font-weight: 700; }

        .card { background: var(--bg-card); border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); padding: 2rem; border: 1px solid var(--border); max-width: 600px; }
        .msg { background: #f0fdf4; color: #166534; padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; font-size: 0.85rem; border: 1px solid #dcfce7; }
        .err { background: #fef2f2; color: #991b1b; padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; font-size: 0.85rem; border: 1px solid #fee2e2; }
        
        label { display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.75rem; color: var(--text-main); }
        input[type=file] { width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 10px; background: #f9fafb; margin-bottom: 1.5rem; }
        button {
            background: linear-gradient(135deg, var(--primary), var(--secondary)); color: #fff; border: none; padding: 0.75rem 1.5rem; border-radius: 10px; font-weight: 700; cursor: pointer; transition: opacity 0.2s;
        }
        button:hover { opacity: 0.9; }

        .current-fav { margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border); display: flex; align-items: center; gap: 1.5rem; }
        .fav-preview { width: 64px; height: 64px; background: #fff; border: 1px solid var(--border); border-radius: 12px; display: flex; align-items: center; justify-content: center; padding: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .fav-preview img { max-width: 100%; max-height: 100%; }

        @media (max-width: 900px) {
            aside { width: 80px; padding: 2rem 0; align-items: center; }
            aside span { display: none; }
            main { margin-left: 80px; padding: 1.5rem; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <aside>
        <a href="index.php" class="logo"><span>📣 Admin</span></a>
        <ul class="nav-links">
            <li><a href="index.php"><span>Dashboard</span></a></li>
            <li><a href="favicon_upload.php" class="active"><span>Fav-Icon</span></a></li>
        </ul>
        <a href="logout.php" class="logout-link"><span>Abmelden</span></a>
    </aside>

    <main>
        <header>
            <h1>Fav-Icon Einstellungen</h1>
        </header>

        <div class="card">
            <?php if (isset($success)): ?><div class="msg">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>
            <?php if (isset($error)): ?><div class="err">❌ <?= htmlspecialchars($error) ?></div><?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <label>Neues Fav-Icon hochladen <span style="font-weight:400; color:var(--text-muted)">(ico, png, jpg, svg)</span></label>
                <input type="file" name="favicon" accept=".ico,.png,.jpg,.svg,image/x-icon,image/png,image/jpeg,image/svg+xml" required>
                <button type="submit">Hochladen & Speichern</button>
            </form>

            <?php if ($currentFavicon): ?>
                <div class="current-fav">
                    <div class="fav-preview">
                        <?php 
                        $mtime = filemtime($uploadDir . basename($currentFavicon)); 
                        ?>
                        <img src="<?= htmlspecialchars($currentFavicon) ?>?v=<?= $mtime ?>" alt="Fav-Icon">
                    </div>
                    <div>
                        <div style="font-size: 0.875rem; font-weight: 700">Aktuelles Icon</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted)">Dieses Icon wird im Browser-Tab angezeigt.</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
