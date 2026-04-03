<?php
// NACH GEBRAUCH SOFORT LÖSCHEN!
require_once __DIR__ . '/config.php';

$passwort = 'HIER_DEIN_PASSWORT'; // ← anpassen

echo '<pre>';
$hash = ADMIN_PASS_HASH;
echo 'ADMIN_PASS_HASH aus config: [' . htmlspecialchars($hash) . "]\n";
echo 'Hash leer: ' . (empty($hash) ? 'JA ← das ist das Problem!' : 'Nein') . "\n";
echo 'Hash-Länge: ' . strlen($hash) . " Zeichen\n";
echo 'password_verify: ' . (password_verify($passwort, $hash) ? 'OK ✓' : 'FEHLGESCHLAGEN ✗') . "\n";

// Neuen Hash generieren zum Vergleich
$neuerHash = password_hash($passwort, PASSWORD_DEFAULT);
echo "\nNeu generierter Hash:\n" . $neuerHash . "\n";
echo '</pre>';
?>
