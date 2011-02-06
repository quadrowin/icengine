<?php

class Executor 
{
	
	const DELIM = '\\';
	
	/**
	 * Кэшер
	 * @var Data_Provider_Abstract
	 */
	protected static $_cacher;
	
	/**
	 * 
	 * @param function $function
	 * @param array $args
	 * @return string
	 */
	protected static function _getCacheKey ($function, array $args)
	{
		if (is_array ($function)) 
		{
			if (is_object ($function [0]))
			{
				$key = get_class ($function [0]) . self::DELIM . $function [1];
			}
			else
			{
				$key = $function [0] . self::DELIM . $function [1];
			}
		}
		elseif (is_string ($function))
		{
			$key = $function;
		}
		else
		{
			$key = md5 ($function);
		}
		
		$key .= self::DELIM;
		
		if ($args)
		{
			$key .= md5 (json_encode ($args));
		}
		
		return $key;
	}
	
	/**
	 * 
	 * @param function $function
	 * @param array $args
	 * @param Cache_Options $options
	 * @return mixed
	 */
	protected static function _executeCaching ($function, array $args, 
		Cache_Options $options)
	{
		$key = self::_getCacheKey ($function, $args);
		$key_hits = $key . '_h';
		
		$expiration = $options->getExpiration ();
		$hits = $options->getHits ();
		
		$cache = self::$_cacher->get ($key);
		
		if ($cache)
		{
			 if (
				$cache ['a'] + $expiration > time () || 
				$expiration == 0
			)
			{
				if (!$hits)
				{
					return $cache ['v'];
				}
				elseif (self::$_cacher->get ($key_hits) < $hits)
				{
					self::$_cacher->increment ($key_hits);
					return $cache ['v'];
				}
			}
			
			if (!self::$_cacher->lock ($key, 5, 1, 1))
			{
				// ключ уже заблокирова параллельным процессом
				return $cache ['v'];
			}
		}
		
		$value = self::_executeUncaching ($function, $args);
		
		self::$_cacher->set (
			$key, 
			array (
				'v' => $value,
				'a' => time ()
			)
		);
		
		if ($hits)
		{
			self::$_cacher->set ($key_hits, 0);
		}
		
		if ($cache)
		{
			self::$_cacher->unlock ($key);
		}
		
		return $value;
	}
	
	/**
	 * 
	 * @param function $function
	 * @param array $args
	 * @return mixed
	 */
	protected static function _executeUncaching ($function, array $args)
	{
		return call_user_func_array ($function, $args);
	}
	
	/**
	 * 
	 * @param function $function
	 * @param array $args
	 * @param Cache_Options $options
	 * @return mixed
	 */
	public static function execute ($function, array $args = array (), 
		$options = null)
	{
		if ($options instanceof Cache_Options && self::$_cacher)
		{
			return self::_executeCaching ($function, $args, $options);
		}
		
		return self::_executeUncaching ($function, $args);
	}
	
	/**
	 * Возвращает текущий кэшер.
	 * @return Data_Provider_Abstract|null
	 */
	public static function getCacher ()
	{
		return self::$_cacher;
	}
	
	/**
	 * Устаналвивает кэшер.
	 * @param Data_Provider_Abstract $cacher
	 */
	public static function setCacher ($cacher)
	{
		self::$_cacher = $cacher;
	}
	
}