<?php
namespace AcfSchemaGuard\Licensing;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class RiskPolicyService {
	private $capabilities;
	public function __construct( CapabilityService $capabilities ) { $this->capabilities = $capabilities; }
	public function policy() {
		$path = $this->team_policy_path();
		if ( is_readable( $path ) ) { $data = json_decode( (string) file_get_contents( $path ), true ); if ( is_array( $data ) && isset( $data['fail_on'] ) && RiskPolicy::is_valid( $data['fail_on'] ) ) { return new RiskPolicy( $data['fail_on'] ); } }
		return new RiskPolicy( get_option( RiskPolicy::OPTION_NAME, 'high' ) );
	}
	public function team_policy_path() { return ABSPATH . 'acf-schema-guard-policy.json'; }
	public function has_team_policy() { return is_readable( $this->team_policy_path() ); }
	public function export() { return wp_json_encode( array( 'schema_version' => 1, 'fail_on' => $this->policy()->fail_on() ), JSON_PRETTY_PRINT ) . "\n"; }
	public function save( $fail_on ) {
		if ( ! $this->capabilities->can( ProCapabilities::CONFIGURABLE_RISK_POLICIES )->is_allowed() || ! RiskPolicy::is_valid( $fail_on ) ) { return false; }
		return update_option( RiskPolicy::OPTION_NAME, (string) $fail_on, false );
	}
}
