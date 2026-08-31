<?php
/**
 * Plugin composition root.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard;

use AcfSchemaGuard\Acf\AcfEnvironment;
use AcfSchemaGuard\Acf\AcfEnvironmentProvider;
use AcfSchemaGuard\Acf\AcfSchemaSource;
use AcfSchemaGuard\Acf\FullSchemaSource;
use AcfSchemaGuard\Schema\NormalizedSchema;
use AcfSchemaGuard\Schema\SchemaNormalizer;
use AcfSchemaGuard\Snapshots\SnapshotRepository;
use AcfSchemaGuard\Snapshots\SnapshotCaptureService;
use AcfSchemaGuard\Snapshots\SchemaSnapshot;
use AcfSchemaGuard\Diff\SchemaDiffer;
use AcfSchemaGuard\Snapshots\WordPressSnapshotRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once ACF_SCHEMA_GUARD_PATH . 'includes/acf/class-field-group-descriptor.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/acf/class-acf-environment.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/acf/class-acf-environment-provider.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/acf/interface-full-schema-source.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/acf/class-acf-schema-source.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/schema/class-canonical-value.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/schema/class-normalized-field.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/schema/class-normalized-field-group.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/schema/class-normalized-schema.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/schema/class-schema-normalizer.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/snapshots/class-schema-snapshot.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/snapshots/interface-snapshot-repository.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/snapshots/class-snapshot-table.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/snapshots/class-wordpress-snapshot-repository.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/snapshots/class-snapshot-capture-service.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/diff/class-schema-change.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/diff/class-schema-diff.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/diff/class-schema-differ.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/diff/class-risk-finding.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/diff/class-risk-classifier.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/diff/class-snapshot-analysis.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/diff/class-snapshot-analysis-service.php';

/**
 * Coordinates the plugin lifecycle without depending on ACF at load time.
 */
final class Plugin {
	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Whether bootstrap completed.
	 *
	 * @var bool
	 */
	private $is_booted = false;

	/**
	 * Read-only ACF environment provider.
	 *
	 * @var AcfEnvironmentProvider|null
	 */
	private $acf_environment_provider = null;

	/** @var FullSchemaSource|null */
	private $schema_source = null;

	/** @var SchemaNormalizer|null */
	private $schema_normalizer = null;

	/** @var SnapshotRepository|null */
	private $snapshot_repository = null;

	/** @var SnapshotCaptureService|null */
	private $snapshot_capture_service = null;
	private $schema_differ = null;

	/**
	 * Gets the plugin instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Boots the plugin once WordPress has loaded all plugins.
	 *
	 * @return void
	 */
	public function boot() {
		if ( $this->is_booted ) {
			return;
		}

		$this->acf_environment_provider = new AcfEnvironmentProvider();
		$this->is_booted = true;

		/**
		 * Fires once the ACF Schema Guard plugin service is ready.
		 *
		 * @param Plugin $plugin Initialized plugin service.
		 */
		do_action( 'acf_schema_guard/booted', $this );
	}

	/**
	 * Gets a fresh, read-only description of the current ACF environment.
	 *
	 * @return AcfEnvironment
	 */
	public function acf_environment() {
		if ( null === $this->acf_environment_provider ) {
			$this->acf_environment_provider = new AcfEnvironmentProvider();
		}

		return $this->acf_environment_provider->discover();
	}

	/**
	 * Gets the complete current ACF schema in canonical form.
	 *
	 * @return NormalizedSchema
	 */
	public function normalized_schema() {
		if ( null === $this->schema_source ) {
			$this->schema_source = new AcfSchemaSource();
		}

		if ( null === $this->schema_normalizer ) {
			$this->schema_normalizer = new SchemaNormalizer();
		}

		return $this->schema_normalizer->normalize( $this->schema_source->field_groups() );
	}

	/**
	 * Gets the append-only schema snapshot repository.
	 *
	 * @return SnapshotRepository
	 */
	public function snapshot_repository() {
		if ( null === $this->snapshot_repository ) {
			global $wpdb;

			$this->snapshot_repository = new WordPressSnapshotRepository( $wpdb );
		}

		return $this->snapshot_repository;
	}

	/**
	 * Explicitly captures and persists the current ACF schema.
	 *
	 * @param string $source_id Caller-selected snapshot source.
	 * @return SchemaSnapshot
	 */
	public function capture_snapshot( $source_id ) {
		if ( null === $this->snapshot_capture_service ) {
			$this->snapshot_capture_service = new SnapshotCaptureService(
				new AcfEnvironmentProvider(),
				new AcfSchemaSource(),
				new SchemaNormalizer(),
				$this->snapshot_repository()
			);
		}

		return $this->snapshot_capture_service->capture( $source_id );
	}
	public function diff_schemas( array $before, array $after ) {
		if ( null === $this->schema_differ ) { $this->schema_differ = new SchemaDiffer(); }
		return $this->schema_differ->compare( $before, $after );
	}
	public function analyze_snapshots( \AcfSchemaGuard\Snapshots\SchemaSnapshot $before, \AcfSchemaGuard\Snapshots\SchemaSnapshot $after ) {
		return ( new \AcfSchemaGuard\Diff\SnapshotAnalysisService( new \AcfSchemaGuard\Diff\SchemaDiffer(), new \AcfSchemaGuard\Diff\RiskClassifier() ) )->analyze( $before, $after );
	}

	/**
	 * Prevents external instantiation.
	 */
	private function __construct() {}
}
