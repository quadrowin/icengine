<?php

/**
 * Реализация паттерна "Composite". 
 * Необходим для обращения к коллекции объектов, как к объекту
 * 
 * @author morph
 */
class Composite 
{
	/**
	 * Объекты композита
     * 
	 * @var mixed
	 */
	protected $items;
	
	/**
	 * (non-PHPDoc)
	 */
	public function __call($method, $args)
	{
		foreach ($this->items as $item) {
			call_user_func_array(array($item, $method), $args);
		}
	}
	
	/**
	 * (non-PHPDoc)
	 * @param array<mixed> $items 
	 */
	public function __construct($items)
	{
		$this->items = $items;
	}
}