<?php

Loader::load ('Registration');

class Controller_Registration extends Controller_Abstract
{
	
    /**
     * Последняя обработанная регистрация
     * @var Registration
     */
    public $registration;
    
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
	 * @return boolean
	 * 		True, если регистрация закончилась успешно.
	 * 		Иначе false.
	 */
	public function emailConfirm ()
	{
		$this->registration = Registration::byCode (
		    $this->_input->receive ('code'));
		
		if (!$this->registration)
		{
		    IcEngine::$application->frontController->getDispatcher ()
		        ->currentIteration ()->setTemplate (
		        	$this->name () . '/emailConfirm/fail_code_uncorrect.tpl');
		    return false;    
		}
		elseif ($this->registration->finished)
		{
			IcEngine::$application->frontController->getDispatcher ()
		        ->currentIteration ()->setTemplate (
		        	$this->name () . '/emailConfirm/fail_already_finished.tpl');
		    return false;
		}
		
		$this->registration->finish ();
		return true;
	}
	
}