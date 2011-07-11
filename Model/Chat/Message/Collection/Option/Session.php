<?php

class Chat_Message_Collection_Option_Session extends Model_Collection_Option_Abstract
{
	/**
	 * 
	 * @param Model_Collection $items
	 * @param Query $query
	 * @param array $params
	 */
	public function before (Model_Collection $items, Query $query, array $params)
	{
		$query
			->innerJoin (
				'Chat_Session_Join',
				'Chat_Session_Join.id=Chat_Message.Chat_Session_Join__id'
			)
			->where (
				'Chat_Session_Join.Chat_Session__id', $params ['id']
			);
	}
}