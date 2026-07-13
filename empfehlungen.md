Hier sind meine Usability-Verbesserungsvorschläge, sortiert nach Impact:

Hoch (größter Nutzen für den Alltag)
#	Bereich	Problem	Vorschlag
1	Admin Dashboard	Nach Speichern/Löschen/Set-Standard fehlt Feedback	Flash-Meldung ("Meldung gespeichert", "Gelöscht" etc.) via Session, analog zum Import
2	Admin Meldungen-Tabelle	Bei vielen Meldungen unübersichtlich	Suchfeld + Status-Filter (Aktiv/Geplant/Abgelaufen) hinzufügen
3	Scan-Seite	Kein "Letzte Aktualisierung" sichtbar	Zeitstempel der aktiven Meldung anzeigen ("Aktualisiert am …")
4	Admin Mobile Sidebar	Icons fehlen, Menüpunkte sind leer	SVG-Icons für Dashboard, Scan, FavIcon, Logout hinzufügen
Mittel
#	Bereich	Problem	Vorschlag
5	Editor Vorschau	HTML wird unsanitisiert gerendert	Sanitizer auch auf die Live-Vorschau anwenden (Konsistenz)
6	"Standard"-Button	Kein Bestätigungsdialog vor dem Umschalten	confirm() hinzufügen, wie beim Löschen
7	Scan-Seite Consent-Banner	Blockiert auf Mobile den unteren Content	Banner als overlay mit padding-bottom am body, oder in den Card-Inhalt integrieren
8	Admin nach Edit	Scrollt immer zum Seitenanfang	Anchor-Link #messages im Redirect oder scroll-position speichern
Niedrig (Nice-to-have)
#	Bereich	Problem	Vorschlag
9	Scan-Seite	Kein Branding/Logo konfigurierbar	Feld für Logo-URL und Titel im Admin hinzufügen
10	Admin Stats	Scan-Logs nur ansehbar, nicht exportierbar	CSV-Export der Scan-Statistiken hinzufügen
11	Abgelaufene Meldungen	Nur grau, kein Hinweis auf Menge	Zusammenfassungs-Badge ("3 abgelaufen") im Dashboard
12	Admin Dark Mode	Nicht vorhanden	prefers-color-scheme CSS media query anbinden