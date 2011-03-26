<?php
/**
 * 
 * @desc Помощник работы с коллекциями 
 * @author Юрий
 * @package IcEngine
 *
 */
class Helper_Model_Collection
{
	
	/**
	 * Возвращает коллекцию по запросу.
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
	
}