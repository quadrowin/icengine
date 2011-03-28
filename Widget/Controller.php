<?php

class Widget_Controller extends Widget_Abstract
{
	
	const DEFAULT_ACTION = 'index';
	
	public function index ()
	{
		$action = explode ('/', $this->_input->receive ('action'));
		
		$controller = $action [0];
		$action = isset ($action [1]) ? $action [1] : self::DEFAULT_ACTION;
		
		Loader::load ('Controller_Broker');
		Loader::load ('Controller_Action');
		Loader::load ('Controller_Dispatcher_Iteration');
		Loader::load ('Route_Action');
		$iteration = Controller_Broker::run (new Controller_Action (array (
			'controller'	=> $controller,
			'action'		=> $action,
			'input'			=> $this->_input
		)));

		//Debug::vardump ($iteration->getTransaction ());
		
		$this->_output->send (
			'content',
			View_Render_Broker::fetchIteration ($iteration)
		);
	}
	
}