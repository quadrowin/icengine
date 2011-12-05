<?php

namespace Ice;

/**
 *
 * @desc Для определения вызова контроллеров POST запросов
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Controller_Checker_Post
{

	/**
	 * @desc Проверяет, является ли вызов консольным.
	 * @return boolean
	 */
	public static function check ()
	{
		return Request::isPost ();
	}

}
