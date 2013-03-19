<?php
/**
 * Фильтр для декодирования Json
 *
 * @author Юрий, neon
 * @package IcEngine
 */
class Filter_Json_Decode
{
	/**
	 * Декодирование Json
	 *
	 * @param string $data
	 * @return mixed
	 */
	public function filter($data)
	{
		return json_decode($data, true);
	}
}