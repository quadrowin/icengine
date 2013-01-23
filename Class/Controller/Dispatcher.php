<?php

/**
 * Диспетчер контроллеров
 *
 * @author goorus, morph
 * @Service("controllerDispatcher")
 */
class Controller_Dispatcher
{
	/**
	 * Ищет соответствие имени файла и метода
     *
	 * @param string $controller
	 * @param string $action
	 * @return array<controller,action>
	 */
	public function dispatch($controller, $action)
	{
		return array(
			'controller'	=> $controller,
			'action'		=> $action
		);
	}

	/**
	 * Запускает цикл диспетчеризации. Находит соответствиие имени файла и
     * метода
     *
	 * @param Route_Action_Collection $actions
	 * @return Route_Action_Collection
	 */
	public function loop($actions)
	{
		foreach ($actions as &$action) {
			$dispacthResult = $this->dispatch(
                $action['controller'], $action['action']
            );
            $action['controller'] = $dispacthResult['controller'];
            $action['action'] = $dispacthResult['action'];
		}
		return $actions;
	}
}