<?php

class Chat_Session extends Model
{
	/**
	 * @desc Получить сессию по коду
	 * @param string $sid
	 * @return Ambigous <Model, NULL>
	 */
	public static function byCode ($sid)
	{
		return Model_Manager::modelBy (
			Query::instance ()
				->where ('code', $code)
		);
	}
}