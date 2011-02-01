<?php

class Helper_Action_Page
{
    
    public static function notFound ()
    {
		IcEngine::$application
			->frontController
			->getDispatcher ()
			->flushStack (true)
			->pushArray (array (
				'controller'	=> 'Authorization',
				'action'		=> 'accessDenied'
			));
    }
    
    public static function obsolete ()
    {
		IcEngine::$application
			->frontController
			->getDispatcher ()
			->flushStack (true)
			->pushArray (array (
				'controller'	=> 'Authorization',
				'action'		=> 'accessDenied'
			));
    }
    
}