
# Das Projekt

Dabei handelt es sich um ein Content Management System wie es diese mittlerweile dutzendfach gibt.

##	Aktueller Stand

Diese Version ist eine frühe, wenn man das so nennen möchte, Alpha Version. Das können Sie machen:

+ Seiten anlegen, bearbeiten, löschen, aber nicht verschieben
+ Text und Überschriften Module einfügen, bearbeiten, löschen
+ Benutzer für Backend anlegen, bearbeiten, löschen
+ Rechtegruppen für Backend benutzer anlegen, bearbeiten, löschen

Aufgrund dessen das ich noch einiges umbauen muss, verzichte ich noch auf eine entsprechende Dokumentation der internen Abläufe. Keiner wäscht sein Auto wenn er weiß das es zwei Stunden später anfängt zu regnen.

Ich schreibe es, obwohl dies sicher klar sein sollte, doch lieber. Das ist eine frühe Version und es fehlt noch eine Menge. Nutzen Sie diese Version nicht im Live Einsatz.

##	Geplante Funktionen und Eigenschaften (Vorläufig)

+ Authentifizierung der Benutzer über mehrere Datenbanken hinweg. Als Beispiel: Sie haben mehrere Projekte, aber die Benutzer sollten Zentral an einer Stelle sein.
+ Umsetzung des MVC Konzept aber in einer flachen Struktur für eine einfache Wartung und Erweiterbarkeit.
+ Umsetzung von festen CORE Modulen und projekt bezogene MANTLE Module.
+ Erweiterbarkeit des CORE mit der Möglichkeit bestehende Projekte mit dem neuen CORE zu versorgen ohne die Funktion der existierenden MANTLE Module zu brechen.
+ Einfache unkomplizierte prozedur um ein neues Modul zu installieren.
+ Vanilla Javascript ab ES6
+ Keine Rücksichtname auf den Internet Explorer

## Nicht geplante Eigenschaften

+ Sich zu weit aus dem Fenster zu lehnen
+ Nach Hause telefonieren
+ Automatische Updates

# Voraussetzungen für dieses CMS

+ Ein Webserver mit PHP7+, HTAccess Support (Apache Style) inklusive URL Rewrite sowie funktionierende sendmail Konfiguration in PHP.
+ Ein MySQL kompatible Datenbank Server

# Installation

Im Verzeichnis install gibt es eine install.php die alles übernimmt. Zur erfolgreichen installation werden grundsätzlich die Datenbank Informationen benötigt, eine eMail Adresse an der System Nachrichten gesendet werden sowie weitere Angabe die dort erklärt werden.

Eine Manuelle Installation ist nicht möglich, Sie könnten diese zwar soweit durchführen das Sie auf den öffentlichen Bereich zugreifen können, aber für das Backend (die Administration) brauchen Sie einen benutzer dessen Zugangsdaten gesondert gehasht werden. Das zu erklären ist zu kompliziert als das man es sinnvoll in eine Anleitung packen könnte. Und wenn ich dafür ein Helfer Script erstellen würden .. Gott verdammt wofür hab ich dann die install.php erstellt ...

# Todo

+ verdammt viel
+ des weiteren einen namen für das CMS ausdenken
+ vielleicht ein wenig sport machen

Einen Zeitplan kann ich nicht geben, aber die nächste Vorabversion wird noch in diesem Jahr kommen. 
