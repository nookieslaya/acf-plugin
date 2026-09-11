<?php
namespace AcfSchemaGuard\Licensing;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class RiskPolicy {
	const OPTION_NAME = 'acf_schema_guard_pro_risk_policy';
	private $fail_on;
	public function __construct( $fail_on = 'high' ) { $this->fail_on = self::is_valid( $fail_on ) ? $fail_on : 'high'; }
	public function fail_on() { return $this->fail_on; }
	public static function is_valid( $value ) { return in_array( (string) $value, array( 'warning', 'high', 'critical' ), true ); }
	public function fails( $severity ) {
		$levels = array( 'safe' => 0, 'warning' => 1, 'high' => 2, 'critical' => 3 );
		return isset( $levels[ $severity ] ) && $levels[ $severity ] >= $levels[ $this->fail_on ];
	}
}
