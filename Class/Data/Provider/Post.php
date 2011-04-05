<?php

class Data_Provider_Post extends Data_Provider_Abstract
{
	
	public function get ($key, $plain = false)
	{
		return isset ($_POST [$key]) ? $_POST [$key] : null;
	}
	
	public function set ($key, $value, $expiration = 0, $tags = array ())
	{
		$_POST [$key] = $value;
	}
	
}