<?php

/**
 * Фильтр для десериализации коллекций моделей
 *
 * @author Юрий, neon
 * @package IcEngine
 */
class Filter_Model_Collection_Unserialize
{
	/**
	 * Десериализация строки в данные коллекции моделей
	 *
	 * @param string $data
	 * @return array
	 */
	public function filter($data)
	{
		return json_decode($data, true);
	}
}