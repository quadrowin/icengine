<?php

namespace Ice;

/**
 *
 * @desc Приложение.
 * Для корректной работы потомки должны находиться
 * в соответвующих приложению пространстве имен в директории "App/Class".
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Application {

	/**
	 * @desc Пути до приложений
	 * @var array
	 */
	protected static $_dirs = array ();

	/**
	 * @desc Возвращает путь до корня приложения.
	 * Путь до приложения отличается от пути до корня сайта.
	 * @return string Путь до корня приложения
	 */
	public static function getDir ()
	{
		$class = get_called_class ();
		if (!isset (self::$_dirs [$class]))
		{
			$r = new \ReflectionClass ($class);
			self::$_dirs [$class] = realpath (
				dirname ($r->getFileName ()) . '/../..'
			);
		}
		return self::$_dirs [$class];
	}

}
