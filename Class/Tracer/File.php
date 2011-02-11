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
		$text = Helper_Date::toUnix () . ' ' . $info;
		for ($i = 1; $i < func_num_args (); ++$i)
		{
			$text .= ' ' . json_encode ($text);
		}
		$f = fopen ($this->file, 'a');
		fwrite ($f, $text . PHP_EOL);
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