<?php

class View_Render_Broker_Data
{
	/**
	 * 
	 * @var array
	 */
	protected $_data;
	
	public function __construct (array $data)
	{
		Loader::load ('View_Render_Broker_Data_Source');
		for ($i = 0, $count = sizeof ($data); $i < $count; $i++)
		{
			$this->_data [$i] = new View_Render_Broker_Data_Source ($data [$i] ['resource'], $data [$i] ['data']);
		}
	}
	
	public function data ()
	{
		return $this->_data;
	}
	
	/**
	 * @return View_Render_Broker_Data_Source
	 */
	public function pop ()
	{
		return array_pop ($this->_data);
	}
}