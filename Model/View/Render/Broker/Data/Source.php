<?php

class View_Render_Broker_Data_Source
{
    /**
     * 
     * @var Controller_Action
     */
	private $_action;
	
	private $_data;
	
	public function __construct (Controller_Action $action, array $data)
	{		
		$this->_action = $action;
		$this->_data = $data;
	}
	
	/**
	 * @return Controller_Action
	 */
	public function getAction ()
	{
		return $this->_action;
	}
	
	/**
	 * @return array
	 */
	public function getData ()
	{
		return $this->_data;
	}
}