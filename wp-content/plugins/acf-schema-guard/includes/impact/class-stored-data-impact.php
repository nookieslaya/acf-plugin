<?php
/**
 * Immutable direct stored-data evidence for one changed ACF field.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Impact;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class StoredDataImpact {
	private $change;
	private $matcher;
	private $record_count;
	private $records;

	public function __construct( array $change, StoredDataImpactMatcher $matcher, $record_count, array $records ) {
		$this->change       = $change;
		$this->matcher      = $matcher;
		$this->record_count = max( 0, (int) $record_count );
		$this->records      = array_slice( $records, 0, 20 );
	}

	public function to_array() {
		return array(
			'change'       => $this->change,
			'field_name'   => $this->matcher->field_name(),
			'matcher'      => $this->matcher->to_array(),
			'record_count' => $this->record_count,
			'records'      => $this->records,
		);
	}
}
