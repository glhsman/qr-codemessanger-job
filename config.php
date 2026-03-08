<?php
// Basic configuration - primary values are loaded from an external JSON file to avoid
// keeping sensitive credentials in source control. The loader checks the environment
// variable `QR_CREDENTIALS_FILE` or falls back to `../credentials.json`.

$secretsPath = getenv('QR_CREDENTIALS_FILE') ?: __DIR__ . '/../credentials.json';
$secrets = [];
if (file_exists($secretsPath)) {
    $raw = file_get_contents($secretsPath);
    $parsed = json_decode($raw, true);
    if (is_array($parsed)) {
        $secrets = $parsed;
    }
}

// DB credentials: prefer secrets, otherwise placeholders
$dbHost = $secrets['DB_HOST'] ?? $secrets['db_host'] ?? 'DB_HOST_PLACEHOLDER';
$dbName = $secrets['DB_NAME'] ?? $secrets['db_name'] ?? 'DB_NAME_PLACEHOLDER';
$dbUser = $secrets['DB_USER'] ?? $secrets['db_user'] ?? 'DB_USER_PLACEHOLDER';
$dbPass = $secrets['DB_PASS'] ?? $secrets['db_pass'] ?? 'DB_PASS_PLACEHOLDER';

define('DB_HOST', $dbHost);
define('DB_NAME', $dbName);
define('DB_USER', $dbUser);
define('DB_PASS', $dbPass);

// Base URL of your installation (no trailing slash)
define('BASE_URL', $secrets['BASE_URL'] ?? 'https://your-domain.tld');

// Admin password hash. You can put this hash into the secrets file as well (key: ADMIN_PASS_HASH)
// Generate locally with: php -r "echo password_hash('YOUR_PASS', PASSWORD_DEFAULT).PHP_EOL;"
define('ADMIN_PASS_HASH', $secrets['ADMIN_PASS_HASH'] ?? '');

// Privacy & behavior flags (can be overridden in secrets)
define('ANONYMIZE_IP', isset($secrets['ANONYMIZE_IP']) ? (bool)$secrets['ANONYMIZE_IP'] : true); // true = store anonymized IPs
define('ALLOW_HTML_WHITELIST', isset($secrets['ALLOW_HTML_WHITELIST']) ? (bool)$secrets['ALLOW_HTML_WHITELIST'] : true); // allow simple whitelisted HTML in landing message

// Session settings
ini_set('session.cookie_httponly', 1);
// Only set secure flag if you serve via HTTPS
if (isset($secrets['SESSION_COOKIE_SECURE'])) {
    ini_set('session.cookie_secure', $secrets['SESSION_COOKIE_SECURE'] ? 1 : 0);
} else {
    ini_set('session.cookie_secure', 1); // default to 1; change if testing locally without HTTPS
}
ini_set('session.use_strict_mode', 1);

// Charset
mb_internal_encoding('UTF-8');

// Helpful constant pointing to the secrets file in use
define('SECRETS_PATH', $secretsPath);

return (object)[];
?>