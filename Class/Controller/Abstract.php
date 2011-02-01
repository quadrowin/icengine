<?php

class Controller_Abstract
{	
    
	protected $_currentAction;
	
	/**
	 * Текущая итерация диспетчера
	 * @var Controller_Dispatcher_Iteration
	 */
	protected $_dispatcherIteration;
	
	/**
	 * 
	 * @var Data_Transport
	 */
	protected $_input;
		
	/**
	 * 
	 * @var Data_Transport
	 */
	protected $_output;
	
	/**
	 * Метод выполняется после вызова метода $action из диспетчера
	 * 
	 * @param string $action
	 * 		Вызываемый метод
	 */
	public function _afterAction ($action)
	{
		
	}
	
	/**
	 * Метод выполняется перед вызовом метода $action из диспетчера
	 * 
	 * @param string $action
	 * 		Вызываемый метод
	 */
	public function _beforeAction ($action)
	{
		
	}
	
	public function __construct ()
	{
	}
	
	/**
	 * Имя контроллера (без приставки Controller_)
	 * 
	 * @return string
	 */
	public function name ()
	{		
		return substr (get_class ($this), 11);
	}
	
	/**
	 * Заменить текущий экшн с передачей всех параметров
	 */
	public function replaceAction ($controller, $action)
	{
		if ($controller instanceof Controller_Abstract)
		{
			$other = $controller;
			$controller = $other->name ();
		}
		else
		{
			$other = Controller_Broker::get ($controller);
		}
		
		$this->_dispatcherIteration->setTemplate (
			'Controller/' .
			str_replace ('_', '/', $controller) .
			'/' . $action . '.tpl'
		);
		
		if ($controller == get_class ($this))
		{
			// Этот же контроллер
			return $this->$action ();
		}
		else
		{
			$other = Controller_Broker::get ($controller);
			$other->setInput ($this->_input);
			$other->setOutput ($this->_output);
			$other->setDispatcherIteration ($this->_dispatcherIteration);
			return $other->$action ();
		}
	}
	
	/**
	 * 
	 * @param Controller_Dispatcher_Iteration $iteration
	 * @return Controller_Abstract
	 */
	public function setDispatcherIteration (
	    Controller_Dispatcher_Iteration $iteration)
	{
	    $this->_dispatcherIteration = $iteration;
	    return $this;
	}
	
	public function setInput (Data_Transport $input)
	{
		$this->_input = $input;
		return $this;
	}
	
	public function setOutput (Data_Transport $output)
	{
		$this->_output = $output;
		return $this;
	}
	
	public function index ()
	{
		
	}
	
	/**
	 * @return Data_Transport
	 */
	public function getInput ()
	{
		return $this->_input;
	}
	
	/**
	 * @return Data_Transport
	 */
	public function getOutput ()
	{
		return $this->_output;
	}

}