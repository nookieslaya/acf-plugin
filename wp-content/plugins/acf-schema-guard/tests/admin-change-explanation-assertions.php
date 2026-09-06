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

	function esc_html__( $text ) {
		return esc_html( $text );
	}

	function esc_attr( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}

	function esc_attr__( $text ) {
		return esc_attr( $text );
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

	$legend_renderer = $reflection->getMethod( 'render_severity_legend' );
	$legend_renderer->setAccessible( true );

	ob_start();
	$legend_renderer->invoke( $controller );
	$legend_output = ob_get_clean();

	foreach ( array( 'Safe', 'Warning', 'High', 'Critical' ) as $severity_label ) {
		acf_schema_guard_admin_explanation_assert( false !== strpos( $legend_output, '>' . $severity_label . '</span>' ), 'Severity legend label is missing.' );
	}
	acf_schema_guard_admin_explanation_assert( false !== strpos( $legend_output, 'aria-labelledby="acf-schema-guard-severity-legend-title"' ), 'Severity legend has no accessible label.' );

	$normalizer = $reflection->getMethod( 'normalize_severity' );
	$normalizer->setAccessible( true );
	acf_schema_guard_admin_explanation_assert( 'critical' === $normalizer->invoke( $controller, 'CRITICAL' ), 'Known severity was not normalized.' );
	acf_schema_guard_admin_explanation_assert( 'warning' === $normalizer->invoke( $controller, 'critical injected-class' ), 'Unknown severity did not use the safe fallback.' );

	$analysis = new class() {
		public function to_array() {
			return array(
				'findings' => array(
					array(
						'change'      => array(
							'kind'      => 'modified',
							'node_type' => 'field',
							'path'      => array( 'group_hero', 'field_title' ),
						),
						'explanation' => array(
							'summary' => 'Field modified.',
							'details' => array( 'Field type: "text" -> "textarea"' ),
						),
						'severity'    => 'high',
						'rationale'   => 'Field type changed.',
					),
				),
			);
		}
	};
	$analysis_callback = $reflection->getProperty( 'analyze_snapshots_callback' );
	$analysis_callback->setAccessible( true );
	$analysis_callback->setValue(
		$controller,
		function () use ( $analysis ) {
			return $analysis;
		}
	);

	$comparison_renderer = $reflection->getMethod( 'render_comparison_results' );
	$comparison_renderer->setAccessible( true );
	ob_start();
	$comparison_renderer->invoke( $controller, null, null );
	$comparison_output = ob_get_clean();

	foreach ( array( 'Kind', 'Node type', 'Path', 'Change details', 'Severity', 'Rationale' ) as $cell_label ) {
		acf_schema_guard_admin_explanation_assert( false !== strpos( $comparison_output, 'data-label="' . $cell_label . '"' ), 'Responsive cell label is missing.' );
	}
	acf_schema_guard_admin_explanation_assert( false !== strpos( $comparison_output, 'acf-schema-guard-finding-high' ), 'Finding severity class is missing.' );
	acf_schema_guard_admin_explanation_assert( false !== strpos( $comparison_output, '>High</span>' ), 'Finding severity text is missing.' );

	echo "Admin change explanation assertions passed.\n";
}
