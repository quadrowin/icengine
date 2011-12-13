<?php

namespace Ice;

/**
 *
 * @desc Хелпер работы с директориями
 * @package Ice
 * 
 */
class Helper_Dir
{

	/**
	 * @desc Правила разименования для директорий
	 * @var array
	 */
	protected static $_replaces;

	/**
	 * @desc Получение полных путей
	 * @param array|Objective $dirs
	 * @return array of string
	 */
	public static function solve ($dirs)
	{
		if (!self::$_replaces)
		{
			self::$_replaces = array(
				'{$app}' => rtrim (Core::bootstrap ()->appPath (), '\\/'),
				'{$ice}' => rtrim (Core::path (), '\\/'),
				'{$root}' => rtrim (Core::root (), '\\/')
			);
		}

		$result = array();
		foreach ($dirs as $dir)
		{
			$result[] = strtr ($dir, self::$_replaces);
		}

		return $result;
	}

}