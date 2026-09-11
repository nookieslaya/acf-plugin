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
	private $field_name;
	private $record_count;
	private $records;

	public function __construct( array $change, $field_name, $record_count, array $records ) {
		$this->change       = $change;
		$this->field_name   = (string) $field_name;
		$this->record_count = max( 0, (int) $record_count );
		$this->records      = array_slice( $records, 0, 20 );
	}

	public function to_array() {
		return array(
			'change'       => $this->change,
			'field_name'   => $this->field_name,
			'record_count' => $this->record_count,
			'records'      => $this->records,
		);
	}
}
