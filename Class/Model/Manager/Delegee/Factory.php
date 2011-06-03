<?php
/**
 * 
 * @desc Класс для создания моделей через фабрики.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Manager_Delegee_Factory
{
	
	/**
	 * @desc Фабрики моделей
	 * @var array
	 */
	protected static $_factories;
	
	/**
	 * @desc Получение данных модели
	 * @param string $model Название модели
	 * @param string $key Ключ (id)
	 * @param Model|array $object Объект или данные
	 * @return Model В случае успеха объект, иначе null.
	 */
	public static function get ($model, $key, $object)
	{
		$factory_name = $model;
		if (!isset (self::$_factories [$factory_name]))
		{
			self::$_factories [$factory_name] = new $model ();
		}
		
		$dmodel = self::$_factories [$factory_name]
			->delegateClass ($model, $key, $object);
		
		Loader::load ($dmodel);
		
		$result = new $dmodel (array ());
		
		$result->setModelFactory (self::$_factories [$factory_name]);
		
		if (is_array ($object) && $object)
		{
			$result->set ($object);
		}
		
		return $result;
	}
	
}