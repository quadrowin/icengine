<?php

namespace Ice;

/**
 *
 * @desc Для определения вызова консольных контроллеров
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Controller_Checker_Console
{

	/**
	 * @desc Проверяет, является ли вызов консольным.
	 * @return boolean
	 */
	public static function check ()
	{
		return Request::isConsole ();
	}

}
