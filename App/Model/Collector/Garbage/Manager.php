<?php

namespace Ice;

/**
 * @desc Менеджер gc
 * @author Илья Колесников
 * @package Ice
 * @copyright i-complex.ru
 */
class Collector_Garbage_Manager extends Manager_Abstract
{
	/**
	 * @desc Получить gc по имени
	 * @param string $name
	 * @return Collector_Garbage_Abstract
	 */
	public static function byName ($name)
	{
		return Model_Manager::getInstance ()->byQuery (
			'Collector_Garbage',
			Query::instance ()
				->where ('name', $name)
		);
	}
}