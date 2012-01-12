<?php

namespace Ice;

/**
 *
 * @desc Фронт контроллер для работы по адресной строке.
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Controller_Front_Router extends Controller_Abstract
{

	/**
	 *
	 * @return Worker_Manager
	 */
	protected function _getWorkerManager ()
	{
		return Core::di ()->getInstance ('Ice\\Worker_Manager', $this);
	}

	public function index ()
	{
		Loader::load ('Router');
		Loader::load ('Controller_Dispatcher');

		/**
		 * @desc Начинаем роутинг.
		 * @var route
		 */
		$route = Router::getRoute ();

		try
		{
			/**
			 * @desc Начинаем цикл диспетчеризации и получаем список
			 * выполняемых руот экшинов.
			 * @var Route_Action_Collection
			 */
			$actions = Controller_Dispatcher::loop (
				$route->actions ()
			);

			// Создаем задания для выполнения. В них отдает входные данные.
			$tasks = $this->_getControllerManager ()->createTasks (
				$actions,
				$this->getInput ()
			);

			// Выполненяем задания.
			$this->_getWorkerManager ()->letAll ($tasks);

			$this->_output->send ('tasks', $tasks);
		}
		catch (Zend_Exception $e)
		{
			Loader::load ('Error');
			Error::render ($e);
		}
	}

}
