<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/sanitize.php';

// Auto-migrate: ensure schema exists
ensure_schema();

// log scan
$ip = $_SERVER['REMOTE_ADDR'] ?? null;
$ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
$ref = $_SERVER['HTTP_REFERER'] ?? null;
log_scan($ip, $ua, $ref);

// Aktive Meldung laden (zeitgesteuert oder Standard)
$msg = get_active_message();

// output
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-cache, max-age=60');
?><!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>Willkommen</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    background-attachment: fixed;
  }

  .container {
    width: 100%;
    max-width: 560px;
    animation: slideUp 0.6s ease-out both;
  }

  /* Logo / Icon */
  .logo-wrap {
    display: flex;
    justify-content: center;
    margin-bottom: 1.8rem;
  }

  .logo {
    width: 72px;
    height: 72px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255,255,255,0.35);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 32px rgba(0,0,0,0.15);
  }

  .logo svg {
    width: 38px;
    height: 38px;
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
  }
</style>
</head>
<body>
<div class="container">

  <div class="logo-wrap">
    <div class="logo">
      <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="18" y="18" width="22" height="22" rx="4" fill="white"/>
        <rect x="60" y="18" width="22" height="22" rx="4" fill="white"/>
        <rect x="18" y="60" width="22" height="22" rx="4" fill="white"/>
        <rect x="44" y="44" width="12" height="12" rx="2" fill="white" opacity="0.7"/>
        <rect x="60" y="60" width="22" height="22" rx="4" fill="white"/>
      </svg>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <span class="badge">📣 Nachricht</span>
      <h1>Für dich</h1>
    </div>
    <div class="card-body">
      <div class="message">
        <?php
if (defined('ALLOW_HTML_WHITELIST') && ALLOW_HTML_WHITELIST) {
  echo sanitize_html_whitelist($msg);
}
else {
  echo nl2br(htmlspecialchars($msg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
}
?>
      </div>

    </div>
  </div>

</div>
</body>
</html>