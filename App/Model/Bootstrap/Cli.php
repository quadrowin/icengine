<?php

namespace Ice;

/**
 *
 * @desc Абстрактный класс загрузчика
 * @author Юрий Шведов
 * @package Ice
 *
 */
class Bootstrap_Cli extends Bootstrap_Abstract
{

	/**
	 * @desc
	 * @param string $path
	 */
	public function __construct ($path)
	{
		parent::__construct ($path);
		IcEngine::$frontController = 'Cli';
		IcEngine::$frontRender = 'Cli';
		IcEngine::$frontInput = 'cli_input';
	}

}