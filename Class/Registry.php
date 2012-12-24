<?php

/**
 * Регистр глобальных переменных
 *
 * @author goorus, morph
 * @Service("registry") 
 */
class Registry
{
	/**
	 * Хранимые данные
	 * 
     * @var array
	 */
	protected static $data = array ();

	/**
	 * Возвращает true, если значение задано и отлично от null.
	 * Иначе false.
	 * 
     * @param string $index
	 * @return boolean
	 */
	public function defined($index)
	{
		return isset(self::$data[$index]);
	}

	/**
	 * Чтение значения
	 * 
     * @param string $index
	 * @return mixed
	 */
	public  function get($index)
	{
		return self::$data[$index];
	}

	/**
	 * Запись значения
	 * 
     * @param string $index
	 * @param mixed $value
	 */
	public function set($index, $value)
	{
		self::$data[$index] = $value;
	}

	/**
	 * Silent get. Не вызывает ошибки, если значение не определено.
	 * 
     * @param string $index
	 * @return mixed|null
	 */
	public function sget($index)
	{
		return isset(self::$data[$index]) ? self::$data[$index] : null;
	}

	/**
	 * Получение по ссылке
	 * 
     * @param string $index
	 * @return $mixed
	 */
	public function &rget($index)
	{
		return self::$data[$index];
	}

	/**
	 * Передача по ссылке
	 * 
     * @param string $index
	 * @param mixed $value
	 */
	public function rset($index, &$value)
	{
		self::$data[$index] = &$value;
	}
}