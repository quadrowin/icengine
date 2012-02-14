<?php
/**
 *
 * @desc Для определения вызова JsHttpRequest
 * @author Yury Shvedov
 *
 */
class Controller_Checker_JsHttpRequest
{

	/**
	 * @desc Проверяет, является ли вызов JsHttpRequest.
	 * @return boolean
	 */
	public static function check ()
	{
		return Request::isJsHttpRequest ();
	}

}
