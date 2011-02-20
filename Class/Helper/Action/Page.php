<?php

/**
 * 
 * Стандартные реакции контроллера на ошибки
 * @author Юрий
 *
 */

class Helper_Action_Page
{
    
	/**
	 * Страница не найдена
	 */
    public static function notFound ()
    {
		IcEngine::$application
			->frontController
			->getDispatcher ()
			->flushStack (true)
			->push (array (
				'controller'	=> 'Error',
				'action'		=> 'notFound'
			));
    }
    
    /**
     * Страница устарела
     */
    public static function obsolete ()
    {
		IcEngine::$application
			->frontController
			->getDispatcher ()
			->flushStack (true)
			->push (array (
				'controller'	=> 'Error',
				'action'		=> 'obsolete'
			));
    }
    
}