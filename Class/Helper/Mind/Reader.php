<?php
/**
 * 
 * @desc Помощник для чтения мыслей
 * @author Юрий
 * @package IcEngine
 *
 */
class Helper_Mind_Reader
{
	
	/**
	 * @return string
	 */
	public static function read ()
	{
		return
			isset (User::getCurrent ()->mind) ?
			User::getCurrent ()->mind : null;
	}
	
}