<?php

/**
 * Фильтр для десериализации моделей
 *
 * @author Юрий, neon
 * @package IcEngine
 */
class Filter_Config_Unserialize
{
	/**
	 * Десериализация строки в модель
	 *
	 * @param string $data
	 * @return Config_Array
	 */
	public function filter($data)
	{
		if (!$data) {
			return null;
		}
		$p = strpos($data, ':');
		return new Config_Array(json_decode(substr($data, $p + 1), true));
	}
}