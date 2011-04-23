<?php
/**
 * 
 * @desc Менеджер алгоритмов шифрования.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Crypt_Manager
{
	
	/**
	 * @desc Загруженные алгоритмы шифрования.
	 * @var array
	 */
	protected static $_crypts = array ();
	
	/**
	 * @desc Возвращает экземпляр класс, реализующего алгоритм шифрования.
	 * @param string $name Название алгоритма шифрования.
	 * @return Crypt_Abstract
	 */
	public static function get ($name)
	{
		if (!isset (self::$_crypts [$name]))
		{
			$crypt = 'Crypt_' . $name;
			Loader::load ($crypt);
			self::$_crypts [$name] = new $crypt;
		}
		return self::$_crypts [$name];
	}
	
}