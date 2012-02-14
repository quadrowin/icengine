<?php

namespace Ice;

/**
 *
 * @desc Для контроллера по умолчанию
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Controller_Checker_Always {

	/**
	 * @desc Всегда возвращает true
	 * @return boolean
	 */
	public static function check ()
	{
		return true;
	}

}
