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
	 * @desc Получение названия класса по названию экземпляра.
	 * @param string $name
	 * @param string $ext
	 * @return string
	 */
	public static function completeClassName ($name, $ext = null)
	{
		if (null === $ext)
		{
			$ext = substr (get_called_class(), 0, -strlen ('_Manager'));
		}

		$p = strrpos ($name, '\\');
		return (false === $p)
			? __NAMESPACE__ . '\\' . $ext . '_' . $name
			: substr ($name, 0, $p + 1) . $ext . '_' . substr ($name, $p + 1);
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

}