<?php

class View_Resource extends Objective
{
	
	/**
	 * Ссылка.
	 * @var string
	 */
	public $href;
	
	/**
	 * Тип ресурса.
	 * @var string
	 */
	public $type;
	
	/**
	 * Путь до файла в системе.
	 * @var string
	 */
	public $filePath;
	
	/**
	 * Не использовать сжатие ресурсов при упаковке.
	 * @var boolean
	 */
	public $nopack;
	
}