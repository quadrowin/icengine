<?php

/**
 * Абстрактный класс загрузчика
 * 
 * @author goorus
 */
class Bootstrap_Cli extends Bootstrap_Abstract
{
	/**
	 * @inheritdoc
	 */
	public function __construct($path)
	{
		parent::__construct($path);
		IcEngine::$frontController = 'Cli';
		IcEngine::$frontRender = 'Cli';
		IcEngine::$frontInput = 'cliInput';
	}
}