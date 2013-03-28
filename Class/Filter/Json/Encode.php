<?php

/**
 * Фильтр для кодирования Json
 *
 * @author goorus, neon
 */
class Filter_Json_Encode
{
	/**
	 * @inheritdoc
	 */
	public function filter($data)
	{
		return json_encode($data);
	}
}