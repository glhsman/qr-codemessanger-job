-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server-Version:               10.4.32-MariaDB - mariadb.org binary distribution
-- Server-Betriebssystem:        Win64
-- HeidiSQL Version:             12.11.0.7065
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Exportiere Daten aus Tabelle qrcode.messages: ~10 rows (ungefähr)
REPLACE INTO `messages` (`id`, `title`, `content`, `active_from`, `active_until`, `daily_start`, `daily_end`, `is_default`, `created_at`) VALUES
	(1, 'Standard-Meldung', '<h2>Hallo meine liebe Pia!</h2><p>Ich wünsche dir einen wundervollen Guten Morgen.</p>', NULL, NULL, NULL, NULL, 0, '2026-03-07 08:17:00'),
	(2, 'Abendbrot', 'Abendbrot ist von 18 Uhr bis 19 Uhr. Bitte seien Sie pünktlich!', '2026-03-07 18:00:00', '2026-03-28 19:59:00', '18:00:00', '19:00:00', 0, '2026-03-07 08:33:40'),
	(3, 'Einkauf bei Lidl', '<h2>Wie sind gerade einkaufen.</h2>\r\n<br>Du erreichst uns wieder gegen 18 Uhr.', NULL, NULL, NULL, NULL, 0, '2026-03-07 14:36:49'),
	(4, 'Lidl ich alleine', '<h1>Wo wir sind</h1><br>Wenn du zu Pia möchtest, sie ist zu Hause.<br>Ich selbst bin gerade beim Lidl um die Ecke für den Wochenendeinkauf.', '2026-03-07 17:00:00', '2026-03-07 18:00:00', NULL, NULL, 0, '2026-03-07 15:29:43'),
	(5, 'Schön, das du da bist', '<p><b>Hallo!</b></p>\r\n\r\n<p>\r\nJemand hat diesen QR-Code gescannt –\r\n<br>und dieser Jemand bist du.\r\n</p>\r\n\r\n<p>\r\n<i>Schön, dass du hier bist.</i>\r\n</p>', NULL, NULL, NULL, NULL, 0, '2026-03-09 16:00:44'),
	(6, 'Kleine Meldung', '<p><b>Scan erfolgreich.</b></p>\r\n\r\n<p>\r\nDer QR-Code hat funktioniert.\r\n<br>\r\nJetzt bist du hier gelandet.\r\n</p>\r\n\r\n<p>\r\n<i>Manchmal beginnen kleine Entdeckungen genau so.</i>\r\n</p>', NULL, NULL, NULL, NULL, 1, '2026-03-09 16:01:09'),
	(7, 'Hilfe anbieten', '<p><b>Schön, dass du hier bist.</b></p>\r\n\r\n<p>\r\nWenn du Fragen hast oder Hilfe brauchst,\r\n<br>melde dich gerne.\r\n</p>\r\n\r\n<p>\r\n<a href="mailto:info@s-r-portrait.de">info@s-r-portrait.de</a>\r\n</p>', NULL, NULL, NULL, NULL, 0, '2026-03-09 16:02:42'),
	(8, 'Morgens', '<p><b>Guten Morgen, Pia ☀️</b></p>\r\n\r\n<p>\r\nIch wünsche dir einen wunderschönen Start in den Tag.\r\n<br>\r\nIch hoffe, du hast gut geschlafen.\r\n</p>\r\n\r\n<p>\r\n<i>Denk daran: Jemand denkt heute an dich.</i>\r\n</p>', '2026-03-09 17:09:00', NULL, '06:00:00', '11:59:00', 0, '2026-03-09 16:07:25'),
	(9, 'Mittags', '<p><b>Hallo Pia 😊</b></p>\r\n\r\n<p>\r\nHalbzeit des Tages.\r\n<br>\r\nIch hoffe, dein Vormittag war gut.\r\n</p>\r\n\r\n<p>\r\n<i>Ich schicke dir einen kleinen digitalen Gruß.</i>\r\n<br>\r\nDu machst das heute bestimmt großartig.\r\n</p>', '2026-03-09 17:09:00', NULL, '12:00:00', '15:59:00', 0, '2026-03-09 16:07:52'),
	(10, 'Abends', '<p><b>Hallo Pia 🌙</b></p>\r\n\r\n<p>\r\nDer Tag neigt sich langsam dem Ende zu.\r\n<br>\r\nIch hoffe, er war freundlich zu dir.\r\n</p>\r\n\r\n<p>\r\n<i>Freu dich auf einen entspannten Abend.</i>\r\n<br>\r\nVielleicht sehen wir uns ja später. ❤️\r\n</p>', '2026-03-09 17:08:00', NULL, '16:00:00', '22:00:00', 0, '2026-03-09 16:08:29');

-- Exportiere Daten aus Tabelle qrcode.scans: ~13 rows (ungefähr)
REPLACE INTO `scans` (`id`, `ts`, `ip`, `user_agent`, `referrer`) VALUES
	(1, '2026-03-05 16:42:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL),
	(2, '2026-03-05 17:03:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL),
	(3, '2026-03-05 17:04:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL),
	(4, '2026-03-05 17:12:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL),
	(5, '2026-03-05 17:17:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL),
	(6, '2026-03-05 17:21:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL),
	(7, '2026-03-05 18:30:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL),
	(8, '2026-03-05 18:36:24', '192.168.1.53', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL),
	(9, '2026-03-05 18:38:31', '192.168.1.42', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL),
	(10, '2026-03-05 19:44:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL),
	(11, '2026-03-09 05:55:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL),
	(12, '2026-03-09 06:00:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL),
	(13, '2026-03-09 14:45:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL);

-- Exportiere Daten aus Tabelle qrcode.settings: ~0 rows (ungefähr)
REPLACE INTO `settings` (`key`, `value`) VALUES
	('landing_message', 'Ätsch, hier gibt es nichts zu sehen!');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
