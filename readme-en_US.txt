=== Plugin Health Check ===
Contributors: michelechesi
Tags: plugins, maintenance, security, updates
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.14
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Checks every installed plugin against the WordPress.org directory and flags closed, orphaned, or long-unmaintained plugins.

== Description ==

On every check, Plugin Health Check scans all installed plugins and compares them against the WordPress.org directory. It detects:

* Plugins that have been removed from the directory (e.g. due to security issues or policy violations)
* Plugins that haven't received an update in many months
* Plugins only tested against a noticeably older WordPress version

Results show up directly as an extra column in the regular Plugins list, as well as on a dedicated report page under **Tools → Plugin Health**. The thresholds for "Review" and "Action needed" can be set freely. Email notifications are on by default (monthly) and can be adjusted or turned off in the settings.

**A note on the "Tested up to" value:** this value is maintained by the plugin author themselves and isn't checked by anyone else. A finding based on it is a hint, not proof — the date of the last update usually says more about how well a plugin is actually maintained.

== Installation ==

1. Download the current ZIP from the [GitHub repository](https://github.com/Michele64/wp-plugin-health-check/releases)
2. Upload it under **Plugins → Add New → Upload Plugin**, or unzip it into `wp-content/plugins/`
3. Activate the plugin

Updates are pulled automatically from the GitHub repository — no separate update server needed.

== Changelog ==

= 1.1.14 =
* The "View Details" popup (Description, Installation, Changelog, Upgrade Notice) is now localized too, instead of always showing the German readme.txt. A matching readme-{locale}.txt in the same format ships for each supported language and is picked up automatically based on the site language. The short description under the plugin name in the Plugins list is now translated as well.
* Added an "Upgrade Notice" section to the readme (there wasn't one before)

= 1.1.13 =
* Added Danish, Italian, and French translations

= 1.1.12 =
* Added an English translation (en_US and en_GB). Previously the /languages folder only contained the .pot template, no compiled .mo file — since the source strings themselves are in German, any non-German site showed the German original text for lack of a translation.

= 1.1.11 =
* Follow-up to 1.1.10: scrolling to the top after "Check now" didn't work reliably because it ran too early, before the browser's own later scroll restoration kicked in. It's now also forced on the load event and after a short delay; the redirect URL is also unique per scan (timestamp instead of a fixed value).

= 1.1.10 =
* Fixed: after "Check now" the page landed at the last-visited scroll position instead of at the fresh report, because the redirect URL is identical on every scan and the browser restores its remembered scroll position for it. Scroll is now explicitly reset to the top of the page.

= 1.1.9 =
* Fixed: after saving settings the page jumped to the top instead of returning to the settings section (redirect was missing its anchor)

= 1.1.8 =
* Email notifications now support multiple, comma-separated recipient addresses. Each address is validated individually; invalid addresses are discarded on save and flagged with a notice.

= 1.1.7 =
* Fixed an incorrect "Action needed" result for plugins that ship their own update checker (including this plugin itself, via GitHub): the "known to the WordPress.org directory" check previously only looked for any entry in the update_plugins transient — but that transient is also populated by third-party updaters, not just WordPress.org. It now additionally checks for the "w.org/plugins/…" id format that only genuine wordpress.org entries carry.

= 1.1.6 =
* Added optional GitHub authentication for the update checker (WPHC_GITHUB_TOKEN constant in wp-config.php) to avoid 403 errors from GitHub rate limiting or IP-based abuse detection on shared hosting

= 1.1.5 =
* Email notifications are now enabled by default (previously: disabled)
* Added an activation hook so the cron is actually set up to match the default on a fresh install

= 1.1.4 =
* Changed the default email notification frequency to "monthly" (previously: "weekly")

= 1.1.3 =
* The version number is now read dynamically from the plugin header instead of also being hardcoded
* Updated "Tested up to" to 7.1

= 1.1.2 =
* First release via the public GitHub repo with automatic updates (Plugin Update Checker)
* Prepared for multiple languages: all visible strings are translatable via the "wp-plugin-health-check" text domain
* Added README.md and readme.txt

Earlier release notes (before the move to GitHub) aren't documented in detail. Older commits can be found in the [GitHub commit history](https://github.com/Michele64/wp-plugin-health-check/commits/main).

== Upgrade Notice ==

= 1.1.13 =
Adds Danish, Italian, and French translations. No action needed.

= 1.1.8 =
Email notifications now accept a comma-separated list of addresses. If you only ever used a single address, nothing changes for you.

= 1.1.6 =
If update checks show a GitHub 403 error, you can now set WPHC_GITHUB_TOKEN in wp-config.php to fix it — see the plugin's README for details.
