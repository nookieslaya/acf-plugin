<?php
namespace AcfSchemaGuard\Diff;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class SchemaChange {
	private $kind;
	private $node_type;
	private $path;
	private $before;
	private $after;

	public function __construct( $kind, $node_type, array $path, $before, $after ) {
		$this->kind = (string) $kind;
		$this->node_type = (string) $node_type;
		$this->path = $path;
		$this->before = $before;
		$this->after = $after;
	}

	public function to_array() {
		return array( 'kind' => $this->kind, 'node_type' => $this->node_type, 'path' => $this->path, 'before' => $this->before, 'after' => $this->after );
	}
}
