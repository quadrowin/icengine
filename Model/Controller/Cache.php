<?php
/**
 * 
 * @desc Кэш контроллеров.
 * Модель сохраняет данные по кэшируемым контроллерам и позже
 * при вызове через бэкграунд агент вызывает их перекомпиляцию.
 * @author Юрий Шведов
 * @package IcEngine
 * 
 */
class Controller_Cache extends Model
{
	
	/**
	 * @desc Config
	 * @var array
	 */
	protected static $_config = array (
		/**
		 * @desc Время обновления по умолчаиню
		 * @var integer
		 */
		'default_refresh_time'		=> 300,
		/**
		 * @desc Особые параметры для отдельных экшенов.
		 */
		'actions'	=> array (
			'Test/index'	=> array (
				'refresh_time'		=> 1
			)
		)
	);
	
	public function html ($action, $args)
	{
		Controller_Manager::html ($action, $args);
	}
	
}
