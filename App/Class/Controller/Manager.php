<?php

namespace Ice;

/**
 *
 * @desc Менеджер контроллеров.
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Controller_Manager extends Manager_Abstract
{

	/**
	 * @desc Загруженные контроллеры.
	 * @var array
	 */
	protected $_controllers = array ();

//	/**
//	 * @desc Стек входных транспортов контроллеров.
//	 * @var array
//	 */
//	protected $_controllersInputs = array ();
//
//	/**
//	 * @desc Стек выходных транспортов контроллеров.
//	 * @var array
//	 */
//	protected $_controllersOutputs = array ();
//
//	/**
//	 * @desc Текущее задание
//	 * @var Controller_Task
//	 */
//	protected $_currentTask;
//
//	/**
//	 * @desc Транспорт входных данных.
//	 * @var Data_Transport
//	 */
//	protected $_input;
//
//	/**
//	 * @desc Транспорт выходных данных.
//	 * @var Data_Transport
//	 */
//	protected $_output;
//
//	/**
//	 * @desc Отложенные очереди заданийs
//	 * @var array <array>
//	 */
//	protected $_tasksBuffer = array ();
//
//	/**
//	 * @desc Очередь заданий.
//	 * @var array <Router_Action>
//	 */
//	protected $_tasksQueue = array ();
//
//	/**
//	 * @desc Результаты выполнения очереди
//	 * @var array <Controller_Task>
//	 */
//	protected $_tasksResults = array ();
//
//	/**
//	 * @desc Буффер результатов
//	 * @var array <array <Controller_Task>>
//	 */
//	protected $_tasksResultsBuffer = array ();

	/**
	 * @desc Config
	 * @var array
	 */
	protected static $_config = array (
		/**
		 * @desc Фильтры для выходных данных
		 * @var array
		 */
		'output_filters'	=> array (),
		/**
		 * @desc Настройки кэширования для экшенов.
		 * @var array
		 */
		'actions'			=> array ()
	);

	/**
	 * @desc Настройки кэширования для контроллера-экшена.
	 * @param string $controller Контроллер
	 * @param string $action Экшен
	 * @return Objective
	 */
	protected function _cacheConfig ($controller, $action)
	{
		$config = $this->config ();
		$cfg = $config ['actions'][$controller . '::' . $action];
		$cfg = $cfg ? $cfg : $config ['actions'] [$controller];

		if (isset ($cfg ['cache_config']))
		{
			list ($class_name, $method) = explode ('::', $cfg ['cache_config']);
			Loader::load ($class_name);
			return call_user_func_array (
				array ($class_name, $method),
				array ($cfg)
			);
		}

		if (isset ($cfg ['tags'], $cfg ['tag_provider']))
		{
			$provider = Data_Provider_Manager::get ($cfg ['tag_provider']);

			if ($provider)
			{
				$tags = $provider->getTags ($cfg ['tags']->__toArray ());

				if ($tags)
				{
					$cfg ['current_tags'] = $tags;
				}
			}
		}

		return $cfg;
	}

	/**
	 *
	 * @return Component_Manager
	 */
	protected function _getComponentManager ()
	{
		return Core::di ()->getInstance ('Ice\\Component_Manager', $this);
	}

	/**
	 *
	 * @return View_Render_Manager
	 */
	protected function _getViewRenderManager ()
	{
		return Core::di ()->getInstance ('Ice\\View_Render_Manager', $this);
	}

	/**
	 * @return Worker_Manager
	 */
	protected function _getWorkerManager ()
	{
		return Core::di ()->getInstance ('Ice\\Worker_Manager', $this);
	}

	/**
	 *
	 * @return Helper_Controller_Template
	 */
	protected function _helperControllerTemplate ()
	{
		return Core::di ()->getInstance (
			'Ice\\Helper_Controller_Template',
			$this
		);
	}

	/**
	 *
	 * @param Controller_Abstract $controller
	 */
	public function beforeAction ($controller)
	{
		$this->_controllersInputs [] = $controller->getInput ();
		$this->_controllersOutputs [] = $controller->getOutput ();

		$this->getOutput ()->beginTransaction ();

		$controller
			->setInput ($this->getInput ())
			->setOutput ($this->getOutput ());
	}

	/**
	 *
	 * @param string $name
	 * @return Controller_Abstract
	 */
	public function byName ($name)
	{
		$p = strrpos ($name, '\\');
		if (false === $p)
		{
			$class = __NAMESPACE__ . '\\Controller_' . $name;
		}
		else
		{
			$class =
				substr ($name, 0, $p + 1) .
				'Controller_' .
				substr ($name, $p + 1);
		}

		return $this->get ($class);
	}

	/**
	 * @desc Вызов экшена контроллера.
	 * @param Task $task Задание
	 * @return $this
	 */
	public function call (Task $task)
	{
		return $this->callUncached ($task);
	}

	/**
	 * @desc Вызов экшена без кэширования.
	 * @param Task $task Задание
	 * @return $this
	 */
	public function callUncached ($task)
	{
		Loader::multiLoad (
			'Controller_Action',
			'Controller_Task',
			'Route_Action'
		);

		$controller = $task->getRequest ()->getExtra ('controller');
		$action = $task->getRequest ()->getExtra ('action');

		if (class_exists ('Tracer', false))
		{
			\Tracer::begin (
				__CLASS__,
				__METHOD__,
				__LINE__,
				$name,
				$action
			);
		}

		$controller = $this->byName ($controller);

		$temp_input = $controller->getInput ();
		$temp_output = $controller->getOutput ();
		$temp_task = $controller->getTask ();

		$controller
			->setTask ($task)
			->setInput ($task->getRequest ()->getInput ())
			->setOutput ($task->getResponse ()->getOutput ());

		//$controller->getOutput ()->beginTransaction ();

		$controller->_beforeAction ($action);

		// _beforeAction не генерировал ошибки, можно продолжать
		if (!$controller->hasErrors ())
		{
			$reflection = new \ReflectionMethod ($controller, $action);

			$params = $reflection->getParameters ();
			$c_input = $controller->getInput ();

			foreach ($params as &$param)
			{
				$param_value = $c_input->receive ($param->name);
				if (null === $param_value)
				{
					if ($param->isOptional ())
					{
						$param_value = $param->getDefaultValue ();
					}
				}
				$param = $param_value;
			}

			try
			{
				call_user_func_array (
					array ($controller, $action),
					$params
				);

				$controller->_afterAction ($action);
			}
			catch (Controller_Exception $e)
			{
				$task->setException ($e);
				$template = $e->getTemplate ();

				if (!$template && $e->getAutoTemplate ())
				{
					$template = $task->getResponse ()->getExtra ('template') .
						'/' . $e->getMessage ();
				}

				$task->getResponse ()
					->setExtra (array (
						'template' => $template
					));
			}
		}

		//$task->setTransaction ($controller->getOutput ()->endTransaction ());

		$controller
			->setTask ($temp_task)
			->setInput ($temp_input)
			->setOutput ($temp_output);

		if (class_exists ('Tracer', false))
		{
			\Tracer::end (null);
		}

		return $this;
	}

	/**
	 * @desc Создаем задания из экшинов
	 * @param Route_Action_Collection $actions
	 * @param Data_Transport $input
	 * @return Task_Collection
	 */
	public function createTasks (Route_Action_Collection $actions,
		Data_Transport $input)
	{
		Loader::multiLoad ('Task', 'Task_Collection');

		$tasks = new Task_Collection;

		$default_render = $this->_getViewRenderManager ()->getDefaultView ();

		foreach ($actions as $action)
		{
			$c = $action->Controller_Action->controller;
			$a = $action->Controller_Action->action;
			$task = new Task (
				'Controller',
				array (
					'controller' => $c,
					'action' => $a,
				)
			);

			$template = $this->_helperControllerTemplate ()->get ($c, $a);

			$task->getRequest ()->setInput ($input);
			$task->getResponse ()->setExtra (array (
				'template' => $template,
				'render' => $default_render
			));

			$tasks->add ($task);
			$tasks->add (new Task ('Render'));
		}

		return $tasks;
	}

	/**
	 * @desc Возвращает контроллер по названию класса.
	 * @param string $class
	 * @return Controller_Abstract
	 */
	public function get ($class)
	{
		if (isset ($this->_controllers [$class]))
		{
			return $this->_controllers [$class];
		}

		Loader::load ($class);
		$controller = new $class;
		$this->_controllers [$class] = $controller;
		return $controller;
	}

	/**
	 * @desc Возвращает экземпляр
	 * @return Controller_Manager
	 */
	public static function getInstance ()
	{
		return Core::di ()->getInstance (__CLASS__);
	}

//	/**
//	 * @desc Возвращает транспорт для выходных данных по умолчанию.
//	 * @return Data_Transport
//	 */
//	public static function getOutput ()
//	{
//		if (!$this->_output)
//		{
//			Loader::load ('Data_Transport');
//			Loader::load ('Data_Provider_Router');
//
//			$this->_output = new Data_Transport ();
//
//			foreach ($this->config ()->output_filters as $filter)
//			{
//				$filter_class = 'Filter_' . $filter;
//				Loader::load ($filter_class);
//				$filter = new $filter_class ();
//				$this->_output->outputFilters ()->append ($filter);
//			}
//		}
//		return $this->_output;
//	}

	/**
	 * @desc Выполняет указанный контроллер, экшен с заданными параметрами.
	 * @param string $action Название контроллера или контроллер и экшен
	 * в формате "Controller/action".
	 * @param array $args Параметры контроллера.
	 * @param boolean $html_only только результат рендера
	 * @return string Результат компиляции шабона.
	 * @todo Это будет в Controller_Render
	 * @tutorial
	 * 		html ('Controller', array ('param'	=> 'val'));
	 * 		html ('Controller/action')
	 */
	public function html ($action, array $args = array (), $html_only = true)
	{
		$a = explode ('/', $action);
		if (count ($a) == 1)
		{
			$a [1] = 'index';
		}

		$cache_config = $this->_cacheConfig ($a [0], $a [1]);

		$html = Executor::execute (
			array ($this, 'htmlUncached'),
			array ($a, $args, $html_only),
			$cache_config
		);

		return $html;
	}

	/**
	 * @desc Выполняет указанный контроллер, экшен с заданными параметрами,
	 * не используется кэширование.
	 * @param string $action Название контроллера или контроллер и экшен
	 * в формате "Controller/action".
	 * @param array $args Параметры контроллера.
	 * @param boolean $html_only Только результаты рендера
	 * @return string Результат компиляции шабона.
	 * @todo Это будет в Controller_Render
	 * @tutorial
	 * 		html ('Controller', array ('param'	=> 'val'));
	 * 		html ('Controller/action')
	 */
	public function htmlUncached ($action, array $args = array (),
		$html_only = true)
	{
		$a = is_array ($action) ? $action : explode ('/', $action);

		if (count ($a) == 1)
		{
			$a [1] = 'index';
		}

		if (class_exists ('Tracer', false))
		{
			Tracer::begin (
				__CLASS__,
				__METHOD__,
				__LINE__,
				$a [0],
				$a [1]
			);
		}

		$tasks = new Task_Collection;

		// Задание контроллера
		$controller_task = new Task (
			'Controller',
			array (
				'controller' => $a [0],
				'action' => $a [1]
			)
		);

		$template = $this->_helperControllerTemplate ()->get ($a [0], $a [1]);

		$input = new Data_Transport;
		$input->appendProvider (new Data_Provider_Buffer ($args));
		$controller_task->getRequest ()->setInput ($input);
		$controller_task->getResponse ()->setExtra (array (
			'render' => 'Smarty',
			'template' => $template
		));
		$tasks [] = $controller_task;

		// Задание рендера
		$tasks [] = new Task ('Render');

		$this->_getWorkerManager ()->letAll ($tasks);

		if (class_exists ('Tracer', false))
		{
			Tracer::end ();
		}

		return $html_only
			? $tasks->getResponse ()->getOutput ()->receive ('content')
			: $tasks;
	}

//	/**
//	 * @desc Добавление задания в текущую очередь выполнения.
//	 * @param mixed $action
//	 */
//	public function pushTasks ($action)
//	{
//		if (
//			$action instanceof Route_Action_Collection ||
//			$action instanceof Controller_Action_Collection
//		)
//		{
//			foreach ($action as $resource)
//			{
//				$this->_tasksQueue [] = new Task (
//					'Controller',
//					array (
//						'controller' => $resource->controller,
//						'action' => $resource->action,
//						'render' => $resource->viewRender ()->name
//					)
//				);
//			}
//		}
//		elseif ($action instanceof Route_Action)
//		{
//			$this->_tasksQueue [] = new Task (
//				'Controller',
//				array (
//					'controller' => $action->controller,
//					'action' => $action->action,
//					'render' => $action->viewRender ()->name ()
//				)
//			);
//		}
//		elseif ($action instanceof Controller_Action)
//		{
//			$this->_tasksQueue [] = new Task (
//				'Controller',
//				array (
//					'controller' => $action->controller,
//					'action' => $action->action,
//					'render' => View_Render_Manager::getView ()->name ()
//				)
//			);
//		}
//		elseif (is_array ($action))
//		{
//			if (isset ($action ['controller']))
//			{
//				$action = func_get_args ();
//			}
//
//			foreach ($action as $info)
//			{
//				$this->pushTasks (new Controller_Action (array (
//					'controller'	=> $info ['controller'],
//					'action'		=> $info ['action']
//				)));
//			}
//		}
//		else
//		{
//			Loader::load ('Zend_Exception');
//			throw new Zend_Exception ('Illegal type.');
//		}
//	}

	/**
	 *
	 * @param Task $task
	 * @return Task
	 */
	public function run ($task)
	{
		$parent_task = $this->_currentTask;

		$this->_currentTask = $task;

		$this->call ($task);

		$this->_currentTask = $parent_task;

		return $task;
	}

//	/**
//	 * @desc Выполнение очереди заданий
//	 * @param array $actions
//	 * @return array
//	 */
//	public function runTasks ($tasks)
//	{
//		$this->_tasksBuffer [] = $this->_tasksQueue;
//		$this->_tasksResultsBuffer [] = $this->_tasksResults;
//
//		$this->_tasksQueue = $tasks;
//
//		$this->_tasksResults = array ();
//
//		for ($i = 0; $i < count ($this->_tasksQueue); ++$i)
//		{
//			$task = $this->run ($this->_tasksQueue [$i]);
//			if (!$task->getResponse ()->getExtra ('ignore'))
//			{
//				$this->_tasksResults [] = $task;
//			}
//		}
//
//		$result = $this->_tasksResults;
//		$this->_tasksQueue = array_pop ($this->_tasksBuffer);
//		$this->_tasksResults = array_pop ($this->_tasksResultsBuffer);
//
//		return $result;
//	}

}