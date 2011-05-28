<?php

class Controller_Render extends Controller_Abstract
{
	public function render ()
	{
		$actions = Model_Collection_Manager::create (
			'Controller_Action'
		)
			->fromArray (array (
				array (
					'controller'	=> $this->_input
						->receive ('action')
				)
			));
			
		$actions->applyTransport (
			'input',
			$this->_input
		);
			
		
	}
}