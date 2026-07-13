<?php
// favicon_upload.php
// Medien-Einstellungen: Fav-Icon, Logo und Branding für die Scan-Seite

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/db.php';

session_start();
if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
    http_response_code(403);
    echo "Nicht autorisiert.";
    exit;
}

// --- Favicon Upload ---
$faviconDir = __DIR__ . '/favicon/';
$allowedTypes = ['image/x-icon' => 'ico', 'image/vnd.microsoft.icon' => 'ico', 'image/png' => 'png', 'image/jpeg' => 'jpg', 'image/svg+xml' => 'svg'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['favicon'])) {
    $file = $_FILES['favicon'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $faviconError = 'Fehler beim Upload.';
    } elseif (!array_key_exists($file['type'], $allowedTypes)) {
        $faviconError = 'Ungültiger Dateityp.';
    } else {
        $ext = $allowedTypes[$file['type']];
        $target = $faviconDir . 'favicon.' . $ext;
        if (!is_dir($faviconDir)) mkdir($faviconDir, 0755, true);
        foreach (glob($faviconDir . 'favicon.*') as $old) {
            unlink($old);
        }
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $faviconSuccess = 'Fav-Icon erfolgreich hochgeladen!';
        } else {
            $faviconError = 'Fehler beim Speichern.';
        }
    }
}

// Favicon löschen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_favicon'])) {
    foreach (glob($faviconDir . 'favicon.*') as $old) {
        unlink($old);
    }
    $faviconSuccess = 'Fav-Icon wurde entfernt.';
}

$currentFavicon = null;
foreach (['ico', 'png', 'jpg', 'svg'] as $ext) {
    if (file_exists($faviconDir . 'favicon.' . $ext)) {
        $currentFavicon = 'favicon/favicon.' . $ext;
        break;
    }
}

// --- Logo Upload ---
$logoDir = __DIR__ . '/logo/';
$logoAllowedTypes = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/svg+xml' => 'svg', 'image/webp' => 'webp'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['logo'])) {
    $file = $_FILES['logo'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $logoError = 'Fehler beim Upload.';
    } elseif (!array_key_exists($file['type'], $logoAllowedTypes)) {
        $logoError = 'Ungültiger Dateityp. Erlaubt: PNG, JPG, SVG, WebP.';
    } elseif ($file['size'] > 2 * 1024 * 1024) {
        $logoError = 'Datei zu groß (max. 2 MB).';
    } else {
        $ext = $logoAllowedTypes[$file['type']];
        $target = $logoDir . 'logo.' . $ext;
        if (!is_dir($logoDir)) mkdir($logoDir, 0755, true);
        foreach (glob($logoDir . 'logo.*') as $old) {
            unlink($old);
        }
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $logoSuccess = 'Logo erfolgreich hochgeladen!';
        } else {
            $logoError = 'Fehler beim Speichern.';
        }
    }
}

// Logo löschen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_logo'])) {
    foreach (glob($logoDir . 'logo.*') as $old) {
        unlink($old);
    }
    $logoSuccess = 'Logo wurde entfernt.';
}

$currentLogo = null;
foreach (['png', 'jpg', 'svg', 'webp'] as $ext) {
    if (file_exists($logoDir . 'logo.' . $ext)) {
        $currentLogo = 'logo/logo.' . $ext;
        break;
    }
}

// --- Branding (Titel) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['brand_title'])) {
    $bTitle = trim($_POST['brand_title']);
    set_setting('brand_title', $bTitle ?: 'Für dich');
    $brandingSuccess = 'Titel wurde gespeichert.';
}
$brandTitle = get_setting('brand_title') ?: 'Für dich';
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Settings – QR Admin</title>
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

        @media (prefers-color-scheme: dark) {
            :root {
                --primary: #818cf8;
                --secondary: #a78bfa;
                --sidebar-bg: #111827;
                --sidebar-hover: #1f2937;
                --bg-body: #0f172a;
                --bg-card: #1e293b;
                --text-main: #f1f5f9;
                --text-muted: #94a3b8;
                --border: #334155;
            }
            input[type=file] { background: #0f172a; color: var(--text-main); }
            .btn-delete { background: #1e293b; }
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; background: var(--bg-body); color: var(--text-main); min-height: 100vh; display: flex; overflow-x: hidden; }

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
        .nav-links svg { width: 18px; height: 18px; flex-shrink: 0; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .logout-link { margin-top: auto; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1); color: #fca5a5; text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 0.75rem; transition: color 0.2s; }
        .logout-link:hover { color: #f87171; }
        .logout-link svg { width: 18px; height: 18px; flex-shrink: 0; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        main { flex: 1; margin-left: 260px; padding: 2rem 2.5rem; max-width: 1200px; width: 100%; }
        header { margin-bottom: 2.5rem; }
        header h1 { font-size: 1.5rem; font-weight: 700; }

        .card { background: var(--bg-card); border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); padding: 2rem; border: 1px solid var(--border); max-width: 600px; margin-bottom: 2rem; }
        .card h2 { font-size: 1.1rem; font-weight: 700; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem; }
        .msg { background: #f0fdf4; color: #166534; padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; font-size: 0.85rem; border: 1px solid #dcfce7; }
        .err { background: #fef2f2; color: #991b1b; padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; font-size: 0.85rem; border: 1px solid #fee2e2; }

        label { display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.75rem; color: var(--text-main); }
        input[type=file] { width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 10px; background: #f9fafb; margin-bottom: 1.5rem; }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary)); color: #fff; border: none; padding: 0.75rem 1.5rem; border-radius: 10px; font-weight: 700; cursor: pointer; transition: opacity 0.2s; font-size: 0.875rem;
        }
        .btn-primary:hover { opacity: 0.9; }
        .btn-delete {
            background: #fff; color: #dc2626; border: 1px solid #fee2e2; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.8rem; transition: all 0.2s;
        }
        .btn-delete:hover { background: #fef2f2; }

        .current-media { margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border); display: flex; align-items: center; gap: 1.5rem; }
        .media-preview { width: 64px; height: 64px; background: #fff; border: 1px solid var(--border); border-radius: 12px; display: flex; align-items: center; justify-content: center; padding: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); flex-shrink: 0; }
        .media-preview img { max-width: 100%; max-height: 100%; }
        .media-info { flex: 1; }
        .media-info .label { font-size: 0.875rem; font-weight: 700; margin-bottom: 0.25rem; }
        .media-info .hint { font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.5rem; }

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
            <li><a href="index.php"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg><span>Dashboard</span></a></li>
            <li><a href="../scan/index.php" target="_blank"><svg viewBox="0 0 24 24"><path d="M6 9l6-6 6 6"/><path d="M6 15l6 6 6-6"/></svg><span>Akt. Scan</span></a></li>
            <li><a href="favicon_upload.php" class="active"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg><span>Settings</span></a></li>
        </ul>
        <a href="logout.php" class="logout-link"><svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg><span>Abmelden</span></a>
    </aside>

    <main>
        <header>
            <h1>Settings</h1>
        </header>

        <!-- Fav-Icon Upload -->
        <div class="card">
            <h2>🔖 Fav-Icon</h2>
            <?php if (isset($faviconSuccess)): ?><div class="msg">✅ <?= htmlspecialchars($faviconSuccess) ?></div><?php endif; ?>
            <?php if (isset($faviconError)): ?><div class="err">❌ <?= htmlspecialchars($faviconError) ?></div><?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <label>Neues Fav-Icon hochladen <span style="font-weight:400; color:var(--text-muted)">(ico, png, jpg, svg)</span></label>
                <input type="file" name="favicon" accept=".ico,.png,.jpg,.svg,image/x-icon,image/png,image/jpeg,image/svg+xml" required>
                <button class="btn-primary" type="submit">Hochladen &amp; Speichern</button>
            </form>

            <?php if ($currentFavicon): ?>
                <div class="current-media">
                    <div class="media-preview">
                        <?php $mtime = filemtime($faviconDir . basename($currentFavicon)); ?>
                        <img src="<?= htmlspecialchars($currentFavicon) ?>?v=<?= $mtime ?>" alt="Fav-Icon">
                    </div>
                    <div class="media-info">
                        <div class="label">Aktuelles Icon</div>
                        <div class="hint">Wird im Browser-Tab angezeigt.</div>
                        <form method="post" style="display:inline" onsubmit="return confirm('Fav-Icon wirklich entfernen?')">
                            <button class="btn-delete" type="submit" name="delete_favicon" value="1">Entfernen</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Logo Upload -->
        <div class="card">
            <h2>🖼️ Logo (Scan-Seite)</h2>
            <?php if (isset($logoSuccess)): ?><div class="msg">✅ <?= htmlspecialchars($logoSuccess) ?></div><?php endif; ?>
            <?php if (isset($logoError)): ?><div class="err">❌ <?= htmlspecialchars($logoError) ?></div><?php endif; ?>

            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.5rem; line-height: 1.6">
                Dieses Logo wird im Header der öffentlichen Scan-Seite angezeigt – links neben dem Titel.
                Empfohlene Größe: quadratisch, mind. 56×56 px.
            </p>

            <form method="post" enctype="multipart/form-data">
                <label>Logo hochladen <span style="font-weight:400; color:var(--text-muted)">(png, jpg, svg, webp – max. 2 MB)</span></label>
                <input type="file" name="logo" accept=".png,.jpg,.jpeg,.svg,.webp,image/png,image/jpeg,image/svg+xml,image/webp" required>
                <button class="btn-primary" type="submit">Hochladen &amp; Speichern</button>
            </form>

            <?php if ($currentLogo): ?>
                <div class="current-media">
                    <div class="media-preview">
                        <?php $logoMtime = filemtime($logoDir . basename($currentLogo)); ?>
                        <img src="<?= htmlspecialchars($currentLogo) ?>?v=<?= $logoMtime ?>" alt="Logo">
                    </div>
                    <div class="media-info">
                        <div class="label">Aktuelles Logo</div>
                        <div class="hint">Wird auf der Scan-Seite im Header angezeigt.</div>
                        <form method="post" style="display:inline" onsubmit="return confirm('Logo wirklich entfernen?')">
                            <button class="btn-delete" type="submit" name="delete_logo" value="1">Entfernen</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Branding (Titel) -->
        <div class="card">
            <h2>✏️ Branding (Scan-Seite)</h2>
            <?php if (isset($brandingSuccess)): ?><div class="msg">✅ <?= htmlspecialchars($brandingSuccess) ?></div><?php endif; ?>

            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.5rem; line-height: 1.6">
                Der Titel wird im Header der öffentlichen Scan-Seite angezeigt – rechts neben dem Badge.
            </p>

            <form method="post">
                <label>Titel auf Scan-Seite</label>
                <input type="text" name="brand_title" value="<?= htmlspecialchars($brandTitle) ?>" placeholder="z.B. Willkommen, Hallo, Für dich" style="width:100%; padding:0.75rem 1rem; border:1px solid var(--border); border-radius:10px; font-size:0.9rem; font-family:inherit; background:#f9fafb; margin-bottom:1.5rem">
                <button class="btn-primary" type="submit">Titel speichern</button>
            </form>
        </div>
    </main>
</div>
</body>
</html>
