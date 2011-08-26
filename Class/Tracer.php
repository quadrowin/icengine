<?php
/**
 * 
 * @desc Трейсер
 * @author Юрий Шведов, Илья Колесников
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
	 * @desc Сливать ли в файл для каждой
	 * @var type 
	 */
	public static $flushPerSession = true;
	
	public static $sessions = array ();
	
	public static $currentSession = 0;
	
	public static function begin ()
	{
		if (!self::$enabled)
		{
			return;
		}
		
		self::$currentSession++;
		
		self::$sessions [self::$currentSession] = array (
			'args'	=> func_get_args (),
			'mt'	=> microtime (true),
			'time'	=> time (),
			'logs'	=> array ()
		);
	}
	
	public static function end ()
	{
		if (!self::$enabled)
		{
			return;
		}
		
		$args = func_get_args ();
		
		if ($args)
		{
			self::log ($args);
		}
		
		self::$sessions [self::$currentSession]['endTime'] = time ();
		
		self::$currentSession--;
		
		if (self::$flushPerSession)
		{
			self::flush (self::$sessions [self::$currentSession + 1]);
		}
	}
	
	public static function log ()
	{
		if (!self::$enabled)
		{
			return;
		}
		
		if (!isset (self::$sessions [self::$currentSession]['logs']))
		{
			self::$sessions [self::$currentSession]['logs'] = array ();
		}
		
		$mt = microtime (true);
		
		$logs = self::$sessions [self::$currentSession]['logs'];
		
		$current_index = sizeof ($logs);
		
		self::$sessions [self::$currentSession]['logs'][] = array (
			'args'	=> func_get_args (),
			'mt'	=> $mt,
			'delta'	=> microtime (true) - (
				isset ($logs [$current_index - 1])
					? $logs [$current_index - 1]['mt']
					: self::$sessions [self::$currentSession]['mt']
				)
		);
	}
	
	public static function flushAll ()
	{
		if (!self::$enabled)
		{
			return;
		}
		
		foreach (self::$sessions as $session)
		{
			self::flush ($session);
		}
	}
	
	public static function flush ($session)
	{
		if (!self::$enabled)
		{
			return;
		}
		
		$file_name = IcEngine::root () . 'log/tracer';
		
		$output  = 'Session start at ' . date ('Y-m-d H:i:s', $session ['time']) . PHP_EOL;
		$output .= 'Args: ' . serialize ($session ['args']) . PHP_EOL;
		$output .= 'Microtime: ' . $session ['mt'] . PHP_EOL;
		$output .= 'Logs: ' . PHP_EOL;

		foreach ($session ['logs'] as $i => $log)
		{
			$output .= '#' . $i . ' '. $log ['mt'] . ' ' . $log ['delta'] . ' ' .
				serialize ($log ['args']) . PHP_EOL;
		}

		$output  .= 'Session finished at ' . date ('Y-m-d H:i:s', $session ['endTime']) . PHP_EOL;
		
		file_put_contents ($file_name, $output, FILE_APPEND);
	}
}