<?php

class Controller_Collector_Garbage extends Controller_Abstract
{
	/**
	 * @desc Запускает gc
	 */
	public function process ()
	{
		Loader::load ('Background_Agent_Manager');
		
		$name = $this->_input->receive ('name');

		Background_Agent_Manager::instance ()->startAgent (
			'Collector_Garbage',
			array (
				'name'	=> $name
			)
		);
	}
}