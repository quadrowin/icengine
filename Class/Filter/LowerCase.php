<?php
/**
 * Фильтр для перевода в нижний регистр
 *
 * @author neon
 */
class Filter_LowerCase
{
	/**
	 * @inheritdoc
	 * @param string $data
	 * @return string
	 */
	public function filter($data)
	{
		return mb_strtolower($data);
	}
}