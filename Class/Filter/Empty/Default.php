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
	 * @param string $data
	 * @return string
	 */
	public function filter($data)
	{
		return $data ? $data : '';
	}

	/**
	 * @inheridoc
	 * @param string $field
	 * @param Objective $data
	 * @param Objective $scheme
	 */
	public function filterEx($field, $data, $scheme)
	{
		$default = isset($scheme->field['default']) ?
			$scheme->field['default'] : '';
		return $data ? $data : $default;
	}
}