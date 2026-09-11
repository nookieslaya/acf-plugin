<?php
/**
 * Read-only source-health result.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Acf;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-schema-source-mode.php';

final class SourceHealthReport {
	/** @var bool */
	private $is_available;

	/** @var SourceHealthFinding[] */
	private $findings;
	private $source_mode;

	/**
	 * @param bool                  $is_available Whether ACF source inspection is available.
	 * @param SourceHealthFinding[] $findings     Source-health findings.
	 */
	public function __construct( $is_available, array $findings, $source_mode = null ) {
		$this->is_available = (bool) $is_available;
		$this->findings     = array_values(
			array_filter(
				$findings,
				function ( $finding ) {
					return $finding instanceof SourceHealthFinding;
				}
			)
		);
		$this->source_mode = $source_mode instanceof SchemaSourceMode ? $source_mode : new SchemaSourceMode( SchemaSourceMode::LOCAL_JSON );
	}

	/** @return bool */
	public function is_available() {
		return $this->is_available;
	}

	/** @return SourceHealthFinding[] */
	public function findings() {
		return $this->findings;
	}

	/** @return SchemaSourceMode */
	public function source_mode() { return $this->source_mode; }
}
