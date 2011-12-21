<?php

namespace Ice;

class Chat_Message extends Model
{
	public function getNew ()
	{
		return Model_Manager::getInstance ()->byQuery (
			'Chat_Message',
			Query::instance ()
				->innerJoin (
					'Chat_Session_Join',
					'Chat_Session_Join.id=Chat_Message.Chat_Session_Join__id'
				)
				->where ('phpSessionId', User_Session::getCurrent ()
					->phpSessionId)
				->where ('readed', 0)
		);
	}
}