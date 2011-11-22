<?php
/**
 *
 * @desc Менеджер коллекций
 * @author Илья Колесников, Юрий Шведов
 * @package IcEngine
 *
 */
abstract class Model_Collection_Manager extends Manager_Abstract
{

	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected static $_config = array (
		'cache_provider'	=> null,
		'delegee'			=> array (
			'Model'				=> 'Simple',
			'Model_Config'		=> 'Simple',
			'Model_Defined'		=> 'Defined',
			'Model_Factory'		=> 'Simple'
		)
	);

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
		$class_collection = $model . '_Collection';

		Loader::multiLoad ($model, $class_collection);

		return new $class_collection ();
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

		$from = $query->getPart (Query::FROM);

		$collection_tags = array ();

		$tags = array ();

		$tag_valid = true;

		if ($from && self::config ()->cache_provider)
		{
			$tables = array ();

			$provider = Data_Provider_Manager::get (
				self::config ()->cache_provider
			);

			foreach ($from as $f)
			{
				$tables [] = Model_Scheme::table ($f [Query::TABLE]);
			}

			$tags = $provider->getTags ($tables);
		}

		$ds = Model_Scheme::dataSource ($collection->modelName ());
		$adapter = $ds->getAdapter ();

		Loader::load ('Composite');
		$composite = new Composite ($collection->getOptions ()->getItems ());

		// Генерируем ключ коллекции
		$key = md5 (
			$model .
			serialize ($query->translate ($adapter->getTranslatorName ())) .
			serialize ($composite->getName ())
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
				$items [] 	= $item ['id'];
				$addicts [] = $item ['addicts'];
			}

			$collection_tags = $pack ['t'];

			if ($collection_tags && array_diff ($tags, $collection_tags))
			{
				$tag_valid = false;
			}

			$pack ['items']	= $items;

			$collection->data ('addicts', $addicts);
		}

		if (!is_array ($pack) || !$tag_valid)
		{
			// Делегируемый класс определяем по первому или нулевому
			// предку.
			$parents = class_parents ($model);

			$first = end ($parents);
			$second = prev ($parents);

			$parent =
				$second && isset (self::$_config ['delegee'][$second]) ?
				$second :
				$first;

			$delegee =
				'Model_Collection_Manager_Delegee_' .
				self::$_config ['delegee'][$parent];

			Loader::load ($delegee);

			$pack = call_user_func (
				array ($delegee, 'load'),
				$collection, $query
			);

			$collection->data ('t', $tags);

			$addicts = $collection->data ('addicts');
		}

		static $key_fields = array ();

		// Инициализируем модели коллекции
		foreach ($pack ['items'] as $i => $item)
		{
			if (!is_array ($item))
			{
				$pack ['items'][$i] = Model_Manager::get ($model, $item);
			}
			else
			{
				if (isset ($key_fields [$model]))
				{
					$kf = $key_fields [$model];
				}
				else
				{
					$kf = Model_Scheme::keyField ($model);
					$key_fields [$model] = $kf;
				}

				if (isset ($item [$kf]))
				{
					$pack ['items'][$i] = Model_Manager::get (
						$model,
						$item [$kf],
						$item
					);
				}
				else
				{
					unset ($pack ['items'][$i]);
					continue;
				}
			}

			if (!empty ($addicts [$i]))
			{
				$pack ['items'][$i]->set ($addicts [$i]);
			}
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