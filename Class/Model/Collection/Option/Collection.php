<?php
/**
 * 
 * @desc Коллекция опций.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Collection_Option_Collection
{
    
    /**
     * @desc Коллекция, к которой привязаны опции.
     * Необходима для определения названий классов опций.
     * @var Model_Collection
     */
	protected $_collection;
	
	/**
	 * @desc Опции
	 * @var array <Model_Collection_Option>
	 */
	protected $_items = array ();
	
	/**
	 * @desc Метод опции, вызываемый после выполнения запроса 
	 * на выбор коллекции.
	 * @var string
	 */
	const AFTER = 'after';
	
	/**
	 * @desc Метод опции, вызываемый до выполнения запроса 
	 * на выбор коллекции.
	 * @var string
	 */
	const BEFORE = 'before';
	
	/**
	 * @desc Создает и возвращает коллекцию опций.
	 * @param Model_Collection $collection
	 */
	public function __construct ($collection)
	{
		Loader::load ('Model_Collection_Option');
		$this->_collection = $collection;
	}
	
	/**
	 * 
	 * @param mixed $item
	 * @return Model_Collection_Option
	 */
	public function add ($item)
	{
		if (is_array ($item))
	    {
	        $item = new Model_Collection_Option (
	        	$item ['name'],
	        	$item
	        ); 
	    }
	    elseif (!$item instanceof Model_Collection_Option)
	    {
	        $item = new Model_Collection_Option (
	        	$item,
	        	array ()
	        );
	    }
	    
	    return $this->_items [] = $item;
	}
	
	/**
	 * @desc 
	 * @param string $type Тип события: "before" или "after".
	 * @param Model_Collection $collection
	 * @param Query $query
	 */
	public function execute ($type, Model_Collection $collection, Query $query)
	{
		foreach ($this->_items as &$option)
		{
			if (!$option instanceof Model_Collection_Option)
			{
			    if (is_array ($this->_items))
			    {
				    $option = new Model_Collection_Option (
				    	$option['name'],
				    	$option
				    );
			    }
			    else
			    {
			    	// по названию опции
			        $option = new Model_Collection_Option ($option);
			    }
			}
			
			$option->execute ($type, $collection, $query);
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
	 * @return Model_Collection
	 */
	public function getCollection ()
	{
		return $this->_collection;
	}
	
	/**
	 * @return array
	 */
	public function getItems ()
	{
		return $this->_items;
	}
	
	/**
	 * @desc 
	 * @param mixed $options
	 */
	public function setItems ($options)
	{
		$this->_items = array ();
		$options = (array) $options;
		for ($i = 0, $count = count ($options); $i < $count; $i++)
		{
		    $this->add ($options [$i]);
		}
	}
}