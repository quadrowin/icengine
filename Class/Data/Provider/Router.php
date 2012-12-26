<?php

if (!class_exists ('Data_Provider_Abstract')) {
	include dirname (__FILE__) . '/Abstract.php';
}

/**
 * Провайдер данных из адресной строки
 *
 * @author Юрий Шведов, neon
 * @package IcEngine
 */
class Data_Provider_Router extends Data_Provider_Abstract
{

	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::get()
	 */
	public function get($key, $plain = false)
	{
        $locator = IcEngine::serviceLocator();
        $request = $locator->getService('request');
		return $request->param($key);
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::getAll()
	 */
	public function getAll()
	{
        $locator = IcEngine::serviceLocator();
        $request = $locator->getService('request');
		return $request->params();
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::set()
	 */
	public function set($key, $value, $expiration = 0, $tags = array ())
	{
        $locator = IcEngine::serviceLocator();
        $request = $locator->getService('request');
		$request->param($key, $value);
	}
}