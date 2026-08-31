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
use AcfSchemaGuard\Admin\AdminController;
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
require_once ACF_SCHEMA_GUARD_PATH . 'includes/scanner/class-code-usage-reference.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/scanner/interface-code-usage-scanner.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/scanner/class-code-usage-scanner-service.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/scanner/class-php-acf-usage-scanner.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/admin/class-admin-controller.php';

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

	/** @var AdminController|null */
	private $admin_controller = null;

	/** @var \AcfSchemaGuard\Cli\CommandRegistrar|null */
	private $cli_command_registrar = null;

	/** @var \AcfSchemaGuard\Cli\DiffCommand|null */
	private $cli_diff_command = null;

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

		if ( is_admin() ) {
			$this->admin_controller = new AdminController();
			$this->admin_controller->register();
		}

		if ( $this->is_wp_cli() ) {
				require_once ACF_SCHEMA_GUARD_PATH . 'includes/cli/class-command-registrar.php';
			require_once ACF_SCHEMA_GUARD_PATH . 'includes/cli/class-scan-command.php';
			require_once ACF_SCHEMA_GUARD_PATH . 'includes/cli/class-diff-command.php';

			$this->cli_command_registrar = new \AcfSchemaGuard\Cli\CommandRegistrar();
			$this->cli_command_registrar->register(
				'acf-schema-guard scan',
				array(
					new \AcfSchemaGuard\Cli\ScanCommand(
						new \AcfSchemaGuard\Scanner\CodeUsageScannerService(
							array( new \AcfSchemaGuard\Scanner\PhpAcfUsageScanner() )
						)
					),
					'scan',
				)
			);
			$this->cli_diff_command = new \AcfSchemaGuard\Cli\DiffCommand(
				$this->snapshot_repository(),
				new \AcfSchemaGuard\Diff\SnapshotAnalysisService(
					new \AcfSchemaGuard\Diff\SchemaDiffer(),
					new \AcfSchemaGuard\Diff\RiskClassifier()
				)
			);
			$this->cli_command_registrar->register(
				'acf-schema-guard diff',
				array( $this->cli_diff_command, 'diff' )
			);
		}

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
	 * Checks whether the current request can safely use WP-CLI.
	 *
	 * @return bool
	 */
	private function is_wp_cli() {
		return defined( 'WP_CLI' ) && WP_CLI && class_exists( 'WP_CLI' );
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
