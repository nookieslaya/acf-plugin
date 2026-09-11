<?php
/**
 * Immutable answer to a Pro capability check.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Licensing;

if ( ! defined( 'ABSPATH' ) ) {

	exit;
}

final class CapabilityDecision {
	/** @var bool */
	private $allowed;
	/** @var string */
	private $capability;
	/** @var string */
	private $reason;

	public function __construct( $allowed, $capability, $reason ) {
		$this->allowed    = (bool) $allowed;
		$this->capability = (string) $capability;
		$this->reason     = (string) $reason;
	}

	/** @return bool */
	public function is_allowed() {
		return $this->allowed;
	}

	/** @return string */
	public function capability() {
		return $this->capability;
	}

	/** @return string */
	public function reason() {
		return $this->reason;
	}
}
