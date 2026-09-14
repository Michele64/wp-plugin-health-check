=== Plugin-Zustandsprüfung ===
Contributors: michelechesi
Tags: plugins, wartung, sicherheit, updates
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Prüft alle installierten Plugins gegen das WordPress.org-Verzeichnis und meldet geschlossene, verwaiste oder lange nicht mehr gepflegte Plugins.

== Description ==

Die Plugin-Zustandsprüfung durchsucht bei jedem Prüflauf alle installierten Plugins und gleicht sie mit dem WordPress.org-Verzeichnis ab. Dabei werden erkannt:

* Plugins, die aus dem Verzeichnis entfernt wurden (z. B. wegen Sicherheitslücken oder Richtlinienverstößen)
* Plugins, die seit vielen Monaten kein Update mehr erhalten haben
* Plugins, die nur mit einer deutlich älteren WordPress-Version getestet wurden

Die Ergebnisse erscheinen direkt als zusätzliche Spalte in der normalen Plugin-Übersicht sowie auf einer eigenen Berichtsseite unter **Werkzeuge → Plugin-Zustand**. Die Schwellenwerte für "Prüfen" und "Handlungsbedarf" lassen sich frei einstellen. Die E-Mail-Benachrichtigung ist standardmäßig aktiv (monatlich) und lässt sich in den Einstellungen anpassen oder deaktivieren.

**Hinweis zum "Getestet bis"-Wert:** Dieser Wert wird vom jeweiligen Plugin-Autor selbst gepflegt und von niemandem überprüft. Ein Befund dazu ist ein Hinweis, kein Beweis — das Datum des letzten Updates sagt in der Regel mehr über den tatsächlichen Pflegezustand aus.

== Installation ==

1. Die aktuelle ZIP-Datei aus dem [GitHub-Repository](https://github.com/Michele64/wp-plugin-health-check/releases) herunterladen
2. Unter **Plugins → Installieren → Plugin hochladen** hochladen, oder in `wp-content/plugins/` entpacken
3. Plugin aktivieren

Updates werden automatisch über das GitHub-Repository bezogen, kein zusätzlicher Update-Server nötig.

== Changelog ==

= 1.1.7 =
* Fehlerhafte "Handlungsbedarf"-Meldung für Plugins mit eigenem Update-Checker (z. B. dieses Plugin selbst über GitHub) behoben: Der Abgleich "im WordPress.org-Verzeichnis bekannt" prüfte bisher nur, ob überhaupt ein Eintrag im update_plugins-Transient existiert — der wird aber auch von Drittanbieter-Updatern befüllt, nicht nur von WordPress.org selbst. Jetzt wird zusätzlich das id-Format ("w.org/plugins/…") geprüft, das nur echte wordpress.org-Einträge tragen.

= 1.1.6 =
* Optionale GitHub-Authentifizierung für den Update-Checker ergänzt (Konstante WPHC_GITHUB_TOKEN in wp-config.php), um 403-Fehler durch GitHub-Rate-Limiting bzw. IP-basierte Abuse-Detection bei Shared Hosting zu vermeiden

= 1.1.5 =
* E-Mail-Benachrichtigung ist jetzt standardmäßig aktiviert (galt zuvor: deaktiviert)
* Aktivierungs-Hook ergänzt, damit der Cron bei einer frischen Installation auch tatsächlich passend zum Standard eingerichtet wird

= 1.1.4 =
* Standard für E-Mail-Benachrichtigungshäufigkeit auf "monatlich" geändert (galt zuvor: "wöchentlich")

= 1.1.3 =
* Versionsnummer wird jetzt dynamisch aus dem Plugin-Header gelesen, statt zusätzlich fest im Code zu stehen
* "Tested up to" auf 7.1 aktualisiert

= 1.1.2 =
* Erster Release über das öffentliche GitHub-Repo mit automatischen Updates (Plugin Update Checker)
* Mehrsprachigkeit vorbereitet: alle sichtbaren Texte über die Text Domain "wp-plugin-health-check" übersetzbar gemacht
* README.md und readme.txt ergänzt

Frühere Versionshinweise (vor dem Umzug auf GitHub) sind nicht mehr im Detail dokumentiert. Ältere Commits sind im [GitHub-Commit-Verlauf](https://github.com/Michele64/wp-plugin-health-check/commits/main) nachvollziehbar.
