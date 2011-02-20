<?php

class Controller_Subscribe_Tour extends Controller_Abstract
{
	public function add ()
	{
		Loader::load ('Subscribe');
		$subscriber = new Subscribe ();
		
		$subscriber->name = Request::post ('name');
		$subscriber->email = Request::post ('email');
		$subscriber->City__id = Request::post ('City__id');
		
		$subscriber->save ();
	}
	
	public function send ()
	{
		Loader::load ('Subscribe_Tour');
		$subscribe = new Subscribe_Tour ();
		$subscribe->run ();	
	}
}