<?php

Loader::load ('Data_Adapter_Abstract');

/**
 * @desc Адаптер для моделей, данные которых перечисленны в самом классе.
 * @author Илья Колесников
 * @package IcEngine
 *
 */
class Data_Adapter_Defined extends Data_Adapter_Abstract
{
	/**
	 * @desc Запрос
	 * @var array
	 */
	protected $_where;

	/**
	 * @desc Фильтрация
	 * @param array $data
	 * @param array $filter
	 * @return array
	 */
	public function filter (array $row)
	{
		$valid = true;

		foreach ($this->_where as $where)
		{
			$field = $where [Query::WHERE];
			$value = $where [Query::VALUE];

			$field = str_replace (' ', '', $field);

			$s = substr ($field, -2, 2);
			$offset = 2;

			if (ctype_alnum ($s))
			{
				$s = '=';
				$offset = 0;
			}

			elseif (ctype_alnum ($s [0]))
			{
				$s = $s [1];
				$offset = 1;
			}

			if ($offset)
			{
				$field = substr ($field, 0, -1 * $offset);
			}

			switch ($s)
			{
				case '=':
					$valid = $row [$field] == $value;
					break;
				case '>':
					$valid = $row [$field] > $value;
					break;
				case '>=':
					$valid = $row [$field] >= $value;
					break;
				case '<': $valid = $row [$field] < $value;
					break;
				case '<=': $valid = $row [$field] <= $value;
					break;
				case '!=': $valid = $row [$field] != $value;
					break;
			}
		}

		return $valid;
	}

	/**
	 * @see Data_Adapter_Abstract::_executeSelect
	 */
	public function _executeSelect (Query $query, Query_Option $option)
	{
		$select = $query->getPart (Query::SELECT);

		$model_name = reset ($select);

		if (is_array ($model_name))
		{
			$model_name = reset ($model_name);
		}
		else
		{
			return new Query_Result (array (
				'source'	=> $source,
				'result'	=> array ()
			));
		}

		Loader::load ($model_name);

		$this->_where = $query->getPart (Query::WHERE);

		$result = array_filter (
			$model_name::$rows,
			array ($this, 'filter')
		);

		return $result;
	}
}