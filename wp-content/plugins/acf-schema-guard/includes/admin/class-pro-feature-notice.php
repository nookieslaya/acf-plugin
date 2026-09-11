<?php
/**
 * Renders a reusable explanation for future Pro-only actions.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Admin;

use AcfSchemaGuard\Licensing\CapabilityDecision;

if ( ! defined( 'ABSPATH' ) ) {

	exit;
}

final class ProFeatureNotice {
	/**
	 * Renders nothing for an available capability.
	 *
	 * @param CapabilityDecision $decision Capability decision.
	 * @return void
	 */
	public function render( CapabilityDecision $decision ) {
		if ( $decision->is_allowed() ) {
			return;
		}
		?>
		<div class="notice notice-info acf-schema-guard-pro-notice" role="status">
			<p><strong><?php esc_html_e( 'ACF Schema Guard Pro', 'acf-schema-guard' ); ?></strong> <?php echo esc_html( $decision->reason() ); ?></p>
		</div>
		<?php
	}
}
