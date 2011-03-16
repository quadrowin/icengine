<?php

/**
 * 
 * @link http://www.i-complex.ru/
 * @copyright 2010 I-complex
 * @package Morphy
 */

include ('Morphy/src/common.php');

abstract class Morphy
{
	private static $_morphy;
	
	private function __construct ()
	{
        
	}
	
	private function __clone ()
	{
		
	}
	
	/**
	 * 
	 * @return phpMorphy
	 */
	public static function get ()
	{
		if (self::$_morphy === null)
		{
			self::$_morphy = new phpMorphy (
				dirname (__FILE__) . '/Morphy/dicts/utf-8',
				'ru_RU',
				array (
					'storage' => PHPMORPHY_STORAGE_FILE,
					'predict_by_suffix' => true,
					'predict_by_db' => true,
					'graminfo_as_text' => true,
				)
			);
		}
		return self::$_morphy;
	}
}