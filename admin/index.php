<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/sanitize.php';

ensure_schema();
session_start();

if (empty($_SESSION['admin'])) {
    header('Location: ' . BASE_URL . '/admin/login.php');
    exit;
}
$csrf = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(16));
$_SESSION['csrf_token'] = $csrf;

// Bearbeitung: Meldung zum Editieren laden
$editing = null;
if (isset($_GET['edit'])) {
    $editing = get_message((int)$_GET['edit']);
}

$messages     = get_all_messages();
$totalScans  = count_all_scans();
$todayScans  = count_today_scans();
$recentScans = get_recent_scans(50);

// Aktuelle Zeit für Status-Anzeige
$now = new DateTimeImmutable('now');

// Abgelaufene/inaktive Meldungen zählen
$expiredCount = 0;
$scheduledCount = 0;
foreach ($messages as $m) {
    $st = message_status($m, $now);
    if ($st === 'expired' || $st === 'inactive') $expiredCount++;
    if ($st === 'scheduled') $scheduledCount++;
}

$import_error = $_SESSION['import_error'] ?? null;
$import_success = $_SESSION['import_success'] ?? null;
unset($_SESSION['import_error'], $_SESSION['import_success']);

$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);



function message_status(array $m, DateTimeImmutable $now): string
{
    if ($m['is_default']) return 'standard';

    $daysMatch = true;
    if (!empty($m['active_days'])) {
        $currentDay = (int)$now->format('N'); // 1=Mo, 7=So
        $activeDays = explode(',', $m['active_days']);

        $matchToday = in_array((string)$currentDay, $activeDays);
        $matchYesterday = false;

        if ($m['daily_start'] && $m['daily_end'] && $m['daily_start'] > $m['daily_end']) {
            $time = $now->format('H:i:s');
            if ($time <= $m['daily_end']) {
                $yesterday = ($currentDay == 1) ? 7 : $currentDay - 1;
                $matchYesterday = in_array((string)$yesterday, $activeDays);
            }
        }

        if (!$matchToday && !$matchYesterday) {
            $daysMatch = false;
        }
    }

    if ($m['active_from'] && $m['active_until']) {
        $from  = new DateTimeImmutable($m['active_from']);
        $until = new DateTimeImmutable($m['active_until']);
        if ($now >= $from && $now <= $until) {
            if (!$daysMatch) return 'scheduled';
            if ($m['daily_start'] && $m['daily_end']) {
                $time = $now->format('H:i:s');
                if (($m['daily_start'] <= $m['daily_end'] && $time >= $m['daily_start'] && $time <= $m['daily_end']) ||
                    ($m['daily_start'] > $m['daily_end'] && ($time >= $m['daily_start'] || $time <= $m['daily_end']))) {
                    return 'active';
                }
                return 'scheduled';
            }
            return 'active';
        }
        if ($now < $from) return 'scheduled';
        return 'expired';
    }
    if ($m['daily_start'] && $m['daily_end']) {
        if (!$daysMatch) return 'scheduled';
        $time = $now->format('H:i:s');
        if (($m['daily_start'] <= $m['daily_end'] && $time >= $m['daily_start'] && $time <= $m['daily_end']) ||
            ($m['daily_start'] > $m['daily_end'] && ($time >= $m['daily_start'] || $time <= $m['daily_end']))) {
            return 'active';
        }
        return 'scheduled';
    }

    if (!empty($m['active_days'])) {
        return $daysMatch ? 'active' : 'scheduled';
    }

    return 'inactive';
}
$wochentageNames = [1=>'Mo', 2=>'Di', 3=>'Mi', 4=>'Do', 5=>'Fr', 6=>'Sa', 7=>'So'];
?><!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin – QR-Meldungen</title>
    <?php
    $fav = null;
    foreach (['ico', 'png', 'jpg', 'svg'] as $ext) {
        if (file_exists(__DIR__ . '/favicon/favicon.' . $ext)) {
            $fav = 'favicon/favicon.' . $ext;
            $mtime = filemtime(__DIR__ . '/favicon/favicon.' . $ext);
            $type = ($ext === 'ico') ? 'x-icon' : $ext;
            echo '<link rel="icon" type="image/' . $type . '" href="' . $fav . '?v=' . $mtime . '">';
            break;
        }
    }
    ?>
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
            --accent: #3b82f6;
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
                --accent: #60a5fa;
                --border: #334155;
            }

            .card, .stat-card, .live-preview-card { background: var(--bg-card); }
            .msg-table tr:hover td { background: rgba(255,255,255,0.03); }
            input[type=text], input[type=datetime-local], input[type=time], textarea, select { background: #0f172a; color: var(--text-main); }
            .toolbar { background: #0f172a; }
            .toolbar-btn { background: #1e293b; border-color: var(--border); color: var(--text-main); }
            .btn-ghost { background: var(--bg-card); color: var(--text-main); border-color: var(--border); }
            .btn-ghost:hover { background: rgba(255,255,255,0.05); }
            .modal-card { background: var(--bg-card); }
            .consent-state { color: var(--text-muted); }
            #preview-render { background: #0f172a; }
            .live-preview-content { background: #0f172a; border-color: var(--border); }
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; background: var(--bg-body); color: var(--text-main); min-height: 100vh; display: flex; overflow-x: hidden; }

        /* Sidebar Layout */
        .admin-wrapper { display: flex; width: 100%; min-height: 100vh; }

        aside {
            width: 260px;
            background: var(--sidebar-bg);
            color: #fff;
            padding: 2rem 1.5rem;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
        }

        .logo { font-size: 1.25rem; font-weight: 800; margin-bottom: 2.5rem; display: flex; align-items: center; gap: 0.75rem; color: #fff; text-decoration: none; }

        .nav-links { list-style: none; flex: 1; }
        .nav-links li { margin-bottom: 0.5rem; }
        .nav-links a {
            display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem;
            color: rgba(255,255,255,0.7); text-decoration: none; border-radius: 8px; font-size: 0.9rem; font-weight: 500; transition: all 0.2s;
        }
        .nav-links a:hover, .nav-links a.active { background: var(--sidebar-hover); color: #fff; }
        .nav-links svg { width: 18px; height: 18px; flex-shrink: 0; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        .logout-link {
            margin-top: auto; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1);
            color: #fca5a5; text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 0.75rem; transition: color 0.2s;
        }
        .logout-link:hover { color: #f87171; }
        .logout-link svg { width: 18px; height: 18px; flex-shrink: 0; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        main { flex: 1; margin-left: 260px; padding: 2rem 2.5rem; max-width: 1200px; width: 100%; }

        header { margin-bottom: 2.5rem; display: flex; justify-content: space-between; align-items: center; }
        header h1 { font-size: 1.5rem; font-weight: 700; color: var(--text-main); }

        .card { background: var(--bg-card); border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06); padding: 1.5rem; border: 1px solid var(--border); margin-bottom: 1.5rem; }
        .card h2 { font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem; }

        /* Dashboard Stats */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; margin-bottom: 2rem; }
        .stat-card { background: #fff; padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border); transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-2px); }
        .stat-val { font-size: 1.75rem; font-weight: 800; color: var(--primary); margin-bottom: 0.25rem; }
        .stat-lbl { font-size: 0.85rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

        /* Tables & UI Components */
        .table-responsive { overflow-x: auto; }
        .msg-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
        .msg-table th { text-align: left; padding: 1rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 1px solid var(--border); }
        .msg-table td { padding: 1rem; border-bottom: 1px solid #f9fafb; vertical-align: middle; }
        .msg-table tr:hover td { background: #f9fafb; }

        .badge { display: inline-flex; align-items: center; padding: 0.25rem 0.75rem; border-radius: 100px; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.2px; text-transform: uppercase; }
        .badge-standard { background: #eff6ff; color: #1e40af; }
        .badge-active { background: #ecfdf5; color: #065f46; }
        .badge-scheduled { background: #fffbeb; color: #92400e; }
        .badge-expired, .badge-inactive { background: #f9fafb; color: #4b5563; }

        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none; cursor: pointer; transition: all 0.2s; border: 1px solid transparent; }
        .btn-ghost { border-color: var(--border); color: var(--text-main); background: #fff; }
        .btn-ghost:hover { background: #f9fafb; border-color: #d1d5db; }
        .btn-primary { background: linear-gradient(135deg, var(--primary), var(--secondary)); color: #fff; border: none; }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-danger { color: #dc2626; border-color: #fee2e2; background: #fff; font-size: 0.75rem; }
        .btn-danger:hover { background: #fef2f2; }
        .btn-std { color: var(--accent); border-color: #dbeafe; background: #fff; font-size: 0.75rem; }
        .btn-std:hover { background: #eff6ff; }

        /* Form Controls */
        label { display: block; font-size: 0.875rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.5rem; }
        input[type=text], input[type=datetime-local], input[type=time], textarea, select {
            width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: 10px;
            font-size: 0.9rem; font-family: inherit; background: #f9fafb; transition: all 0.2s; margin-bottom: 1.25rem;
        }
        input:focus, textarea:focus, select:focus { outline: none; border-color: var(--primary); background: #fff; box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1); }
        textarea { height: 160px; resize: vertical; margin-bottom: 0; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; }

        /* Custom UI */
        .live-preview-card { background: #fff; border: 1px solid var(--border); border-radius: 16px; padding: 1.5rem; margin-bottom: 2.5rem; position: relative; overflow: hidden; }
        .live-preview-content { background: #f9fafb; border-radius: 12px; padding: 1.25rem; border: 1px dashed var(--border); min-height: 80px; }
        .refresh-dot { width: 8px; height: 8px; border-radius: 50%; background: #10b981; margin-right: 0.5rem; display: inline-block; position: relative; }
        .refresh-dot::after { content: ''; position: absolute; width: 100%; height: 100%; border-radius: 50%; background: inherit; animation: pulse 1.5s infinite; }
        @keyframes pulse { 0% { opacity: 0.8; transform: scale(1); } 100% { opacity: 0; transform: scale(3); } }

        .toolbar { display: flex; gap: 0.3rem; background: #f9fafb; padding: 0.5rem; border: 1px solid var(--border); border-bottom: none; border-radius: 10px 10px 0 0; flex-wrap: wrap; align-items: center; }
        .toolbar-sep { width: 1px; height: 1.4rem; background: var(--border); margin: 0 0.15rem; flex-shrink: 0; }
        .toolbar-btn { background: #fff; border: 1px solid var(--border); border-radius: 6px; padding: 0.35rem 0.55rem; font-size: 0.78rem; font-weight: 700; cursor: pointer; transition: all 0.2s; line-height: 1; position: relative; }
        .toolbar-btn:hover { border-color: var(--primary); color: var(--primary); }
        .toolbar-btn[title]::after { content: attr(title); position: absolute; bottom: calc(100% + 6px); left: 50%; transform: translateX(-50%); background: #1f2937; color: #fff; font-size: 0.68rem; font-weight: 500; padding: 0.25rem 0.5rem; border-radius: 4px; white-space: nowrap; opacity: 0; pointer-events: none; transition: opacity 0.15s; }
        .toolbar-btn:hover[title]::after { opacity: 1; }
        .emoji-picker { position: absolute; top: calc(100% + 4px); right: 0; background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 0.75rem; box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 50; display: none; width: max-content; }
        .emoji-picker.open { display: block; }
        .emoji-grid { display: grid; grid-template-columns: repeat(8, 1fr); gap: 0.25rem; }
        .emoji-grid button { background: none; border: none; font-size: 1.25rem; padding: 0.3rem; border-radius: 6px; cursor: pointer; transition: background 0.15s; line-height: 1; }
        .emoji-grid button:hover { background: rgba(102, 126, 234, 0.12); }
        .tpl-dropdown { position: absolute; top: calc(100% + 4px); right: 0; background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 0.5rem; box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 50; display: none; min-width: 240px; }
        .tpl-dropdown.open { display: block; }
        .tpl-dropdown button { display: block; width: 100%; text-align: left; background: none; border: none; padding: 0.6rem 0.75rem; border-radius: 8px; font-size: 0.82rem; cursor: pointer; color: var(--text-main); transition: background 0.15s; font-weight: 500; }
        .tpl-dropdown button:hover { background: rgba(102, 126, 234, 0.08); }

        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 200; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
        .modal-card { background: #fff; width: 95%; max-width: 550px; border-radius: 20px; padding: 2.5rem; position: relative; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }

        @media (max-width: 900px) {
            aside { width: 80px; padding: 2rem 0; align-items: center; }
            aside span { display: none; }
            .logo { margin-bottom: 2rem; justify-content: center; }
            main { margin-left: 80px; padding: 1.5rem; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <aside>
        <a href="index.php" class="logo">
            <span>📣 Admin</span>
        </a>
        <ul class="nav-links">
            <li><a href="index.php" class="active"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg><span>Dashboard</span></a></li>
            <li><a href="../scan/index.php" target="_blank"><svg viewBox="0 0 24 24"><path d="M6 9l6-6 6 6"/><path d="M6 15l6 6 6-6"/></svg><span>Akt. Scan</span></a></li>
			<li><a href="favicon_upload.php"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg><span>Settings</span></a></li>
        </ul>
        <a href="logout.php" class="logout-link"><svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg><span>Abmelden</span></a>
    </aside>

    <main>
        <header>
            <h1>Dashboard</h1>
            <div style="font-size: 0.85rem; color: var(--text-muted)">
                <?= $now->format('d.m.Y H:i') ?>
            </div>
        </header>

        <?php if ($flash_success): ?>
            <div style="background:#f0fdf4; color:#166534; padding:1rem 1.25rem; border-radius:12px; margin-bottom:1.5rem; font-size:0.875rem; border:1px solid #dcfce7; display:flex; justify-content:space-between; align-items:center">
                <span>✅ <?= htmlspecialchars($flash_success) ?></span>
                <button onclick="this.parentElement.remove()" style="background:none;border:none;color:#166534;cursor:pointer;font-size:1.1rem;padding:0 0.25rem">&times;</button>
            </div>
        <?php endif; ?>
        <?php if ($flash_error): ?>
            <div style="background:#fef2f2; color:#991b1b; padding:1rem 1.25rem; border-radius:12px; margin-bottom:1.5rem; font-size:0.875rem; border:1px solid #fee2e2; display:flex; justify-content:space-between; align-items:center">
                <span>❌ <?= htmlspecialchars($flash_error) ?></span>
                <button onclick="this.parentElement.remove()" style="background:none;border:none;color:#991b1b;cursor:pointer;font-size:1.1rem;padding:0 0.25rem">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Live Dashboard Preview -->
        <div class="card" id="live-dashboard-preview" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05)); border-color: rgba(102, 126, 234, 0.2);">
            <div style="display:flex; justify-content:space-between; align-items:center, margin-bottom: 1rem">
                <h2 style="margin-bottom:0; border:none"><span class="refresh-dot"></span>Aktuelle öffentliche Meldung</h2>
                <div id="live-preview-status" class="badge badge-active">Lade...</div>
            </div>
            <div id="live-preview-render" class="live-preview-content">
                <div style="color:#9ca3af; font-style:italic">Lade aktuelle Meldung...</div>
            </div>
        </div>

        <!-- Statistiken -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-val"><?= $totalScans ?></div>
                <div class="stat-lbl">Scans gesamt</div>
            </div>
            <div class="stat-card">
                <div class="stat-val"><?= $todayScans ?></div>
                <div class="stat-lbl">Scans heute</div>
            </div>
            <div class="stat-card">
                <div class="stat-val"><?= count($messages) ?></div>
                <div class="stat-lbl">Meldungen</div>
            </div>
        </div>

        <?php if ($expiredCount > 0 || $scheduledCount > 0): ?>
        <div style="display:flex; gap:0.75rem; margin-bottom:1.5rem; flex-wrap:wrap">
            <?php if ($expiredCount > 0): ?>
                <a href="?filter=expired" class="badge badge-expired" style="text-decoration:none;padding:0.4rem 1rem;font-size:0.8rem;cursor:pointer" onclick="document.getElementById('msg-status-filter').value='expired';filterMessages()">⚠ <?= $expiredCount ?> abgelaufen</a>
            <?php endif; ?>
            <?php if ($scheduledCount > 0): ?>
                <a href="?filter=scheduled" class="badge badge-scheduled" style="text-decoration:none;padding:0.4rem 1rem;font-size:0.8rem;cursor:pointer" onclick="document.getElementById('msg-status-filter').value='scheduled';filterMessages()">⏰ <?= $scheduledCount ?> geplant</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Meldungsverwaltung -->
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem">
                <h2 style="margin-bottom:0">Meldungen verwalten</h2>
                <div style="display:flex; gap:0.5rem;">
                    <a href="export_messages.php" class="btn btn-ghost">📥 Export</a>
                    <button onclick="document.getElementById('import-form').style.display='flex'" class="btn btn-ghost">📤 Import</button>
                </div>
            </div>

            <div style="display:flex; gap:0.75rem; margin-bottom:1.25rem; flex-wrap:wrap; align-items:center">
                <input type="text" id="msg-search" placeholder="Suche nach Titel..." style="width:auto; min-width:220px; margin-bottom:0; flex:1" oninput="filterMessages()">
                <select id="msg-status-filter" style="width:auto; margin-bottom:0; min-width:160px" onchange="filterMessages()">
                    <option value="all">Alle Status</option>
                    <option value="standard">Standard</option>
                    <option value="active">Aktiv</option>
                    <option value="scheduled">Geplant</option>
                    <option value="expired">Abgelaufen</option>
                    <option value="inactive">Inaktiv</option>
                </select>
            </div>

            <?php if ($import_error): ?>
                <div style="background:#fef2f2; color:#991b1b; padding:1rem; border-radius:12px; margin-bottom:1.5rem; font-size:0.85rem; border:1px solid #fee2e2">
                    ❌ <?= htmlspecialchars($import_error) ?>
                </div>
            <?php endif; ?>
            <?php if ($import_success): ?>
                <div style="background:#f0fdf4; color:#166534; padding:1rem; border-radius:12px; margin-bottom:1.5rem; font-size:0.85rem; border:1px solid #dcfce7">
                    ✅ <?= htmlspecialchars($import_success) ?>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="msg-table">
                    <thead><tr><th>Titel</th><th>Status</th><th>Zeitraum</th><th>Aktionen</th></tr></thead>
                    <tbody>
                    <?php foreach ($messages as $m):
                        $st = message_status($m, $now);
                    ?>
                    <tr data-status="<?= htmlspecialchars($st) ?>" data-title="<?= htmlspecialchars(strtolower($m['title'])) ?>">
                        <td style="font-weight: 600"><?= htmlspecialchars($m['title']) ?></td>
                        <td>
                            <span class="badge badge-<?= $st ?>">
                                <?= ['standard'=>'Standard','active'=>'Aktiv','scheduled'=>'Geplant','expired'=>'Abgelaufen','inactive'=>'Inaktiv'][$st] ?>
                            </span>
                        </td>
                        <td style="font-size:0.8rem; color:var(--text-muted)">
                            <?php if ($m['active_from'] && $m['active_until']): ?>
                                <?= date('d.m.Y H:i', strtotime($m['active_from'])) ?> – <?= date('d.m.Y H:i', strtotime($m['active_until'])) ?>
                            <?php else: echo '—'; endif; ?>
                            <?php if ($m['daily_start'] && $m['daily_end']): ?>
                                <br><span style="color:var(--primary)">Täglich: <?= substr($m['daily_start'], 0, 5) ?> – <?= substr($m['daily_end'], 0, 5) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex; gap:0.4rem">
                                <button class="btn btn-ghost" style="padding:0.3rem 0.6rem; font-size:0.75rem" onclick="openPreview(this)" data-content="<?= htmlspecialchars($m['content']) ?>">Vorschau</button>
                                <a class="btn btn-ghost" style="padding:0.3rem 0.6rem; font-size:0.75rem" href="?edit=<?= $m['id'] ?>">Bearbeiten</a>
                                <?php if (!$m['is_default']): ?>
                                    <form method="post" action="save.php" style="display:inline" onsubmit="return confirm('Diese Meldung als Standard festlegen?')">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                        <input type="hidden" name="action" value="set_default">
                                        <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                        <button class="btn btn-std" type="submit">Standard</button>
                                    </form>
                                    <form method="post" action="save.php" style="display:inline" onsubmit="return confirm('Meldung löschen?')">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                        <button class="btn btn-danger" type="submit">Löschen</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <h2><?= $editing ? 'Meldung bearbeiten' : 'Neue Meldung' ?></h2>
            <?php if ($editing): ?>
                <div style="background:#eff6ff; color:#1e40af; padding:1rem; border-radius:10px; margin-bottom:1.5rem; font-size:0.85rem; border:1px solid #dbeafe">
                    ✏️ Sie bearbeiten: <strong><?= htmlspecialchars($editing['title']) ?></strong>
                </div>
            <?php endif; ?>

            <form method="post" action="save.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="scroll_position" id="scroll_position" value="0">
                <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
                <?php if ($editing): ?>
                    <input type="hidden" name="id" value="<?= $editing['id'] ?>">
                <?php endif; ?>

                <label>Interner Titel</label>
                <input type="text" name="title" required maxlength="255" value="<?= $editing ? htmlspecialchars($editing['title']) : '' ?>">

                <label>Meldungstext <span style="font-weight:400; color:var(--text-muted)">(HTML erlaubt)</span></label>
                <div class="toolbar">
                    <!-- Überschriften -->
                    <button type="button" class="toolbar-btn" onclick="insertTag('<h1>', '</h1>')" title="Überschrift 1">H1</button>
                    <button type="button" class="toolbar-btn" onclick="insertTag('<h2>', '</h2>')" title="Überschrift 2">H2</button>
                    <button type="button" class="toolbar-btn" onclick="insertTag('<h3>', '</h3>')" title="Überschrift 3">H3</button>
                    <div class="toolbar-sep"></div>
                    <!-- Textformatierung -->
                    <button type="button" class="toolbar-btn" onclick="insertTag('<b>', '</b>')" title="Fett"><b>B</b></button>
                    <button type="button" class="toolbar-btn" onclick="insertTag('<i>', '</i>')" title="Kursiv"><i>I</i></button>
                    <button type="button" class="toolbar-btn" onclick="insertTag('<u>', '</u>')" title="Unterstrichen" style="text-decoration:underline">U</button>
                    <button type="button" class="toolbar-btn" onclick="insertTag('<s>', '</s>')" title="Durchgestrichen" style="text-decoration:line-through">S</button>
                    <button type="button" class="toolbar-btn" onclick="insertTag('<mark>', '</mark>')" title="Hervorheben">🖍</button>
                    <button type="button" class="toolbar-btn" onclick="insertTag('<small>', '</small>')" title="Kleiner Text" style="font-size:0.65rem">sm</button>
                    <div class="toolbar-sep"></div>
                    <!-- Struktur -->
                    <button type="button" class="toolbar-btn" onclick="insertTag('<p>', '</p>')" title="Absatz">¶</button>
                    <button type="button" class="toolbar-btn" onclick="insertAtCursor('<br>')" title="Zeilenumbruch">↵</button>
                    <button type="button" class="toolbar-btn" onclick="insertAtCursor('<hr>')" title="Trennlinie">―</button>
                    <button type="button" class="toolbar-btn" onclick="insertTag('<blockquote>', '</blockquote>')" title="Zitat">❝</button>
                    <div class="toolbar-sep"></div>
                    <!-- Listen -->
                    <button type="button" class="toolbar-btn" onclick="insertList('ul')" title="Aufzählung">• ≡</button>
                    <button type="button" class="toolbar-btn" onclick="insertList('ol')" title="Nummerierung">1. ≡</button>
                    <div class="toolbar-sep"></div>
                    <!-- Link -->
                    <button type="button" class="toolbar-btn" onclick="insertLink()" title="Link einfügen">🔗</button>
                    <div class="toolbar-sep"></div>
                    <!-- Emoji -->
                    <span style="position:relative">
                        <button type="button" class="toolbar-btn" onclick="toggleEmojiPicker(event)" title="Emoji">😊</button>
                        <div id="emoji-picker" class="emoji-picker">
                            <div class="emoji-grid">
                                <?php foreach(['📢','📣','✅','❌','⚠️','ℹ️','❤️','👋','🎉','🔔','⏰','📍','☎️','📧','🏠','🔑','🚗','🛒','🍕','☕','🌟','⭐','💡','🔧'] as $e): ?>
                                    <button type="button" onclick="insertEmoji('<?= $e ?>')"><?= $e ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </span>
                    <!-- Vorlagen -->
                    <span style="position:relative">
                        <button type="button" class="toolbar-btn" onclick="toggleTemplates(event)" title="Vorlage einfügen">📋</button>
                        <div id="tpl-dropdown" class="tpl-dropdown">
                            <button type="button" onclick="insertTemplate('absent')">🚪 Abwesend bis …</button>
                            <button type="button" onclick="insertTemplate('meeting')">📅 Bin im Meeting</button>
                            <button type="button" onclick="insertTemplate('erreichbar')">☎️ Erreichbar unter …</button>
                            <button type="button" onclick="insertTemplate('pause')">☕ In der Pause</button>
                            <button type="button" onclick="insertTemplate('homeoffice')">🏠 Im Homeoffice</button>
                            <button type="button" onclick="insertTemplate('urlaub')">🌴 Im Urlaub</button>
                        </div>
                    </span>
                </div>
                <textarea name="content" id="msg-content" required><?= $editing ? htmlspecialchars($editing['content']) : '' ?></textarea>

                <div style="margin: 1.5rem 0; padding: 1.25rem; background: #fafafa; border-radius: 12px; border: 1px solid var(--border)">
                    <span style="font-size:0.75rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:0.75rem">Live-Vorschau</span>
                    <div id="preview-render" style="background:#fff; padding:1rem; border-radius:8px; border:1px solid var(--border); min-height:60px">
                        <?php if ($editing): ?>
                            <?= defined('ALLOW_HTML_WHITELIST') && ALLOW_HTML_WHITELIST ? sanitize_html_whitelist($editing['content']) : nl2br(htmlspecialchars($editing['content'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')) ?>
                        <?php else: ?>
                            <i>Ihre Nachricht erscheint hier...</i>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-grid">
                    <div>
                        <label>Aktiv ab</label>
                        <input type="datetime-local" name="active_from" value="<?= $editing && $editing['active_from'] ? date('Y-m-d\TH:i', strtotime($editing['active_from'])) : '' ?>">
                    </div>
                    <div>
                        <label>Aktiv bis</label>
                        <input type="datetime-local" name="active_until" value="<?= $editing && $editing['active_until'] ? date('Y-m-d\TH:i', strtotime($editing['active_until'])) : '' ?>">
                    </div>
                </div>

                <div class="form-grid">
                    <div>
                        <label>Täglich von</label>
                        <input type="time" name="daily_start" value="<?= $editing && $editing['daily_start'] ? substr($editing['daily_start'], 0, 5) : '' ?>">
                    </div>
                    <div>
                        <label>Täglich bis</label>
                        <input type="time" name="daily_end" value="<?= $editing && $editing['daily_end'] ? substr($editing['daily_end'], 0, 5) : '' ?>">
                    </div>
                </div>

                <div style="margin-bottom: 2rem;">
                    <label>An Wochentagen</label>
                    <div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-bottom:1rem;">
                        <?php
                        $selDays = $editing && $editing['active_days'] ? explode(',', $editing['active_days']) : [];
                        foreach([1=>'Mo', 2=>'Di', 3=>'Mi', 4=>'Do', 5=>'Fr', 6=>'Sa', 7=>'So'] as $dVal => $dName): ?>
                            <label style="display:inline-flex; align-items:center; background:#fff; padding:0.5rem 0.75rem; border-radius:10px; border:1px solid var(--border); font-size:0.85rem; font-weight:500; cursor:pointer; margin-bottom:0">
                                <input type="checkbox" name="active_days[]" value="<?= $dVal ?>" <?= in_array((string)$dVal, $selDays) ? 'checked' : '' ?> class="day-cb" style="margin-right:0.5rem">
                                <?= $dName ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div style="display:flex; gap:0.5rem;">
                        <button type="button" class="btn btn-ghost" style="padding:0.4rem 0.75rem; font-size:0.75rem" onclick="checkDays([1,2,3,4,5])">Mo-Fr</button>
                        <button type="button" class="btn btn-ghost" style="padding:0.4rem 0.75rem; font-size:0.75rem" onclick="checkDays([6,7])">Sa+So</button>
                        <button type="button" class="btn btn-ghost" style="padding:0.4rem 0.75rem; font-size:0.75rem" onclick="checkDays([1,2,3,4,5,6,7])">Alle</button>
                        <button type="button" class="btn btn-ghost" style="padding:0.4rem 0.75rem; font-size:0.75rem" onclick="checkDays([])">Keine</button>
                    </div>
                </div>

                <div style="display:flex; gap:1rem; align-items:center">
                    <button class="btn btn-primary" type="submit"><?= $editing ? 'Änderungen speichern' : 'Meldung erstellen' ?></button>
                    <?php if ($editing): ?>
                        <a class="btn btn-ghost" href="index.php">Abbrechen</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="card">
            <h2>Letzte Scans</h2>
            <div class="table-responsive">
                <table class="msg-table">
                    <thead><tr><th>Zeitstempel</th><th>IP-Adresse</th><th>User Agent</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentScans as $s): ?>
                    <tr>
                        <td style="white-space:nowrap"><?= date('d.m.Y H:i:s', strtotime($s['ts'])) ?></td>
                        <td style="font-family:monospace; font-size:0.8rem"><?= htmlspecialchars($s['ip'] ?? '—') ?></td>
                        <td style="max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:0.75rem; color:var(--text-muted)" title="<?= htmlspecialchars($s['user_agent'] ?? '') ?>">
                            <?= htmlspecialchars($s['user_agent'] ?? '—') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>


    </main>
</div>

<!-- Modals -->
<div id="preview-modal" class="modal-overlay" onclick="if(event.target==this) closePreview()">
    <div class="modal-card">
        <button onclick="closePreview()" style="position:absolute; top:1.5rem; right:1.5rem; border:none; background:none; font-size:1.5rem; cursor:pointer; color:var(--text-muted)">&times;</button>
        <h2 style="border:none; margin-bottom:1.5rem">Inhalts-Vorschau</h2>
        <div id="modal-content-render" style="background:#f9fafb; padding:1.5rem; border-radius:12px; border:1px solid var(--border); min-height:100px; line-height:1.6"></div>
        <button class="btn btn-primary" style="width:100%; margin-top:1.5rem; justify-content:center" onclick="closePreview()">Schließen</button>
    </div>
</div>

<div id="import-form" class="modal-overlay" onclick="if(event.target==this) this.style.display='none'">
    <div class="modal-card">
        <button onclick="document.getElementById('import-form').style.display='none'" style="position:absolute; top:1.5rem; right:1.5rem; border:none; background:none; font-size:1.5rem; cursor:pointer; color:var(--text-muted)">&times;</button>
        <h2 style="border:none; margin-bottom:1.5rem">Importieren</h2>
        <form method="post" action="import_messages.php" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <label>JSON-Datei auswählen</label>
            <input type="file" name="import_file" accept=".json" required style="margin-bottom:1.5rem">
            <div style="display:flex; gap:1rem">
                <button class="btn btn-primary" style="flex:1; justify-content:center" type="submit">Import starten</button>
                <button type="button" class="btn btn-ghost" style="flex:1; justify-content:center" onclick="document.getElementById('import-form').style.display='none'">Abbrechen</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Scroll position restoration
    const savedScroll = <?= $_SESSION['scroll_position'] ?? 0 ?>;
    if (savedScroll > 0) {
        window.scrollTo(0, savedScroll);
        <?php unset($_SESSION['scroll_position']); ?>
    }

    // Save scroll position before form submit
    document.querySelectorAll('form[method="post"]').forEach(form => {
        form.addEventListener('submit', () => {
            const input = document.getElementById('scroll_position');
            if (input) input.value = window.scrollY;
        });
    });

    // Live Dashboard Preview Logic
    const livePreviewRender = document.getElementById('live-preview-render');
    const livePreviewStatus = document.getElementById('live-preview-status');
    const livePreviewCard = document.getElementById('live-dashboard-preview');

    async function updateLivePreview() {
        livePreviewCard.classList.add('refreshing');
        try {
            const resp = await fetch('get_current_message.php');
            if (resp.ok) {
                const data = await resp.json();
                livePreviewRender.innerHTML = data.content_html;
                livePreviewStatus.textContent = data.title + (data.is_default ? ' (Standard)' : ' (Aktiv)');

                // Status color based on is_default
                if (data.is_default) {
                    livePreviewStatus.style.borderColor = '#9ca3af33';
                    livePreviewStatus.style.color = '#6b7280';
                } else {
                    livePreviewStatus.style.borderColor = '#667eea33';
                    livePreviewStatus.style.color = '#667eea';
                }
            }
        } catch (e) {
            console.error("Live preview update failed", e);
        } finally {
            setTimeout(() => livePreviewCard.classList.remove('refreshing'), 500);
        }
    }

    // Initial load
    updateLivePreview();
    // Auto refresh every 30 seconds
    setInterval(updateLivePreview, 30000);

    const textarea = document.getElementById('msg-content');

    const preview = document.getElementById('preview-render');

    if (textarea && preview) {
        textarea.addEventListener('input', () => {
            const val = textarea.value.trim();
            if (val === '') {
                preview.innerHTML = '<i>Ihre Nachricht erscheint hier...</i>';
            } else {
                // Sanitize: only allow whitelisted tags (same as ALLOW_HTML_WHITELIST)
                const sanitized = sanitizeForPreview(val);
                preview.innerHTML = sanitized;
            }
        });
    }

    function sanitizeForPreview(html) {
        const allowedTags = ['p', 'br', 'b', 'strong', 'i', 'em', 'a', 'h1', 'h2', 'h3', 'u', 's', 'del', 'ul', 'ol', 'li', 'hr', 'mark', 'small', 'blockquote'];
        const selfClosing = ['br', 'hr'];
        const div = document.createElement('div');
        div.innerHTML = html;
        return processNode(div, allowedTags, selfClosing).trim();
    }

    function processNode(node, allowedTags, selfClosing) {
        if (node.nodeType === 3) {
            return node.textContent.replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }
        if (node.nodeType !== 1) return '';
        const tag = node.nodeName.toLowerCase();
        if (!allowedTags.includes(tag)) {
            let s = '';
            for (const child of node.childNodes) s += processNode(child, allowedTags, selfClosing);
            return s;
        }
        if (selfClosing.includes(tag)) return '<' + tag + '>';
        let attrs = '';
        if (tag === 'a') {
            const href = node.getAttribute('href') || '';
            const validProtocols = ['http://', 'https://', 'mailto:', 'tel:', '/'];
            const valid = validProtocols.some(p => href.toLowerCase().startsWith(p));
            if (valid) {
                attrs = ' href="' + href.replace(/"/g, '&quot;') + '" rel="noopener noreferrer"';
            } else {
                let s = '';
                for (const child of node.childNodes) s += processNode(child, allowedTags, selfClosing);
                return s;
            }
        }
        let inner = '';
        for (const child of node.childNodes) inner += processNode(child, allowedTags, selfClosing);
        return '<' + tag + attrs + '>' + inner + '</' + tag + '>';
    }
});

function insertTag(tagOpen, tagClose) {
    const textarea = document.getElementById('msg-content');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    const selection = text.substring(start, end);
    const replacement = tagOpen + selection + tagClose;

    textarea.value = text.substring(0, start) + replacement + text.substring(end);

    // Cursor position setzen
    textarea.focus();
    if (selection.length > 0) {
        textarea.setSelectionRange(start, start + replacement.length);
    } else {
        textarea.setSelectionRange(start + tagOpen.length, start + tagOpen.length);
    }

    // Preview triggern
    textarea.dispatchEvent(new Event('input'));
}

function insertAtCursor(text) {
    const textarea = document.getElementById('msg-content');
    const start = textarea.selectionStart;
    textarea.value = textarea.value.substring(0, start) + text + textarea.value.substring(start);
    textarea.focus();
    textarea.setSelectionRange(start + text.length, start + text.length);
    textarea.dispatchEvent(new Event('input'));
}

function insertLink() {
    const url = prompt('URL eingeben:', 'https://');
    if (url && url !== 'https://') {
        insertTag('<a href="' + url + '" target="_blank">', '</a>');
    }
}

function insertList(type) {
    const textarea = document.getElementById('msg-content');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selected = textarea.value.substring(start, end);
    let items;
    if (selected.trim()) {
        items = selected.split('\n').filter(l => l.trim()).map(l => '  <li>' + l.trim() + '</li>').join('\n');
    } else {
        items = '  <li>Punkt 1</li>\n  <li>Punkt 2</li>\n  <li>Punkt 3</li>';
    }
    const replacement = '<' + type + '>\n' + items + '\n</' + type + '>';
    textarea.value = textarea.value.substring(0, start) + replacement + textarea.value.substring(end);
    textarea.focus();
    textarea.setSelectionRange(start, start + replacement.length);
    textarea.dispatchEvent(new Event('input'));
}

function toggleEmojiPicker(e) {
    e.stopPropagation();
    const picker = document.getElementById('emoji-picker');
    const tpl = document.getElementById('tpl-dropdown');
    if (tpl) tpl.classList.remove('open');
    picker.classList.toggle('open');
}

function insertEmoji(emoji) {
    insertAtCursor(emoji);
    document.getElementById('emoji-picker').classList.remove('open');
}

function toggleTemplates(e) {
    e.stopPropagation();
    const tpl = document.getElementById('tpl-dropdown');
    const picker = document.getElementById('emoji-picker');
    if (picker) picker.classList.remove('open');
    tpl.classList.toggle('open');
}

function insertTemplate(key) {
    const templates = {
        'absent': '<h2>🚪 Abwesend</h2>\n<p>Ich bin bis <b>[Datum]</b> nicht im Büro.</p>\n<p>Bei dringenden Anliegen wenden Sie sich bitte an <b>[Vertretung]</b>.</p>',
        'meeting': '<h2>📅 Im Meeting</h2>\n<p>Bin voraussichtlich bis <b>[Uhrzeit]</b> im Meeting.</p>\n<p>Danach wieder erreichbar.</p>',
        'erreichbar': '<h2>☎️ Erreichbar</h2>\n<p>Ich bin telefonisch erreichbar unter:</p>\n<p><b>[Telefonnummer]</b></p>',
        'pause': '<h2>☕ In der Pause</h2>\n<p>Bin kurz in der Pause. Bin in <b>[X Minuten]</b> zurück.</p>',
        'homeoffice': '<h2>🏠 Homeoffice</h2>\n<p>Ich arbeite heute von zu Hause.</p>\n<p>Erreichbar per <b>[E-Mail / Telefon]</b>.</p>',
        'urlaub': '<h2>🌴 Im Urlaub</h2>\n<p>Ich bin vom <b>[Datum]</b> bis <b>[Datum]</b> im Urlaub.</p>\n<p>Vertretung: <b>[Name]</b></p>'
    };
    const textarea = document.getElementById('msg-content');
    textarea.value = templates[key] || '';
    textarea.focus();
    textarea.dispatchEvent(new Event('input'));
    document.getElementById('tpl-dropdown').classList.remove('open');
}

// Dropdowns schließen bei Klick außerhalb
document.addEventListener('click', function(e) {
    const picker = document.getElementById('emoji-picker');
    const tpl = document.getElementById('tpl-dropdown');
    if (picker && !e.target.closest('.emoji-picker') && !e.target.closest('[onclick*="toggleEmoji"]')) picker.classList.remove('open');
    if (tpl && !e.target.closest('.tpl-dropdown') && !e.target.closest('[onclick*="toggleTemplates"]')) tpl.classList.remove('open');
});

function openPreview(btn) {
    const content = btn.getAttribute('data-content');
    const modal = document.getElementById('preview-modal');
    const render = document.getElementById('modal-content-render');
    render.innerHTML = content;
    modal.style.display = 'flex';
}

function closePreview() {
    document.getElementById('preview-modal').style.display = 'none';
}

function checkDays(daysArray) {
    document.querySelectorAll('.day-cb').forEach(cb => {
        cb.checked = daysArray.includes(parseInt(cb.value));
    });
}

function filterMessages() {
    const search = document.getElementById('msg-search').value.toLowerCase().trim();
    const statusFilter = document.getElementById('msg-status-filter').value;
    const rows = document.querySelectorAll('.msg-table tbody tr');

    rows.forEach(row => {
        const title = row.getAttribute('data-title') || '';
        const status = row.getAttribute('data-status') || '';

        let show = true;

        if (search && !title.includes(search)) {
            show = false;
        }

        if (statusFilter !== 'all' && status !== statusFilter) {
            show = false;
        }

        row.style.display = show ? '' : 'none';
    });
}
</script>
</body>
</html>
