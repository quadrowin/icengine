<?php
/**
 * Фильтр для кодирования Json
 *
 * @author Юрий, neon
 * @package IcEngine
 */
class Filter_Json_Encode
{
	/**
	 * Кодирование Json
	 *
	 * @param mixed $data
	 * @return string
	 */
	public function filter($data)
	{
		return json_encode($data);
	}
}