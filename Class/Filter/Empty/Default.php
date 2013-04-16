<?php

/**
 * Фильтр проверяет на пустоту данных
 *
 * @author neon
 */
class Filter_Empty_Default
{
	/**
	 * @inheritdoc
	 */
	public function filter($data)
	{
		return $data ? $data : '';
	}

	/**
	 * @inheridoc
	 */
	public function filterEx($field, $data, $scheme)
	{
		$default = isset($scheme->field['default']) ?
			$scheme->field['default'] : '';
		return $data ? $data : $default;
	}
}