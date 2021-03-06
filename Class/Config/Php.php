<?php

/**
 * Конфигурация, загружаемая из php файла
 * 
 * @author morph
 */
class Config_Php extends Config_Array
{
	/**
	 * Конструктор
     * 
	 * @param string $path
	 */
	public function __construct($path)
	{
		$config = null;
		if (is_file($path)) {
			$config = include($path);
		}
		if ($config) {
			parent::__construct($config);
		}
	}
}