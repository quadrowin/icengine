<?php

namespace Ice;

/**
 *
 * @desc Менеджер загрузчиков
 * @author Юрий Шведов
 * @package Ice
 *
 */
class Bootstrap_Manager
{

	/**
	 * @desc Загрузчики
	 * @var array <Bootstrap_Abstract>
	 */
	protected static $_items;

	/**
	 * @desc Текущий загрузчик
	 * @var Bootstrab_Abstract
	 */
	protected static $_current;

	/**
	 * @desc Создает и возвращает загрузчик
	 * @param string $class Класс загрузчика
	 * @param string $path [optional] Путь до файла загрузчика
	 * @return Bootstrap_Abstract Экземпляр загрузчика.
	 */
	public static function get ($class, $path = null)
	{
		if (!isset (self::$_items [$class]))
		{
			if (!class_exists ($class))
			{
				Loader::load ('Bootstrap_Abstract');

				if ($path)
				{
					require $path;
				}
				else
				{
					Loader::load ($class);
				}
			}

			self::$_items [$class] = new $class ();
		}

		if (!self::$_current)
		{
			self::$_current = self::$_items [$class];
		}

		return self::$_items [$class];
	}

	/**
	 * @desc Возвращает текущий загрузчик.
	 * @return Bootstrap_Abstract
	 */
	public static function current ()
	{
		return self::$_current;
	}

}