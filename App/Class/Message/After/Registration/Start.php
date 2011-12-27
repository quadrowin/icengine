<?php

namespace Ice;

/**
 *
 * @desc Событие после начала регистрации, до подтверждения e-maila.
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Message_After_Registration_Start extends Message_Abstract
{

	/**
	 * @desc Добавление в очередь сообщений
	 * @param Registration $registration
	 * @param array $params
	 */
	public static function push (Registration $registration,
		array $params = array ())
	{
		Core::$messageQueue->push (
			'After_Registration_Start',
			array_merge (
				$params,
				array ('registration' => $registration)
			)
		);
	}

}