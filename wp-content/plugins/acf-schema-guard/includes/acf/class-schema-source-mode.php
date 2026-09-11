<?php
namespace AcfSchemaGuard\Acf;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class SchemaSourceMode {
	const DATABASE_FIRST = 'database_first';
	const LOCAL_JSON = 'local_json';
	private $mode;
	private $paths;
	public function __construct( $mode, array $paths = array() ) {
		$this->mode = self::LOCAL_JSON === $mode ? self::LOCAL_JSON : self::DATABASE_FIRST;
		$this->paths = array_values( array_filter( $paths, 'is_string' ) );
	}
	public function mode() { return $this->mode; }
	public function paths() { return $this->paths; }
	public function is_database_first() { return self::DATABASE_FIRST === $this->mode; }
	public function message() { return $this->is_database_first() ? 'ACF Local JSON is not configured. Schema Guard is using the database-first workflow.' : 'ACF Local JSON is configured. Schema Guard is comparing the database and Local JSON.'; }
}
