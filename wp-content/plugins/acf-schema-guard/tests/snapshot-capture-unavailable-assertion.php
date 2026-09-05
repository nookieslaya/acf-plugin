<?php
define( 'ABSPATH', __DIR__ . '/' );

require_once dirname( __DIR__ ) . '/includes/acf/class-field-group-descriptor.php';
require_once dirname( __DIR__ ) . '/includes/acf/class-acf-environment.php';
require_once dirname( __DIR__ ) . '/includes/acf/class-acf-environment-provider.php';
require_once dirname( __DIR__ ) . '/includes/acf/interface-full-schema-source.php';
require_once dirname( __DIR__ ) . '/includes/schema/class-canonical-value.php';
require_once dirname( __DIR__ ) . '/includes/schema/class-normalized-field.php';
require_once dirname( __DIR__ ) . '/includes/schema/class-normalized-field-group.php';
require_once dirname( __DIR__ ) . '/includes/schema/class-normalized-schema.php';
require_once dirname( __DIR__ ) . '/includes/schema/class-schema-normalizer.php';
require_once dirname( __DIR__ ) . '/includes/snapshots/class-schema-snapshot.php';
require_once dirname( __DIR__ ) . '/includes/snapshots/interface-snapshot-repository.php';
require_once dirname( __DIR__ ) . '/includes/snapshots/class-snapshot-capture-service.php';

$source = new class implements \AcfSchemaGuard\Acf\FullSchemaSource {
	public $calls = 0;
	public function field_groups() { $this->calls++; return array(); }
};
$repository = new class implements \AcfSchemaGuard\Snapshots\SnapshotRepository {
	public $inserts = 0;
	public function insert( \AcfSchemaGuard\Snapshots\SchemaSnapshot $snapshot ) { $this->inserts++; }
	public function find( $id ) { return null; }
	public function latest_for_source( $source_id ) { return null; }
	public function all() { return array(); }
};
$service = new \AcfSchemaGuard\Snapshots\SnapshotCaptureService( new \AcfSchemaGuard\Acf\AcfEnvironmentProvider(), $source, new \AcfSchemaGuard\Schema\SchemaNormalizer(), $repository );

try { $service->capture( 'acf-runtime' ); exit( 1 ); } catch ( \RuntimeException $exception ) {}

if ( 0 !== $source->calls || 0 !== $repository->inserts ) { exit( 1 ); }

echo "Unavailable capture assertion passed.\n";
