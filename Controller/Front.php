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
		$route = Router::getRoute();
		try
		{
			if (Tracer::$enabled) {
				$startTime = microtime(true);
			}
			/**
			 * Начинаем цикл диспетчеризации и получаем список
			 * выполняемых руот экшинов.
			 */
			$actions = Controller_Dispatcher::loop($route->actions());
			// Создаем задания для выполнения. В них отдает входные данные.
			$tasks = Controller_Manager::createTasks($actions, $this->getInput());
			if (Tracer::$enabled) {
				$endTime = microtime(true);
				Tracer::setDispatcherTime($endTime - $startTime);
			}
			// Выполненяем задания
			$resultTasks = Controller_Manager::runTasks($tasks);
			$this->_output->send('tasks', $resultTasks);
		} catch (Exception $e) {
            Error::render($e);
		}
	}
}
