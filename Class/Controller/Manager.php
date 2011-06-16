<?php
/**
 * 
 * @desc Менеджер контроллеров.
 * @author Юрий
 * @package IcEngine
 *
 */
class Controller_Manager extends Manager_Abstract
{
	
	/**
	 * @desc Загруженные контроллеры.
	 * @var array
	 */
	protected static $_controllers = array ();
	
	/**
	 * @desc Стек входных транспортов контроллеров.
	 * @var array
	 */
	protected static $_controllersInputs = array ();
	
	/**
	 * @desc Стек выходных транспортов контроллеров.
	 * @var array
	 */
	protected static $_controllersOutputs = array ();
	
	/**
	 * @desc Текущее задание
	 * @var Controller_Task
	 */
	protected static $_currentTask;
	
	/**
	 * @desc Транспорт входных данных.
	 * @var Data_Transport
	 */
	protected static $_input;
	
	/**
	 * @desc Транспорт выходных данных.
	 * @var Data_Transport
	 */
	protected static $_output;
	
	/**
	 * @desc Отложенные очереди заданийs
	 * @var array <array>
	 */
	protected static $_tasksBuffer = array ();
	
	/**
	 * @desc Очередь заданий.
	 * @var array <Router_Action>
	 */
	protected static $_tasksQueue = array ();
	
	/**
	 * @desc Результаты выполнения очереди
	 * @var array <Controller_Task>
	 */
	protected static $_tasksResults = array ();
	
	/**
	 * @desc Буффер результатов
	 * @var array <array <Controller_Task>>
	 */
	protected static $_tasksResultsBuffer = array ();
	
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
		'actions'			=> array (
		)
	);
	
	/**
	 * @desc Настройки кэширования для контроллера-экшена.
	 * @param string $controller Контроллер
	 * @param string $action Экшен
	 * @return Objective
	 */
	protected static function _cacheConfig ($controller, $action)
	{
		$config = self::config ();
		$cfg = $config ['actions'][$controller . '::' . $action];
		return $cfg ? $cfg : $config ['actions'] [$controller];
	}

	/**
	 * 
	 * @param Controller_Abstract $controller
	 */
	public static function beforeAction ($controller)
	{
		self::$_controllersInputs [] = $controller->getInput ();
		self::$_controllersOutputs [] = $controller->getOutput ();
		
		self::getOutput ()->beginTransaction ();
		
		$controller
			->setInput (self::getInput ())
			->setOutput (self::getOutput ());
	}
	
	/**
	 * @desc Вызов экшена контроллера.
	 * @param string $name Название контроллера.
	 * @param string $method Метод.
	 * @param array|Data_Transport $input Входные данные.
	 * @param Controller_Task $task [optional] Задание
	 * @return Controller_Task
	 */
	public static function call ($name, $method = 'index', $input, 
		$task = null)
	{
		return self::callUncached ($name, $method, $input, $task);
	}
	
	/**
	 * @desc Вызов экшена без кэширования.
	 * @param string $name Название контроллера.
	 * @param string $method Метод.
	 * @param array|Data_Transport $input Входные данные.
	 * @param Controller_Task $task [optional] Итерация
	 * диспетчера.
	 * @return Controller_Task Итерация с результатами.
	 */
	public static function callUncached ($name, $method = 'index', $input, 
		$task = null)
	{
		Loader::load ('Controller_Action');
		Loader::load ('Controller_Task');
		Loader::load ('Route_Action');
		
		if (!$task)
		{
			$task = new Controller_Task (
				new Controller_Action (array (
					'id'			=> null,
					'controller'	=> $name,
					'action'		=> $method
				))
			);
		}
		
		$controller = self::get ($name);
		
		$temp_input = $controller->getInput ();
		$temp_output = $controller->getOutput ();
		$temp_task = $controller->getTask ();
		
		if ($input === null)
		{
			$controller->setInput (self::getInput ());
		}
		elseif (is_array ($input))
		{
			Loader::load ('Data_Transport');
			$tmp = new Data_Transport ();
			$tmp->beginTransaction ()->send ($input);
			$controller->setInput ($tmp);
		}
		else
		{
			$controller->setInput ($input);
		}
		
		$controller
			->setOutput (self::getOutput ())
			->setTask ($task);
		
		$controller->getOutput ()->beginTransaction ();
		
		$controller->_beforeAction ($method);
		
		$controller->{$method} ();
		
		$controller->_afterAction ($method);
		
		$task->setTransaction ($controller->getOutput ()->endTransaction ());
		
		$controller
			->setInput ($temp_input)
			->setOutput ($temp_output)
			->setTask ($temp_task);
			
		return $task;
	}
	
	/**
	 * @desc Создаем задания из экшинов
	 * @param Route_Action_Collection $actions
	 * @param Data_Transport $input
	 * @return array <Controller_Task>
	 */
	public static function createTasks (Route_Action_Collection $actions,
		Data_Transport $input)
	{
		$tasks = array ();
		
		Loader::load ('Controller_Task');
		
		foreach ($actions as $action)
		{
			$task = new Controller_Task ($action);
			$task->setInput ($input);
			
			$tasks [] = $task;
		}
		
		return $tasks;
	}
	
	/**
	 * @desc Очистка результатов работы контроллеров.
	 */
	public static function flushResults ()
	{
		self::$_tasksResults = array ();
	}
	
	/**
	 * @desc Возвращает контроллер по названию.
	 * @param string $controller_name
	 * @return Controller_Abstract
	 */
	public static function get ($controller_name)
	{
		$class_name = 'Controller_' . $controller_name;
		$controller = Resource_Manager::get (
			'Controller', 
			$class_name
		);
			
		if (!($controller instanceof Controller_Abstract))
		{
			$file = str_replace ('_', '/', $controller_name) . '.php';
			
			if (!Loader::requireOnce ($file, 'Controller'))
			{
				Loader::load ('Controller_Exception');
				throw new Controller_Exception ("Controller $class_name not found.");
			}
			
			$controller = new $class_name;
			
			Resource_Manager::set (
				'Controller',
				$class_name, 
				$controller
			);
		}
		return $controller;
	}
	
	/**
	 * @return Data_Transport
	 */
	public static function getInput ()
	{
		if (!self::$_input)
		{
			Loader::load ('Data_Transport');
			self::$_input  = new Data_Transport ();
		}
		return self::$_input;
	}
	
	/**
	 * @desc Возвращает транспорт для выходных данных по умолчанию.
	 * @return Data_Transport
	 */
	public static function getOutput ()
	{
		if (!self::$_output)
		{
			Loader::load ('Data_Transport');
			Loader::load ('Data_Provider_Router');
			
			self::$_output = new Data_Transport ();
			
			foreach (self::config ()->output_filters as $filter)
			{
				$filter_class = 'Filter_' . $filter;
				Loader::load ($filter_class);
				$filter = new $filter_class ();
				self::$_output->outputFilters ()->append ($filter);
			}
		}
		return self::$_output;
	}
	
	/**
	 * @desc Выполняет указанный контроллер, экшен с заданными параметрами.
	 * @param string $action Название контроллера или контроллер и экшен
	 * в формате "Controller/action".
	 * @param array $args Параметры.
	 * @param boolean $html_only=true Только результат рендера.
	 * @return string Результат компиляции шабона.
	 * @todo Это будет в Controller_Render
	 * @tutorial
	 * 		html ('Controller', array ('param'	=> 'val'));
	 * 		html ('Controller/action')
	 */
	public static function html ($action, array $args = array (), 
		$html_only = true)
	{
		$a = explode ('/', $action);
		if (count ($a) == 1)
		{
			$a [1] = 'index';
		}
		
		$cache_config = self::_cacheConfig ($a [0], $a [1]);
		
		return Executor::execute (
			array (__CLASS__, 'htmlUncached'),
			array ($a, $args, $html_only),
			$cache_config
		);
	}
	
	/**
	 * @desc Выполняет указанный контроллер, экшен с заданными параметрами,
	 * не используется кэширование.
	 * @param string $action Название контроллера или контроллер и экшен
	 * в формате "Controller/action".
	 * @param array $args Параметры.
	 * @param boolean $html_only Только вывод.
	 * @return string Результат компиляции шабона.
	 * @todo Это будет в Controller_Render
	 * @tutorial
	 * 		html ('Controller', array ('param'	=> 'val'));
	 * 		html ('Controller/action')
	 */
	public static function htmlUncached ($action, array $args = array (), 
		$html_only = true)
	{
		$a = is_array ($action) ? $action : explode ('/', $action);
		
		if (count ($a) == 1)
		{
			$a [1] = 'index';
		}
		
		$iteration = self::call ($a [0], $a [1], $args);
		
		$buffer = $iteration->getTransaction ()->buffer ();
		$result = array (
			'data'		=> 
				isset ($buffer ['data']) ? 
				$buffer ['data'] : 
				array (),
			'html'		=> null
		);
	   
		$tpl = $iteration->getTemplate ();
		
		if ($tpl)
		{
			$view = View_Render_Manager::pushViewByName ('Smarty');
			
			try
			{
				$view->assign ($buffer);
				$result ['html'] = $view->fetch ($tpl);
			}
			catch (Exception $e)
			{
				$msg = 
					'[' . $e->getFile () . '@' . 
					$e->getLine () . ':' . 
					$e->getCode () . '] ' .
					$e->getMessage () . PHP_EOL;
					
				error_log (
					$msg . PHP_EOL .
					$e->getTraceAsString () . PHP_EOL, 
					E_USER_ERROR, 3
				);
				
				Debug::log ($msg);
			
				$result ['error'] = 'Controller_Manager: Error in template.';
			}
			
			View_Render_Manager::popView ();
		}
		
		return $html_only ? $result ['html'] : $result;
	}
	
	/**
	 * @desc Добавление задания в текущую очередь выполнения.
	 * @param mixed $action
	 */
	public static function pushTasks ($action)
	{
		if (
			$action instanceof Route_Action_Collection ||
			$action instanceof Controller_Action_Collection
		)
		{
			foreach ($action as $resource)
			{
				self::$_tasksQueue [] =
					new Controller_Task ($resource);
			}
		}
		elseif (
			$action instanceof Controller_Action ||
			$action instanceof Route_Action
		)
		{
			self::$_tasksQueue [] = new Controller_Task ($action);
		}
		elseif (is_array ($action))
		{
			if (isset ($action ['controller']))
			{
				$action = func_get_args ();
			}

			foreach ($action as $info)
			{
				self::pushTasks (new Controller_Action (array (
					'controller'	=> $info ['controller'],
					'action'		=> $info ['action']
				)));
			}
		}
		else
		{
			Loader::load ('Zend_Exception');
			throw new Zend_Exception ('Illegal type.');
		}
	}
	
	/**
	 * 
	 * @param Controller_Task|Route_Action|Controller_Action $action
	 * @return Controller_Task
	 */
	public static function run ($task)
	{
		$parent_task = self::$_currentTask;

		self::$_currentTask = $task;

		$action = $task->controllerAction ();
		
		$task = self::call (
			$action->controller,
			$action->action,
			$task->getInput (),
			$task
		);

		self::$_currentTask = $parent_task;
		
		return $task;
	}
	
	/**
	 * @desc Выполнение очереди заданий
	 * @param array $actions
	 * @return array
	 */
	public static function runTasks ($tasks)
	{
		self::$_tasksBuffer [] = self::$_tasksQueue;
		self::$_tasksResultsBuffer [] = self::$_tasksResults;
		
		self::$_tasksQueue = $tasks;
			
		self::$_tasksResults = array ();
		
		for ($i = 0; $i < count (self::$_tasksQueue); ++$i)
		{
			$task = self::run (self::$_tasksQueue [$i]);
			if (!$task->getIgnore ())
			{
				self::$_tasksResults [] = $task;
			}
		}
		
		$result = self::$_tasksResults;
		self::$_tasksQueue = array_pop (self::$_tasksBuffer);
		self::$_tasksResults = array_pop (self::$_tasksResultsBuffer);
		
		return $result;
	}
	
}