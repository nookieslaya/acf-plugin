<?php
/**
 * Applies a repository-owned Markdown shell without changing the template file.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Licensing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ReviewReportTemplate {
	const SUMMARY_MARKER  = '<!-- acf-schema-guard:summary -->';
	const FINDINGS_MARKER = '<!-- acf-schema-guard:findings -->';

	public function render( $template_path, $summary, $findings, $fallback ) {
		if ( ! is_string( $template_path ) || ! is_readable( $template_path ) ) {
			return $fallback;
		}

		$template = file_get_contents( $template_path );
		if ( false === $template || false === strpos( $template, self::SUMMARY_MARKER ) || false === strpos( $template, self::FINDINGS_MARKER ) ) {
			return $fallback;
		}

		return str_replace( array( self::SUMMARY_MARKER, self::FINDINGS_MARKER ), array( $summary, $findings ), $template );
	}
}
