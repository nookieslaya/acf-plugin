<?php
/**
 * Isolated assertions for safe Admin change-explanation rendering.
 *
 * @package ACFSchemaGuard
 */

namespace {
	define( 'ABSPATH', __DIR__ . '/' );

	function __( $text ) {
		return $text;
	}

	function esc_html( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}

	require_once dirname( __DIR__ ) . '/includes/admin/class-admin-controller.php';

	function acf_schema_guard_admin_explanation_assert( $condition, $message ) {
		if ( ! $condition ) {
			throw new \RuntimeException( $message );
		}
	}

	$reflection = new \ReflectionClass( '\\AcfSchemaGuard\\Admin\\AdminController' );
	$controller = $reflection->newInstanceWithoutConstructor();
	$renderer   = $reflection->getMethod( 'render_change_explanation' );
	$renderer->setAccessible( true );

	ob_start();
	$renderer->invoke(
		$controller,
		array(
			'summary' => 'Field <script>alert(1)</script> modified.',
			'details' => array( 'Field type: <img src=x> -> "text"', array( 'ignored' ), '' ),
		)
	);
	$output = ob_get_clean();

	acf_schema_guard_admin_explanation_assert( false === strpos( $output, '<script>' ), 'Admin summary was not escaped.' );
	acf_schema_guard_admin_explanation_assert( false === strpos( $output, '<img' ), 'Admin detail was not escaped.' );
	acf_schema_guard_admin_explanation_assert( false !== strpos( $output, '&lt;script&gt;' ), 'Escaped Admin summary is missing.' );
	acf_schema_guard_admin_explanation_assert( false !== strpos( $output, '&lt;img src=x&gt;' ), 'Escaped Admin detail is missing.' );
	acf_schema_guard_admin_explanation_assert( 1 === substr_count( $output, '<li>' ), 'Invalid or empty detail values should be ignored.' );

	ob_start();
	$renderer->invoke( $controller, array( 'summary' => 'Field modified.', 'details' => array() ) );
	$empty_output = ob_get_clean();
	acf_schema_guard_admin_explanation_assert( false !== strpos( $empty_output, 'Field modified.' ), 'Admin summary with empty details is missing.' );
	acf_schema_guard_admin_explanation_assert( false === strpos( $empty_output, '<ul' ), 'Empty details should not render a list.' );

	echo "Admin change explanation assertions passed.\n";
}
