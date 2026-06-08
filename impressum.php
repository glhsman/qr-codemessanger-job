<?php
header('Content-Type: text/html; charset=UTF-8');
?><!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Impressum</title>
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
      max-width: 860px;
      margin: 0 auto;
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 14px;
      padding: 1.4rem;
      box-shadow: 0 8px 24px rgba(17, 24, 39, 0.08);
    }
    h1 { margin-top: 0; font-size: 1.6rem; }
    h2 { margin-top: 1.6rem; font-size: 1.1rem; }
    p { margin: 0.4rem 0; }
    .hint {
      background: #fffbeb;
      border: 1px solid #f59e0b;
      color: #92400e;
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
    <h1>Impressum</h1>

    <h2>Angaben gemäß § 5 TMG</h2>
    <p>[Name/Firma]</p>
    <p>[Strasse und Hausnummer]</p>
    <p>[PLZ Ort]</p>
    <p>[Land]</p>

    <h2>Vertreten durch</h2>
    <p>[Vertretungsberechtigte Person]</p>

    <h2>Kontakt</h2>
    <p>Telefon: [Telefonnummer]</p>
    <p>E-Mail: [E-Mail-Adresse]</p>

    <h2>Umsatzsteuer-ID</h2>
    <p>[Umsatzsteuer-Identifikationsnummer, falls vorhanden]</p>

    <h2>Verantwortlich für den Inhalt nach § 55 Abs. 2 RStV</h2>
    <p>[Name]</p>
    <p>[Anschrift]</p>

    <p style="margin-top:1.5rem;"><a href="scan/index.php">Zurück zur Scan-Seite</a></p>

    <div class="hint">
      Bitte alle Platzhalter vor dem Produktivbetrieb mit den korrekten Pflichtangaben ersetzen.
    </div>
  </main>
</body>
</html>
