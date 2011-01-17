<?php

class Model_Manager
{
    
    /**
     * Кэширование моделей
     * @var Data_Provider_Abstract
     */
    protected $_altProvider;
	
	/**
	 * Источник данных об объектах
	 * @var Data_Source_Abstract
	 */
	protected $_dataSource;
	
	/**
	 * Менеджер объектов
	 * @var Resource_Manager
	 */
	protected $_resourceManager;
	
	/**
	 * Фабрики моделей
	 * @var array
	 */
	protected $_factories;
	
	/**
	 * Следующая модель будет создана с выключенным autojoin.
	 * @var boolean
	 */
	protected $_forced = false;
	
	/**
	 * @param Data_Source_Abstract $data_source
	 * @param Resource_Manager $resource_manager
	 * 		Менеджер ресурсов
	 */
	public function __construct (Data_Source_Abstract $data_source, 
		Resource_Manager $resource_manager)
	{
		Loader::load ('Model_Factory');
		$this->_dataSource = $data_source;
		$this->_resourceManager = $resource_manager;
	}
	
	/**
	 * Получение условий выборки из запроса
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
	 * 
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
			->from ($object->table ())
			->where ($object->keyField (), $key);
		
		$data = $this->_dataSource->execute ($query)->getResult ()->asRow ();
		
		$object->set ($data);
	}
	
	/**
	 * 
	 * @param Model $object
	 */
	public function _remove (Model $object)
	{
		if ($object->key ())
		{
			$this->_dataSource->execute (
				Query::instance ()
				->delete ()
				->from ($object->table ())
				->where ($object->keyField (), $object->key ())
			);
		}
	}
	
	/**
	 * 
	 * @param Model $object
	 */
	protected function _write (Model $object)
	{
		if ($object->key ())
		{
			$this->_dataSource->execute (
				Query::instance ()
				->delete ()
				->from ($object->table ())
				->where ($object->keyField (), $object->key ())
			);
		}
		
		$this->_dataSource->execute (
			Query::instance ()
			->insert ($object->table ())
			->values ($object->asRow ())
		);
	}
	
	/**
	 * Следующая модель будет создана без autojoin.
	 * @return Model_Manager
	 */
	public function forced ()
	{
	    $this->_forced = true;
	    return $this;
	}
	
	/**
	 * Получение данных модели
	 * 
	 * @param string $model
	 * 		Название модели
	 * @param string $key
	 * 		Ключ (id)
	 * @param Model|array $object
	 * 		Объект или данные
	 * @throws Zend_Exception
	 * @return Model
	 * 		В случае успеха объект, иначе null.
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
			$result = $this->_resourceManager->get (
				'Model',
				$model . '__' . $key
			);
			
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
					throw new Zend_Exception('Error model class: ' . get_class ($result));
					return;
				}
				
				$result->set ($result->keyField (), $key);
				$this->_resourceManager->set ('Model', $model . '__' . $key, $result);
			}
		}
		
		$this->_read ($result);
		
		return $result;
	}
	
	/**
	 * 
	 * @return Data_Source_Abstract
	 */
	public function getDataSource ()
	{
		return $this->_dataSource;
	}
	
	/**
	 * 
	 * @return Resource_Manager
	 */
	public function getResourceManager ()
	{
		return $this->_resourceManager;
	}
	
	/**
	 * Удаление модели
	 * 
	 * @param Model $object
	 * 		Объект
	 */
	public function remove (Model $object)
	{
	    $this->_remove ($object);
	    
	    $this->_resourceManager->set (
			'Model',
			$object->resourceKey (),
			null
		);
	}
	
	/**
	 * Сохранение данных модели
	 * 
	 * @param Model $object
	 * 		Объект
	 */
	public function set (Model $object)
	{
	    $this->_write ($object);
	    
		$this->_resourceManager->set (
			'Model',
			$object->resourceKey (),
			$object
		);
	}
	
	/**
	 * 
	 * @param string $model
	 * @param Query $query
	 * @return Model|null
	 */
	public function modelBy ($model, Query $query)
	{
        $forced = $this->_forced;
	    $this->_forced = false;
	    
	    $data = null;
	    
	    if ($this->_altProvider)
	    {
	        $scheme = Model_Scheme::get ($model);
	        if ($scheme)
	        {
    	        $conditions = $this->_prepareSelectQuery ($query);
    	        if ($conditions)
    	        {
    	            //$this->_altProvider->getBy ($conditions);
    	        }
	        }
	    }
	    
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
	        $data = DDS::execute ($query)->getResult ()->asRow ();
	    }
	    
	    if (!$data || !Loader::load ($model))
	    {
	        return null;
	    }
	    
	    $mm = $forced ? $this->forced () : $this;
	    
	    return $mm->get (
	        $model, $data [$this->modelScheme ()->keyField ($model)], $data);
	}
	
    /**
     * 
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
	 * @return Model_Scheme_Abstract
	 */
	public function modelScheme ()
	{
	    return $this->_dataSource->getDataMapper ()->getModelScheme ();
	}
	
	/**
	 * 
	 * @param string $model
	 * @param Query $query
	 * @return Model_Collection
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
	    
	    $data = null;
	    
	    if ($this->_altProvider)
	    {
	        $scheme = Model_Scheme::get ($model);
	        if ($scheme)
	        {
    	        $conditions = $this->_prepareSelectQuery ($query);
    	        if ($conditions)
    	        {
    	            //$this->_altProvider->getBy ($conditions);
    	        }
	        }
	    }
	    
	    if (is_null ($data))
	    {
	        if (!$query->getPart (Query::SELECT))
	        {
	            $query->select ("$model.*");
	        }
	        if (!$query->getPart (Query::FROM))
	        {
	            $query->from ($model);
	        }
	        $data = DDS::execute ($query)->getResult ()->asTable ();
	    }
	    
	    $collection = new $class_collection ();
	    $collection->setItems (array ());
	    $collection->setAutojoin (!$forced);
	    
	    $key_field = $this->modelScheme ()->keyField ($model);
	    foreach ($data as $row)
	    {
	        $collection->add ($this->get ($model, $row [$key_field], $row));
	    }
	    
	    return $collection;
	}
	
}