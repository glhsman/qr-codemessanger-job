<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/db.php';

session_start();

// Authentifizierung
if (empty($_SESSION['admin'])) {
    header('Location: ' . BASE_URL . '/admin/login.php');
    exit;
}

// CSRF prüfen
if (($_POST['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
    http_response_code(403);
    die('Ungültiger CSRF-Token.');
}

$action = $_POST['action'] ?? '';
$id = (int)($_POST['id'] ?? 0);

switch ($action) {
    case 'add':
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $from = trim($_POST['active_from'] ?? '');
        $until = trim($_POST['active_until'] ?? '');
        $daily_start = trim($_POST['daily_start'] ?? '');
        $daily_end = trim($_POST['daily_end'] ?? '');
        if ($title && $content) {
            upsert_message(0, $title, $content, $from ?: null, $until ?: null, $daily_start ?: null, $daily_end ?: null);
        }
        header('Location: ' . BASE_URL . '/admin/');
        break;

    case 'edit':
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $from = trim($_POST['active_from'] ?? '');
        $until = trim($_POST['active_until'] ?? '');
        $daily_start = trim($_POST['daily_start'] ?? '');
        $daily_end = trim($_POST['daily_end'] ?? '');
        if ($id && $title && $content) {
            upsert_message($id, $title, $content, $from ?: null, $until ?: null, $daily_start ?: null, $daily_end ?: null);
        }
        header('Location: ' . BASE_URL . '/admin/');
        break;

    case 'delete':
        if ($id)
            delete_message($id);
        header('Location: ' . BASE_URL . '/admin/');
        break;

    case 'set_default':
        if ($id)
            set_default_message($id);
        header('Location: ' . BASE_URL . '/admin/');
        break;

    default:
        header('Location: ' . BASE_URL . '/admin/');
}
exit;
?>