<?php
require_once __DIR__ . '/../config.php';

function get_pdo(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $opts = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
    }
    return $pdo;
}

function ensure_schema(): void
{
    $pdo = get_pdo();

    // Legacy settings-Tabelle (bleibt für Abwärtskompatibilität)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `settings` (
      `key` VARCHAR(191) NOT NULL,
      `value` TEXT NOT NULL,
      PRIMARY KEY (`key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Scan-Log-Tabelle
    $pdo->exec("CREATE TABLE IF NOT EXISTS `scans` (
      `id` INT NOT NULL AUTO_INCREMENT,
      `ts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `ip` VARCHAR(45) DEFAULT NULL,
      `user_agent` TEXT DEFAULT NULL,
      `referrer` TEXT DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Neue Meldungs-Tabelle
    $pdo->exec("CREATE TABLE IF NOT EXISTS `messages` (
      `id` INT NOT NULL AUTO_INCREMENT,
      `title` VARCHAR(255) NOT NULL,
      `content` TEXT NOT NULL,
      `active_from` DATETIME DEFAULT NULL,
      `active_until` DATETIME DEFAULT NULL,
      `daily_start` TIME DEFAULT NULL,
      `daily_end` TIME DEFAULT NULL,
      `is_default` TINYINT(1) NOT NULL DEFAULT 0,
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Migration: neue Spalten hinzufügen falls sie fehlen
    try {
        $pdo->exec("ALTER TABLE `messages` ADD COLUMN `daily_start` TIME DEFAULT NULL AFTER `active_until`, ADD COLUMN `daily_end` TIME DEFAULT NULL AFTER `daily_start` ");
    }
    catch (Exception $e) {
    // Spalten existieren wohl schon
    }

    // Migration: bestehende landing_message aus settings als Default übernehmen
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM `messages`");
    if ((int)$stmt->fetch()['cnt'] === 0) {
        $legacy = $pdo->query("SELECT `value` FROM `settings` WHERE `key` = 'landing_message' LIMIT 1")->fetch();
        $content = $legacy ? $legacy['value'] : 'Hallo! Scanne erfolgreich. Passe diese Nachricht im Adminbereich an.';
        $pdo->prepare("INSERT INTO `messages` (`title`, `content`, `is_default`) VALUES ('Standard-Meldung', :c, 1)")
            ->execute([':c' => $content]);
    }
}

// --- Meldungs-Abfrage für die Scan-Seite ---

function get_active_message(): string
{
    $pdo = get_pdo();

    // 1. Zeitgesteuerte Meldung: active_from <= NOW() <= active_until
    $stmt = $pdo->prepare(
        "SELECT `content` FROM `messages`
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
           AND `is_default` = 0
           AND (`active_from` IS NOT NULL OR `daily_start` IS NOT NULL)
         ORDER BY `active_from` DESC, `id` DESC
         LIMIT 1"
    );
    $stmt->execute();
    $row = $stmt->fetch();
    if ($row)
        return $row['content'];

    // 2. Standard-Meldung (is_default = 1)
    $stmt = $pdo->query("SELECT `content` FROM `messages` WHERE `is_default` = 1 LIMIT 1");
    $row = $stmt->fetch();
    if ($row)
        return $row['content'];

    // 3. Hardcodierter Fallback
    return 'Hallo! Hier ist deine Standard-Nachricht.';
}

// --- CRUD für den Adminbereich ---

function get_all_messages(): array
{
    $pdo = get_pdo();
    return $pdo->query(
        "SELECT id, title, content, active_from, active_until, daily_start, daily_end, is_default, created_at
         FROM `messages` ORDER BY is_default DESC, created_at DESC"
    )->fetchAll();
}

function get_message(int $id): ?array
{
    $pdo = get_pdo();
    $stmt = $pdo->prepare("SELECT * FROM `messages` WHERE `id` = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function upsert_message(int $id, string $title, string $content, ?string $activeFrom, ?string $activeUntil, ?string $dailyStart = null, ?string $dailyEnd = null): void
{
    $pdo = get_pdo();
    $stmt = null;
    $params = [
        ':t' => $title,
        ':c' => $content,
        ':af' => $activeFrom ?: null,
        ':au' => $activeUntil ?: null,
        ':ds' => $dailyStart ?: null,
        ':de' => $dailyEnd ?: null
    ];

    if ($id === 0) {
        $stmt = $pdo->prepare(
            "INSERT INTO `messages` (`title`, `content`, `active_from`, `active_until`, `daily_start`, `daily_end`)
             VALUES (:t, :c, :af, :au, :ds, :de)"
        );
    }
    else {
        $stmt = $pdo->prepare(
            "UPDATE `messages` SET `title`=:t, `content`=:c, `active_from`=:af, `active_until`=:au, `daily_start`=:ds, `daily_end`=:de
             WHERE `id`=:id"
        );
        $params[':id'] = $id;
    }
    $stmt->execute($params);
}

function delete_message(int $id): void
{
    $pdo = get_pdo();
    $pdo->prepare("DELETE FROM `messages` WHERE `id` = :id")->execute([':id' => $id]);
}

function set_default_message(int $id): void
{
    $pdo = get_pdo();
    $pdo->exec("UPDATE `messages` SET `is_default` = 0");
    $pdo->prepare("UPDATE `messages` SET `is_default` = 1 WHERE `id` = :id")->execute([':id' => $id]);
}

// --- Scan-Logging ---

function log_scan(?string $ip, ?string $ua, ?string $ref): bool
{
    if (ANONYMIZE_IP && $ip) {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            $parts[3] = '0';
            $ip = implode('.', $parts);
        }
        else {
            $parts = explode(':', $ip);
            $ip = implode(':', array_slice($parts, 0, 4)) . '::';
        }
    }
    $pdo = get_pdo();
    $stmt = $pdo->prepare('INSERT INTO `scans` (`ip`, `user_agent`, `referrer`) VALUES (:ip, :ua, :ref)');
    return $stmt->execute([':ip' => $ip, ':ua' => $ua, ':ref' => $ref]);
}

function get_recent_scans(int $limit = 50): array
{
    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT id, ts, ip, user_agent, referrer FROM `scans` ORDER BY ts DESC LIMIT :l');
    $stmt->bindValue(':l', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function count_all_scans(): int
{
    $pdo = get_pdo();
    return (int)($pdo->query('SELECT COUNT(*) as cnt FROM `scans`')->fetch()['cnt'] ?? 0);
}

function count_today_scans(): int
{
    $pdo = get_pdo();
    return (int)($pdo->query("SELECT COUNT(*) as cnt FROM `scans` WHERE DATE(ts) = CURDATE()")->fetch()['cnt'] ?? 0);
}

// Legacy-Kompatibilität
function get_setting(string $key): ?string
{
    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT `value` FROM `settings` WHERE `key` = :k LIMIT 1');
    $stmt->execute([':k' => $key]);
    $row = $stmt->fetch();
    return $row ? $row['value'] : null;
}

function set_setting(string $key, string $value): bool
{
    $pdo = get_pdo();
    $stmt = $pdo->prepare('INSERT INTO `settings` (`key`, `value`) VALUES (:k, :v) ON DUPLICATE KEY UPDATE `value` = :v2');
    return $stmt->execute([':k' => $key, ':v' => $value, ':v2' => $value]);
}
?>