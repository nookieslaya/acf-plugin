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

final class SourceHealthReport {
	/** @var bool */
	private $is_available;

	/** @var SourceHealthFinding[] */
	private $findings;

	/**
	 * @param bool                  $is_available Whether ACF source inspection is available.
	 * @param SourceHealthFinding[] $findings     Source-health findings.
	 */
	public function __construct( $is_available, array $findings ) {
		$this->is_available = (bool) $is_available;
		$this->findings     = array_values(
			array_filter(
				$findings,
				function ( $finding ) {
					return $finding instanceof SourceHealthFinding;
				}
			)
		);
	}

	/** @return bool */
	public function is_available() {
		return $this->is_available;
	}

	/** @return SourceHealthFinding[] */
	public function findings() {
		return $this->findings;
	}
}
