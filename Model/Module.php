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
			'isMain'	=> true
		),
		array(
			'id'		=> 2,
			'name'		=> 'Admin',
			'isMain'	=> false
		)
	);
}