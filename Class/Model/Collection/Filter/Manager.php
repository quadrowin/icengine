<?php
/**
 * 
 * @desc 
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Collection_Filter_Manager
{
	
	/**
	 * @desc 
	 * @var array <Model_Collection_Filter_Abstract>
	 */
	protected static $_filters = array ();
	
	/**
	 * @desc 
	 * @param string $name
	 * @return Model_Collection_Filter_Abstract
	 */
	public static function get ($name)
	{
		if (!isset (self::$_filters [$name]))
		{
			Loader::load ($name);
			self::$_filters [$name] = new $name;
		}
		return self::$_filters [$name];
	}
	
}