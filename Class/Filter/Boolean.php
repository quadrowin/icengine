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
	 */
	public function filter($data)
	{
		return (bool) $data;
	}
}