<?php

/**
 * Фильтр для перевода в нижний регистр
 *
 * @author neon
 */
class Data_Filter_LowerCase extends Data_Filter_Abstract
{
	/**
	 * @inheritdoc
	 */
	public function filter($data)
	{
		return mb_strtolower($data);
	}
}