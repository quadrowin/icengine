<?php

namespace Ice;

/**
 *
 * @desc Фильтр для кодирования Json
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Filter_Json_Encode
{

	/**
	 * @desc Кодирование Json
	 * @param mixed $data
	 * @return string
	 */
	public function filter ($data)
	{
		return json_encode ($data);
	}

}