<?php

namespace Ice;

/**
 *
 * @desc Фильтр для десериализации коллекций моделей
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Filter_Model_Collection_Unserialize
{

	/**
	 * @desc Десириализация строки в данные коллекции моделей
	 * @param string $data
	 * @return array
	 */
	public function filter ($data)
	{
		return json_decode ($data, true);
	}

}