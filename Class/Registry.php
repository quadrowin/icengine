<?php

class Registry
{
	
	/**
	 * Хранимые данные
	 * @var array
	 */
	public static $data = array ();
	
	/**
	 * Возвращает true, если значение задано и отлично от null.
	 * Иначе false.
	 * @param string $index
	 * @return boolean
	 */
	public static function defined ($index)
	{
		return isset (self::$data [$index]);
	}
	
	/**
	 * Чтение значения
	 * @param string $index
	 * @return mixed
	 */
	public static function get ($index)
	{
		return self::$data [$index];
	}
	
	/**
	 * Запись значения
	 * @param string $index
	 * @param mixed $value
	 */
	public static function set ($index, $value)
	{
		self::$data [$index] = $value;
	}
	
	/**
	 * silent get. Не вызывает ошибки, если значение не определено.
	 * @param string $index
	 * @return mixed|null
	 */
	public static function sget ($index)
	{
		return isset (self::$data [$index]) ? self::$data [$index] : null;
	}
	
	/**
	 * Получение по ссылке
	 * @param string $index
	 * @return $mixed
	 */
	public static function &rget ($index)
	{
		return self::$data [$index];
	}
	
	/**
	 * Передача по ссылке 
	 * @param string $index
	 * @param mixed $value
	 */
	public static function rset ($index, &$value)
	{
		self::$data [$index] = &$value;
	}
	
}