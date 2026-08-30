<?php
namespace AcfSchemaGuard\Diff;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class SchemaDiff {
	private $changes;
	public function __construct( array $changes ) { $this->changes = $changes; }
	public function to_array() {
		$changes = array();
		foreach ( $this->changes as $change ) { if ( $change instanceof SchemaChange ) { $changes[] = $change->to_array(); } }
		return array( 'changes' => $changes );
	}
}
