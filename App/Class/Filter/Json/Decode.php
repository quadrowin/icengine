<?php

namespace Ice;

/**
 *
 * @desc Фильтр для декодирования Json
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Filter_Json_Decode
{

	/**
	 * @desc Декодирование Json
	 * @param string $data
	 * @return mixed
	 */
	public function filter ($data)
	{
		return json_decode ($data, true);
	}

}