<?php

if (!class_exists ('View_Render_Abstract'))
{
    include dirname (__FILE__) . '/Render/Abstract.php';
}

class View_Render extends Model_Factory
{
	
	/**
	 * 
	 * @var View_Render_Abstract
	 */
//	protected $_delegee;
//	
//	public function __call ($method, $args)
//	{
//		if (method_exists ($this->delegee (), $method))
//		{
//			return call_user_func_array (array ($this->_delegee, $method), $args);
//		}
//		else
//		{
//			Loader::load ('View_Render_Exception');
//			throw new View_Render_Exception ("Method $method not found.");
//		}
//	}
//	
//	public function __get ($field)
//	{
//		return $this->delegee ()->field ($field);
//	}
//	
//	public function __set ($field, $value)
//	{
//		parent::__set($field, $value);
//		$this->delegee ()->field ($field, $value);
//	}
	
	
	/**
	 * 
	 * 
	 * @param string $name
	 * @return View_Render_Abstract
	 */
	public static function byName ($name)
	{
	    return IcEngine::$modelManager->modelBy (
	        'View_Render',
	        Query::instance ()
	        ->where ('name', $name)
	    );
	    
//        $id = (int) DDS::execute (
//	        Query::instance ()
//	        ->select ('id')
//	        ->from ('View_Render')
//	        ->where ('name=?', $name)
//	    )->getResult ()->asValue ();
//	    
//	    $render = IcEngine::$modelManager->get ('View_Render', $id);
//	    
//	    if ($render instanceof View_Render)
//	    {
//	        return $render->delegee ();
//	    }
//	    
//	    return $render;
	}
	
	/**
	 * @return View_Render_Abstract
	 */
//	public function delegee ()
//	{
//		if (!$this->_delegee)
//		{
//			if (!isset ($this->_fields ['name']))
//			{
//				throw new Zend_Exception ('No necessary data.');
//			}
//			$class = get_class ($this) . '_' . $this->_fields ['name'];
//			Loader::load ('View_Render_Abstract');
//			Loader::load ($class);
//			$this->_delegee = new $class ($this->_fields);
//		}
//		return $this->_delegee;
//	}
	
	public function table ()
	{
		return 'View_Render';
	}
	
}