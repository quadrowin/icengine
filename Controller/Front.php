<?php

/**
 * Фронт контроллер
 *
 * @author goorus, morph
 */
class Controller_Front extends Controller_Abstract
{
	/**
	 * Запускаем фронт контролер
	 */
	public function index()
	{
		$route = $this->getService('router')->getRoute();
		try
		{
			if (Tracer::$enabled) {
				$startTime = microtime(true);
			}
			/**
			 * Начинаем цикл диспетчеризации и получаем список
			 * выполняемых руот экшинов.
			 */
            $dispatcher = $this->getService('controllerDispatcher');
			$actions = $dispatcher->loop($route->actions());
            $controllerManager = $this->getService('controllerManager');
			// Создаем задания для выполнения. В них отдает входные данные.
			$tasks = $controllerManager->createTasks($actions, $this->getInput());
			if (Tracer::$enabled) {
				$endTime = microtime(true);
				Tracer::setDispatcherTime($endTime - $startTime);
			}
			// Выполненяем задания
			$resultTasks = $controllerManager->runTasks($tasks);
			$this->output->send('tasks', $resultTasks);
		} catch (Exception $e) {
            Error::render($e);
		}
	}
}
