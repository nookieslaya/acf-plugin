# WordPress.org publishing guide

## Release package

Build the release ZIP from the `acf-schema-guard` plugin directory only. Do not include the Local WordPress installation, test theme, `.git`, Blueprint files, or development-only fixtures.

## Required repository assets

The prepared assets are staged locally in `assets/wordpress-org/`. Copy the
following files to the separate WordPress.org assets repository after the plugin
has been approved:

- `banner-1544x500.png`
- `banner-772x250.png`
- `icon-256x256.png`
- `icon-128x128.png`
- screenshots named `screenshot-1.png`, `screenshot-2.png`, and so on.

The supplied banner presents the schema comparison, PHP call-site analysis, and
data-impact workflow. The icon is a simplified schema-stack mark designed to
remain legible at the smaller 128px size.

Recommended screenshots show Overview, Changes with code and data impact, Code Usage, and the Polish Admin locale. Do not include field values, customer content, license keys, database credentials, or other sensitive data.

## Publication order

1. Run the release checklist and fresh-install verification.
2. Create the plugin ZIP with `sh wp-content/plugins/acf-schema-guard/scripts/build-release-package.sh`.
3. Validate `readme.txt`, version metadata, license, and language files.
4. Submit the ZIP through the WordPress.org plugin submission flow.
5. After approval, commit the tagged plugin files and assets to the supplied SVN repository.
6. Tag the release as `1.0.0` in the WordPress.org repository.

This guide prepares local files only. It does not publish the plugin or change any remote repository.
