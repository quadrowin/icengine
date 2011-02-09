<?php

class View_Resource extends Objective
{
	
	/**
	 * Время модификации файла
	 * @var integer
	 */
	protected $_filemtime;
	
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
	
	/**
	 * Содержимое ресурса.
	 * @return string
	 */
	public function content ()
	{
		$path = $this->filePath;
		return file_exists($path) ? file_get_contents ($path) : null;
	}
	
	/**
	 * Время модификации файла.
	 * @return integer|null
	 * 		Время модификации. Если файл не существует null.
	 */
	public function filemtime ()
	{
		if (!$this->_filemtime)
		{
			$path = $this->filePath;
			$this->_filemtime = file_exists ($path) ? filemtime ($path) : null;
		}
		return $this->_filemtime;
	}
	
}