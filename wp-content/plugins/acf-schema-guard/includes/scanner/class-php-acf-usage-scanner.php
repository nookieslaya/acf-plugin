<?php
namespace AcfSchemaGuard\Scanner;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class PhpAcfUsageScanner implements CodeUsageScanner {
	public function strategy() { return 'php-acf'; }
	public function scan( array $source_roots ) { $references=array(); foreach($source_roots as $root){if(!is_string($root)||!is_dir($root)){continue;} $iterator=new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root,\FilesystemIterator::SKIP_DOTS)); foreach($iterator as $file){if($file->isFile()&&is_readable($file->getPathname())&&'php'===strtolower($file->getExtension())){$references=array_merge($references,$this->scan_file($file->getPathname(),$root));}}} return $references; }
	private function scan_file( $path, $root ) { $content=file_get_contents($path); if(false===$content){return array();} $references=array(); $pattern='/\b(get_field|the_field|get_sub_field|the_sub_field|have_rows|get_field_object)\s*\(\s*([\'\"])([^\'\"]+)\2/'; preg_match_all($pattern,$content,$matches,PREG_OFFSET_CAPTURE); foreach($matches[0] as $index=>$match){$line=substr_count(substr($content,0,$match[1]),"\n")+1;$references[]=new CodeUsageReference($matches[3][$index][0],$this->strategy(),ltrim(str_replace($root,'',$path),DIRECTORY_SEPARATOR),$line,$match[0]);} return $references; }
}
