<?php
/**
 * 
 * Запись трейса в файл
 * @author Юрий
 *
 */
class Tracer_File extends Tracer_Abstract
{
	
	/**
	 * Файл для сохранения
	 * @var string
	 */
	public $file = 'cache/trace.txt';
	
	public function add ($info)
	{
		$f = fopen ($this->file, 'a');
		fwrite ($f, Helper_Date::toUnix () . ' ' . $info . PHP_EOL);
		fclose ($f);
	}
	
	public function filter ($filter)
	{
		
	}
	
	public function full ()
	{
		return array ();
	}
	
}