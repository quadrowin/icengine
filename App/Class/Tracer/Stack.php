<?php

namespace Ice;

if (!class_exists (__NAMESPACE__ . '\\Tracer_Abstract'))
{
	include __DIR__ . '/Abstract.php';
}

class Tracer_Stack extends Tracer_Abstract
{

	protected $_stack = array ();

	/**
	 *
	 *
	 * @param string $info
	 * @param string $_ [optional]
	 */
	public function add ($info)
	{
		$this->_stack [] = func_get_args ();
	}

	/**
	 * Фильтр вызовов
	 * @param string $filter
	 * @return array
	 */
	public function filter ($filter)
	{
		$result = array ();

		foreach ($this->_stack as $row)
		{
			if ($row [0] == $filter)
			{
				return $result;
			}
		}

		return $result;
	}

	public function full ()
	{
		return $this->_stack;
	}

}