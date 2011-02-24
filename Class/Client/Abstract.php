<?php

/**
 * 
 * @desc Абстрактный клиент
 * @author Илья
 * @package IcEngine
 */
abstract class Client_Abstract
{
	/**
	 * 
	 * @desc Получить имя клиента без префикса "Client_"
	 * @return string
	 */
	public function name ()
	{
		return substr (__CLASS__, 7);
	}
}