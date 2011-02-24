<?php

class Model_Collection_Option_Item
{
    
    /**
     * Название опции
     * @var string
     */
    protected $_name;
    
    /**
     * Параметры
     * @var array
     */
    protected $_params;
    
    /**
     * Опции
     * @var StdClass
     */
    protected $_option;
    
    public function __construct ($name, $params = array ())
    {
        $this->_name = $name;
        $this->_params = $params;
    }
    
	private function _className ($modelName)
	{
		return $modelName . '_Collection_Option';
	}
	
	/**
	 * 
	 * @param string $model_name
	 * @param string $option
	 */
	private function _loadOption ($model_name, $option)
	{
        $class_name = $this->_className ($model_name);	
		
	    $class_based = $class_name . '_' . $option;
	    
		if ( 
		    Loader::load ($class_based, 'Class', true)
		)
		{
		    $this->_option = new $class_based ();
		    return ;
		}
	    
	    Loader::load ($class_name);
	    $this->_option = new $class_name ($option);
	}
    
	private function _methodName ($option, $beforeAfter)
	{
	    if (is_array ($option))
	    {
	        return $option ['name'] . '_' . $beforeAfter;
	    }
		return $option . '_' . $beforeAfter;
	}
    
	public function execute ($model_name, $before_after, array $args)
	{
		if (!$this->_name)
		{
			return;	
		}
		
	    if (!$this->_option)
	    {
	        $this->_loadOption ($model_name, $this->_name, $before_after);
	    }
	    
	    Loader::load ('Executor');
		
		if ($this->_option instanceof Model_Collection_Option_Abstract)
		{
    	    return Executor::execute (
    	        array ($this->_option, $before_after), $args);
		}
		elseif (is_object ($this->_option))
		{
			$method_name = $this->_methodName ($this->_name, $before_after);
			if ($method_name)
			{
			    return Executor::execute (
			        array (
			            $this->_option,
			            $method_name
			        ),
			        $args
			    );
			}
		}
		
		include_once ('Zend/Exception.php');
		throw new Zend_Exception ('Models loading error');
		return null;
	}
	
	/**
	 * 
	 * @desc Получить имя опшина
	 * @return string
	 */
	public function getName ()
	{
		return $this->_name;
	}
	
	public function getParams ()
	{
	    return $this->_params;
	}
    
}