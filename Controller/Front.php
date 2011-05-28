<?php
/**
 * 
 * @desc Фронт контроллер.
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Controller_Front extends Controller_Abstract
{
	/**
	 * @desc Название транспорта входа
	 * @var string
	 */
	const TRANSPORT_INPUT = 'input';
	
	/**
	 * @desc Запускаем фронт контролер.
	 */
	public function run ()
	{
		Loader::load ('Router');
		
		// Начинаем роутинг
		$route = Router::getRoute ();
		
		try 
		{
			Loader::load ('Controller_Dispatcher');
			// Начинаем цикл диспетчеризации и получаем список
			// выполняемых экшинов.
			$actions = Controller_Dispatcher::loop (
				$route->actions ()
			);
			
			// Создаем задания для выполнения.
			// В них отдает входные данные.
			$tasks = Controller_Manager::createTask (
				$actions,
				$this->_input
			);
			
			// Выполненяем задания.
			Controller_Manager::runTasks ($tasks);
			
			$this->_output->send (array (
				'render'	=> $route->viewRender (),
				'tasks'		=> $tasks
			));
		}
		catch (Zend_Exception $e)
		{
			Loader::load ('Error');
			Error::render ($e);
		}
	}
	
}
