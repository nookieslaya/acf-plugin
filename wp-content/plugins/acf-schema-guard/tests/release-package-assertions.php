<?php
/**
 * Protects public release metadata and WordPress.org staging assets.
 *
 * @package ACFSchemaGuard
 */

function acf_schema_guard_release_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

$plugin_root = dirname( __DIR__ );
$bootstrap   = (string) file_get_contents( $plugin_root . '/acf-schema-guard.php' );
$readme      = (string) file_get_contents( $plugin_root . '/readme.txt' );
$changelog   = (string) file_get_contents( $plugin_root . '/CHANGELOG.md' );
$license     = (string) file_get_contents( $plugin_root . '/LICENSE' );
$publisher   = (string) file_get_contents( $plugin_root . '/docs/wordpress-org-publishing.md' );
$builder     = (string) file_get_contents( $plugin_root . '/scripts/build-release-package.sh' );

preg_match( '/^ \* Version:\s*(.+)$/m', $bootstrap, $matches );
$version = isset( $matches[1] ) ? trim( $matches[1] ) : '';

acf_schema_guard_release_assert( '1.0.0' === $version, 'Plugin release version must be 1.0.0.' );
acf_schema_guard_release_assert( false !== strpos( $bootstrap, "define( 'ACF_SCHEMA_GUARD_VERSION', '" . $version . "' )" ), 'Plugin version constant must match the header.' );
acf_schema_guard_release_assert( false !== strpos( $readme, 'Stable tag: ' . $version ), 'readme.txt stable tag must match the plugin header.' );
acf_schema_guard_release_assert( false !== strpos( $changelog, '## ' . $version ), 'CHANGELOG.md must contain the current release.' );
acf_schema_guard_release_assert( false !== strpos( $license, 'GPL-2.0-or-later' ) && false !== strpos( $license, 'GNU General Public License' ), 'LICENSE must identify the GPL-2.0-or-later terms.' );
acf_schema_guard_release_assert( false !== strpos( $builder, "-x 'acf-schema-guard/tests/*'" ), 'Release builder must exclude test files.' );
acf_schema_guard_release_assert( false !== strpos( $builder, "-x 'acf-schema-guard/assets/wordpress-org/*'" ), 'Release builder must exclude WordPress.org staging assets.' );
acf_schema_guard_release_assert( false !== strpos( $builder, "-x 'acf-schema-guard/dist/*'" ), 'Release builder must exclude generated release archives.' );

$assets = array(
	'banner-1544x500.png' => array( 1544, 500 ),
	'banner-772x250.png'  => array( 772, 250 ),
	'icon-256x256.png'    => array( 256, 256 ),
	'icon-128x128.png'    => array( 128, 128 ),
);

foreach ( $assets as $filename => $dimensions ) {
	$path = $plugin_root . '/assets/wordpress-org/' . $filename;
	$size = getimagesize( $path );

	acf_schema_guard_release_assert( false !== $size, 'WordPress.org asset is not a readable image: ' . $filename );
	acf_schema_guard_release_assert( $dimensions[0] === $size[0] && $dimensions[1] === $size[1], 'WordPress.org asset dimensions are incorrect: ' . $filename );
	acf_schema_guard_release_assert( false !== strpos( $publisher, '`' . $filename . '`' ), 'Publishing guide must reference staged asset: ' . $filename );
}

echo "Release package assertions passed.\n";
