<?php
/**
 * @desc Менеджер коллекций
 * @author Илья Колесников, Юрий Шведов
 * @package IcEngine
 */
abstract class Model_Collection_Manager
{
	/**
	 * @desc Возвращает коллекцию по запросу.
	 * @author Юрий Шведов
	 * @param string $model Модель коллекции.
	 * @param Query $query Запрос.
	 * @return Model_Collection
	 */
	public static function byQuery ($model, Query $query)
	{
		$collection = self::create ($model); 
		
		$collection->setQuery ($query);
		
		return $collection;
	}
	
	/**
	 * @desc Создает коллекцию по имени.
	 * @param string $model Модель колекции.
	 * @return Model_Collection Коллекция.
	 */
	public static function create ($model)
	{
		Loader::load ($model);
		
		$class_collection = $model . '_Collection';
		
		Loader::load ($class_collection);
		
		$collection = new $class_collection ();
		
		return $collection;
	}
	
	/**
	 * @desc получить коллекцию из хранилища по запросу и опшинам
	 * @param Model_Collection
	 * @param Query $query
	 */
	public static function load (Model_Collection $collection, Query $query)
	{
		// Название модели
		$model = $collection->modelName ();
		
		// Генерируем ключ коллекции
		$key = md5 (
			$model .
			$query->translate ('Mysql') .
			serialize ($collection->getOptions ()->getItems ())
		);
		
		// Получаем коллецию из менеджера ресурсов
		$pack = Resource_Manager::get ('Model_Collection', $key);
		
		// Если коллекцию уже использовалась в текущем сценарии,
		// то в менеджере ресурсов она будет уже инициализированная
		if ($pack instanceof Model_Collection)
		{
			$collection->setItems ($pack->items ());
			$collection->data ($pack->data ());
			return ;
		}
		
		$addicts = array ();
		
		// Из менеджера ресурсов получили свернутую коллекцию
		if (is_array ($pack))
		{
			$collection->data ($pack ['data']);
			
			$items = array ();
			
			foreach ($pack ['items'] as $i => $item)
			{
				$items [] 	= $item [$i]['id'];
				$addicts [] = $item [$i]['addicts'];
			}
			
			$pack ['items']	= $items;
			
			$collection->data ('addicts', $addicts);
		}
		else
		{
			// Выполняем запрос, получаем элементы коллеции
			$query_result = 
				Model_Scheme::dataSource ($model)
					->execute ($query)
						->getResult ();
						
			$collection->queryResult ($query_result);
			
			// Если установлен флаг CALC_FOUND_ROWS,
			// то назначаем ему значение
			if ($query->getPart (Query::CALC_FOUND_ROWS))
			{
				$collection->data ('foundRows', $query_result->foundRows ());
			}
			
			Loader::load ('Helper_Data_Source');
		
			$fields = Helper_Data_Source::fields ($collection->table ())
				->column ('Field');

			$table = $query_result->asTable ();
			
			$key_field = Model_Scheme::keyField ($model);
			
			$items = array ();
			
			foreach ($table as $i => $item)
			{
				foreach ($item as $field=>$value)
				{
					if (!in_array ($field, $fields))
					{
						$addicts [$i][$field] = $value;
					}	
				}
				$items [] = $item [$key_field];
			}
			
			$collection->data ('addicts', $addicts);
				
			$pack = array (
				'items'	=> $items,
			);
		}
		
		// Инициализируем модели коллекции
		foreach ($pack ['items'] as &$item)
		{
			$item = Model_Manager::get ($model, $item);
		}
		
		$collection->setItems ($pack ['items']);
		
		// В менеджере ресурсов сохраняем клона коллеции
		Resource_Manager::set (
			'Model_Collection', 
			$key, 
			self::create ($collection->modelName ())
				->assign ($collection)
		);
	}
}