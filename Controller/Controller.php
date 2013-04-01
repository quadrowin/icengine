<?php

/**
 * Контроллер контроллеров.
 *
 * @author goorus, morph, neon
 */
class Controller_Controller extends Controller_Abstract
{
	/**
	 * Ajax вызов контроллера
     *
     * @Route(
     *      "/Controller/ajax/",
     *      "name"="ajaxPage",
     *      "weight"=10,
     *      "params"={
     *          "View_Render__id"=3
     *      }
     * )
	 */
	public function ajax($call, $back, $params)
	{
        $controllerManager = $this->getService('controllerManager');
        $_SERVER ['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		if (is_string($params)) {
			$params = json_decode(urldecode($params), true);
		}
        $params = $params ?: array();
        $call = urldecode($call);
		$result = $controllerManager->html($call, $params, false);
		$this->output->send(array(
			'back'		=> $back,
			'result'	=> $result
		));
	}

	/**
	 * Вызов экшена контроллера по названию из входных параметров
	 */
	public function auto($controller, $action)
	{
		return $this->replaceAction($controller, $action);
	}
    
    /**
	 * Ajax вызов контроллера (синхронный)
     *
     * @Route(
     *      "/Controller/sync/",
     *      "name"="syncPage",
     *      "weight"=10,
     *      "params"={
     *          "View_Render__id"=5
     *      }
     * )
	 */
	public function sync($call, $back, $params)
	{
        $controllerManager = $this->getService('controllerManager');
		if (is_string($params)) {
			$params = json_decode(urldecode($params), true);
		}
		$params = $params ?: array();
        $call = urldecode($call);
		$result = $controllerManager->html($call, $params, false);
		$this->output->send(array(
			'back'		=> $back,
			'result'	=> $result
		));
	}
}