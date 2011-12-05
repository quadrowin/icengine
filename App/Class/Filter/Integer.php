<?php

namespace Ice;

/**
 *
 * @desc Стандартный фильтр чисел.
 * @author Yury Shvedov
 *
 */
class Filter_Integer extends Filter_Abstract
{

	public function filter ($data)
	{
		return Helper_String::str2int (trim ($data));
	}

}