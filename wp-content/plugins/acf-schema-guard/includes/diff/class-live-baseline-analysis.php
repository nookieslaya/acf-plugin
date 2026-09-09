<?php
/**
 * Read-only comparison result for an approved snapshot and the live schema.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Diff;

use AcfSchemaGuard\Snapshots\SchemaSnapshot;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LiveBaselineAnalysis {
	/** @var SchemaSnapshot|null */
	private $baseline;

	/** @var SnapshotAnalysis|null */
	private $analysis;

	/** @var string */
	private $message;

	/**
	 * @param SchemaSnapshot|null  $baseline Approved stored snapshot.
	 * @param SnapshotAnalysis|null $analysis Classified live comparison.
	 * @param string                $message Unavailable-state explanation.
	 */
	public function __construct( $baseline, $analysis, $message = '' ) {
		$this->baseline = $baseline instanceof SchemaSnapshot ? $baseline : null;
		$this->analysis = $analysis instanceof SnapshotAnalysis ? $analysis : null;
		$this->message  = (string) $message;
	}

	/** @return bool */
	public function is_available() {
		return null !== $this->baseline && null !== $this->analysis;
	}

	/** @return SchemaSnapshot|null */
	public function baseline() {
		return $this->baseline;
	}

	/** @return SnapshotAnalysis|null */
	public function analysis() {
		return $this->analysis;
	}

	/** @return string */
	public function message() {
		return $this->message;
	}
}
