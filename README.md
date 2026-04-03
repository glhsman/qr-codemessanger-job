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
2. **Konfiguration erstellen**:
   Erstellen Sie eine `credentials.json` im Hauptverzeichnis (oder eine Ebene darüber, falls die Zugangsdaten außerhalb des Web-Roots liegen sollen). Die Anwendung sucht prioritär im Projektverzeichnis.
   
   Beispielinhalt:
   ```json
   {
       "DB_HOST": "192.168.1.5",
       "DB_NAME": "qrcode",
       "DB_USER": "Db-QrCode",
       "DB_PASS": "Ihr_Passwort",
       "BASE_URL": "http://localhost/QR_WebApp",
       "ADMIN_PASS_HASH": "Ihr_Passwort_Hash"
   }
   ```
   > [!NOTE]
   > Über die `credentials.json` lassen sich optional auch `ANONYMIZE_IP` (true/false) und `SESSION_COOKIE_SECURE` (true/false) steuern.
3. **Passwort festlegen**:
   - Rufen Sie im Browser `http://ihre-domain.de/admin/set_pass.php` auf.
   - Geben Sie Ihr gewünschtes Admin-Passwort ein, um den Hash für die `credentials.json` zu generieren.
   - Kopieren Sie den generierten Hash in Ihre `credentials.json` unter `ADMIN_PASS_HASH`.
   - > [!WARNING]
     > Löschen Sie die Datei `admin/set_pass.php` umgehend nach der Verwendung vom Server, um Sicherheitsrisiken zu vermeiden.

4. Importieren Sie das Datenbankschema (wird beim ersten Aufruf von `/scan` automatisch versucht zu erstellen).
5. Konfigurieren Sie Ihren Webserver so, dass er auf das Hauptverzeichnis zeigt.

## Projektstruktur

- `/admin`: Der passwortgeschützte Bereich für den Administrator.
- `/lib`: Kernfunktionen für Datenbank-Interaktion und Sanitisierung.
- `/scan`: Die Landing-Page für die QR-Code-Scans.
- `config.php`: Zentrale Konfiguration und Laden der Secrets.

## Lizenz

Dieses Projekt ist unter der MIT-Lizenz lizenziert.
