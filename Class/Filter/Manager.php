<?php

Loader::load ('Filter_Abstract');

class Filter_Manager
{
	
	/**
	 * 
	 * @var array <Filter_Abstract>
	 */
	protected static $_filters = array ();
	
	/**
	 * @param string $name
	 * 		Фильтр.
	 * @return Filter_Abstract
	 */
	public static function get ($name)
	{
		if (isset (self::$_filters [$name]))
		{
			return self::$_filters [$name];
		}
		
		$class = 'Filter_' . $name;
		Loader::load ($class);
		return self::$_filters [$name] = new $class ();
	}
	
	/**
	 * Фильтрация
	 * @param string $name
	 * 		Фильтр
	 * @param mixed $data
	 * @return mixed
	 */
	public static function filter ($name, $data)
	{
		return self::get ($name)->filter ($data);
	}
	
	/**
	 * Фильтрация с использованием схемы
	 * @param string $name
	 * 		Фильтр
	 * @param string $field
	 * @param stdClass $data
	 * @param stdClass $scheme
	 * @return mixed
	 */
	public static function filterEx ($name, $field, $data, 
		stdClass $scheme)
	{
		return self::get ($name)->filterEx ($field, $data, $scheme);
	}
	
}