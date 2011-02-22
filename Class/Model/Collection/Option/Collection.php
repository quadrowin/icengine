<?php

class Model_Collection_Option_Collection
{
    
    /**
     * 
     * @var string
     */
	protected $_modelName;
	
	/**
	 * 
	 * @var array
	 */
	protected $_options;
	
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
	 * @param mixed $item
	 */
	public function add ($item)
	{
	    if ($item instanceof Model_Collection_Option)
	    {
	        $this->_options [] = $item;
	    }
	    elseif (is_array ($item))
	    {
	        $this->_options [] = new Model_Collection_Option (
	            $item ['name'], $item); 
	    }
	    else
	    {
	        $this->_options [] = new Model_Collection_Option ($item);
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
	public function execute ($beforeAfter, Model_Collection $collection, Query $query)
	{
		if (is_null ($this->_modelName))
		{
			include_once ('Zend/Exception.php');
			throw new Zend_Exception ('Model name is null');
			return false;
		}
		for ($i = 0, $count = sizeof ($this->_options); $i < $count; $i++)
		{
			if (!($this->_options [$i] instanceof Model_Collection_Option))
			{
			    if (is_array ($this->_options))
			    {
				    $this->_options [$i] = new Model_Collection_Option (
				        $this->_options [$i]['name'], $this->_options [$i]);
			    }
			    else
			    {
			        $this->_options [$i] = new Model_Collection_Option (
			            $this->_options [$i]);
			    }
			}
			$option = $this->_options [$i]->getName ();
			$this->_results [$option][$beforeAfter] = $this->_execute (
				$this->_modelName, 
				$option, 
				$beforeAfter,
				array (
				    $collection,
				    $query,
				    $this->_options [$i]->getParams ()
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
		return $this->_options;
	}
	
	/**
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
	 * @param string $modelName
	 */
	public function setModel ($modelName)
	{
		$this->_modelName = $modelName;
		return $this;
	}
	
	public function setOptions ($options)
	{
		Loader::load ('Model_Collection_Option');
		$this->_options = array ();
		$options = (array) $options;
		for ($i = 0, $count = sizeof ($options); $i < $count; $i++)
		{
		    $this->add ($options [$i]);
//			$this->_options [$i] = new Model_Collection_Option ($options [$i]);
		}
	}
}