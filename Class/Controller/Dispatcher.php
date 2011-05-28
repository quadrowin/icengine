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
	 * @desc Экшин по умолчанию
	 * @param string
	 */
	const DEFAULT_ACTION = 'index';
	
	/**
	 * @desc Ищет соответствие имени файла и метода
	 * @param string $controller
	 * @param string $action
	 * @return array<controller,action>
	 */
	public static function dispatch ($controller, $action)
	{
		return array (
			'controller'	=> $controller,
			'action'		=> $action
		);
	}
	
	/**
	 * @desc Запускает цикл диспетчеризации.
	 * Находит соответствиие имени файла и метода
	 * @param Model_Collection $actions
	 * @return Controller_Action_Collection
	 */
	public static function loop ($actions)
	{
		foreach ($actions as $action)
		{
			$_controller = $action->controller;
			
			if (strpos ($_controller, '/') !== false)
			{
				list ($_controller, $_action) = explode ('/', $_controller);
			}
			else
			{
				$_action = isset ($action->action) ?
					$action->action :
					self::DEFAULT_ACTION;
			}
			
			$action->set (self::dispatch (
				$_controller,
				$_action
			));		
		}
		
		return $actions;
	}
}