<?php

Loader::load ('Registration');

class Controller_Registration extends Controller_Abstract
{
	
	/**
	 * Начало регистрации
	 */
	public function index ()
	{
	    View_Render_Broker::getView ()->resources ()->add (
	    	'/js/Widget/Registration.js');
	    
		if (User::authorized ())
		{
		    Loader::load ('Header');
			Header::redirect ('/');
			die ();
		}
	}
	
	/**
	 * Подтверждение email
	 */
	public function emailConfirm ()
	{
		$this->item = Registration::byCode ($this->_input->receive ('code'));
		
		if (!$this->item)
		{
		    IcEngine::$application->frontController->getDispatcher ()
		        ->currentIteration ()->setTemplate (
		        	'Registration/emailConfirm/fail_code_uncorrect.tpl');
		    return;    
		}
		elseif ($this->item->finished)
		{
			IcEngine::$application->frontController->getDispatcher ()
		        ->currentIteration ()->setTemplate (
		        	'Registration/emailConfirm/fail_already_finished.tpl');
		    return;
		}
		
		$this->item->finish ();
	}
	
}