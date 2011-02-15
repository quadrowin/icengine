<?php

class Data_Provider_Get extends Data_Provider_Abstract
{
	
	public function get ($key, $plain = false)
	{
		return isset ($_GET [$key]) ? urldecode ($_GET [$key]) : null;
	}
	
	public function set ($key, $value, $expiration = 0, $tags = array ())
	{
		$_GET [$key] = $value;
	}
	
}