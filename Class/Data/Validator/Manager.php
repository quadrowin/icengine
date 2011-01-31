<?php

class Data_Validator_Manager
{
	
	/**
	 * Валидаторы
	 * @var array <Data_Validator_Abstract>
	 */
	private static $_validators = array ();
	
	/**
	 * 
	 * @param string $name
	 * @return Data_Validator_Abstract
	 */
	public static function get ($name)
	{
		if (isset (self::$_validators [$name]))
		{
			return self::$_validators [$name];
		}
		
		$class = 'Data_Validator_' . $name;
		Loader::load ($class);
		return self::$_validators [$name] = new $class;
	}
	
	/**
	 * Проверка данных
	 * @param string $name
	 * 		Валидатор.
	 * @param mixed $data
	 * @return true|string
	 */
	public static function validate ($name, $data)
	{
		return self::get ($name)->validate ($data);
	}
	
	/**
	 * Проверка данных валидатором
	 * 
	 * @param string $name
	 * 		Валидатор.
	 * @param string $field
	 * 		Проверяемое поле
	 * @param StdClass $data
	 * @param StdClass $scheme
	 * @return true|string
	 * 		true, если данные прошли валидацию.
	 * 		Иначе - строкове представление ошибки в виде: 
	 * 		"Имя_Валидатора/ошибка"
	 */
	public static function validateEx ($name, $field, StdClass $data, 
		StdClass $scheme)
	{
		return self::get ($name)->validateEx ($field, $data, $scheme);
	}
	
}