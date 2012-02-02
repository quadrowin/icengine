<?php

namespace Ice;

/**
 *
 * @desc Абстрактный класс менеджера
 * @author Юрий Шведов, Илья Колесников
 * @package Ice
 *
 */
abstract class Manager_Abstract
{

	/**
	 * @desc Конфигурация
	 * @var array
	 */
	protected static $_config;

	/**
	 * @desc Загруженнные объекты
	 * @var array
	 */
	protected static $_objects = array ();

	/**
	* @desc Получить объект по имени
	* @param string $name
	* @return Object
	*/
	public function byName ($name)
	{
		$class = self::completeClassName ($name, $this->getName ());
		return $this->get ($class);
	}

	/**
	 * @desc Получение названия класса по названию экземпляра.
	 * @param string $name
	 * @param string $ext [optional]
	 * @return string
	 */
	public static function completeClassName ($name, $ext = null)
	{
		if (null === $ext)
		{
			$ext = substr (get_called_class (), 0, -strlen ('_Manager'));
		}

		$p = strrpos ($name, '\\');

		// название без неймспейса
		if (!$p)
		{
			return $ext . '_' . $name;
		}

		// исключаем неймспейс менеджера
		$p_ext = strrpos ($ext, '\\');
		if ($p_ext)
		{
			$ext = substr ($ext, $p_ext + 1);
		}

		return substr ($name, 0, $p + 1) . $ext . '_' . substr ($name, $p + 1);
	}

	/**
	 * @desc Конфиги менеджера
	 * @return Objective
	 */
	public static function config ()
	{
		if (is_array (static::$_config))
		{
			static::$_config = Config_Manager::get (
				get_called_class (),
				static::$_config
			);
		}
		return static::$_config;
	}

	/**
	 * @desc Получить имя менеджера
	 * @return string
	 */
	public function getName ()
	{
		$class = get_class ($this);
		return substr ($class, 0, -strlen ('_Manager'));
	}

	/**
	 * @desc Получить объект по имени класса
	 * @param string $class
	 * @return Object
	 */
	public function get ($class)
	{
		if (!isset (self::$_objects [$class]))
		{
			Loader::load ($class);
			self::$_objects [$class] = new $class;
		}

		return self::$_objects [$class];
	}

}