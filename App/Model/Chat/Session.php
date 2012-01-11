<?php

namespace Ice;

class Chat_Session extends Model
{
	/**
	 * @desc Получить сессию по коду
	 * @param string $code
	 * @return Ambigous <Model, NULL>
	 */
	public static function byCode ($code)
	{
		return Model_Manager::getInstance ()->byQuery (
			'Chat_Session',
			Query::instance ()
				->where ('code', $code)
		);
	}
}