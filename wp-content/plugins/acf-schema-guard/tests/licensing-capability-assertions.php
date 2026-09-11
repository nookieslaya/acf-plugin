<?php
/**
 * Focused assertions for the provider-neutral Free and Pro boundary.
 *
 * @package ACFSchemaGuard
 */

define( 'ABSPATH', __DIR__ . '/' );

require_once dirname( __DIR__ ) . '/includes/licensing/class-pro-capabilities.php';
require_once dirname( __DIR__ ) . '/includes/licensing/class-license-state.php';
require_once dirname( __DIR__ ) . '/includes/licensing/class-capability-decision.php';
require_once dirname( __DIR__ ) . '/includes/licensing/class-capability-service.php';
require_once dirname( __DIR__ ) . '/includes/licensing/class-local-preview-state-provider.php';

function acf_schema_guard_license_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

use AcfSchemaGuard\Licensing\CapabilityService;
use AcfSchemaGuard\Licensing\LicenseState;
use AcfSchemaGuard\Licensing\LocalPreviewStateProvider;
use AcfSchemaGuard\Licensing\ProCapabilities;

$policy = ProCapabilities::CONFIGURABLE_RISK_POLICIES;
$report = ProCapabilities::REVIEW_READY_REPORTS;

$valid = new CapabilityService( LicenseState::valid( array( $policy ) ) );
acf_schema_guard_license_assert( $valid->can( $policy )->is_allowed(), 'A valid state must allow its explicitly granted capability.' );
acf_schema_guard_license_assert( ! $valid->can( $report )->is_allowed(), 'A valid state must not grant a capability it does not list.' );
acf_schema_guard_license_assert( ! $valid->can( 'unknown' )->is_allowed(), 'Unknown capabilities must never be granted.' );

foreach ( array( LicenseState::EXPIRED, LicenseState::INVALID, LicenseState::MISSING, LicenseState::UNVERIFIABLE ) as $status ) {
	$decision = ( new CapabilityService( new LicenseState( $status, ProCapabilities::all() ) ) )->can( $policy );
	acf_schema_guard_license_assert( ! $decision->is_allowed() && '' !== $decision->reason(), $status . ' licenses must deny Pro capabilities with a reason.' );
}

$local_without_token = new LocalPreviewStateProvider( static function() { return 'local'; } );
acf_schema_guard_license_assert( LicenseState::UNVERIFIABLE === $local_without_token->state()->status(), 'Local preview must remain Free when no token is configured.' );

define( 'ACF_SCHEMA_GUARD_LOCAL_PRO_PREVIEW_TOKEN', LocalPreviewStateProvider::TOKEN );
$local_with_token = new LocalPreviewStateProvider( static function() { return 'local'; } );
acf_schema_guard_license_assert( $local_with_token->state()->allows( $policy ), 'The local token must enable only local Pro preview.' );

$production_with_token = new LocalPreviewStateProvider( static function() { return 'production'; } );
acf_schema_guard_license_assert( LicenseState::UNVERIFIABLE === $production_with_token->state()->status(), 'The preview token must never grant Pro capability outside a local environment.' );

$plugin_source = file_get_contents( dirname( __DIR__ ) . '/includes/class-plugin.php' );
acf_schema_guard_license_assert( false !== strpos( $plugin_source, "apply_filters( 'acf_schema_guard/license_state'" ), 'Plugin composition must provide a provider-neutral license-state filter seam.' );

echo "Licensing capability assertions passed.\n";
