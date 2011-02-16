<?php

abstract class View_Helper_Manager
{
	/**
	 * 
	 * @var array <View_Helper_Abstract>
	 */
	protected static $_helpers;
	
	/**
	 * 
	 * @param string $name
	 * @param array $params
	 * @return View_Helper_Abstract
	 */
	public static function get ($name, $params = array ())
	{
		if (!isset (self::$_helpers [$name]))
		{
			$helperName = 'View_Helper_' . $name;
			Loader::load ($helperName);
			self::$_helpers [$name] = new $helperName;
		}
		return self::$_helpers [$name]->get ($params);
	}
}