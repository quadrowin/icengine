<?php

class Controller_Broker
{
    
    /**
     * 
     * @var array
     */
	protected static $_controllers;
    
    /**
     * 
     * @var Data_Transport
     */
	protected static $_input;
	
	/**
	 * 
	 * @var Data_Transport
	 */
	protected static $_output;
	
	/**
	 * 
	 * 
	 * @var array
	 */
	protected static $_iterations = array ();
	
	/**
	 * Сохранение результата работы контроллера
	 * 
	 * @param Controller_Abstract $controller
	 * @param Controller_Dispatcher_Iteration $iteration
	 */
	public static function afterAction (Controller_Abstract $controller, 
	    Controller_Dispatcher_Iteration $iteration)
	{
	    $transaction = $controller->getOutput ()->endTransaction ();
	    
	    if (!$iteration->getIgnore ())
	    {
    	    $iteration->setTransaction ($transaction);
    	    self::$_iterations [] = $iteration;
	    };
	}

	/**
	 * 
	 * @param Controller_Abstract $controller
	 */
	public static function beforeAction ($controller)
	{	
	    self::$_output->beginTransaction ();
	    
		$controller->
		    setInput (self::$_input)->
		    setOutput (self::$_output);
	}
	
	/**
	 * Очистка результатов вывода
	 */
	public static function flushResults ()
	{
	    self::$_iterations = array ();
	}
	
	/**
	 * 
	 * @param string $controller_name
	 * @return Controller_Abstract
	 */
	public static function get ($controller_name)
	{
		$class_name = 'Controller_' . $controller_name;
		$controller = IcEngine::$application->
			behavior->
			resourceManager->
			get (
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
			
			IcEngine::$application->
				behavior->
				resourceManager->
				set (
					'Controller',
					$class_name, 
					$controller
				);
		}
		return $controller;
	}
	
	public static function initTransports ()
	{
		Loader::load ('Data_Transport');
		Loader::load ('Data_Provider_Router');
		
		self::$_input  = new Data_Transport ();
		self::$_output = new Data_Transport ();
		
		self::$_input->appendProvider (new Data_Provider_Router ());
		
		Loader::load ('Data_Provider_View');
		
		self::$_output->appendProvider (new Data_Provider_View ()); 
		
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
		
//		if (Request::isFiles ())
//		{
//			Loader::load ('Data_Provider_Files');
//			self::$_input->appendProvider (new Data_Provider_Files ());
//		}
	}
	
	/**
	 * @return array
	 */
	public static function iterations ()
	{
	    return self::$_iterations;
	}
	
	/**
	 * 
	 * @param Route_Action|Controller_Action $action
	 * @return Controller_Dispatcher_Iteration
	 */
	public static function run ($action)
	{
		$iteration = new Controller_Dispatcher_Iteration ($action);
		
		IcEngine::$application
			->frontController
			->getDispatcher ()
			->dispatch ($iteration);
		
		return $iteration;
	}
	
}