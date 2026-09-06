#!/bin/bash
# bump-version.sh — Versionsnummer in Header und readme.txt synchron setzen
#
# Nutzung:  ./bump-version.sh 1.1.3
#
# Danach wie gewohnt:
#   git add .
#   git commit -m "Version 1.1.3"
#   git push
#   + Release-Tag v1.1.3 auf GitHub erstellen

set -euo pipefail

if [ -z "${1:-}" ]; then
	echo "Nutzung: $0 <neue-version>   (z.B. $0 1.1.3)"
	exit 1
fi

NEW="$1"
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PHP_FILE="$DIR/wp-plugin-health-check.php"
README_FILE="$DIR/readme.txt"

if [[ ! "$NEW" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
	echo "Warnung: '$NEW' sieht nicht nach major.minor.patch aus (z.B. 1.1.3)."
	read -p "Trotzdem fortfahren? (y/n) " -n 1 -r
	echo
	[[ $REPLY =~ ^[Yy]$ ]] || exit 1
fi

sed -i "s/^ \* Version:.*/ * Version:           $NEW/" "$PHP_FILE"
sed -i "s/^Stable tag:.*/Stable tag: $NEW/" "$README_FILE"

echo "Version auf $NEW gesetzt in:"
echo "  - $(basename "$PHP_FILE")"
echo "  - $(basename "$README_FILE")"
echo
echo "Nicht vergessen: git add . && git commit -m \"Version $NEW\" && git push"
echo "                 + Release-Tag v$NEW auf GitHub erstellen"
