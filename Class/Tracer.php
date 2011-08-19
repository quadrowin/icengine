<?php
/**
 * 
 * @desc Трейсер
 * @author Юрий Шведов
 * @package IcEngine
 * 
 */
class Tracer
{
	
	/**
	 * @desc Состояние трейсера.
	 * @var boolean
	 */
	public static $enabled = true;
	
	/**
	 * @desc лог событий
	 * @var array
	 */
	public static $logs = array ();
	
	/**
	 * @desc Время подключения трейсераs
	 * @var float
	 */
	public static $startTime;
	
	/**
	 * @desc Запись результата работы в файл
	 * @param mixed $target Куда направить результат.
	 */
	public static function flushFile ($file, $mode = 'w')
	{
		$f = fopen ($file, $mode);
		
		fwrite (
			$f,
			date ('Y-m-d H:i:s ') .
			(
				isset ($_SERVER ['HTTP_HOST']) 
				? $_SERVER ['HTTP_HOST'] 
				: 'nohost'
			) .
			'/' .
			(
				isset ($_SERVER ['REQUEST_URI'])
				? $_SERVER ['REQUEST_URI']
				: 'nouri'
			) .
			"\r\n"
		);
		
		$border = '';
		$level = 0;
		// Время по уровням
		$times = array ($level => self::$startTime);
		
		foreach (self::$logs as $log)
		{
			if ($log ['l'] < 0)
			{
				$border = substr ($border, 0, -1);
				--$level;
			}
			
			$dt = $log ['a'] - $times [$level];
			
			fwrite (
				$f,
				$border . 
				round ($log ['a'] - self::$startTime, 5) . "\t" .
				$dt . "\t" .
				$type . "\t" .
				(
					is_scalar ($log ['m'])
					? $log ['m']
					: json_encode ($log ['m'])
				) .
				"\r\n"
			);
			
			if ($log ['l'] > 0)
			{
				++$level;
				$border .= "\t";
				$times [$level] = $log ['m'];
			}
		}
		fwrite ($f, "\r\n");
		
		fclose ($f);
	}
	
	/**
	 * @desc Записо события
	 * @param string $message
	 * @param integer $level (-1, 0, +1)
	 * @param string $type 
	 */
	public static function log ($message, $level = 0, $type = '')
	{
		if (self::$enabled)
		{
			self::$logs [] = array (
				'a'		=> time (),
				'm'		=> $message,
				't'		=> $type,
				'l'		=> $level
			);
		}
	}
	
	/**
	 * @desc Возвращает время, прошедшее с подключение трейсера.
	 * @return float 
	 */
	public static function microtime ()
	{
		return microtime (true) - self::$startTime;
	}
	
}

Tracer::$startTime = microtime (true);