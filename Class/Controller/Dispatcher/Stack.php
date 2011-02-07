<?php

abstract class Controller_Dispatcher_Stack
{
	private static $_stack;
	
	public static function push (Controller_Action $action, Controller_Abstract $controller)
	{	
		self::$_stack [] = array (
			'resource'	=> $resource,
			'data'		=> $controller->getOutput ()->getProvider (0)->getAll ()
		); 
	}
	
	public static function stack ()
	{
		Loader::load ('View_Render_Broker_Data');
		return new View_Render_Broker_Data ((array) self::$_stack);
	}
	
}