<?php

if (!class_exists ('Data_Provider_Abstract'))
{
	include dirname (__FILE__) . '/Abstract.php';
}

class Data_Provider_Router extends Data_Provider_Abstract
{
	
	public function get ($key, $plain = false)
	{
		return Request::param ($key);
	}
	
	public function set ($key, $value, $expiration = 0, $tags = array ())
	{
		Request::param ($key, $value);
	}
	
}