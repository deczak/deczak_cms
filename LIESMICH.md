
## Das Projekt - cosCMS

Dieses Content Management System erstelle ich primär für mich und meinen Anforderungen, stelle es aber der Allgemeinheit zur Verfügung. Das Kürzel cos steht dabei für cooking own soup, in Anlehnung dessen das jemand seine eigene Suppe kocht anstatt die von anderen zu verwenden.

Es existieren zwei Branchens:

+ master-branch, Version die zuletzt soweit getestet wurde und tendenziell funktionieren sollte (fehler sind natürlich nicht ausgeschlossen)
+ dev-branch, Version die in Entwicklung ist, Fehler enthalten kann und in einigen Bereichen möglicherweise nicht funktioniert

Eine Dokumentation zum CMS wird später in Etappen aufgebaut.

###	Aktueller Stand

Das CMS befindet sich noch in einem frühen Entwicklungszustand. Es können Fehler enthalten sein die zur Funktionsunfähigkeit oder zum Datenverlust der im CMS eingetragenen Daten führen kann. Es exisitiert keine CMS eigene Backup oder Wiederherstellungs Funktion der Daten. Ebenso gibt es zur Zeit keine Update Funktion, neue Versionen können daher einen neue Installation erforderlich machen. Daher bitte das CMS **nicht produktiv** einsetzen, eine Update Funktion wird im laufe des jahres folgen.

**Funktionen die existieren**
+ Seiten anlegen, bearbeiten, löschen, aber nicht verschieben
+ Text, Überschrift, Quelltext Modul
+ Benutzer anlegen und verwalten für Backend und Frontend (die Bereiche haben eigene Benutzer Tabellen)
+ Sprachen für Frontend hinzufügen
+ Seiten Zugriff beschränken für Authed Benutzer
+ Diverse Sichtbarkeits Einstellungen der Seiten
+ Automatisiertes Sperren von Zugriffen unter bestimmten Bedingungen
+ Sperren von Zugriff anhand der IP/CIDR Notation
+ Sperren von Zugriff anhand des User-Agent 

###	Eigenschaften dieses CMS

+ Authentifizierung der Benutzer über mehrere Datenbanken hinweg. Als Beispiel: Sie haben mehrere Projekte, aber die Benutzer sollten Zentral an einer Stelle sein.
+ Module als feste Core und projekt bezogene Mantel Module, Mantel Module können (in Konzept) Core Module überladen.
+ Vanilla Javascript im Backend ab ES6
+ Keine Unterstützung für den Internet Explorer

## Voraussetzungen für dieses CMS

+ Ein Webserver mit PHP7+, HTAccess Support inklusive URL Rewrite sowie funktionierende sendmail Konfiguration in PHP.
+ Ein MySQL kompatible Datenbank Server

## Installation

Im Verzeichnis install gibt es eine index.php die alles übernimmt, halten Sie dazu die benötigen Daten wie Datenbank Informationen bereit. Eine Manuelle Installation ist nicht möglich, eine erneute Installation ist solange nicht möglich bis Sie die Datenbank Tabellen und die htaccess Datei gelöscht haben.

Wird das CMS produktiv eingesetzt, so müssen die cronjobs als diese beim Server erstellt werden. Im Verzeichnis /cron befinden sich 3 Dateien:
+ cron_24_hours.php, diese Datei alle 24 Stunden
+ cron_7_days.php, diese Datei einmal die Woche
+ cron_6_hours.php, diese Datei alle 6 Stunden

Bei der Installation wird geprüft ob das CMS über eine SSL Transportverschlüsselung installiert wurde. Wenn sich dieser Zustand ändert, muss in der Konfiguration unter /config/standard.php der Wert unter $COOKIE_HTTPS geändert werden. Andernfalls könnte eine Anmeldung im Backend scheitern.

###	Content dritter

Das Content Management System enthält folgende Sourcen dritter:

+ Font Awesome
+ Flatpickr
