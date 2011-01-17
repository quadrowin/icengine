<?php

class Controller_Front
{
	/**
	 * @desc Название дефолтового диспетчера
	 * @var string
	 */
	private $_defaultDispatcher = 'Controller_Dispatcher';
	
	/**
	 * @desc Название дефолтового роутера
	 * @var string
	 */
	private $_defaultRouter = 'Router';
	
	/**
	 * @desc Текущий диспетчер
	 * @var Controller_Dispatcher
	 */
	private $_dispatcher;
	
	/**
	 * @desc Текущий роутер
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
	 * @desc Получаем (и инициализируем, если еще не проинициализирован) текущий диспетчер
	 * @return Controller_Dispatcher
	 */
	public function getDispatcher ()
	{
		if (!$this->_dispatcher && Loader::load ($this->_defaultDispatcher))
		{
			$this->_dispatcher = new $this->_defaultDispatcher;
		}
		return $this->_dispatcher;
	}
	
	/**
	 * @desc Получаем название дефолтового диспетчера 
	 * @return string
	 */
	public function getDefaultDispatcher ()
	{
		return $this->_defaultDispatcher;
	}
	
	/**
	 * @desc Получаем название дефолтового роутера
	 * @return string
	 */
	public function getDefaultRouter ()
	{
		return $this->_defaultRouter;
	}
	
	/**
	 * @desc Получаем (и инициализируем, если еще не проинициализирован) дефолтовый роутер
	 * @return Router
	 */
	public function getRouter ()
	{
		if ($this->_router === null && Loader::load ($this->_defaultRouter))
		{
			$this->_router = new $this->_defaultRouter;
		}
		return $this->_router;
	}
	
	/**
	 * 
	 * @desc Запускаем фронт контролер!
	 */
	public function run ()
	{
		// Проверяем наличие роутера. Если его нет, то создаем дефолтовый роутер
		$this->getRouter ();
		
		// Парсим пользовательский запрос
		$this->_router->parse ();
		
		// Инициализируем вьюшник из запроса
		$view = View_Render_Broker::pushView (
		    $this->_router->getRoute ()->View_Render
		);
		
		// Отправляем сообщение, что вью был изменен
		Loader::load ('Message_After_Router_View_Set');
		Message_After_Router_View_Set::push ($this->_router->getRoute (), $view);
		
		// Получаем диспетчер
		$this->getDispatcher ();
		
		try 
		{
			Loader::load ('Controller_Broker');
			
			// Закидываем в пул диспетчеру полученные роутеров экшины
			$this->_dispatcher->push ($this->_router->actions ());
			
			// Запускаем цикл диспетчеризации
			$this->_dispatcher->dispathCircle ();
			
			// Начинаем рендерить итерации контролеров
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
	 * @desc Устанавливаем новый диспетчер
	 * @param Controller_Dispatcher $dispatcher
	 */
	public function setDispatcher (Controller_Dispatcher $dispatcher)
	{
		$this->_dispatcher = $dispatcher;
	}
	
	/**
	 * @desc Меняем название дефолтового диспетчера
	 * @param string $defaultDispatcher
	 */
	public function setDefaultDispatcher ($defaultDispatcher)
	{
		$this->_defaultDispatcher = $defaultDispatcher;
	}
	
	/**
	 * @desc Меняем название дефолтового роутера
	 * @param string $defaultRouter
	 */
	public function setDefaultRouter ($defaultRouter)
	{
		$this->_defaultRouter = $defaultRouter;
	}
	
	/**
	 * @desc Устанавливаем новый роутер
	 * @param Router $router
	 */
	public function setRouter (Router $router)
	{
		$this->_router = $router;
	}
	
}
