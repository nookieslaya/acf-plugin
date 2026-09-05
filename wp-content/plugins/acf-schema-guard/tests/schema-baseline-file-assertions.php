<?php
/**
 * Isolated assertions for the portable schema baseline file.
 *
 * @package ACFSchemaGuard
 */

define( 'ABSPATH', __DIR__ . '/' );

require_once dirname( __DIR__ ) . '/includes/baseline/class-schema-baseline-file.php';

function acf_schema_guard_baseline_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

$baseline = new \AcfSchemaGuard\Baseline\SchemaBaselineFile();
$schema   = array( 'schema_version' => 1, 'field_groups' => array() );
$path     = tempnam( sys_get_temp_dir(), 'acf-schema-baseline-' );
unlink( $path );

$baseline->write( $path, $schema );
acf_schema_guard_baseline_assert( $schema === $baseline->read( $path ), 'Baseline file did not round trip.' );

try {
	$baseline->write( $path, $schema );
	throw new RuntimeException( 'Existing baseline file was overwritten without force.' );
} catch ( RuntimeException $exception ) {
	acf_schema_guard_baseline_assert( false !== strpos( $exception->getMessage(), 'already exists' ), 'Existing-file error is wrong.' );
}

file_put_contents( $path, '{' );
try {
	$baseline->read( $path );
	throw new RuntimeException( 'Malformed baseline file was accepted.' );
} catch ( RuntimeException $exception ) {
	acf_schema_guard_baseline_assert( false !== strpos( $exception->getMessage(), 'malformed JSON' ), 'Malformed-file error is wrong.' );
}

file_put_contents( $path, json_encode( array( 'format_version' => 2, 'schema_version' => 1, 'schema' => $schema ) ) );
try {
	$baseline->read( $path );
	throw new RuntimeException( 'Unsupported baseline version was accepted.' );
} catch ( RuntimeException $exception ) {
	acf_schema_guard_baseline_assert( false !== strpos( $exception->getMessage(), 'format version is unsupported' ), 'Unsupported-version error is wrong.' );
}

unlink( $path );

echo "Schema baseline file assertions passed.\n";
