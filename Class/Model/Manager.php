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
	 * @desc Данные о моделях.
	 * @var Model_Scheme
	 */
	protected $_modelScheme;
	
	/**
	 * @desc Фабрики моделей
	 * @var array
	 */
	protected $_factories;
	
	/**
	 * @desc Следующая модель будет создана с выключенным autojoin.
	 * @var boolean
	 */
	protected $_forced = false;
	
	/**
	 * @param Model_Scheme $data_source
	 */
	public function __construct (Model_Scheme $model_scheme)
	{
		Loader::load ('Model_Factory');
		$this->_modelScheme = $model_scheme;
	}
	
	/**
	 * @desc Получение условий выборки из запроса
	 * @param Query $query
	 * @return array|null
	 */
	protected function _prepareSelectQuery (Query $query)
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
	protected function _read (Model $object)
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
		
		$data = $this->_modelScheme
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
	public function _remove (Model $object)
	{
		if (!$object->key ())
		{
			return ;
		}
		$this->modelScheme ()
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
	protected function _write (Model $object, $hard_insert = false)
	{
		$ds = $this->_modelScheme->dataSource ($object->modelName ());

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
			$new_id = $this->_modelScheme->generateKey ($object);
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
	 * @desc Следующая модель будет создана без autojoin.
	 * @return Model_Manager
	 */
	public function forced ()
	{
		$this->_forced = true;
		return $this;
	}
	
	/**
	 * @desc Получение данных модели
	 * @param string $model Название модели
	 * @param string $key Ключ (id)
	 * @param Model|array $object Объект или данные
	 * @throws Zend_Exception
	 * @return Model В случае успеха объект, иначе null.
	 */
	public function get ($model, $key, $object = null)
	{
		$forced = $this->_forced;
		$this->_forced = false;
		
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
					if (!isset ($this->_factories [$factory_name]))
					{
						$this->_factories [$factory_name] = new $model ();
					}
					$dmodel = $this->_factories [$factory_name]->delegateClass ($model, $key, $object);
					if (!Loader::load ($dmodel))
					{
						Loader::load ('Zend_Exception');
						throw new Zend_Exception ('Delegate model not found: ' . $dmodel);
					}
					$result = new $dmodel (array (), !$forced);
					$result->setModelFactory ($this->_factories [$factory_name]);
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
					$this->_forced = false;
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
		
		$this->_read ($result);
		
		return $result;
	}
	
	/**
	 * @desc Удаление модели.
	 * @param Model $object Объект модели.
	 */
	public function remove (Model $object)
	{
		// из хранилища моделей
		Resource_Manager::set ('Model', $object->resourceKey (), null);
		// Из БД (или другого источника данных)
		$this->_remove ($object);
	}
	
	/**
	 * @desc Сохранение данных модели
	 * @param Model $object Объект модели.
	 * @param boolean $hard_insert Объект будет вставлен в источник данных.
	 */
	public function set (Model $object, $hard_insert = false)
	{
		$this->_write ($object, $hard_insert);
		Resource_Manager::set ('Model', $object->resourceKey (), $object);
	}
	
	/**
	 * @desc Получение модели по запросу.
	 * @param string $model
	 * @param Query $query
	 * @return Model|null
	 */
	public function modelBy ($model, Query $query)
	{
		$forced = $this->_forced;
		$this->_forced = false;
		
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
				$this->_modelScheme
				->dataSource ($model)
				->execute ($query)
				->getResult ()->asRow ();
		}
		
		if (!$data)
		{
			return null;
		}
		
		$mm = $forced ? $this->forced () : $this;
		
		return $mm->get (
			$model, $data [$this->modelScheme ()->keyField ($model)], $data);
	}
	
	/**
	 * @desc Получение модели по первичному ключу.
	 * @param string $model
	 * @param integer $key
	 * @return Model|null
	 */
	public function modelByKey ($model, $key)
	{
		return $this->modelBy (
			$model,
			Query::instance ()
			->where ($this->modelScheme ()->keyField ($model), $key)
		);
	}
	
	/**
	 * @return Model_Scheme
	 */
	public function modelScheme ()
	{
		return $this->_modelScheme;
	}
	
	/**
	 * @desc Возвращает коллекцию по запросу.
	 * Следует использовать Model_Collection_Manager::byQuery().
	 * @param string $model
	 * @param Query $query
	 * @return Model_Collection
	 * @deprecated
	 */
	public function collectionBy ($model, Query $query)
	{
		$forced = $this->_forced;
		$this->_forced = false;
		
		if (!Loader::load ($model))
		{
			return null;
		}
		
		$class_collection = $model . '_Collection';
		
		if (!Loader::load ($class_collection))
		{
			return null;
		}
		
//		$data = null;
//		
//		if (is_null ($data))
//		{
//			if (!$query->getPart (Query::SELECT))
//			{
//				$query->select ("`$model`.*");
//			}
//			if (!$query->getPart (Query::FROM))
//			{
//				$query->from ($model);
//			}
//			$data = 
//				$this->modelScheme ()->dataSource ($model)
//				->execute ($query)
//				->getResult ()
//				->asTable ();
//		}
		
		$collection = new $class_collection ();
		//$collection->setItems (array ());
		$collection->setAutojoin (!$forced);
		$collection->setQuery ($query);
		
//		$key_field = $this->modelScheme ()->keyField ($model);
//		foreach ($data as $row)
//		{
//			$collection->add ($this->get ($model, $row [$key_field], $row));
//		}
		
		return $collection;
	}
	
}