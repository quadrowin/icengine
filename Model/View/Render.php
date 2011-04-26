<?php

Loader::load ('View_Render_Abstract');

/**
 * 
 * @desc Фабрика рендеров.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class View_Render extends Model_Factory
{
	
	/**
	 * @desc Возвращает рендер по названию.
	 * @param string $name
	 * @return View_Render_Abstract
	 */
	public static function byName ($name)
	{
		return Model_Manager::byQuery (
			'View_Render',
			Query::instance ()
				->where ('name', $name)
		);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Model_Factory::table()
	 */
	public function table ()
	{
		return 'View_Render';
	}
	
}