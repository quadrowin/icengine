<?php

class Controller_Chat_Session extends Controller_Abstract
{
	public function create ()
	{
		$code = $this->_input->receive ('code');
		
		Loader::load ('Chat_Session');
		Loader::load ('Helper_Date');
		
		$session = new Chat_Session (array (
			'code'		=> $code,
			'createdAt'	=> Helper_Date::toUnix ()
		));
		
		$session->save ();
		
		$this->_task->setTemplate (null);
	}
	
	public function join ()
	{
		list (
			$code,
			$name,
			$uri
		) = $this->_input->receive (
			'code',
			'name',
			'uri'
		);
		
		if (!$uri && !User::id ())
		{
			return $this->replaceAction (
				'Error',
				'accessDenied'
			);
		}
		
		$session_join = Registry::sget ('session_join');
		
		if ($session_join)
		{
			$name = $session_join->name;
			$code = $session_join->Chat_Session->code;
		}
		 
		Loader::load ('Chat_Session');
		Loader::load ('Chat_Session_Join');
		
		$session = Chat_Session::byCode ($code);
		
		if (!$session) 
		{
			$session = new Chat_Session (array (
				'code'		=> $code,
				'createdAt'	=> Helper_Date::toUnix ()
			));
			
			$session->save ();
		}
		
		$query = Query::instance ()
			->where (
				'phpSessionId', 
				User_Session::getCurrent ()->phpSessionId
			)
			->where (
				'Chat_Session__id',
				$session->key ()
			);

		if ($uri)
		{
			$query 
				->where ('uri', $uri);
		}
		
		$join = Model_Manager::byQuery (
			'Chat_Session_Join',
			$query
		);
		
		Loader::load ('Helper_String');
		$name = Helper_String::charset_x_utf8 ($name);
		
		if (!$join)
		{
			$join = Chat_Session_Join::forUser (
				$session,
				$name,
				$uri
			);
		}
		
		$this->_output->send (array (
			'data'	=> array (
				'join_id'	=> $join->key (),
				'name'		=> $name,
				'code'		=> $code,
				'uri'		=> $uri
			)
		));
	}
}