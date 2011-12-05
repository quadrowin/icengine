<?php

namespace Ice;

if (!class_exists (__NAMESPACE__ . '\\Data_Provider_Abstract'))
{
	include __DIR__ . '/Abstract.php';
}

/**
 *
 * @desc Провайдер данных из адресной строки
 * @author Юрий Шведов
 * @package Ice
 *
 */
class Data_Provider_Router extends Data_Provider_Abstract
{

	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::get()
	 */
	public function get ($key, $plain = false)
	{
		return Request::param ($key);
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::getAll()
	 */
	public function getAll ()
	{
		return Request::$_params;
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::set()
	 */
	public function set ($key, $value, $expiration = 0, $tags = array ())
	{
		Request::param ($key, $value);
	}

}