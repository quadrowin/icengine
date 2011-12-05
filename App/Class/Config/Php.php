<?php

namespace Ice;

if (!class_exists (__NAMESPACE__ . '\\Config_Array'))
{
	include __DIR__ . '/Array.php';
}

class Config_Php extends Config_Array
{

	/**
	 *
	 * @param string $path
	 * 		Путь до файла конфига.
	 * 		В файле должен быть определен массив $config.
	 */
	public function __construct ($path)
	{
		$config = null;

		if (is_file ($path))
		{
			include $path;
		}

		if (isset ($config))
		{
			parent::__construct ($config);
		}
	}

}