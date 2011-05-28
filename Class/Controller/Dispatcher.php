<?php
/**
 *
 * @desc Диспетчер контроллеров.
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Controller_Dispatcher
{
	
	/**
	 * @desc 
	 * @param string $controller
	 * @param string $action
	 */
	public function dispatch ($controller, $action)
	{
		return 'Controller_' . $controller;
	}

}