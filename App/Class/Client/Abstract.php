<?php

namespace Ice;

/**
 *
 * @desc Абстрактный клиент
 * @author Ilya Kolesnikov
 * @package Ice
 *
 */
abstract class Client_Abstract
{

	protected $_config;

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