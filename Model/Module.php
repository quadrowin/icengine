<?php

/**
 * Фабрика модулей
 *
 * @author neon
 */
class Module extends Model_Defined
{
	/**
	 * @inheritdoc
	 */
	public static $rows = array(
		array(
			'id'		=> 1,
			'name'		=> 'Ice',
			'isMain'	=> true,
			'hasResource'	=> array(
				'css', 'js'
			)
		),
		array(
			'id'		=> 2,
			'name'		=> 'Admin',
			'isMain'	=> false,
			'hasResource'	=> array(
				'css', 'js'
			)
		)
	);
}