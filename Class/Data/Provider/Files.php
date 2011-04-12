<?php

class Data_Provider_Files extends Data_Provider_Abstract
{
	public function get ($key, $plain = false)
	{
		if (isset ($_FILES [$key]))
		{
			return $_FILES [$key];
		}
		return null;
	}
	
	public function set ($key, $value, $expiration = 0, $tags = array ())
	{
		return;
	}
}