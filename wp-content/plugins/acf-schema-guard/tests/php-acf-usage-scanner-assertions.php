<?php
/**
 * Focused assertions for token-aware PHP ACF usage scanning.
 *
 * Run with: php tests/php-acf-usage-scanner-assertions.php
 */

define( 'ABSPATH', __DIR__ . '/' );

require_once dirname( __DIR__ ) . '/includes/scanner/class-code-usage-reference.php';
require_once dirname( __DIR__ ) . '/includes/scanner/interface-code-usage-scanner.php';
require_once dirname( __DIR__ ) . '/includes/scanner/class-php-acf-usage-scanner.php';

function acf_schema_guard_scanner_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

$root = sys_get_temp_dir() . '/acf-schema-guard-scan-' . uniqid();
mkdir( $root );

$fixture = <<<'PHP'
<?php
get_field( 'hero' );
the_field( "title" );
get_sub_field( 'sub' );
the_sub_field( 'sub_title' );
have_rows( 'items' );
get_field_object( 'settings' );
\get_field /* comment before bracket */ ( 'explicit_global' );
// get_field( 'comment_only' );
/* the_field( 'block_comment_only' ); */
$example = "get_field( 'string_only' )";
function get_field( $name ) { return $name; }
function &the_field( $name ) { return $name; }
$object->get_field( 'object_method' );
Example::the_field( 'static_method' );
Vendor\get_field( 'qualified_name' );
get_field( $dynamic );
get_field( 'concatenated' . '_value' );
PHP;

file_put_contents( $root . '/fixture.php', $fixture );

try {
	$references = ( new \AcfSchemaGuard\Scanner\PhpAcfUsageScanner() )->scan( array( $root ) );
	$actual     = array_map(
		static function ( $reference ) {
			return $reference->to_array();
		},
		$references
	);

	$expected_names = array( 'hero', 'title', 'sub', 'sub_title', 'items', 'settings', 'explicit_global' );
	acf_schema_guard_scanner_assert( $expected_names === array_column( $actual, 'field_name' ), 'Only real supported literal calls should be found.' );
	acf_schema_guard_scanner_assert( 'fixture.php' === $actual[0]['path'], 'Scanner should report the path relative to its root.' );
	acf_schema_guard_scanner_assert( 2 === $actual[0]['line'], 'Scanner should retain 1-based source lines.' );
	acf_schema_guard_scanner_assert( "get_field('hero'" === $actual[0]['expression'], 'Scanner should retain the call expression.' );
	acf_schema_guard_scanner_assert( 8 === $actual[6]['line'], 'Explicit global calls should retain their source line.' );
	acf_schema_guard_scanner_assert( "\\get_field('explicit_global'" === $actual[6]['expression'], 'Explicit global calls should retain their expression.' );
} finally {
	unlink( $root . '/fixture.php' );
	rmdir( $root );
}

echo "PHP ACF scanner assertions passed.\n";
