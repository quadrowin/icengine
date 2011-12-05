<?php

namespace Ice;

/**
 * 
 * @desc Реализация паттерна "Composite".
 * Необходим для обращения к коллекции объектов, как к объекту
 * @author Илья Колесников
 * @package Ice
 *
 */
class Composite
{
	/**
	 * @desc Объекты композита
	 * @var mixed
	 */
	protected $_items = array ();

	/**
	 * (non-PHPDoc)
	 */
	public function __call ($method, $args)
	{
		$result = array ();
		foreach ($this->_items as $item)
		{
			$result [] = $item->$method ($args);
		}
		return $result;
	}

	/**
	 * (non-PHPDoc)
	 * @param array<mixed> $items
	 */
	public function __construct ($items)
	{
		$this->_items = $items;
	}
}