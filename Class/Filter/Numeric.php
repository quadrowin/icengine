<?php

/**
 * Фильтр преобразования данных к integer
 * Тоже что и Filter_Integer
 *
 * @author neon
 */
class Filter_Numeric
{
	/**
	 * @inheritdoc
	 * @param string $data
	 * @return int
	 */
	public function filter($data)
	{
		return (int) $data;
	}

	/**
	 * @inheritdoc
	 * @param string $field
	 * @param string $data
	 * @return int
	 */
	public function filterEx($field, $data)
	{
		return (int) $data->$field;
	}
}