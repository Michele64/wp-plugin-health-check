# WP Plugin Health Check

A WordPress admin tool that checks every installed plugin against the WordPress.org directory and flags plugins that have been closed, removed, or not updated in a long time.

## Features

- Adds a "Zustand" (health) column to the Plugins list, with a warning row for anything that needs attention
- Configurable thresholds for "check this" vs. "action needed" (based on months since last update)
- Optional scheduled scans with email notification (daily / weekly / monthly)
- A dedicated report page under **Tools → Plugin-Zustand**

The plugin's UI is German-only at this point (built for German-speaking clients); the codebase is prepared for translation via standard WordPress i18n (`__()` / `_e()` with the `wp-plugin-health-check` text domain, `.pot` template included in `/languages`).

## Installation

1. Download the latest release ZIP from the [Releases](../../releases) page
2. Upload it via **Plugins → Add New → Upload Plugin** in WordPress, or extract it into `wp-content/plugins/`
3. Activate the plugin

The plugin checks this repository for updates automatically via the bundled [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker) library — no separate update server needed.

## Requirements

- WordPress 6.0+
- PHP 7.4+

## Versioning

This project follows [Semantic Versioning](https://semver.org/) (`major.minor.patch`). The version in the plugin header must match the Git release tag (e.g. header `1.1.2` → tag `v1.1.2`).

## License

GPL-2.0-or-later — see [license.txt](vendor/plugin-update-checker/license.txt) for the bundled library's license; the plugin itself is licensed under the same terms.

## Author

Michele Chesi — [chesi.net](https://chesi.net/)
