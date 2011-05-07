<?php

Loader::load ('Controller_Dispatcher_Iteration');
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
	 * @desc Текущая итерация диспетчеризации.
	 * @var Controller_Dispatcher_Iteration
	 */
	private $_currentIteration;

	/**
	 * @desc Очередь экшенов для обработки.
	 * @var array
	 */
	protected $_actions = array ();

	/**
	 * @desc Список результатов диспетчеризации.
	 * @var array
	 */
	protected $_results = array ();

//	/**
//	 *
//	 * @param Controller_Abstract $current
//	 * @param Controller_Dispatcher_Iteration $iteration
//	 * @param string $method_name
//	 */
//	private function _onDispatchIterationFinish (Controller_Abstract $current,
//		Controller_Dispatcher_Iteration $iteration, $method_name)
//	{
//		$current->_afterAction ($method_name);
//		$this->onDispatchIterationFinish ($current, $iteration, $method_name);
//	}

//	/**
//	 *
//	 * @param Controller_Abstract $current
//	 * @param Controller_Dispatcher_Iteration $iteration
//	 * @param string $method_name
//	 */
//	private function _onDispatchIterationStart (Controller_Abstract $current,
//		Controller_Dispatcher_Iteration $iteration, $method_name)
//	{
//		$current
//			->setDispatcherIteration ($iteration)
//			->_beforeAction ($method_name);
//		$this->onDispatchIterationStart ($current, $iteration, $method_name);
//	}

	/**
	 * @return Controller_Dispatcher_Iteration
	 */
	public function currentIteration ()
	{
		return $this->_currentIteration;
	}

	/**
	 *
	 * @param Controller_Dispatcher_Iteration $iteration
	 */
	public function dispatch (Controller_Dispatcher_Iteration $iteration)
	{
		$parent_iteration = $this->_currentIteration;

		$this->_currentIteration = $iteration;

		$controller_action = $iteration->controllerAction ();

		Loader::load ('Controller_Manager');
		
		$iteration = Controller_Manager::call (
			$controller_action->controller,
			$controller_action->action,
			isset ($controller_action->input) ? 
				$controller_action->input :
				null,
			$iteration
		);

		$this->_currentIteration = $parent_iteration;
		
//		/**
//		 * @desc Контроллер
//		 * @var Controller_Abstract $controller
//		 */
//		$controller = Controller_Manager::get ($controller_action->controller);
//
//		$method_name = $controller_action->action;
//
//		if (!method_exists ($controller, $method_name))
//		{
//			Loader::load ('Controller_Exception');
//
//			throw new Controller_Exception (
//				"Action " . $controller_action->controller . "::" .
//				$controller_action->action . " unexists."
//			);
//		}
//
//		// Инициализация транспортов
//		Controller_Manager::beforeAction ($controller);
//		if (isset ($controller_action->input))
//		{
//			$controller->setInput ($controller_action->input);
//		}
//
//		if (isset ($controller_action->output))
//		{
//			$controller->getOutput ()->endTransaction ();
//			$controller->setOutput ($controller_action->output);
//			$controller->getOutput ()->beginTransaction ();
//		}
//
//		$this->_onDispatchIterationStart ($controller, $iteration, $method_name);
//
//		if (!$this->_currentIteration->getIgnore ())
//		{
//			Loader::load ('Executor');
//			Executor::execute (array ($controller, $method_name));
//		}
//
//		Controller_Manager::afterAction ($controller, $iteration);
//
//		$this->_onDispatchIterationFinish ($controller, $iteration, $method_name);
//
//		$this->_currentIteration = $parent_iteration;
	}

	/**
	 * @desc Цикл диспетчеризации.
	 * Работает пока список контроллеров не будет пуст.
	 * @return Controller_Dispatcher
	 */
	public function dispatchCircle ()
	{
		while ($this->_actions)
		{
			$iteration = array_shift ($this->_actions);
			$this->dispatch ($iteration);
			if (!$iteration->getIgnore ())
			{
				$this->_results [] = $iteration;
			};
		}

		return $this;
	}

	/**
	 * @desc Очистка списка контроллеров в заданиях.
	 * @param boolean $current Если true, экшен текущей итерации не попадет
	 * в результаты.
	 * @return Controller_Dispatcher Этот диспатчер.
	 */
	public function flushActions ($current = true)
	{
		$this->_actions = array ();
		$this->flushResults ();
		if ($current && $this->_currentIteration)
		{
			$this->_currentIteration->setIgnore (true);
		}
		return $this;
	}

	/**
	 * @desc Очистка результатов работы контроллеров.
	 * @return Controller_Dispatcher Этот диспатчер.
	 */
	public function flushResults ()
	{
		$this->_results = array ();
		return $this;
	}

	/**
	 * @desc Добавление задания в очередь диспетчера.
	 * @param Controller_Action_Collection|Controller_Action|array $resources
	 * @return Controller_Dispatcher
	 */
	public function push ($resources)
	{
		if (
			$resources instanceof Route_Action_Collection ||
			$resources instanceof Controller_Action_Collection
		)
		{
			foreach ($resources as $resource)
			{
				$this->_actions [] =
					new Controller_Dispatcher_Iteration ($resource);
			}
		}
		elseif (
			$resources instanceof Controller_Action ||
			$resources instanceof Route_Action
		)
		{
			$this->_actions [] =
				new Controller_Dispatcher_iteration ($resources);
		}
		elseif (is_array ($resources))
		{
			if (isset ($resources ['controller']))
			{
				$resources = array (
					$resources
				);
			}

			foreach ($resources as $action)
			{
				$this->push (new Controller_Action (array (
					'controller'	=> $action ['controller'],
					'action'		=> $action ['action']
				)));
			}
		}
		else
		{
			Loader::load ('Zend_Exception');
			throw new Zend_Exception ('Illegal type.');
		}

		return $this;
	}

	/**
	 * @desc Возвращает результаты работы контроллеров
	 * @return array
	 */
	public function results ()
	{
		return $this->_results;
	}

}