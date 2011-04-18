<?php

class Controller_Chat_Session extends Controller_Abstract
{
	public function create ()
	{
		Loader::load ('Helper_Date');
		Loader::load ('Chat_Session');
		Loader::load ('Helper_Unique');
		
		$code = Helper_Unique::hash ();
		
		$session = new Chat_Session (array (
			'code'		=> $code,
			'createdAt'	=> Helper_Date::toUnix ()
		));
		
		$session->save ();
		
		if (!$session->key ())
		{
			return;
		}
		
		Loader::load ('Chat_Session_Join');
		
		$join = Chat_Session_Join::forUser ($session);
		
		$this->_output->send (
			'code', $code
		);
	}
	
	public function join ()
	{
		$code = $this->_input->receive ('code');
		
		Loader::load ('Chat_Session');
		
		$session = Chat_Session::byCode ($code);
		
		if (!$session)
		{
			return;
		}
		
		$join = Chat_Session_Join::forUser ($session);
		
		$this->_output->send (array (
			'code'		=> $code,
			'user'		=> User::getCurrent () 	
		));
	}
}