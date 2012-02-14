<?php

namespace Ice;

/**
 *
 * @desc Помощник для чтения мыслей
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Helper_Mind_Reader
{

	/**
	 * @desc Чтение мыслей текущего пользователя
	 * @return mixed Мысль
	 */
	public static function read ()
	{
		return
			isset (User::getCurrent ()->mind)
			? User::getCurrent ()->mind
			: null;
	}

}