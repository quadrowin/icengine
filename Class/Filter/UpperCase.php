<?php

/**
 * Приводит строку к верхнему регистру
 *
 * @author goorus, neon
 */
class Filter_UpperCase
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