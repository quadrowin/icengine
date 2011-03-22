<?php
/**
 * 
 * @desc Класс для подключения движка к тестам
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Test_Implementator
{
	
	/**
	 * @desc Подключение движка
	 */
	public static function implement ()
	{
		if (class_exists ('IcEngine'))
		{
			// Уже подключен
			return ;
		}
		
		date_default_timezone_set ('UTC');
		
		require dirname (__FILE__) . '/../IcEngine.php';
		IcEngine::init ();
		Loader::load ('Loader_Auto');
		Loader_Auto::register ();
		
		Loader::addPath ('includes', IcEngine::root () . 'includes/');
		
		IcEngine::initApplication (
			'Icengine',
			IcEngine::path () . 'Class/Application/Behavior/Icengine.php'
		);
		IcEngine::run ();
	}
	
}