<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/db.php';

session_start();

if (empty($_SESSION['admin'])) {
    header('Location: ' . BASE_URL . '/admin/login.php');
    exit;
}

$messages = get_all_messages();

// Remove sensitive or unnecessary data if needed, but here we want a full backup
// We might want to remove the 'id' to avoid confusion on import, but keeping it is fine for reference.

$filename = 'messages_export_' . date('Y-m-d_H-i') . '.json';

header('Content-Type: application/json; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

echo json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
exit;
