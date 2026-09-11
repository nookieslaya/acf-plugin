<?php
define( 'ABSPATH', __DIR__ . '/' );
require_once dirname( __DIR__ ) . '/includes/licensing/class-risk-policy.php';
$warning = new \AcfSchemaGuard\Licensing\RiskPolicy( 'warning' );
$high = new \AcfSchemaGuard\Licensing\RiskPolicy( 'high' );
$critical = new \AcfSchemaGuard\Licensing\RiskPolicy( 'critical' );
if ( ! $warning->fails( 'warning' ) || ! $high->fails( 'high' ) || $high->fails( 'warning' ) || ! $critical->fails( 'critical' ) || $critical->fails( 'high' ) || 'high' !== ( new \AcfSchemaGuard\Licensing\RiskPolicy( 'bad' ) )->fail_on() ) { throw new RuntimeException( 'Risk policy assertion failed.' ); }
echo "Risk policy assertions passed.\n";
