<?php

namespace Ice;

/**
 * @desc Менеджер рассылок
 * @author Ilya Kolesnikov
 * @package Ice
 *
 */
class Subscribe_Manager
{
	/**
	 *
	 * @desc Получить рассылку по имени
	 * @param string $name
	 * @return Model
	 */
	public static function byName ($name)
	{
		return Model_Manager::getInstance ()->byQuery (
			'Subscribe',
			Query::instance ()
				->where ('name', $name)
		);
	}

	/**
	 *
	 * @desc Получить конфиг по имени
	 * @param string $name
	 * @return Config_Array
	 */
	public static function config ($name)
	{
		$config = Config_Manager::get (get_class ($this));
		if (!$config)
		{
			return;
		}
		return $config->$name;
	}
}