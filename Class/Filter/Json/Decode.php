<?php

/**
 * Фильтр для декодирования Json
 *
 * @author goorus, neon
 */
class Filter_Json_Decode
{
	/**
	 * @inheritdoc
	 */
	public function filter($data)
	{
		return json_decode($data, true);
	}
}