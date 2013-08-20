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
     * Создать 
     * 
     * @param array $actions
     * @return array
     */
    public function createActions($actions)
    {
        $i = 0;
        $resultActions = array();
		foreach ($actions as $action => $assign) {
			if (is_numeric($action)) {
				if (is_scalar($assign)) {
					$action	= $assign;
					$assign = 'content';
				} else {
					$assign = reset($assign);
					$action = key($assign);
				}
			}
			$tmp = explode('/', $action);
			$controller = $tmp[0];
			$controllerAction = !empty($tmp[1])
                ? $tmp[1] : Controller_Manager::DEFAULT_ACTION;
			$action = array(
                'controller'	=> $controller,
                'action'		=> $controllerAction,
				'sort'			=> ++$i,
				'assign'		=> $assign
			);
			$resultActions[] = $action;
		}
		return $resultActions;
    }
    
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
        $actions = $this->createActions($actions);
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