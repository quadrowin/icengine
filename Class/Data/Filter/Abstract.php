<?php

/**
 * Абстрактный класс фильтра
 *
 * @author goorus
 */
abstract class Data_Filter_Abstract
{
	/**
	 * Обычная фильтрация.
	 *
	 * @param string $data
	 * @return string
	 */
	public function filter($data)
	{
		return $data;
	}
}