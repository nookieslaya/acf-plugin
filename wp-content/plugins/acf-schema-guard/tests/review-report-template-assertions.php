<?php
define( 'ABSPATH', __DIR__ . '/' );
require_once dirname( __DIR__ ) . '/includes/licensing/class-review-report-template.php';
$path = tempnam( sys_get_temp_dir(), 'asg-template-' );
file_put_contents( $path, "# Team\n\n<!-- acf-schema-guard:summary -->\n\n<!-- acf-schema-guard:findings -->\n\nFooter\n" );
$template = new \AcfSchemaGuard\Licensing\ReviewReportTemplate();
$result = $template->render( $path, 'Summary', 'Findings', 'Fallback' );
unlink( $path );
if ( '# Team' !== substr( $result, 0, 6 ) || false === strpos( $result, 'Summary' ) || false === strpos( $result, 'Findings' ) || false === strpos( $result, 'Footer' ) || 'Fallback' !== $template->render( '/missing', 'Summary', 'Findings', 'Fallback' ) ) { throw new RuntimeException( 'Review report template assertions failed.' ); }
echo "Review report template assertions passed.\n";
