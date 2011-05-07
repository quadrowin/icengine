<?php
/**
 * 
 * @desc Стандартные реакции контроллера на ошибки
 * @author Юрий
 * @package IcEngine
 *
 */
class Helper_Action_Page
{
    
	/**
	 * Страница не найдена
	 */
    public static function notFound ()
    {
		IcEngine::frontController ()
			->getDispatcher ()
			->flushActions (true)
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
		IcEngine::frontController ()
			->getDispatcher ()
			->flushActions (true)
			->push (array (
				'controller'	=> 'Error',
				'action'		=> 'obsolete'
			));
    }
    
}