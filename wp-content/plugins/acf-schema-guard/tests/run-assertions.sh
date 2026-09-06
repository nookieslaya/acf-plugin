#!/bin/sh

set -eu

SCRIPT_DIR=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)

for test_file in "$SCRIPT_DIR"/*assertion*.php; do
	php "$test_file"
done
