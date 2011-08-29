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
			self::flush (
				self::$sessions [self::$currentSession + 1],
				self::$currentSession
			);
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
		
		$delta = microtime (true) - (
			isset ($logs [$current_index - 1])
				? $logs [$current_index - 1]['mt']
				: self::$sessions [self::$currentSession]['mt']
			);
		
		self::$sessions [self::$currentSession]['logs'][] = array (
			'args'	=> func_get_args (),
			'mt'	=> $mt,
			'delta'	=> $delta
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
	
	public static function flush ($session, $offset = 0)
	{
		if (!self::$enabled)
		{
			return;
		}
		
		$file_name = IcEngine::root () . 'log/tracer';
		
		$offset = $offset 
			? str_repeat ("\t", $offset)
			: '';
		
		$output  = 
			$offset . 'Start at ' . date ('Y-m-d H:i:s', $session ['time']) . 
			PHP_EOL .
			$offset . 'Args: ' . json_encode ($session ['args']) . 
			PHP_EOL .
			$offset . 'Microtime: ' . $session ['mt'] . 
			PHP_EOL .
			$offset . 'Logs: ' . PHP_EOL;

		foreach ($session ['logs'] as $i => $log)
		{
			$output .= $offset . '#' . $i . ' '. 
				round ($log ['mt'], 4) . ' ' . 
				round ($log ['delta'], 6) . ' ' . 
				json_encode ($log ['args']) . PHP_EOL;
		}

		$output  .= $offset . 'Finished at ' . date ('Y-m-d H:i:s', $session ['endTime']) . PHP_EOL;
		
		file_put_contents ($file_name, $output, FILE_APPEND);
	}
}