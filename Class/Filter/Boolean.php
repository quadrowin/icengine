<?php
/**
 * Фильтр преобразует дату к логическому типу
 *
 * @author neon
 */
class Filter_Boolean
{
	/**
	 * @inheritdoc
	 * @param string $data
	 * @return boolean
	 */
	public function filter($data)
	{
		return (bool) $data;
	}
}