<?php

namespace Ice;

if (!class_exists (__NAMESPACE__ . '\\Data_Provider_Abstract'))
{
	include __DIR__ . '/Abstract.php';
}

class Data_Provider_View extends Data_Provider_Abstract
{

	public function get ($key, $plain = false)
	{
		return null;
	}

	public function set ($key, $value, $expiration = 0, $tags = array ())
	{
		View_Render_Manager::getView ()->assign ($key, $value);
	}

}