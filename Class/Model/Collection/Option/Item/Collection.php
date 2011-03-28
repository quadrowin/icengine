<?php

class Model_Collection_Option_Item_Collection
{
    
    /**
     * 
     * @var string
     */
	protected $_modelName;
	
	/**
	 * 
	 * @var array <Model_Collection_Option_Item>
	 */
	protected $_items = array ();
	
	/**
	 * 
	 * @var array
	 */
	protected $_results = array ();
	
	const AFTER = 'after';
	const BEFORE = 'before';
	
	/**
	 * 
	 * @param string $modelName
	 * @param array $options
	 */
	public function __construct ($modelName = null, $options = array ())
	{
	    Loader::load ('Model_Collection_Option_Item');
	    Loader::load ('Model_Collection_Option_Abstract');
		$this->setModel ($modelName)
			 ->setOptions ($options);
	}
	
	/** 
	 * 
	 * @param string $modelName 
	 */
	private function _className ($modelName)
	{
		return $modelName.'_Collection_Option';
	}
	
/**

	 * 
	 * @param string $modelName
	 * @param string $option
	 * @param string $beforeAfter
	 * @param array $args
	 * @throws Zend_Exception
	 */
	private function _execute ($modelName, $option, $beforeAfter, array $args)
	{
		$className = $this->_className ($modelName);	
		$methodName = $this->_methodName ($option, $beforeAfter);

		Loader::load ('Executor');
		if (Loader::load ($className))
		{
			return Executor::execute (
				array (new $className ($option), $methodName),
				$args
			);
		}
		else
		{
			include_once ('Zend/Exception.php');
			throw new Zend_Exception ('Models loading error');
			return null;
		}
	}
	
	/**
	 * 
	 * @param mixed $item
	 */
	public function add ($item)
	{
	    if ($item instanceof Model_Collection_Option_Item)
	    {
		    if ($this->exists ($item->getName ()))
			{
				return;
			}
	        $this->_items [] = $item;
	    }
	    elseif (is_array ($item))
	    {
		    if ($this->exists ($item ['name']))
			{
				return;
			}
	        $this->_items [] = new Model_Collection_Option_Item (
	            $item ['name'], $item); 
	    }
	    else
	    {
		    if ($this->exists ($item))
			{
				return;
			}
	        $this->_items [] = new Model_Collection_Option_Item ($item);
	    }
	}
	
	/**
	 * 
	 * @param string $beforeAfter 
	 * 		Тип события: "before" или "after".
	 * @param Model_Collection $collection
	 * @param Query $query
	 * @throws Zend_Exception
	 */
	public function execute ($before_after, Model_Collection $collection, 
	    Query $query)
	{
		if (!$this->_modelName)
		{
			include_once ('Zend/Exception.php');
			throw new Zend_Exception ('Model name is empty.');
			return false;
		}
		
		$this->_items = array_values ($this->_items);
		
		for ($i = 0, $count = sizeof ($this->_items); $i < $count; $i++)
		{
		    $this->_items [$i]->execute (
		        $this->_modelName, 
		        $before_after,
		        array (
				    $collection,
				    $query,
				    $this->_items [$i]->getParams ()
				) 
		    );
		}
	}
	
	/**
	 * 
	 * @param Model_Collection $collection
	 * @param Query $query
	 * @rturn mixed
	 */
	public function executeAfter (Model_Collection $collection, Query $query)
	{
		return $this->execute (self::AFTER, $collection, $query);
	}
	
	/**
	 * 
	 * @param Model_Collection $collection
	 * @param Query $query
	 */
	public function executeBefore (Model_Collection $collection, Query $query)
	{
		return $this->execute (self::BEFORE, $collection, $query);
	}
	
	
    public function exists ($item)
    {
    	foreach ($this->_items as $_item)
    	{
    		if ($_item->getName () == $item)
    		{
    			return true;
    		}
    	}
    	return false;
    }
	
	
	/**
	 * 
	 * @desc Получить элементы
	 * @return array<Model_Collection_Option_Item>
	 */
	public function getItems ()
	{
		return $this->_items;
	}
	
	/**
	 * @desc Получить имя модели
	 * @return string
	 */
	public function getModel ()
	{
		return $this->_modelName;
	}
	
	/**
	 * @return array
	 */
	public function getOptions ()
	{
		return $this->_items;
	}
	
	/**
	 * 
	 * @return mixed
	 */
	public function getResults ()
	{
		return $this->_results;
	}
	
	/**
	 * 
	 * @param string $option
	 * @param string $beforeAfter
	 * @retrun string
	 */
	private function _methodName ($option, $beforeAfter)
	{
	    if (is_array ($option))
	    {
	        return $option ['name'] . '_' . $beforeAfter;
	    }
		return $option.'_'.$beforeAfter;
	}

	/**
	 * 
	 * @desc Удалить опшин по имени
	 * @param string $name
	 */
	public function remove ($name)
	{
		foreach ($this->_items as $i=>$item);
		{
			if ($item->getName () == $name)
			{
				unset ($this->_items [$i]);
			}
		}
	}
	
	/**
	 *
	 * @param string $modelName
	 */
	public function setModel ($modelName)
	{
		$this->_modelName = $modelName;
		return $this;
	}
	
	public function setOption ($option)
	{
		if ($this->_items)
		{
			foreach ($this->_items as $i=>$item)
			{
		    	if ($item->getName () == $option ['name'])
		    	{
		    		$this->_items [$i]
		    			= new Model_Collection_Option_Item ($option ['name'], $option);
		    		return;
		    	}
			}
		}
		$this->_items [] = new Model_Collection_Option_Item ($option ['name'], $option);
	}
	
	public function setOptions ($options)
	{
		$this->_items = array ();
		$options = (array) $options;
		for ($i = 0, $count = sizeof ($options); $i < $count; $i++)
		{
		    $this->add ($options [$i]);
		}
	}
}