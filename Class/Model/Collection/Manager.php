<?php
/**
 * @desc Менеджер коллекций
 * @author Илья
 * @package IcEngine
 */
abstract class Model_Collection_Manager
{
	
	/**
	 * @desc Возвращает коллекцию по запросу.
	 * @author Goorus
	 * @param string $model Модель коллекции.
	 * @param Query $query Запрос.
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
	 * @desc Создает коллекцию по именю
	 * @param string $className
	 * @param boolean $forced
	 * @desc Включать ли автожоин
	 * @return Model_Collection
	 */
	public static function create ($className, $forced = false)
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
	 * @desc получить коллекцию из хранилища по запросу и опшинам
	 * @param Model_Collection
	 * @param Query $query
	 * @param boolean $forced
	 */
	public static function load (Model_Collection $collection, 
		Query $query, $forced = false)
	{
		$model = $collection->modelName ();
		
		$key = md5 (
			$model .
			$query->translate ('Mysql', IcEngine::$modelScheme) .
			serialize ($collection->getOptions ()->getItems ())
		);
		
		$pack = null;//Resource_Manager::get ('Model_Collection', $key);
		
		if ($pack instanceof Model_Collection)
		{
			$collection->setItems ($pack->items ());
			$collection->data ($pack->data ());
			return ;
		}
		
		if (is_array ($pack))
		{
			$collection->data ($pack ['data']);
		}
		else
		{
			$query_result = 
				IcEngine::$modelScheme
					->dataSource ($model)
						->execute ($query)->getResult ();
						
			$collection->queryResult ($query_result);
			
			if ($query->getPart (Query::CALC_FOUND_ROWS))
			{
				$collection->data ('foundRows', $query_result->foundRows ());
			}
			
			Resource_Manager::set ('Model_Collection', $key, $collection);
			
			$pack = array (
				'items'	=> $query_result->asTable (),
			);
		}
		
		$model_manager = IcEngine::$modelManager;
		$key_field = $collection->keyField ();

		foreach ($pack ['items'] as &$item)
		{
			$key = $item [$key_field];
			$item = !$forced ? 
				$model_manager->get ($model, $key, $item) :
				$model_manager->forced ()->get ($model, $key, $item);
		}
		
		$collection->setItems ($pack ['items']);
	}
	
}