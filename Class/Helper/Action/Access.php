<?php
/**
 * 
 * @desc Помощник для стандартных ответов контроллера, связанных с доступом
 * @author Юрий
 *
 */
class Helper_Action_Access
{
    
	/**
	 * @desc Доступ закрыт
	 */
	public static function denied ()
	{
		Controller_Manager::pushTasks (array (
			'controller'	=> 'Authorization',
			'action'		=> 'accessDenied'
		));
	}
	
}