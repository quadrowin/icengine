<?php

/**
 * Фильтр удаления начальных, конечных пробелов
 *
 * @author neon
 */
class Filter_Trim
{
	/**
	 * @inheritdoc
	 * @param string $data
	 * @return string
	 */
	public function filter($data)
	{
		return trim($data);
	}

	/**
	 * Удаляет начальные, концевые символы по схеме,
	 * либо пробелыы
	 *
	 * @param string $field
	 * @param Objective $data
	 * @param Objective $scheme
	 */
	public function filterEx($field, $data, $scheme)
	{
		$chars =
			isset($scheme->field['trimChars']) ?
			$scheme->field['trimChars'] : null;
		return trim($data->$field, $chars);
	}
}