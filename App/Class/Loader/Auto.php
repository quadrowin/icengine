<?php
/**
 *
 * @desc Класс для автоматического подключения классов движка.
 * @author Юрий Шведов
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
		spl_autoload_register ('Loader::load');
	}

	/**
	 * @desc Отключение автозагрузки классов
	 */
	public static function unregister ()
	{
		spl_autoload_unregister ('Loader::load');
	}

}