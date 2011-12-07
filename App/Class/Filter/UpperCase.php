<?php

namespace Ice;

/**
 *
 * @desc Приводит строку к верхнему регистру
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Filter_UpperCase
{

	/**
	 *
	 * @param string $data
	 * @return string
	 */
	public function filter ($data)
	{
		return mb_strtoupper ($data);
	}

}