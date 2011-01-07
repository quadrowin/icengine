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
						'Password/Recovery/index/code_ok.tpl');
				return ;
			}
			else
			{
			    IcEngine::$application
			        ->frontController
			        ->getDispatcher ()
			        ->currentIteration ()
			        ->setTemplate (
			            'Password/Recovery/index/code_fail.tpl');
				return ;
			}
		}
		
		Password_Recovery::resetSession ();
	}
	
}