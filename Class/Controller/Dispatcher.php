<?php

Loader::load ('Controller_Dispatcher_Iteration');

class Controller_Dispatcher
{
	
	/**
	 * 
	 * @var Controller_Dispatcher_Iteration
	 */
	private $_currentIteration;
	
	/**
	 * 
	 * @var string
	 */
	private $_defaultController = 'Excursion';
	
	/**
	 * 
	 * @var string
	 */
	private $_defaultAction = 'index';
	
	/**
	 * 
	 * @var array
	 */
	public $_dispatchStack = array ();
    
	public function __construct ()
	{
		
	}
	
	/**
	 * 
	 * @param Controller_Abstract $current
	 * @param Controller_Dispatcher_Iteration $iteration
	 * @param string $method_name
	 */
	private function _onDispatchIterationFinish (Controller_Abstract $current, 
	    Controller_Dispatcher_Iteration $iteration, $method_name)
	{
		$current->_afterAction ($method_name);
		$this->onDispatchIterationFinish ($current, $iteration, $method_name);
	}
	
	/**
	 * 
	 * @param Controller_Abstract $current
	 * @param Controller_Dispatcher_Iteration $iteration
	 * @param string $method_name
	 */
	private function _onDispatchIterationStart (Controller_Abstract $current, 
	    Controller_Dispatcher_Iteration $iteration, $method_name)
	{
	    $current
	        ->setDispatcherIteration ($iteration)
		    ->_beforeAction ($method_name);
		$this->onDispatchIterationStart ($current, $iteration, $method_name); 
	}
	
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
	    $this->_currentIteration = $iteration;
	    
	    $controller_action = $iteration->controllerAction ();
		// Инициализация объекта контроллера
		Loader::load ('Controller_Broker');
		
		/**
		 * 
		 * @var Controller_Abstract $current
		 */
		$controller = Controller_Broker::get ($controller_action->controller);
        
		$method_name = $controller_action->action ? 
		    $controller_action->action : $this->_defaultAction;

		if (!method_exists ($controller, $method_name))
		{
			Loader::load ('Controller_Exception');
			throw new Controller_Exception (
				"Action " . $controller_action->controller . "::" . 
				$controller_action->action . " unexists."
			);
		}
		
		// Инициализация транспортов
		Controller_Broker::beforeAction ($controller);
		if (isset ($controller_action->input))
		{
		    $controller->setInput ($controller_action->input);
		}
		
		$this->_onDispatchIterationStart ($controller, $iteration, $method_name);
		
		if (!$this->_currentIteration->getIgnore ())
		{
    		Loader::load ('Executor');
    		Executor::execute (array ($controller, $method_name));
		}
		
		Controller_Broker::afterAction ($controller, $iteration);
		
		$this->_onDispatchIterationFinish ($controller, $iteration, $method_name);
		
		$this->_currentIteration = null;	
	}
	
	public function dispathCircle ()
	{
		$this->onDispatchCircleStart ();
		
		while ($this->_dispatchStack)
		{
			$iteration = array_shift ($this->_dispatchStack);
			$this->dispatch ($iteration);
		}
		
		$this->onDispatchCircleFinish ();
	}
	
	/**
	 * @param boolean $current
	 * 		Убрать из результатов текущий экшн.
	 * @return Controller_Dispatcher
	 */
	public function flushStack ($current = true)
	{
		$this->_dispatchStack = array ();
		Controller_Broker::flushResults ();
		if ($current && $this->_currentIteration)
		{
		    $this->_currentIteration->setIgnore (true);
		}
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getDefaultAction ()
	{
		return $this->_defaultAction;
	}
	
	/**
	 * @return string
	 */
	public function getDefaultController ()
	{
		return $this->_defaultController;
	}
	
	
	public function onDispatchCircleStart ()
	{
	}
	
	public function onDispatchCircleFinish ()
	{
	}
	
	/**
	 *
	 * @param Controller_Abstract $current
	 * @param Controller_Dispatcher_Iteration $iteration
	 * @param string $method_name
	 */
	public function onDispatchIterationFinish (Controller_Abstract $current, 
	    Controller_Dispatcher_Iteration $iteration, $method_name)
	{
		
	}
	
	/**
	 * 
	 * @param Controller_Abstract $current
	 * @param Controller_Dispatcher_Iteration $iteration
	 * @param string $method_name
	 */
	public function onDispatchIterationStart (Controller_Abstract $current, 
	    Controller_Dispatcher_Iteration $iteration, $method_name)
	{
	
	}
	
	/**
	 * 
	 * @param Controller_Action_Collection|Controller_Action $resources
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
                $this->_dispatchStack [] = 
                    new Controller_Dispatcher_Iteration ($resource);
    	    }
	    }
	    elseif (
	        $resources instanceof Controller_Action ||
	        $resources instanceof Route_Action
	    )
	    {
            $this->_dispatchStack [] = 
                new Controller_Dispatcher_iteration ($resources);
	    }
	    else
	    {
	        throw new Exception ('Illegal type.');
	    }
	}
	
	/**
	 * 
	 * @param array $resources
	 * 		
	 */
	public function pushArray (array $actions)
	{
	    if (!$actions || !is_array ($actions))
	    {
	        Loader::load ('Zend_Exception');
	        throw new Zend_Exception ('Illegal type.');
	        return;
	    }
	    
	    if (isset ($actions ['controller']))
	    {
	        $actions = array (
	            $actions
	        );
	    }
	    
	    foreach ($actions as $action)
	    {
	        $this->push (new Controller_Action (array (
	            'controller'	=> $action ['controller'],
	            'action'		=> $action ['action']
	        )));
	    }
	}
	
	/**
	 * 
	 * @param string $action
	 */
	public function setDefaultAction ($action)
	{
		$this->_defaultAction = $action;
	}
	
	/**
	 * 
	 * @param string $controller
	 */
	public function setDefaultController ($controller)
	{
		$this->_defaultController = $controller;
	}
}