<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/sanitize.php';

// Auto-migrate: ensure schema exists
ensure_schema();

$consentCookieName = defined('SCAN_CONSENT_COOKIE') ? SCAN_CONSENT_COOKIE : 'scan_ip_consent';
$consentAction = $_GET['consent'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['scan_consent'])) {
    $decision = $_POST['scan_consent'] === 'accept' ? '1' : '0';
    setcookie($consentCookieName, $decision, [
        'expires' => time() + (60 * 60 * 24 * 180),
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => false,
        'samesite' => 'Lax'
    ]);
    header('Location: ' . strtok($_SERVER['REQUEST_URI'] ?? '/scan/index.php', '?'));
    exit;
}

$hasConsentDecision = isset($_COOKIE[$consentCookieName]);
$hasIpConsent = $hasConsentDecision && $_COOKIE[$consentCookieName] === '1';
$showConsentBanner = !$hasConsentDecision || $consentAction === 'change';

// Löschfrist für Scan-Logs erzwingen
purge_old_scans();

// log scan
$ip = $_SERVER['REMOTE_ADDR'] ?? null;
$ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
$ref = $_SERVER['HTTP_REFERER'] ?? null;
if ($hasIpConsent) {
  log_scan($ip, $ua, $ref);
}

// Aktive Meldung laden (zeitgesteuert oder Standard)
$msg = get_active_message();
$msgContent = $msg['content'];
$msgCreatedAt = $msg['created_at'] ?? null;

// Branding laden
$brandTitle = get_setting('brand_title');
$brandLogoUrl = null;
// Lokales Logo bevorzugen (admin/logo/logo.*)
foreach (['png', 'jpg', 'svg', 'webp'] as $_ext) {
    if (file_exists(__DIR__ . '/../admin/logo/logo.' . $_ext)) {
        $brandLogoUrl = '../admin/logo/logo.' . $_ext . '?v=' . filemtime(__DIR__ . '/../admin/logo/logo.' . $_ext);
        break;
    }
}
// Fallback: URL aus Settings
if (!$brandLogoUrl) {
    $brandLogoUrl = get_setting('brand_logo_url');
}
if (!$brandTitle) {
    $brandTitle = 'Für dich';
}

// output
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-cache, max-age=60');
?><!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>Willkommen</title>
<?php
foreach (["ico","png","jpg","svg"] as $ext) {
  if (file_exists(__DIR__ . "/../admin/favicon/favicon.$ext")) {
    echo '<link rel="icon" type="image/' . ($ext === 'ico' ? 'x-icon' : $ext) . '" href="../admin/favicon/favicon.' . $ext . '">';
    break;
  }
}
?>
<style>
  *, *::before, *::after {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    background-attachment: fixed;
  }

  body.has-consent-banner {
    padding-bottom: calc(1.5rem + 140px);
  }

  .container {
    width: 100%;
    max-width: 560px;
    animation: slideUp 0.6s ease-out both;
  }



  /* Card */
  .card {
    background: rgba(255,255,255,0.97);
    border-radius: 20px;
    box-shadow: 0 24px 64px rgba(0,0,0,0.18);
    overflow: hidden;
  }

  .card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 1.4rem 2rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  .badge {
    background: rgba(255,255,255,0.2);
    border: 1px solid rgba(255,255,255,0.4);
    color: white;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    padding: 0.25rem 0.65rem;
    border-radius: 100px;
  }

  .card-header h1 {
    color: white;
    font-size: 1.1rem;
    font-weight: 600;
    opacity: 0.92;
  }

  .card-body {
    padding: 2rem 2rem 2.2rem;
  }

  .message {
    font-size: 1.25rem;
    font-weight: 400;
    line-height: 1.7;
    color: #2d2d2d;
    letter-spacing: -0.01em;
  }

  .message strong {
    font-weight: 700;
    color: #667eea;
  }

  .divider {
    height: 1px;
    background: linear-gradient(90deg, #667eea33, #764ba233, transparent);
    margin: 1.6rem 0 1.2rem;
  }

  .footer-note {
    font-size: 0.78rem;
    color: #aaa;
    display: flex;
    align-items: center;
    gap: 0.4rem;
  }

  .dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #667eea;
    animation: pulse 2s infinite;
  }

  .legal-links {
    margin-top: 1.2rem;
    display: flex;
    justify-content: center;
    gap: 1rem;
    font-size: 0.86rem;
    flex-wrap: wrap;
  }

  .legal-links a {
    color: #4f46e5;
    text-decoration: none;
    font-weight: 600;
  }

  .legal-links a:hover {
    text-decoration: underline;
  }

  .consent-state {
    width: 100%;
    text-align: center;
    color: #475569;
    font-size: 0.82rem;
    margin-top: 0.1rem;
  }

  .consent-state a {
    color: #4f46e5;
    font-weight: 700;
  }

  .consent-banner {
    position: fixed;
    left: 1rem;
    right: 1rem;
    bottom: 1rem;
    background: rgba(17, 24, 39, 0.96);
    color: #f9fafb;
    border-radius: 14px;
    padding: 1rem;
    box-shadow: 0 16px 32px rgba(0, 0, 0, 0.3);
    z-index: 20;
  }

  .consent-banner p {
    font-size: 0.9rem;
    line-height: 1.5;
    margin-bottom: 0.8rem;
  }

  .consent-banner a {
    color: #bfdbfe;
  }

  .consent-actions {
    display: flex;
    gap: 0.6rem;
    flex-wrap: wrap;
  }

  .consent-actions button {
    border: 1px solid transparent;
    border-radius: 8px;
    padding: 0.55rem 0.85rem;
    font-weight: 700;
    cursor: pointer;
  }

  .consent-accept {
    background: #22c55e;
    color: #052e16;
  }

  .consent-decline {
    background: #111827;
    color: #f9fafb;
    border-color: #4b5563 !important;
  }

  @keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: 0.5; transform: scale(0.8); }
  }

  @keyframes slideUp {
    from { opacity: 0; transform: translateY(28px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  @media (max-width: 480px) {
    .card-body { padding: 1.5rem 1.4rem 1.8rem; }
    .message   { font-size: 1.1rem; }
    .consent-banner {
      left: 0.5rem;
      right: 0.5rem;
      bottom: 0.5rem;
      padding: 0.8rem;
    }
    .consent-banner p { font-size: 0.82rem; }
    .consent-actions button { padding: 0.5rem 0.65rem; font-size: 0.85rem; }
  }
</style>
</head>
<body>
<div class="container">



  <div class="card">
    <div class="card-header">
      <?php if ($brandLogoUrl): ?>
      <img src="<?= htmlspecialchars($brandLogoUrl) ?>" alt="" style="width:28px;height:28px;border-radius:6px;object-fit:cover;background:#fff">
      <?php endif; ?>
      <span class="badge">📣 Nachricht</span>
      <h1><?= htmlspecialchars($brandTitle) ?></h1>
    </div>
    <div class="card-body">
      <div class="message">
        <?php
if (defined('ALLOW_HTML_WHITELIST') && ALLOW_HTML_WHITELIST) {
  echo sanitize_html_whitelist($msgContent);
}
else {
  echo nl2br(htmlspecialchars($msgContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
}
?>
      </div>
      <?php if ($msgCreatedAt): ?>
      <div style="text-align:center; margin-top:1rem; font-size:0.75rem; color:#bbb;">
        Aktualisiert am <?= date('d.m.Y H:i', strtotime($msgCreatedAt)) ?> Uhr
      </div>
      <?php endif; ?>
      <div class="legal-links">
        <a href="../datenschutz.php" rel="noopener">Datenschutz</a>
        <a href="../impressum.php" rel="noopener">Impressum</a>
        <a href="?consent=change">Einwilligung ändern</a>
        <div class="consent-state">
          <?php if ($hasConsentDecision): ?>
            Status: <?= $hasIpConsent ? 'Einwilligung erteilt' : 'Einwilligung abgelehnt' ?>
          <?php else: ?>
            Status: Noch keine Entscheidung gespeichert
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

</div>
<?php if ($showConsentBanner): ?>
  <script>document.body.classList.add('has-consent-banner');</script>
  <div class="consent-banner" role="dialog" aria-live="polite" aria-label="Einwilligung zur IP-Speicherung">
    <p>
      Wir speichern Ihre IP-Adresse ausschließlich mit Ihrer Einwilligung zur Reichweitenanalyse und löschen Scan-Daten automatisch nach <?= (int)SCAN_RETENTION_DAYS ?> Tagen.
      Details finden Sie in unserer <a href="../datenschutz.php" target="_blank" rel="noopener">Datenschutzerklärung</a>.
    </p>
    <form method="post" class="consent-actions">
      <button class="consent-accept" type="submit" name="scan_consent" value="accept">Einwilligen</button>
      <button class="consent-decline" type="submit" name="scan_consent" value="decline">Ablehnen</button>
    </form>
  </div>
<?php endif; ?>
</body>
</html>
