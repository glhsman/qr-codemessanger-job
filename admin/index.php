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
$total_scans  = count_all_scans();
$today_scans  = count_today_scans();
$recent_scans = get_recent_scans(50);

// Aktuelle Zeit für Status-Anzeige
$now = new DateTimeImmutable('now');

function message_status(array $m, DateTimeImmutable $now): string
{
    if ($m['is_default']) return 'standard';
    if ($m['active_from'] && $m['active_until']) {
        $from  = new DateTimeImmutable($m['active_from']);
        $until = new DateTimeImmutable($m['active_until']);
        if ($now >= $from && $now <= $until) return 'active';
        if ($now < $from) return 'scheduled';
        return 'expired';
    }
    return 'inactive';
}
?><!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin – QR-Meldungen</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: system-ui, -apple-system, Segoe UI, sans-serif; background: #f3f4f6; color: #1f2937; min-height: 100vh; }
  header { background: linear-gradient(135deg,#667eea,#764ba2); color: #fff; padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
  header h1 { font-size: 1.2rem; font-weight: 600; }
  header a { color: rgba(255,255,255,0.8); font-size: 0.85rem; text-decoration: none; }
  header a:hover { color: #fff; }
  main { max-width: 960px; margin: 2rem auto; padding: 0 1rem; display: grid; gap: 1.5rem; }
  .card { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.07); padding: 1.5rem; }
  .card h2 { font-size: 1rem; font-weight: 600; color: #374151; margin-bottom: 1rem; padding-bottom: .5rem; border-bottom: 1px solid #e5e7eb; }
  .stats { display: flex; gap: 1rem; }
  .stat { flex: 1; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: .75rem 1rem; text-align: center; }
  .stat-val { font-size: 1.6rem; font-weight: 700; color: #667eea; }
  .stat-lbl { font-size: .75rem; color: #6b7280; margin-top: .2rem; }

  /* Meldungs-Tabelle */
  .msg-table { width: 100%; border-collapse: collapse; font-size: .875rem; }
  .msg-table th { text-align: left; padding: .5rem .75rem; color: #6b7280; font-weight: 600; border-bottom: 2px solid #e5e7eb; }
  .msg-table td { padding: .6rem .75rem; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
  .msg-table tr:hover td { background: #fafafa; }
  .badge { display: inline-block; padding: .2rem .55rem; border-radius: 100px; font-size: .7rem; font-weight: 600; letter-spacing: .4px; text-transform: uppercase; }
  .badge-standard  { background: #dbeafe; color: #1d4ed8; }
  .badge-active    { background: #d1fae5; color: #065f46; }
  .badge-scheduled { background: #fef3c7; color: #92400e; }
  .badge-expired   { background: #f3f4f6; color: #9ca3af; }
  .badge-inactive  { background: #f3f4f6; color: #9ca3af; }
  .act-btn { display: inline-block; padding: .25rem .6rem; border-radius: 6px; font-size: .78rem; font-weight: 500; text-decoration: none; border: 1px solid transparent; cursor: pointer; background: none; }
  .btn-edit    { border-color: #d1d5db; color: #374151; }
  .btn-edit:hover { background: #f9fafb; }
  .btn-std     { border-color: #bfdbfe; color: #1d4ed8; }
  .btn-std:hover { background: #eff6ff; }
  .btn-del     { border-color: #fca5a5; color: #dc2626; }
  .btn-del:hover { background: #fef2f2; }

  /* Formular */
  form label { display: block; font-size: .85rem; font-weight: 500; color: #374151; margin-bottom: .3rem; }
  form input[type=text], form input[type=datetime-local], form textarea {
    width: 100%; padding: .5rem .75rem; border: 1px solid #d1d5db; border-radius: 8px;
    font-size: .9rem; font-family: inherit; margin-bottom: .9rem; background: #f9fafb;
    transition: border-color .15s;
  }
  form input:focus, form textarea:focus { outline: none; border-color: #667eea; background: #fff; }
  form textarea { height: 140px; resize: vertical; }
  .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
  .hint { font-size: .75rem; color: #6b7280; margin-top: -.6rem; margin-bottom: .9rem; }
  .btn-primary { padding: .55rem 1.2rem; background: linear-gradient(135deg,#667eea,#764ba2); color: #fff; border: none; border-radius: 8px; font-weight: 600; font-size: .9rem; cursor: pointer; }
  .btn-primary:hover { opacity: .9; }
  .btn-cancel  { padding: .55rem 1rem; color: #6b7280; background: none; border: 1px solid #d1d5db; border-radius: 8px; font-size: .9rem; cursor: pointer; text-decoration: none; display: inline-block; margin-left: .5rem; }
  .edit-banner { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: .6rem 1rem; font-size: .85rem; color: #1d4ed8; margin-bottom: 1rem; }

  /* Scan-Tabelle */
  .scan-table { width: 100%; border-collapse: collapse; font-size: .8rem; }
  .scan-table th { text-align: left; padding: .4rem .6rem; color: #6b7280; border-bottom: 2px solid #e5e7eb; }
  .scan-table td { padding: .4rem .6rem; border-bottom: 1px solid #f3f4f6; color: #374151; }
  .ua { max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

  /* Live-Vorschau Styling (analog zu index.html) */
  .preview-container { margin-top: 1.5rem; border: 1px dashed #d1d5db; border-radius: 12px; padding: 1rem; background: #fafafa; }
  .preview-label { font-size: .75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; margin-bottom: .5rem; display: block; }
  .message-box {
    background: #f8f9fa;
    border-left: 4px solid #667eea;
    padding: 1.2rem;
    border-radius: 8px;
    line-height: 1.6;
    color: #444;
    font-size: 0.95rem;
  }
  .message-box strong {
    color: #667eea;
    display: block;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  /* Toolbar Styling */
  .toolbar { display: flex; gap: 0.5rem; background: #f3f4f6; padding: 0.5rem; border: 1px solid #d1d5db; border-bottom: none; border-radius: 8px 8px 0 0; margin-bottom: 0; }
  .toolbar-btn {
    background: #fff; border: 1px solid #d1d5db; border-radius: 4px; padding: 0.3rem 0.6rem;
    font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s;
    display: flex; align-items: center; justify-content: center; min-width: 32px;
  }
  .toolbar-btn:hover { background: #eff6ff; border-color: #667eea; color: #667eea; }
  .toolbar-btn i { font-style: normal; }
  #msg-content { border-top-left-radius: 0; border-top-right-radius: 0; }

  /* Modal Styling */
  .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 1rem; }
  .modal-card { background: #fff; width: 100%; max-width: 500px; border-radius: 16px; padding: 2rem; position: relative; animation: slideUp 0.3s ease-out; }
  .modal-close { position: absolute; top: 1rem; right: 1rem; cursor: pointer; font-size: 1.5rem; color: #9ca3af; border: none; background: none; }
  .modal-close:hover { color: #374151; }
  @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>
</head>
<body>
<header>
  <h1>📣 QR Admin</h1>
  <a href="logout.php">Abmelden</a>
</header>
<main>

  <!-- Statistiken -->
  <div class="card">
    <h2>Statistiken</h2>
    <div class="stats">
      <div class="stat"><div class="stat-val"><?= $total_scans ?></div><div class="stat-lbl">Scans gesamt</div></div>
      <div class="stat"><div class="stat-val"><?= $today_scans ?></div><div class="stat-lbl">Scans heute</div></div>
      <div class="stat"><div class="stat-val"><?= count($messages) ?></div><div class="stat-lbl">Meldungen</div></div>
    </div>
  </div>

  <!-- Meldungsverwaltung -->
  <div class="card">
    <h2>Meldungen verwalten</h2>
    <?php if (!empty($messages)): ?>
    <table class="msg-table">
      <thead><tr><th>Titel</th><th>Status</th><th>Zeitraum</th><th>Aktionen</th></tr></thead>
      <tbody>
      <?php foreach ($messages as $m):
        $st = message_status($m, $now);
      ?>
      <tr>
        <td><?= htmlspecialchars($m['title']) ?></td>
        <td>
          <span class="badge badge-<?= $st ?>">
            <?= ['standard'=>'Standard','active'=>'Aktiv','scheduled'=>'Geplant','expired'=>'Abgelaufen','inactive'=>'Inaktiv'][$st] ?>
          </span>
        </td>
        <td style="font-size:.78rem;color:#6b7280">
          <?php if ($m['active_from'] && $m['active_until']): ?>
            <?= date('d.m.Y H:i', strtotime($m['active_from'])) ?> –<br>
            <?= date('d.m.Y H:i', strtotime($m['active_until'])) ?>
          <?php else: echo '—'; endif; ?>
        </td>
        <td>
          <button class="act-btn btn-edit" style="border-color:#d1d5db;color:#374151" 
                  onclick="openPreview(this)" 
                  data-content="<?= htmlspecialchars($m['content']) ?>">Vorschau</button>
          <a class="act-btn btn-edit" href="?edit=<?= $m['id'] ?>">Bearbeiten</a>
          <?php if (!$m['is_default']): ?>
            <form method="post" action="save.php" style="display:inline">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
              <input type="hidden" name="action" value="set_default">
              <input type="hidden" name="id" value="<?= $m['id'] ?>">
              <button class="act-btn btn-std" type="submit">Als Standard</button>
            </form>
            <form method="post" action="save.php" style="display:inline" onsubmit="return confirm('Meldung löschen?')">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $m['id'] ?>">
              <button class="act-btn btn-del" type="submit">Löschen</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <!-- Formular: Hinzufügen / Bearbeiten -->
  <div class="card">
    <h2><?= $editing ? 'Meldung bearbeiten' : 'Neue Meldung' ?></h2>
    <?php if ($editing): ?>
    <div class="edit-banner">✏️ Sie bearbeiten: <strong><?= htmlspecialchars($editing['title']) ?></strong></div>
    <?php endif; ?>
    <form method="post" action="save.php">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
      <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
      <?php if ($editing): ?>
      <input type="hidden" name="id" value="<?= $editing['id'] ?>">
      <?php endif; ?>

      <label>Interner Titel (nur im Adminbereich sichtbar)</label>
      <input type="text" name="title" required maxlength="255"
             value="<?= $editing ? htmlspecialchars($editing['title']) : '' ?>">

      <label>Meldungstext (einfache HTML-Tags erlaubt: &lt;b&gt;, &lt;i&gt;, &lt;a href="…"&gt;, &lt;p&gt;, &lt;br&gt;)</label>
      <div class="toolbar">
        <button type="button" class="toolbar-btn" onclick="insertTag('<h1>', '</h1>')" title="Überschrift 1">H1</button>
        <button type="button" class="toolbar-btn" onclick="insertTag('<h2>', '</h2>')" title="Überschrift 2">H2</button>
        <button type="button" class="toolbar-btn" onclick="insertTag('<b>', '</b>')" title="Fett"><b>B</b></button>
        <button type="button" class="toolbar-btn" onclick="insertTag('<i>', '</i>')" title="Kursiv"><i>I</i></button>
        <button type="button" class="toolbar-btn" onclick="insertLink()" title="Link">🔗</button>
        <button type="button" class="toolbar-btn" onclick="insertTag('<p>', '</p>')" title="Absatz">¶</button>
        <button type="button" class="toolbar-btn" onclick="insertTag('<br>', '')" title="Zeilenumbruch">↵</button>
      </div>
      <textarea name="content" id="msg-content" required><?= $editing ? htmlspecialchars($editing['content']) : '' ?></textarea>

      <div class="preview-container">
        <span class="preview-label">Live-Vorschau</span>
        <div class="message-box">
          <strong>📣 Nachricht</strong>
          <div id="preview-render"><?= $editing ? $editing['content'] : 'Ihre Nachricht erscheint hier...' ?></div>
        </div>
      </div>

      <div class="form-row">
        <div>
          <label>Aktiv ab (optional)</label>
          <input type="datetime-local" name="active_from"
                 value="<?= $editing && $editing['active_from'] ? date('Y-m-d\TH:i', strtotime($editing['active_from'])) : '' ?>">
        </div>
        <div>
          <label>Aktiv bis (optional)</label>
          <input type="datetime-local" name="active_until"
                 value="<?= $editing && $editing['active_until'] ? date('Y-m-d\TH:i', strtotime($editing['active_until'])) : '' ?>">
        </div>
      </div>
      <p class="hint">Ohne Zeitraum: Meldung ist inaktiv, bis Sie sie als Standard setzen.</p>

      <button class="btn-primary" type="submit"><?= $editing ? 'Speichern' : 'Hinzufügen' ?></button>
      <?php if ($editing): ?>
      <a class="btn-cancel" href="index.php">Abbrechen</a>
      <?php endif; ?>
    </form>
  </div>

  <!-- Letzte Scans -->
  <div class="card">
    <h2>Letzte Scans</h2>
    <table class="scan-table">
      <thead><tr><th>Zeitstempel</th><th>IP-Adresse</th><th>User Agent</th></tr></thead>
      <tbody>
      <?php foreach ($recent_scans as $s): ?>
      <tr>
        <td><?= htmlspecialchars($s['ts']) ?></td>
        <td><?= htmlspecialchars($s['ip'] ?? '—') ?></td>
        <td class="ua" title="<?= htmlspecialchars($s['user_agent'] ?? '') ?>">
          <?= htmlspecialchars(mb_substr($s['user_agent'] ?? '—', 0, 80)) ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Modal für Listen-Vorschau -->
  <div id="preview-modal" class="modal-overlay" onclick="if(event.target==this) closePreview()">
    <div class="modal-card">
      <button class="modal-close" onclick="closePreview()">&times;</button>
      <h2 style="margin-bottom:1.5rem;border:none">Vorschau</h2>
      <div class="message-box">
        <strong>📣 Nachricht</strong>
        <div id="modal-content-render"></div>
      </div>
      <button class="btn-primary" style="width:100%;margin-top:1rem" onclick="closePreview()">Schließen</button>
    </div>
  </div>

</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const textarea = document.getElementById('msg-content');
    const preview = document.getElementById('preview-render');
    
    if (textarea && preview) {
        textarea.addEventListener('input', () => {
            const val = textarea.value.trim();
            if (val === '') {
                preview.innerHTML = '<i>Ihre Nachricht erscheint hier...</i>';
            } else {
                preview.innerHTML = val;
            }
        });
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

function insertLink() {
    const url = prompt('URL eingeben:', 'https://');
    if (url && url !== 'https://') {
        insertTag('<a href="' + url + '" target="_blank">', '</a>');
    }
}

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
</script>
</body>
</html>