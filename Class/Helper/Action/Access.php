<?php

class Helper_Action_Access
{
    
	public static function denied ()
	{
		$dispatcher = IcEngine::$application->frontController->getDispatcher ();
		$dispatcher
			->flushStack (true)
			->pushArray (array (
				'controller'	=> 'Authorization',
				'action'		=> 'accessDenied'
			));
	}
	
}