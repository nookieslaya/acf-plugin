<?php
namespace AcfSchemaGuard\Cli;
use AcfSchemaGuard\Diff\SnapshotAnalysisService;
use AcfSchemaGuard\Snapshots\SnapshotRepository;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class CheckCommand {
	private $snapshots; private $analysis_service;
	public function __construct( SnapshotRepository $snapshots, SnapshotAnalysisService $analysis_service ) { $this->snapshots=$snapshots; $this->analysis_service=$analysis_service; }
	public function check( $args, $assoc_args ) {
		if ( 2 !== count( $args ) ) { \WP_CLI::error( 'Provide exactly two snapshot IDs: before-id and after-id.' ); }
		$before=$this->snapshots->find($args[0]); $after=$this->snapshots->find($args[1]);
		if ( null === $before || null === $after ) { \WP_CLI::error( 'One or both snapshots could not be found.' ); }
		$analysis=$this->analysis_service->analyze($before,$after)->to_array(); $format=isset($assoc_args['format'])?strtolower((string)$assoc_args['format']):'table';
		if ( ! in_array($format,array('table','json'),true) ) { \WP_CLI::error( sprintf('Unsupported format: %s. Use table or json.',$format) ); }
		if ( 'json' === $format ) { \WP_CLI::line(json_encode($analysis,JSON_PRETTY_PRINT)); } else { $this->table($analysis['findings']); }
		if ( isset($assoc_args['fail-on-breaking']) && $this->has_breaking($analysis['findings']) ) { \WP_CLI::error( 'Breaking schema changes found.' ); }
	}
	private function table($findings) { if(empty($findings)){\WP_CLI::success('No schema changes found.');return;} $items=array();foreach($findings as $f){$c=$f['change'];$items[]=array('kind'=>$c['kind'],'node_type'=>$c['node_type'],'path'=>implode('.',$c['path']),'severity'=>$f['severity'],'rationale'=>$f['rationale']);}\WP_CLI\Utils\format_items('table',$items,array('kind','node_type','path','severity','rationale')); }
	private function has_breaking($findings) { foreach($findings as $f){if(in_array($f['severity'],array('high','critical'),true)){return true;}} return false; }
}
