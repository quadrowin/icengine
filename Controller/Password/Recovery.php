<?php

class Controller_Password_Recovery extends Controller_Abstract
{
	
	public function index ()
	{
	    Loader::load ('Password_Recovery');
	    
		$code = Request::get ('code');
		
		if ($code)
		{
			$recovery = Password_Recovery::byCode ($code);
			if ($recovery && $recovery->active)
			{
			    $recovery->startSession ();
				
				IcEngine::$application
				    ->frontController
				    ->getDispatcher ()
				    ->currentIteration ()
				    ->setTemplate (
				        str_replace (array ('::', '_'), '/', __METHOD__) .
						'/code_ok.tpl');
				return ;
			}
			else
			{
			    IcEngine::$application
			        ->frontController
			        ->getDispatcher ()
			        ->currentIteration ()
			        ->setTemplate (
			            str_replace (array ('::', '_'), '/', __METHOD__) .
			            '/code_fail.tpl');
				return ;
			}
		}
		
		Password_Recovery::resetSession ();
	}
	
}