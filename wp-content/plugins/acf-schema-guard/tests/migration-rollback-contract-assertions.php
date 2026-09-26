<?php
define( 'ABSPATH', __DIR__ . '/' );
require_once dirname( __DIR__ ) . '/includes/migrations/class-migration-execution.php';
require_once dirname( __DIR__ ) . '/includes/migrations/class-migration-execution-table.php';
require_once dirname( __DIR__ ) . '/includes/migrations/class-migration-rollback-service.php';
function asg_rollback_assert( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } }
$source = file_get_contents( dirname( __DIR__ ) . '/includes/migrations/class-migration-rollback-service.php' );
asg_rollback_assert( false !== strpos( $source, 'target_meta_id' ), 'Rollback must use the journaled target meta identifier.' );
asg_rollback_assert( false !== strpos( $source, "'meta_key' => \$row['target_key']" ), 'Rollback must also match the expected target key.' );
asg_rollback_assert( false !== strpos( $source, 'MigrationExecution::COMPLETED' ), 'Rollback must reject unfinished executions.' );
asg_rollback_assert( false === strpos( $source, 'meta_value' ), 'Rollback must never read field values.' );
echo "Migration rollback contract assertions passed.\n";
