=== Plugin-Zustandsprüfung ===
Contributors: michelechesi
Tags: plugins, wartung, sicherheit, updates
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.1.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Prüft alle installierten Plugins gegen das WordPress.org-Verzeichnis und meldet geschlossene, verwaiste oder lange nicht mehr gepflegte Plugins.

== Description ==

Die Plugin-Zustandsprüfung durchsucht bei jedem Prüflauf alle installierten Plugins und gleicht sie mit dem WordPress.org-Verzeichnis ab. Dabei werden erkannt:

* Plugins, die aus dem Verzeichnis entfernt wurden (z. B. wegen Sicherheitslücken oder Richtlinienverstößen)
* Plugins, die seit vielen Monaten kein Update mehr erhalten haben
* Plugins, die nur mit einer deutlich älteren WordPress-Version getestet wurden

Die Ergebnisse erscheinen direkt als zusätzliche Spalte in der normalen Plugin-Übersicht sowie auf einer eigenen Berichtsseite unter **Werkzeuge → Plugin-Zustand**. Die Schwellenwerte für "Prüfen" und "Handlungsbedarf" lassen sich frei einstellen, ebenso eine optionale E-Mail-Benachrichtigung mit wählbarer Häufigkeit.

**Hinweis zum "Getestet bis"-Wert:** Dieser Wert wird vom jeweiligen Plugin-Autor selbst gepflegt und von niemandem überprüft. Ein Befund dazu ist ein Hinweis, kein Beweis — das Datum des letzten Updates sagt in der Regel mehr über den tatsächlichen Pflegezustand aus.

== Installation ==

1. Die aktuelle ZIP-Datei aus dem [GitHub-Repository](https://github.com/Michele64/wp-plugin-health-check/releases) herunterladen
2. Unter **Plugins → Installieren → Plugin hochladen** hochladen, oder in `wp-content/plugins/` entpacken
3. Plugin aktivieren

Updates werden automatisch über das GitHub-Repository bezogen, kein zusätzlicher Update-Server nötig.

== Changelog ==

= 1.1.2 =
* Aktueller Stand

Ältere Versionshinweise sind im [GitHub-Commit-Verlauf](https://github.com/Michele64/wp-plugin-health-check/commits/main) nachvollziehbar.
