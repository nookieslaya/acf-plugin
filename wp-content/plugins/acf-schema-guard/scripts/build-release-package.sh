#!/bin/sh

set -eu

SCRIPT_DIR=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
PLUGIN_DIR=$(CDPATH= cd -- "$SCRIPT_DIR/.." && pwd)
PLUGINS_DIR=$(CDPATH= cd -- "$PLUGIN_DIR/.." && pwd)
VERSION=$(sed -n "s/^ \* Version:[[:space:]]*//p" "$PLUGIN_DIR/acf-schema-guard.php" | head -n 1 | tr -d '[:space:]')
OUTPUT_DIR=${1:-"$PLUGIN_DIR/dist"}
ARCHIVE="$OUTPUT_DIR/acf-schema-guard-$VERSION.zip"

if [ -z "$VERSION" ]; then
	echo "Could not determine the plugin version." >&2
	exit 1
fi

if [ -e "$ARCHIVE" ]; then
	echo "Release archive already exists: $ARCHIVE" >&2
	echo "Choose another output directory or remove that archive deliberately." >&2
	exit 1
fi

mkdir -p "$OUTPUT_DIR"

(
	cd "$PLUGINS_DIR"
	zip -qr "$ARCHIVE" acf-schema-guard \
		-x 'acf-schema-guard/tests/*' \
		-x 'acf-schema-guard/docs/*' \
		-x 'acf-schema-guard/scripts/*' \
		-x 'acf-schema-guard/assets/wordpress-org/*' \
		-x 'acf-schema-guard/dist/*' \
		-x 'acf-schema-guard/readme.md' \
		-x 'acf-schema-guard/.DS_Store'
)

echo "Release archive created: $ARCHIVE"
