<?php
// NACH GEBRAUCH SOFORT LÖSCHEN!
$credPath = realpath(__DIR__ . '/../../credentials.json');
$saved = false;
$newHash = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['pw'])) {
    $pw = $_POST['pw'];
    $newHash = password_hash($pw, PASSWORD_DEFAULT);

    $creds = json_decode(file_get_contents($credPath), true);
    $creds['ADMIN_PASS_HASH'] = $newHash;
    file_put_contents($credPath, json_encode($creds, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $saved = true;
}
?><!doctype html>
<html lang="de">
<head><meta charset="utf-8"><title>Admin Passwort setzen</title></head>
<body>
<h2>Admin-Passwort neu setzen</h2>
<?php if ($saved): ?>
<p style="color:green">✓ Hash gespeichert! <a href="login.php">Zum Login</a></p>
<pre>Hash: <?php echo htmlspecialchars($newHash); ?></pre>
<p><strong>Diese Datei jetzt löschen!</strong></p>
<?php
else: ?>
<form method="post">
<label>Neues Passwort: <input type="text" name="pw" required></label>
<button type="submit">Hash generieren &amp; speichern</button>
</form>
<p style="color:#888">Pfad zu credentials.json: <?php echo htmlspecialchars($credPath); ?></p>
<?php
endif; ?>
</body>
</html>
