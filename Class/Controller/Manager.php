<?php
/**
 * 
 * @desc Менеджер контроллеров.
 * @author Юрий
 * @package IcEngine
 *
 */
class Controller_Manager
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
	 * 
	 * @var array
	 */
	public static $config = array (
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
	 * @desc Сохранение результата работы контроллера
	 * @param Controller_Abstract $controller
	 * @param Controller_Dispatcher_Iteration $iteration
	 */
	public static function afterAction (Controller_Abstract $controller, 
		Controller_Dispatcher_Iteration $iteration)
	{
		$transaction = $controller->getOutput ()->endTransaction ();
				
		$iteration->setTransaction ($transaction);
		
		$controller
			->setInput (array_pop (self::$_controllersInputs))
			->setOutput (array_pop (self::$_controllersOutputs));
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
	 * @param Controller_Dispatcher_Iteration $iteration [optional] Итерация
	 * диспетчера.
	 * @return Controller_Dispatcher_Iteration Итерация с результатами.
	 */
	public static function call ($name, $method = 'index', $input, 
		$iteration = null)
	{
		return self::callUncached ($name, $method, $input, $iteration);
	}
	
	/**
	 * @desc Вызов экшена без кэширования.
	 * @param string $name Название контроллера.
	 * @param string $method Метод.
	 * @param array|Data_Transport $input Входные данные.
	 * @param Controller_Dispatcher_Iteration $iteration [optional] Итерация
	 * диспетчера.
	 * @return Controller_Dispatcher_Iteration Итерация с результатами.
	 */
	public static function callUncached ($name, $method = 'index', $input, 
		$iteration = null)
	{
		Loader::load ('Controller_Action');
		Loader::load ('Controller_Dispatcher_Iteration');
		Loader::load ('Route_Action');
		
		if (!$iteration)
		{
			$iteration = new Controller_Dispatcher_Iteration (
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
		$temp_iteration = $controller->getDispatcherIteration ();
		
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
			->setDispatcherIteration ($iteration);
		
		$controller->getOutput ()->beginTransaction ();
		
		$controller->_beforeAction ($method);
		
		$controller->{$method} ();
		
		$controller->_afterAction ($method);
		
		$iteration->setTransaction (
			$controller->getOutput ()->endTransaction ()
		);
		
		$controller
			->setInput ($temp_input)
			->setOutput ($temp_output)
			->setDispatcherIteration ($temp_iteration);
			
		return $iteration;
	}
	
	/**
	 * @desc Загрузка конфига
	 * @return Objective
	 */
	public static function config ()
	{
		if (is_array (self::$config))
		{
			self::$config = Config_Manager::get (__CLASS__, self::$config);
		}
		return self::$config;
	}
	
	/**
	 * @desc Выполняет указанный контроллер, экшен с заданными параметрами.
	 * @param string $action Название контроллера или контроллер и экшен
	 * в формате "Controller/action".
	 * @param array $args Параметры.
	 * @return string Результат компиляции шабона.
	 * @tutorial
	 * 		fetch ('Controller', array ('param'	=> 'val'));
	 * 		fetch ('Controller/action')
	 */
	public static function fetch ($action, array $args = array ())
	{
		$a = explode ('/', $action);
		if (count ($a) == 1)
		{
			$a [1] = 'index';
		}
		
		$cache_config = self::_cacheConfig ($a [0], $a [1]);
		
		return Executor::execute (
			array (__CLASS__, 'callUncached'),
			array ($a [0], $a [1], $args, true),
			$cache_config
		);
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
			
			Loader::load ('Data_Provider_Router');
			self::$_input->appendProvider (new Data_Provider_Router ());
			
			if (Request::isPost ())
			{
				Loader::load ('Data_Provider_Post');
				self::$_input->appendProvider (new Data_Provider_Post ());
			}
			
			if (Request::isGet ())
			{
				Loader::load ('Data_Provider_Get');
				self::$_input->appendProvider (new Data_Provider_Get ());
			}
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
			Loader::load ('Data_Provider_View');
			
			self::$_output->appendProvider (new Data_Provider_View ()); 
		}
		return self::$_output;
	}
	
	/**
	 * 
	 * @param Route_Action|Controller_Action $action
	 * @return Controller_Dispatcher_Iteration
	 */
	public static function run ($action)
	{
		$iteration = new Controller_Dispatcher_Iteration ($action);
		
		IcEngine::frontController ()
			->getDispatcher ()
			->dispatch ($iteration);
		
		return $iteration;
	}
	
}