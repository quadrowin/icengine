<?php

namespace Ice;

/**
 *
 * @desc Класс для автоматического подключения классов движка.
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Loader_Auto
{

	/**
	 * @desc Подключение автозагрузки классов
	 */
	public static function register ()
	{
		spl_autoload_register ('Ice\\Loader::load');
	}

	/**
	 * @desc Отключение автозагрузки классов
	 */
	public static function unregister ()
	{
		spl_autoload_unregister ('Ice\\Loader::load');
	}

}