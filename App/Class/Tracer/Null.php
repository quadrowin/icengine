<?php

namespace Ice;

if (!class_exists (__NAMESPACE__ . '\\Tracer_Abstract'))
{
	include __DIR__ . '/Abstract.php';
}

class Tracer_Null extends Tracer_Abstract
{

	/**
	 *
	 *
	 * @param string $info
	 * @param string $_ [optional]
	 */
	public function add ($info)
	{

	}

	/**
	 * Фильтр вызовов
	 * @param string $filter
	 * @return array
	 */
	public function filter ($filter)
	{
		return array ();
	}

	public function full ()
	{
		return array ();
	}

}