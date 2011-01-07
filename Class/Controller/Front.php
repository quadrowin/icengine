<?php

class Controller_Front
{
	
	/**
	 * 
	 * @var string
	 */
	private $_controllersPath = 'Controllers/';
	
	/**
	 * 
	 * @var string
	 */
	private $_defaultDispatcher = 'Controller_Dispatcher';
	
	/**
	 * 
	 * @var string
	 */
	private $_defaultRouter = 'Router';
	
	/**
	 * 
	 * @var Controller_Dispatcher
	 */
	private $_dispatcher;
	
	/**
	 * 
	 * @var Router
	 */
	private $_router;
	
	
	/**
	 * 
	 * @param string $controllers_path
	 */
	public function __construct ($controllers_path)
	{
		$this->_controllersPath = $controllers_path;
	}
	
	/**
	 * 
	 * @return Controller_Dispatcher
	 */
	public function getDispatcher ()
	{
		return $this->_dispatcher;
	}
	
	/**
	 * @return string
	 */
	public function getDefaultDispatcher ()
	{
		return $this->_defaultDispatcher;
	}
	
	/**
	 * @return string
	 */
	public function getDefaultRouter ()
	{
		return $this->_defaultRouter;
	}
	
	/**
	 * @return Router
	 */
	public function getRouter ()
	{
		return $this->_router;
	}
	
	public function run ()
	{
		if ($this->_router === null && Loader::load ($this->_defaultRouter))
		{
			$this->_router = new $this->_defaultRouter;
		}
		
		$this->_router->initRoute ();
		$view = View_Render_Broker::pushView (
		    $this->_router->getRoute ()->View_Render);
		
		Loader::load ('Message_After_Router_View_Set');
		Message_After_Router_View_Set::push ($this->_router->getRoute (), $view);
		
		if ($this->_dispatcher === null && Loader::load ($this->_defaultDispatcher))
		{
			$this->_dispatcher = new $this->_defaultDispatcher;
		}
		
		try 
		{
			Loader::load ('Controller_Broker');
			Controller_Broker::initTransports ();
			$this->_dispatcher->push ($this->_router->actions ());
			$this->_dispatcher->dispathCircle ();
			
			View_Render_Broker::render (Controller_Broker::iterations ());
		}
		catch (Zend_Exception $e)
		{
		    $msg = 
		    	'[' . $e->getFile () . '@' . 
				$e->getLine () . ':' . 
				$e->getCode () . '] ' .
				$e->getMessage () . "\r\n";
				
		    error_log ($msg . PHP_EOL, E_USER_ERROR, 3);
		    
			Loader::load ('Errors');
			Errors::render ($e);
			
			echo '<pre>' . $msg . $e->getTraceAsString () . '</pre>';
		}
	}
	
	/**
	 * 
	 * @param Controller_Dispatcher $dispatcher
	 */
	public function setDispatcher (Controller_Dispatcher $dispatcher)
	{
		$this->_dispatcher = $dispatcher;
	}
	
	/**
	 * 
	 * @param string $defaultDispatcher
	 */
	public function setDefaultDispatcher ($defaultDispatcher)
	{
		$this->_defaultDispatcher = $defaultDispatcher;
	}
	
	/**
	 * 
	 * @param string $defaultRouter
	 */
	public function setDefaultRouter ($defaultRouter)
	{
		$this->_defaultRouter = $defaultRouter;
	}
	
	/**
	 * 
	 * @param Router $router
	 */
	public function setRouter (Router $router)
	{
		$this->_router = $router;
	}
	
}
