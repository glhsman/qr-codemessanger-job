<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/sanitize.php';

session_start();

if (empty($_SESSION['admin'])) {
    header('Location: ' . BASE_URL . '/admin/login.php');
    exit;
}

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Ungültiger CSRF-Token.";
    }
    elseif (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
        $error = "Fehler beim Datei-Upload.";
    }
    else {
        $data = file_get_contents($_FILES['import_file']['tmp_name']);
        $messages = json_decode($data, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = "Ungültiges JSON-Format: " . json_last_error_msg();
        }
        elseif (!is_array($messages)) {
            $error = "JSON muss ein Array von Meldungen enthalten.";
        }
        else {
            $count = 0;
            foreach ($messages as $m) {
                // Basic validation: must have title and content
                if (empty($m['title']) || !isset($m['content']))
                    continue;

                // Import as new message (id=0) to avoid ID conflicts
                try {
                    upsert_message(
                        0,
                        $m['title'],
                        $m['content'],
                        $m['active_from'] ?? null,
                        $m['active_until'] ?? null,
                        $m['daily_start'] ?? null,
                        $m['daily_end'] ?? null
                    );
                    $count++;
                }
                catch (Exception $e) {
                // Skip or log error
                }
            }
            $success = "$count Meldungen erfolgreich importiert.";
        }
    }
}

// Store message in session and redirect back
if ($error)
    $_SESSION['import_error'] = $error;
if ($success)
    $_SESSION['import_success'] = $success;

header('Location: index.php');
exit;
