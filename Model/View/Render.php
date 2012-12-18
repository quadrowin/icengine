<?php

/**
 *
 * @desc Фабрика рендеров.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class View_Render extends Model_Defined
{
    /**
	 * @inheritdoc
	 */
	public static $rows = array(
		array(
			'id'	=> 1,
			'name'	=> 'Smarty'
		),
		array(
			'id'	=> 2,
			'name'	=> 'Front'
		),
		array(
			'id'	=> 3,
			'name'	=> 'JsHttpRequest'
		),
		array(
			'id'	=> 4,
			'name'	=> 'Post'
		),
		array(
			'id'	=> 5,
			'name'	=> 'Ajax'
		)
	);
    
    
	/**
	 * (non-PHPdoc)
	 * @see Model_Factory::table()
	 */
	public function table ()
	{
		return 'View_Render';
	}

}