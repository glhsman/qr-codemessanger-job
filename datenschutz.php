<?php
require_once __DIR__ . '/config.php';
header('Content-Type: text/html; charset=UTF-8');
?><!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Datenschutzerklärung</title>
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
      background: #f3f4f6;
      color: #111827;
      line-height: 1.6;
      padding: 1.25rem;
    }
    .card {
      max-width: 920px;
      margin: 0 auto;
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 14px;
      padding: 1.4rem;
      box-shadow: 0 8px 24px rgba(17, 24, 39, 0.08);
    }
    h1 { margin-top: 0; font-size: 1.6rem; }
    h2 { margin-top: 1.6rem; font-size: 1.1rem; }
    p { margin: 0.45rem 0; }
    ul { margin: 0.4rem 0 0.4rem 1.2rem; }
    .hint {
      background: #eff6ff;
      border: 1px solid #60a5fa;
      color: #1e3a8a;
      border-radius: 10px;
      padding: 0.8rem;
      margin-top: 1.2rem;
      font-size: 0.92rem;
    }
    a { color: #1d4ed8; }
  </style>
</head>
<body>
  <main class="card">
    <h1>Datenschutzerklärung</h1>

    <h2>1. Verantwortliche Stelle</h2>
    <p>Frank Schönbrodt</p>
    <p>44534 Lünen</p>
    <p>E-Mail: info@schoenbrodt-ruehl.de</p>

    <h2>2. Zweck und Rechtsgrundlage der Verarbeitung</h2>
    <p>Diese Website zeigt Meldungen nach dem Scan eines QR-Codes an. Für die Analyse der Nutzung können Scan-Daten nur mit ausdrücklicher Einwilligung gespeichert werden.</p>
    <p>Rechtsgrundlage für die Speicherung der vollen IP-Adresse ist Ihre Einwilligung gemäß Art. 6 Abs. 1 lit. a DSGVO.</p>

    <h2>3. Welche Daten werden gespeichert</h2>
    <ul>
      <li>IP-Adresse (nur bei Einwilligung)</li>
      <li>Zeitpunkt des Scans</li>
      <li>Browserkennung (User-Agent)</li>
      <li>Referrer (sofern vom Browser uebermittelt)</li>
    </ul>

    <h2>4. Speicherdauer</h2>
    <p>Scan-Daten werden automatisiert nach <?= (int)SCAN_RETENTION_DAYS ?> Tagen gelöscht.</p>

    <h2>5. Cookies und Einwilligung</h2>
    <p>Zur Speicherung Ihrer Entscheidung wird ein technisch erforderliches Cookie gesetzt (Einwilligung erteilt/abgelehnt). Dieses Cookie hat eine Laufzeit von bis zu 180 Tagen.</p>

    <h2>6. Widerruf der Einwilligung</h2>
    <p>Sie können Ihre Einwilligung jederzeit mit Wirkung für die Zukunft widerrufen, indem Sie Ihre Cookie-Einstellungen löschen und die Auswahl erneut treffen.</p>

    <h2>7. Betroffenenrechte</h2>
    <p>Sie haben insbesondere das Recht auf Auskunft, Berichtigung, Löschung, Einschränkung der Verarbeitung und Beschwerde bei einer Aufsichtsbehörde.</p>

    <p style="margin-top:1.5rem;"><a href="scan/index.php">Zurück zur Scan-Seite</a></p>
  </main>
</body>
</html>
