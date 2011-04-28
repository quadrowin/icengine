<?php

class Controller_Dispatcher_Iteration
{
    
    /**
     * 
     * @var Route_Action
     */
    protected $_routeAction;
    
    /**
     * 
     * @var boolean
     */
    protected $_ignore = false;
    
    /**
     * 
     * @var integer
     */
    protected $_index;
    
    /**
     * 
     * @var string
     */
    protected $_template;
    
    /**
     * 
     * @var Data_Transport_Transaction
     */
    protected $_transaction;
    
    /**
     * 
     * @param Route_Action|Controller_Action $action
     */
	public function __construct ($action)
    {
        if ($action instanceof Route_Action)
        {
            $this->_routeAction = $action;
            $action = $this->controllerAction ();
        }
        elseif ($action instanceof Controller_Action)
        {
            $this->_routeAction = new Route_Action (array (
                'id'				=> null,
                'Route__id'	        => 0,
                'Controller_Action'	=> $action,
                'Controller_Action__id'	=> 0,
                'sort'				    => 0,
                'assign'			    => isset ($action->assign) ? $action->assign : 'content'
            ));
        }
        
        if ($action)
        {
            $this->_template =
                'Controller/' .
    			str_replace ('_', '/', $action->controller) . '/' .
    			$action->action . '.tpl';
        }
    }
    
	/**
     * @return Controller_Action
     */
    public function controllerAction ()
    {
        return $this->_routeAction->Controller_Action;
    }
    
    /**
     * @return boolean
     */
    public function getIgnore ()
    {
        return $this->_ignore;
    }
    
    /**
     * @return integer
     */
    public function getIndex ()
    {
        return $this->_index;
    }
    
    /**
     * @return Route_Action
     */
    public function getRouteAction ()
    {
        return $this->_routeAction;
    }

    /**
     * @return string
     */
    public function getTemplate ()
    {
        return $this->_template;
    }
    
	/**
     * @return Data_Transport_Transaction
     */
    public function getTransaction ()
    {
        return $this->_transaction;
    }
    
    /**
     * @desc Задать шаблон на основе названия класса
     * @param string $class Класс или метод (контроллера).
     * @param string $template Шаблон.
     * @param string $ext Расшираение.
     */
    public function setClassTpl ($class, $template = '', $ext = '.tpl')
    {
    	$template = $template ? ('/' . ltrim ($template, '/')) : '';
    	
    	$this->setTemplate (
    		str_replace (array ('_', '::'), '/', $class) . $template . '.tpl'
    	);
    }
    
    /**
     * 
     * @param boolean $value
     */
    public function setIgnore ($value)
    {
        $this->_ignore = (bool) $value;
    }
    
	/**
     * @param integer $value
     */
    public function setIndex ($value)
    {
        $this->_index = $value;
    }

	/**
     * @param Route_Action $value
     */
    public function setRouteAction (Route_Action $value)
    {
        $this->_routeAction = $value;
    }

	/**
     * @param string $value
     */
    public function setTemplate ($value, $ext = '')
    {
        $this->_template = $value;
    }
    
	/**
     * @param Data_Transport_Transaction $value
     */
    public function setTransaction (Data_Transport_Transaction $value)
    {
        $this->_transaction = $value;
    }
    
}