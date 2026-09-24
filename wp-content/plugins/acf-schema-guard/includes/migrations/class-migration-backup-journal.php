<?php
/**
 * Value-free map of the exact target meta rows written by one execution.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Migrations;

use InvalidArgumentException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MigrationBackupJournal {
	private $data;

	public function __construct( array $data ) {
		$required = array( 'execution_id', 'post_id', 'source_meta_id', 'source_key', 'target_meta_id', 'target_key', 'source_reference_meta_id', 'source_reference_key', 'target_reference_meta_id', 'target_reference_key', 'created_at' );
		foreach ( $required as $key ) {
			if ( empty( $data[ $key ] ) ) {
				throw new InvalidArgumentException( 'Migration backup journal is missing ' . $key . '.' );
			}
		}
		$this->data = array_map( 'strval', $data );
	}

	public function to_array() { return $this->data; }
}
