# Wo ist der Admin? - QR-Code Messenger

Ein PHP-basiertes System zur schnellen Kommunikation via QR-Code. Besucher scannen einen QR-Code und erhalten eine aktuelle Statusmeldung oder Nachricht vom Administrator.

## Features

### Kernfunktionen
- **QR-Code Scanning**: Einfaches Scannen führt direkt zur aktuellen Nachricht (klares, minimalistisches Design).
- **Zeitgesteuerte Nachrichten**: Nachrichten können für bestimmte Zeiträume, Tageszeiten oder Wochentage geplant werden.
- **Admin-Panel mit Live-Vorschau**: Komfortable Verwaltung der Nachrichten inklusive permanenter Live-Vorschau der aktuell aktiven Nachricht.
- **Besucher-Statistik**: Logging von Scans (IP, User-Agent, Referrer) mit automatischer Löschfrist und DSGVO-konformer Einwilligung.
- **Sicherheit**: Trennung von Konfiguration und sensiblen Zugangsdaten (`credentials.json`), CSRF-Schutz, HTML-Sanitizer.

### Editor-Toolbar
Der Meldungs-Editor bietet eine umfangreiche Toolbar mit:

| Gruppe | Werkzeuge |
|--------|-----------|
| **Überschriften** | H1, H2, H3 |
| **Textformat** | Fett, Kursiv, Unterstrichen, Durchgestrichen, Hervorheben, Klein |
| **Struktur** | Absatz, Zeilenumbruch, Trennlinie, Blockzitat |
| **Listen** | Aufzählung (ul), Nummerierung (ol) – markierter Text wird automatisch in Listenpunkte umgewandelt |
| **Link** | URLs mit Protokollvalidierung |
| **Emoji-Picker** | 26 häufig gebrauchte Emojis per Klick einfügen |
| **Vorlagen** | Fertige Textbausteine: Abwesend, Meeting, Erreichbar, Pause, Homeoffice, Urlaub |

Alle Inhalte werden serverseitig durch einen Whitelist-basierten HTML-Sanitizer gereinigt. Die Live-Vorschau im Editor verwendet den gleichen Sanitizer clientseitig.

### Settings-Seite
- **Fav-Icon Upload**: Eigenes Browser-Tab-Icon hochladen (ico, png, jpg, svg).
- **Logo Upload**: Logo für den Header der öffentlichen Scan-Seite hochladen (png, jpg, svg, webp). Wird lokal gespeichert.
- **Branding-Titel**: Konfigurierbarer Titel auf der Scan-Seite (z.B. „Willkommen", „Für dich").

### Dashboard
- **Live-Vorschau** der aktuell öffentlich sichtbaren Meldung (Auto-Refresh alle 30 Sekunden).
- **Flash-Meldungen**: Visuelles Feedback nach Speichern, Löschen und Standardsetzen von Meldungen.
- **Suchfeld & Status-Filter**: Meldungen nach Titel durchsuchen und nach Status filtern (Aktiv, Geplant, Abgelaufen, Inaktiv).
- **Abgelaufen-/Geplant-Badges**: Zusammenfassende Hinweise auf abgelaufene oder geplante Meldungen.
- **Scroll-Position**: Nach Aktionen (Speichern, Löschen) wird die Scroll-Position beibehalten.
- **Dark Mode**: Automatisch via `prefers-color-scheme` (folgt der Systemeinstellung).

### Scan-Seite
- **Zeitstempel**: „Aktualisiert am …" zeigt an, wann die Meldung erstellt wurde.
- **Branding**: Konfigurierbarer Titel und optionales Logo im Header.
- **Consent-Banner**: DSGVO-konformes Overlay mit Einwilligungs-/Ablehnungsoption für IP-Speicherung.
- **Responsive Design**: Optimiert für Mobile mit angepasstem Consent-Banner (`padding-bottom`).

### Import/Export
- **Meldungen exportieren**: Alle Meldungen als JSON-Datei herunterladen.
- **Meldungen importieren**: JSON-Datei mit Meldungen hochladen.

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

- `/admin` – Passwortgeschützter Admin-Bereich (Dashboard, Editor, Settings).
  - `/admin/favicon/` – Hochgeladenes Fav-Icon.
  - `/admin/logo/` – Hochgeladenes Logo für die Scan-Seite.
- `/lib` – Kernfunktionen: Datenbankzugriff (`db.php`) und HTML-Sanitizer (`sanitize.php`).
- `/scan` – Öffentliche Landing-Page für QR-Code-Scans.
- `config.php` – Zentrale Konfiguration und Laden der Secrets.
- `datenschutz.php` – Datenschutzerklärung.
- `impressum.php` – Impressum.

## Lizenz

Dieses Projekt ist unter der MIT-Lizenz lizenziert.
