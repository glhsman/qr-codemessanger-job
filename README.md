# Wo ist der Admin? - QR-Code Messenger

Ein PHP-basiertes System zur schnellen Kommunikation via QR-Code. Besucher scannen einen QR-Code und erhalten eine aktuelle Statusmeldung oder Nachricht vom Administrator.

## Features

- **QR-Code Scanning**: Einfaches Scannen führt direkt zur aktuellen Nachricht (klares, minimalistisches Design ohne störende Icons).
- **Zeitgesteuerte Nachrichten**: Nachrichten können für bestimmte Zeiträume oder Wochentage geplant werden.
- **Admin-Panel mit Live-Vorschau**: Komfortable Verwaltung der Nachrichten und Einstellungen über einen geschützten Bereich inklusive permanenter Live-Vorschau der aktuell aktiven Nachricht.
- **Besucher-Statistik**: Logging von Scans (anonymisierte IP, User-Agent, Referrer) zur Analyse der Nutzung.
- **Sicherheit**: Trennung von Konfiguration und sensiblen Zugangsdaten (`credentials.json`).

## Anforderungen

- PHP 7.4 oder höher
- MySQL / MariaDB Datenbank
- Webserver (Apache mit `.htaccess` Support empfohlen)

## Installation

1. Klonen Sie das Repository:
   ```bash
   git clone https://github.com/glhsman/qr-codemessanger-job.git
   ```
2. Erstellen Sie eine `credentials.json` basierend auf der Projektstruktur (siehe `config.php` für Details):
   ```json
   {
       "DB_HOST": "Ihr_Host",
       "DB_NAME": "Ihr_DB_Name",
       "DB_USER": "Ihr_Nutzer",
       "DB_PASS": "Ihr_Passwort",
       "BASE_URL": "https://Ihre-Domain.de",
       "ADMIN_PASS_HASH": "Ihr_Passwort_Hash"
   }
   ```
3. Importieren Sie das Datenbankschema (wird beim ersten Aufruf von `/scan` automatisch versucht zu erstellen).
4. Konfigurieren Sie Ihren Webserver so, dass er auf das Hauptverzeichnis zeigt.

## Projektstruktur

- `/admin`: Der passwortgeschützte Bereich für den Administrator.
- `/lib`: Kernfunktionen für Datenbank-Interaktion und Sanitisierung.
- `/scan`: Die Landing-Page für die QR-Code-Scans.
- `config.php`: Zentrale Konfiguration und Laden der Secrets.

## Lizenz

Dieses Projekt ist unter der MIT-Lizenz lizenziert.
