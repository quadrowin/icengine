<?php

class Helper_Action_Page
{
    
    public static function notFound ()
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