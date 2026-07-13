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
        $active_days_arr = $_POST['active_days'] ?? [];
        $active_days = is_array($active_days_arr) && !empty($active_days_arr) ? implode(',', $active_days_arr) : '';
        if ($title && $content) {
            upsert_message(0, $title, $content, $from ?: null, $until ?: null, $daily_start ?: null, $daily_end ?: null, $active_days ?: null);
            $_SESSION['flash_success'] = 'Meldung "' . htmlspecialchars($title) . '" wurde erstellt.';
        } else {
            $_SESSION['flash_error'] = 'Titel und Inhalt dürfen nicht leer sein.';
        }
        $_SESSION['scroll_position'] = (int)($_POST['scroll_position'] ?? 0);
        header('Location: ' . BASE_URL . '/admin/');
        exit;

    case 'edit':
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $from = trim($_POST['active_from'] ?? '');
        $until = trim($_POST['active_until'] ?? '');
        $daily_start = trim($_POST['daily_start'] ?? '');
        $daily_end = trim($_POST['daily_end'] ?? '');
        $active_days_arr = $_POST['active_days'] ?? [];
        $active_days = is_array($active_days_arr) && !empty($active_days_arr) ? implode(',', $active_days_arr) : '';
        if ($id && $title && $content) {
            upsert_message($id, $title, $content, $from ?: null, $until ?: null, $daily_start ?: null, $daily_end ?: null, $active_days ?: null);
            $_SESSION['flash_success'] = 'Meldung "' . htmlspecialchars($title) . '" wurde aktualisiert.';
        } else {
            $_SESSION['flash_error'] = 'Titel und Inhalt dürfen nicht leer sein.';
        }
        $_SESSION['scroll_position'] = (int)($_POST['scroll_position'] ?? 0);
        header('Location: ' . BASE_URL . '/admin/');
        exit;

    case 'delete':
        if ($id) {
            $msg = get_message($id);
            $msgTitle = $msg ? $msg['title'] : 'Unbekannt';
            delete_message($id);
            $_SESSION['flash_success'] = 'Meldung "' . htmlspecialchars($msgTitle) . '" wurde gelöscht.';
        }
        $_SESSION['scroll_position'] = (int)($_POST['scroll_position'] ?? 0);
        header('Location: ' . BASE_URL . '/admin/');
        exit;

    case 'set_default':
        if ($id) {
            $msg = get_message($id);
            $msgTitle = $msg ? $msg['title'] : 'Unbekannt';
            set_default_message($id);
            $_SESSION['flash_success'] = '"' . htmlspecialchars($msgTitle) . '" ist jetzt die Standard-Meldung.';
        }
        header('Location: ' . BASE_URL . '/admin/');
        exit;

    case 'save_branding':
        $bTitle = trim($_POST['brand_title'] ?? '');
        $bLogo = trim($_POST['brand_logo_url'] ?? '');
        if ($bTitle) {
            set_setting('brand_title', $bTitle);
        } else {
            set_setting('brand_title', 'Für dich');
        }
        if ($bLogo) {
            // Only allow http/https URLs
            if (preg_match('/^https?:\/\/.+/i', $bLogo)) {
                set_setting('brand_logo_url', $bLogo);
            }
        } else {
            set_setting('brand_logo_url', '');
        }
        $_SESSION['brand_save_msg'] = 'Branding wurde gespeichert.';
        header('Location: ' . BASE_URL . '/admin/');
        exit;

    default:
        header('Location: ' . BASE_URL . '/admin/');
        exit;
}
exit;
?>