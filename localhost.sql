-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Erstellungszeit: 09. Mrz 2026 um 22:05
-- Server-Version: 10.4.32-MariaDB
-- PHP-Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `qrcode`
--

--
-- Daten für Tabelle `messages`
--

DELETE FROM messages;
INSERT INTO `messages` (`id`, `title`, `content`, `active_from`, `active_until`, `daily_start`, `daily_end`, `is_default`, `created_at`) VALUES
(1, 'Standard-Meldung', '<h2>Hallo meine liebe Pia!</h2><p>Ich wünsche dir einen wundervollen Guten Morgen.</p>', NULL, NULL, NULL, NULL, 0, '2026-03-07 08:17:00'),
(2, 'Abendbrot', 'Abendbrot ist von 18 Uhr bis 19 Uhr. Bitte seien Sie pünktlich!', '2026-03-07 18:00:00', '2026-03-28 19:59:00', '18:00:00', '19:00:00', 0, '2026-03-07 08:33:40'),
(3, 'Einkauf bei Lidl', '<h2>Wie sind gerade einkaufen.</h2>\r\n<br>Du erreichst uns wieder gegen 18 Uhr.', NULL, NULL, NULL, NULL, 0, '2026-03-07 14:36:49'),
(4, 'Lidl ich alleine', '<h1>Wo wir sind</h1><br>Wenn du zu Pia möchtest, sie ist zu Hause.<br>Ich selbst bin gerade beim Lidl um die Ecke für den Wochenendeinkauf.', '2026-03-07 17:00:00', '2026-03-07 18:00:00', NULL, NULL, 0, '2026-03-07 15:29:43'),
(5, 'Schön, das du da bist', '<p><b>Hallo!</b></p>\r\n\r\n<p>\r\nJemand hat diesen QR-Code gescannt –\r\n<br>und dieser Jemand bist du.\r\n</p>\r\n\r\n<p>\r\n<i>Schön, dass du hier bist.</i>\r\n</p>', NULL, NULL, NULL, NULL, 0, '2026-03-09 16:00:44'),
(6, 'Kleine Meldung', '<p><b>Scan erfolgreich.</b></p>\r\n\r\n<p>\r\nDer QR-Code hat funktioniert.\r\n<br>\r\nJetzt bist du hier gelandet.\r\n</p>\r\n\r\n<p>\r\n<i>Manchmal beginnen kleine Entdeckungen genau so.</i>\r\n</p>', NULL, NULL, NULL, NULL, 1, '2026-03-09 16:01:09'),
(7, 'Hilfe anbieten', '<p><b>Schön, dass du hier bist.</b></p>\r\n\r\n<p>\r\nWenn du Fragen hast oder Hilfe brauchst,\r\n<br>melde dich gerne.\r\n</p>\r\n\r\n<p>\r\n<a href=\"mailto:info@s-r-portrait.de\">info@s-r-portrait.de</a>\r\n</p>', NULL, NULL, NULL, NULL, 0, '2026-03-09 16:02:42'),
(8, 'Morgens', '<p><b>Guten Morgen, Pia ☀️</b></p>\r\n\r\n<p>\r\nIch wünsche dir einen wunderschönen Start in den Tag.\r\n<br>\r\nIch hoffe, du hast gut geschlafen.\r\n</p>\r\n\r\n<p>\r\n<i>Denk daran: Jemand denkt heute an dich.</i>\r\n</p>', '2026-03-09 17:09:00', NULL, '06:00:00', '11:59:00', 0, '2026-03-09 16:07:25'),
(9, 'Mittags', '<p><b>Hallo Pia 😊</b></p>\r\n\r\n<p>\r\nHalbzeit des Tages.\r\n<br>\r\nIch hoffe, dein Vormittag war gut.\r\n</p>\r\n\r\n<p>\r\n<i>Ich schicke dir einen kleinen digitalen Gruß.</i>\r\n<br>\r\nDu machst das heute bestimmt großartig.\r\n</p>', '2026-03-09 17:09:00', NULL, '12:00:00', '15:59:00', 0, '2026-03-09 16:07:52'),
(10, 'Abends', '<p><b>Hallo Pia 🌙</b></p>\r\n\r\n<p>\r\nDer Tag neigt sich langsam dem Ende zu.\r\n<br>\r\nIch hoffe, er war freundlich zu dir.\r\n</p>\r\n\r\n<p>\r\n<i>Freu dich auf einen entspannten Abend.</i>\r\n<br>\r\nVielleicht sehen wir uns ja später. ❤️\r\n</p>', '2026-03-09 17:08:00', NULL, '16:00:00', '22:00:00', 0, '2026-03-09 16:08:29');



/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
