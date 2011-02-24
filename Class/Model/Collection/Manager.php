<?php

/**
 * 
 * @desc Менеджер коллекций
 * @author Илья
 *
 */

abstract class Model_Collection_Manager
{
	/**
	 * Возвращает коллекцию по запросу.
	 * @author Goorus
	 * @param string $model
	 * 		Модель коллекции.
	 * @param Query $query
	 * 		Запрос.
	 * @param boolean $forced [optional]
	 * 		Отключение автоджайна для моделей коллекции.
	 * @return Model_Collection
	 */
	public static function byQuery ($model, Query $query, $forced = false)
	{
		Loader::load ($model);
		
		$class_collection = $model . '_Collection';
		
		if (!Loader::load ($class_collection))
		{
			return null;
		}
		
		$collection = new $class_collection ();
		$collection->setAutojoin (!$forced);
		$collection->setQuery ($query);
		
		return $collection;
	}
	
	/**
	 * 
	 * @desc Создает коллекцию по именю
	 * @param string $className
	 * @param boolean $forced
	 * @desc Включать ли автожоин
	 * @return Model_Collection
	 */
	public static function get ($className, $forced = false)
	{
		Loader::load ($className);
		$collection = new $className;
		if ($forced)
		{
			$collection->setAutojoin (false);
		}
		return $collection;
	}
	
	/**
	 * 
	 * @desc Получить из хранилища коллекцию
	 * @param string $name
	 * @return Model_Collection
	 */
	public static function getByName ($name)
	{
		return Resource_Manager::get ('Model_Collection', $name);
	}
	
	/**
	 * 
	 * @desc получить коллекцию из хранилища по запросу и опшинам
	 * @param Model_Collection
	 * @param Query $query
	 * @param boolean $forced
	 * @return Model_Collection
	 */
	public static function getByQuery (Model_Collection $collection, 
		Query $query, $forced = false)
	{
		$model = $collection->modelName ();
		
		$key = md5 (
			$model .
			$query->translate (
				'Mysql',
				DDS::modelScheme ()
			) . serialize (
					$collection->getOptions ()->getItems ()
				)
		);
		
		$items = self::getByName ($key);
		
		if (is_null ($items))
		{
			$items = DDS::execute ($query)->getResult ()->asTable ();
			self::set ($key, $items);
		}
			
		$model_manager = IcEngine::$modelManager;
		$key_field = $collection->keyField ();

		foreach ($items as &$item)
		{
			$key = $item [$key_field];
			$item = !$forced ? 
				$model_manager->get ($model, $key, $item) :
				$model_manager->forced ()->get ($model, $key, $item);
		}
			
		return $items;
	}
	
	/**
	 * 
	 * @desc Сохранить коллекцию в хранилище
	 * @param string $name
	 * @param array $items
	 */
	public static function set ($name, array $items)
	{
		Resource_Manager::set (
			'Model_Collection',
			$name,
			$items
		);
	}
}