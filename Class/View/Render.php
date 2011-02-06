<?php

if (!class_exists ('View_Render_Abstract'))
{
	include dirname (__FILE__) . '/Render/Abstract.php';
}

class View_Render extends Model_Factory
{
	
	/**
	 * 
	 * 
	 * @param string $name
	 * @return View_Render_Abstract
	 */
	public static function byName ($name)
	{
		return IcEngine::$modelManager->modelBy (
			'View_Render',
			Query::instance ()
			->where ('name', $name)
		);
	}
	
	public function table ()
	{
		return 'View_Render';
	}
	
}