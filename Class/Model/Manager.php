<?php
/**
 * 
 * @desc Менеджер моделей.
 * @author Юрий
 * @package IcEngine
 *
 */
class Model_Manager
{
	
	/**
	 * @desc Фабрики моделей
	 * @var array
	 */
	protected static $_factories;
	
	/**
	 * @desc Следующая модель будет создана с выключенным autojoin.
	 * @var boolean
	 */
	protected static $_forced = false;
	
	/**
	 * @desc Экземпляр менеджера моделей
	 * @var Model_Manager
	 */
	protected static $_instance;
	
	/**
	 * @desc Данные о моделях.
	 * @var Model_Scheme
	 */
	protected static $_modelScheme;
	
	/**
	 * @param Model_Scheme $data_source
	 */
	public function __construct (Model_Scheme $model_scheme)
	{
		self::$_instance = $this;
		Loader::load ('Model_Factory');
		self::$_modelScheme = $model_scheme;
	}
	
	/**
	 * @desc Получение условий выборки из запроса
	 * @param Query $query
	 * @return array|null
	 */
	protected static function _prepareSelectQuery (Query $query)
	{
		$where = $query->getPart (Query::WHERE);
		$conditions = array ();
		foreach ($where as $w)
		{
			$condition = $w [Query::WHERE];
			$value = $w [Query::VALUE];
			
			$p = strpos ($condition, '=?');
			if ($p)
			{
				$condition = substr ($condition, 0, $p);
			}
			
			$conditions [$condition] = $value;
		}
		return $conditions;
	}
	
	/**
	 * @desc Получение данных модели из источника данных.
	 * @param Model $object
	 */
	protected static function _read (Model $object)
	{
		$key = $object->key ();
		
		if (!$key)
		{
			return;
		}
		
		$query = Query::instance ()
			->select ('*')
			->from ($object->modelName ())
			->where ($object->keyField (), $key);
		
		$data = self::$_modelScheme
			->dataSource ($object->modelName ())
			->execute ($query)
			->getResult ()->asRow ();
		
		if ($data)
		{
			$object->set ($data);
		}
	}
	
	/**
	 * @desc Удаление данных модели из источника.
	 * @param Model $object
	 */
	public static function _remove (Model $object)
	{
		if (!$object->key ())
		{
			return ;
		}
		self::$_modelScheme
			->dataSource ($object->modelName ())
				->execute (
					Query::instance ()
						->delete ()
						->from ($object->table ())
						->where ($object->keyField (), $object->key ())
				);
	}
	
	/**
	 * @desc Сохранение модели в источник данных
	 * @param Model $object
	 * @param boolean $hard_insert
	 */
	protected static function _write (Model $object, $hard_insert = false)
	{
		$ds = self::$_modelScheme->dataSource ($object->modelName ());

		$kf = $object->keyField ();
		$id = $object->key ();
		
		if ($id && !$hard_insert)
		{
			// Обновление данных
			$ds->execute (
				Query::instance ()
					->update ($object->modelName ())
					->values ($object->asRow ())
					->where ($kf, $id)
			);
		}
		else
		{
			// Вставка
			$new_id = self::$_modelScheme->generateKey ($object);
			if ($new_id)
			{
				// Ключ указан
				$object->set ($kf, $new_id);
				$ds->execute (
					Query::instance ()
						->insert ($object->modelName ())
						->values ($object->asRow ())
				);
			}
			else
			{
				if (!$id)
				{
					$object->unsetField ($kf);
					$id = $ds->execute (
						Query::instance ()
							->insert ($object->modelName ())
							->values ($object->asRow ())
					)->getResult ()->insertId ();
					
					$object->set ($kf, $id);
				}
				else
				{
					$ds->execute (
						Query::instance ()
							->insert ($object->modelName ())
							->values ($object->asRow ())
					);
				}
			}
		}
	}
	
	/**
	 * @desc Получение модели по первичному ключу.
	 * @param string $model Имя класса модели.
	 * @param integer $key Значение первичного ключа.
	 * @return Model|null
	 */
	public static function byKey ($model, $key)
	{
		return self::byQuery (
			$model,
			Query::instance ()
				->where (self::$_modelScheme->keyField ($model), $key)
		);
	}
	
	/**
	 * @desc Получение модели по запросу.
	 * @param string $model Название модели.
	 * @param Query $query Запрос.
	 * @return Model|null
	 */
	public static function byQuery ($model, Query $query)
	{
		$forced = self::$_forced;
		self::$_forced = false;
		
		$data = null;
		
		if (is_null ($data))
		{
			if (!$query->getPart (Query::SELECT))
			{
				$query->select (array ($model => '*'));
			}
			
			if (!$query->getPart (Query::FROM))
			{
				$query->from ($model, $model);
			}
			
			$data = 
				self::$_modelScheme
					->dataSource ($model)
					->execute ($query)
					->getResult ()
						->asRow ();
		}
		
		if (!$data)
		{
			return null;
		}
		
		$mm = $forced ? self::forced () : self::$_instance;
		
		return $mm->get (
			$model,
			$data [self::$_modelScheme->keyField ($model)],
			$data
		);
	}
	
	/**
	 * @desc Следующая модель будет создана без autojoin.
	 * @return Model_Manager
	 */
	public static function forced ()
	{
		self::$_forced = true;
		return self::$_instance;
	}
	
	/**
	 * @desc Получение данных модели
	 * @param string $model Название модели
	 * @param string $key Ключ (id)
	 * @param Model|array $object Объект или данные
	 * @throws Zend_Exception
	 * @return Model В случае успеха объект, иначе null.
	 */
	public static function get ($model, $key, $object = null)
	{
		$forced = self::$_forced;
		self::$_forced = false;
		
		if ($object instanceof Model)
		{
			$result = $object;
		}
		else
		{
			$result = Resource_Manager::get ('Model', $model . '__' . $key);
			
			if ($result instanceof Model)
			{
				if (is_array ($object))
				{
					$result->set ($object);
				}
			}
			else
			{
				Loader::load ($model);
				
				$parents = class_parents ($model);
				$parent = reset ($parents);
				if ('Model_Factory' == $parent)
				{
					$factory_name = $model;
					if (!isset (self::$_factories [$factory_name]))
					{
						self::$_factories [$factory_name] = new $model ();
					}
					$dmodel = self::$_factories [$factory_name]->delegateClass ($model, $key, $object);
					if (!Loader::load ($dmodel))
					{
						Loader::load ('Zend_Exception');
						throw new Zend_Exception ('Delegate model not found: ' . $dmodel);
					}
					$result = new $dmodel (array (), !$forced);
					$result->setModelFactory (self::$_factories [$factory_name]);
					if (is_array ($object) && $object)
					{
						$result->set ($object);
					}
				}
				else
				{
					$result = new $model (
						is_array ($object) ? $object : array (),
						!$forced
					);
					self::$_forced = false;
				}
				
				if (!method_exists ($result, 'set'))
				{
					Loader::load ('Zend_Exception');
					throw new Zend_Exception ('Error model class: ' . get_class ($result));
					return;
				}
				
				$result->set ($result->keyField (), $key);
				Resource_Manager::set ('Model', $model . '__' . $key, $result);
			}
		}
		
		self::_read ($result);
		
		return $result;
	}
	
	/**
	 * @desc Удаление модели.
	 * @param Model $object Объект модели.
	 */
	public static function remove (Model $object)
	{
		// из хранилища моделей
		Resource_Manager::set ('Model', $object->resourceKey (), null);
		// Из БД (или другого источника данных)
		self::_remove ($object);
	}
	
	/**
	 * @desc Сохранение данных модели
	 * @param Model $object Объект модели.
	 * @param boolean $hard_insert Объект будет вставлен в источник данных.
	 */
	public static function set (Model $object, $hard_insert = false)
	{
		self::_write ($object, $hard_insert);
		Resource_Manager::set ('Model', $object->resourceKey (), $object);
	}
	
	/**
	 * @desc Получение модели по запросу.
	 * @param string $model
	 * @param Query $query
	 * @return Model|null
	 * @deprecated Следует использовать byQuery.
	 */
	public static function modelBy ($model, Query $query)
	{
		$forced = self::$_forced;
		self::$_forced = false;
		
		$data = null;
		
		if (is_null ($data))
		{
			if (!$query->getPart (Query::SELECT))
			{
				$query->select (array ($model => '*'));
			}
			
			if (!$query->getPart (Query::FROM))
			{
				$query->from ($model, $model);
			}
			
			$data = 
				self::$_modelScheme
					->dataSource ($model)
					->execute ($query)
					->getResult ()
						->asRow ();
		}
		
		if (!$data)
		{
			return null;
		}
		
		$mm = $forced ? self::forced () : self::$_instance;
		
		return $mm->get (
			$model,
			$data [self::$_modelScheme->keyField ($model)],
			$data
		);
	}
	
	/**
	 * @desc Получение модели по первичному ключу.
	 * @param string $model
	 * @param integer $key
	 * @return Model|null
	 * @deprecated Следует использовать byKey.
	 */
	public static function modelByKey ($model, $key)
	{
		return self::byQuery (
			$model,
			Query::instance ()
				->where (self::$_modelScheme->keyField ($model), $key)
		);
	}
	
	/**
	 * @desc Возвращает схему моделей.
	 * @return Model_Scheme
	 */
	public static function modelScheme ()
	{
		return self::$_modelScheme;
	}
	
	/**
	 * @desc Возвращает коллекцию по запросу.
	 * Следует использовать Model_Collection_Manager::byQuery().
	 * @param string $model
	 * @param Query $query
	 * @return Model_Collection
	 * @deprecated
	 */
	public static function collectionBy ($model, Query $query)
	{
		$forced = self::$_forced;
		self::$_forced = false;
		
		if (!Loader::load ($model))
		{
			return null;
		}
		
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