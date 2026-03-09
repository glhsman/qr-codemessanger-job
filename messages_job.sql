-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: sql.local:3306
-- Erstellungszeit: 09. Mrz 2026 um 22:11
-- Server-Version: 10.11.14-MariaDB-0+deb12u2
-- PHP-Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `db267600004`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `active_from` datetime DEFAULT NULL,
  `active_until` datetime DEFAULT NULL,
  `daily_start` time DEFAULT NULL,
  `daily_end` time DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `messages`
--

INSERT INTO `messages` (`id`, `title`, `content`, `active_from`, `active_until`, `daily_start`, `daily_end`, `is_default`, `created_at`) VALUES
(3, 'Homeoffice', '<b>Heute befinde ich mich im Homeoffice.</b><br>Probleme und Störungen melden Sie bitte zuerst immer an den Servicedesk. <p></p>Erreichbar über das<a href=\"https://getraenkeundmehr.atlassian.net/servicedesk/customer/portals\" target=\"_blank\"> Meldungsporta</a>l oder die Telefonnummer: <a href=\"tel:+49309609609\" target=\"_blank\">030 9609609</a>. <br>Sie können den Servicedesk aber auch eine E-Mail schreiben an<br> <a href=\"mailto:it-servicedesk@radeberger-gruppe.de\" target=\"_blank\">it-servicedesk@radeberger-gruppe.de</a>', NULL, NULL, NULL, NULL, 0, '2026-03-07 11:34:39'),
(4, 'Feierabend', '<h1>Feierabend</h1>\r\n<h2>Aktuell befinde ich mich nicht im Dienst.</h2>\r\n\r\n<p>\r\nProbleme und Störungen melden Sie bitte zuerst immer an den Servicedesk.\r\n</p>\r\n\r\n<p>\r\nErreichbar über das Meldungsportal oder telefonisch unter\r\n<a href=\"tel:+49309609609\">+49 (30) 9609609</a>.\r\n</p>\r\n\r\n<p>\r\nOder per E-Mail:\r\n<a href=\"mailto:it-servicedesk@radeberger-gruppe.de\">\r\nit-servicedesk@radeberger-gruppe.de\r\n</a>\r\n</p>', '2026-03-07 16:30:00', '2026-03-31 08:00:00', '16:30:00', '07:45:00', 0, '2026-03-07 11:38:17'),
(5, 'Im Haus unterwegs', '<h2>Ich bin da, aber nicht im Büro!</h2><br>Versuchen Sie es bitte später noch einmal, da ich mich gerade auf Störungsbehebung im Haus befinde.', NULL, NULL, NULL, NULL, 0, '2026-03-07 11:40:07'),
(6, 'Raucherpause', '<h2>Auch ein Admin muss mal Pause machen</h2><br>Bin mal kurz vor der Tür frische Luft schnappen.', NULL, NULL, '13:00:00', '14:00:00', 0, '2026-03-07 11:40:54'),
(7, 'Wochenende', '<h1>Wochenende</h1>\r\n<h2>Ich bin im Wochenende.</h2>\r\n\r\n<p>\r\nProbleme und Störungen melden Sie bitte zuerst immer an den Servicedesk.\r\n</p>\r\n\r\n<p>\r\nErreichbar über das <a href=\"https://getraenkeundmehr.atlassian.net/servicedesk/customer/portals\" target=\"_blank\">Meldungsportal</a> oder telefonisch unter\r\n<a href=\"tel:+49309609609\">+49 (30) 9609609</a>.\r\n</p>\r\n\r\n<p>\r\nOder per E-Mail:\r\n<a href=\"mailto:it-servicedesk@radeberger-gruppe.de\">\r\nit-servicedesk@radeberger-gruppe.de\r\n</a>\r\n</p>', NULL, NULL, NULL, NULL, 0, '2026-03-08 09:45:02'),
(8, 'M365-Großstörung', '<h1>Achtung</h1>Auf Grund einer<b> Großstörung</b> am Freitag im M365 Umfeld, kommt es aktuell zu Problemen bei der Windows Anmeldung. Die Anmeldung mit dem Windows Hello PIN funktioniert bei einigen nicht mehr, sondern nur das <b>Windows-Passwort.</b><p>Deshalb müssen Betroffene die Einrichtung der PIN erneut durchlaufen und sich mit 2FA (Authenticator od. SMS) authentifizieren. </p><br>', NULL, NULL, NULL, NULL, 0, '2026-03-09 06:46:20'),
(9, 'Streik', '<b>Heute befinde ich mich im Homeoffice.</b><br>Auf Grund des Streiks im öffentlichem Dienst, befinde ich mich heute im Homeoffice. <br><br>Probleme und Störungen melden Sie bitte zuerst immer an den Servicedesk. <p></p>Erreichbar über das<a href=\"https://getraenkeundmehr.atlassian.net/servicedesk/customer/portals\" target=\"_blank\"> Meldungsporta</a>l oder die Telefonnummer: <a href=\"tel:+49309609609\" target=\"_blank\">030 9609609</a>. <br>Sie können den Servicedesk aber auch eine E-Mail schreiben an<br> <a href=\"mailto:it-servicedesk@radeberger-gruppe.de\" target=\"_blank\">it-servicedesk@radeberger-gruppe.de</a>', NULL, NULL, NULL, NULL, 1, '2026-03-09 07:04:35');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
