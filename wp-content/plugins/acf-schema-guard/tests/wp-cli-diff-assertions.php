<?php
namespace WP_CLI\Utils { function format_items($format,$items,$fields){ \WP_CLI::$items=$items; } }
namespace {
class WP_CLI { public static $items=array(); public static $lines=array(); public static $successes=array(); public static function error($m){throw new \RuntimeException($m);} public static function line($m){self::$lines[]=$m;} public static function success($m){self::$successes[]=$m;} }
define('ABSPATH',__DIR__.'/');
foreach(array('class-schema-snapshot.php','interface-snapshot-repository.php') as $f){require_once dirname(__DIR__).'/includes/snapshots/'.$f;}
foreach(array('class-schema-change.php','class-schema-diff.php','class-schema-differ.php','class-risk-finding.php','class-risk-classifier.php','class-snapshot-analysis.php','class-snapshot-analysis-service.php') as $f){require_once dirname(__DIR__).'/includes/diff/'.$f;}
require_once dirname(__DIR__).'/includes/cli/class-diff-command.php';
class AcfSchemaGuardDiffRepository implements \AcfSchemaGuard\Snapshots\SnapshotRepository { private $items; public function __construct($items){$this->items=$items;} public function insert(\AcfSchemaGuard\Snapshots\SchemaSnapshot $s){} public function find($id){return isset($this->items[$id])?$this->items[$id]:null;} public function latest_for_source($id){return null;} public function all(){return array_values($this->items);} }
function acf_schema_guard_diff_assert($ok,$msg){if(!$ok){throw new \RuntimeException($msg);}}
$before=new \AcfSchemaGuard\Snapshots\SchemaSnapshot('11111111-1111-1111-1111-111111111111','test',array('schema_version'=>1,'field_groups'=>array()),'2026-01-01 00:00:00');
$after=new \AcfSchemaGuard\Snapshots\SchemaSnapshot('22222222-2222-2222-2222-222222222222','test',array('schema_version'=>1,'field_groups'=>array()),'2026-01-02 00:00:00');
$command=new \AcfSchemaGuard\Cli\DiffCommand(new AcfSchemaGuardDiffRepository(array($before->id()=>$before,$after->id()=>$after)),new \AcfSchemaGuard\Diff\SnapshotAnalysisService(new \AcfSchemaGuard\Diff\SchemaDiffer(),new \AcfSchemaGuard\Diff\RiskClassifier()));
$command->diff(array($before->id(),$after->id()),array()); acf_schema_guard_diff_assert(!empty(WP_CLI::$successes),'No-change diff did not succeed.');
$command->diff(array($before->id(),$after->id()),array('format'=>'json')); acf_schema_guard_diff_assert(isset(json_decode(WP_CLI::$lines[0],true)['findings']),'JSON output is invalid.');
try{$command->diff(array($before->id(),'33333333-3333-3333-3333-333333333333'),array());throw new \RuntimeException('Missing snapshot did not fail.');}catch(\RuntimeException $e){acf_schema_guard_diff_assert(false!==strpos($e->getMessage(),'could not be found'),'Missing snapshot error is wrong.');}
echo "WP-CLI diff assertions passed.\n";
}
