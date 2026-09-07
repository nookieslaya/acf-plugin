<?php
namespace AcfSchemaGuard\Impact;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class CodeImpactAnalyzer {
	public function analyze( array $changes, array $references ) { $impacts=array(); foreach($changes as $change){ if(!is_array($change)||'field'!==$change['node_type']){continue;} $before=isset($change['before']['name'])?$change['before']['name']:''; $after=isset($change['after']['name'])?$change['after']['name']:''; foreach($references as $reference){$data=is_object($reference)&&method_exists($reference,'to_array')?$reference->to_array():(is_array($reference)?$reference:array()); if($before!==''&&isset($data['field_name'])&&$before===$data['field_name']){$impacts[]=new CodeImpact($change,$data,$this->severity($change),'Review this PHP ACF reference.');}}} return $impacts; }
	private function severity( array $change ) { if('removed'===$change['kind']){return 'critical';} if(!empty($change['before']['name'])&&!empty($change['after']['name'])&&$change['before']['name']!==$change['after']['name']){return 'high';} return 'warning'; }
}
