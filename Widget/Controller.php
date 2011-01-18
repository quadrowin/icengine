<?php

class Widget_Controller extends Widget_Abstract
{
	
	public function index ()
	{
		$this->_output->send ();
	}
	
}