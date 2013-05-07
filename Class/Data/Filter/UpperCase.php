<?php

/**
 * Приводит строку к верхнему регистру
 *
 * @author goorus, neon
 */
class Data_Filter_UpperCase extends Data_Filter_Abstract
{
	/**
	 * @inheritdoc
	 * @param string $data
	 * @return string
	 */
	public function filter($data)
	{
		return mb_strtoupper($data);
	}
}