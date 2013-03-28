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
	 */
	public function filter($data)
	{
		return mb_strtolower($data);
	}
}