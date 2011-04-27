<?php

class Chat_Session_Join extends Model
{
	/**
	 * @desc Создает джоин для текущего пользователя
	 * @param Model $session
	 * @return Model
	 */
	public static function forUser (Model $session)
	{
		$join = new self (array (
			'Chat_Session__id'		=> $session->key (),
			'phpSessionId'			=> User_Session::getCurrent ()->phpSessionId
		));
		return $join->save ();
	}
}