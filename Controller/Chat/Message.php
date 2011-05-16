<?php

class Controller_Chat_Message extends Controller_Abstract
{
	public function last ()
	{
		list (
			$session_join_id,
			$last_message_id
		) = $this->_input->receive (
			'session_join_id',	
			'last_message_id'
		);
		
		$session_join = Model_Manager::byKey (
			'Chat_Session_Join',
			$session_join_id
		);
		
		if (!$session_join)
		{
			return $this->_output->send (
				'code',	'404'
			);
		}
		
		$messages = Model_Collection_Manager::create (
			'Chat_Message_Collection'
		)
			->addOptions (array (
				'name'			=> 'Last',
				'session_id'	=> $session_join->Chat_Session->key (),
				'id'			=> $last_message_id
			));
			
		$this->_output->send (array (
			'data'	=> array (
				'messages'	=> $messages->serialize ()
			)
		));	
		
	}
	
	public function roll ()
	{
		$session_join_id = $this->_input->receive (
			'session_join_id'
		);
		
		$session_join = Model_Manager::byKey (
			'Chat_Session_Join',
			$session_join_id
		);
		
		if (!$session_join)
		{
			return $this->_output->send (
				'code',	'404'
			);
		}
		
		$messages = Model_Collection_Manager::create (
			'Chat_Message_Collection'
		)
			->addOptions (array (
				'name'	=> 'Session',
				'id'	=> $session_join->Chat_Session->key ()
			));
			
		$this->_output->send (array (
			'data'	=> array (
				'messages'	=> $messages->serialize ()
			)
		));	
	}
	
	public function send ()
	{
		list (
			$session_join_id,
			$message
		) = $this->_input->receive (
			'session_join_id',
			'message'
		);
		
		Loader::load ('Chat_Message');
		Loader::load ('Helper_Date');
		
		$message = new Chat_Message (array (
			'Chat_Session_Join__id'	=> $session_join_id,
			'message'				=> $message,
			'createdAt'				=> Helper_Date::toUnix ()
		));
		
		$message->save ();
		
		$this->_output->send (array (
			'data'	=> array (
				'id'	=> $message->key (),
				'date'	=> date ('H:i')
			)
		));
	}
}