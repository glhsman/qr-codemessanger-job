<?php
$path = __DIR__ . '/../credentials.json';
echo '<pre>';
echo 'Erwarteter Pfad: ' . realpath($path) . "\n";
echo 'Datei existiert: ' . (file_exists($path) ? 'JA' : 'NEIN') . "\n";
if (file_exists($path)) {
    $raw = file_get_contents($path);
    $parsed = json_decode($raw, true);
    echo 'JSON gültig: ' . ($parsed !== null ? 'JA' : 'NEIN') . "\n";
    echo 'JSON-Fehler: ' . json_last_error_msg() . "\n";
    echo 'DB_HOST: ' . ($parsed['DB_HOST'] ?? '(nicht gefunden)') . "\n";
}
echo '</pre>';
?>
