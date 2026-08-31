<?php
namespace AcfSchemaGuard\Scanner;
use RuntimeException;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class CodeUsageReference {
	private $field_name; private $strategy; private $path; private $line; private $expression;
	public function __construct( $field_name, $strategy, $path, $line, $expression ) { foreach(array($field_name,$strategy,$path,$expression) as $value){if(''===trim((string)$value)){throw new RuntimeException('Code usage reference values cannot be empty.');}} if(1>(int)$line){throw new RuntimeException('Code usage reference line must be positive.');} $this->field_name=(string)$field_name;$this->strategy=(string)$strategy;$this->path=(string)$path;$this->line=(int)$line;$this->expression=(string)$expression; }
	public function to_array(){return array('field_name'=>$this->field_name,'strategy'=>$this->strategy,'path'=>$this->path,'line'=>$this->line,'expression'=>$this->expression);}
}
