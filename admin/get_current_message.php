<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/sanitize.php';

session_start();

// Auth check
if (empty($_SESSION['admin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

header('Content-Type: application/json');

// Get active message
$pdo = get_pdo();

// 1. Zeitgesteuerte Meldung
$stmt = $pdo->prepare(
    "SELECT id, title, content, active_from, active_until, daily_start, daily_end, active_days, is_default 
     FROM `messages`
     WHERE (`active_from` IS NULL OR `active_from` <= NOW())
       AND (`active_until` IS NULL OR `active_until` >= NOW())
       AND (
         (`daily_start` IS NULL OR `daily_end` IS NULL)
         OR
         (
           (`daily_start` <= `daily_end` AND CURTIME() BETWEEN `daily_start` AND `daily_end`)
           OR
           (`daily_start` > `daily_end` AND (CURTIME() >= `daily_start` OR CURTIME() <= `daily_end`))
         )
       )
       AND (
         `active_days` IS NULL
         OR `active_days` = ''
         OR FIND_IN_SET(WEEKDAY(NOW()) + 1, `active_days`) > 0
         OR (
           `daily_start` > `daily_end`
           AND CURTIME() <= `daily_end`
           AND FIND_IN_SET((WEEKDAY(NOW()) + 6) % 7 + 1, `active_days`) > 0
         )
       )
       AND `is_default` = 0
       AND (`active_from` IS NOT NULL OR `daily_start` IS NOT NULL OR (`active_days` IS NOT NULL AND `active_days` != ''))
     ORDER BY `active_from` DESC, `id` DESC
     LIMIT 1"
);
$stmt->execute();
$row = $stmt->fetch();

if (!$row) {
    // 2. Standard-Meldung
    $stmt = $pdo->query("SELECT id, title, content, is_default FROM `messages` WHERE `is_default` = 1 LIMIT 1");
    $row = $stmt->fetch();
}

if ($row) {
    // Sanitize content if needed (analog to index.php)
    if (defined('ALLOW_HTML_WHITELIST') && ALLOW_HTML_WHITELIST) {
        $row['content_html'] = sanitize_html_whitelist($row['content']);
    }
    else {
        $row['content_html'] = nl2br(htmlspecialchars($row['content'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
    }
    echo json_encode($row);
}
else {
    echo json_encode([
        'title' => 'Standard',
        'content' => 'Hallo! Hier ist deine Standard-Nachricht.',
        'content_html' => 'Hallo! Hier ist deine Standard-Nachricht.',
        'is_default' => 1
    ]);
}
