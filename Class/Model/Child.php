<?php

abstract class Model_Child extends Model
{
    
	/**
	 * @return Model_Child
	 */
	public function getParent ()
	{
		return $this->parentId ? 
		    IcEngine::$modelManager->modelByKey (
		        $this->modelName (), $this->parentId) : 
		    null;
	} 
	
	/**
	 * 
	 * @param integer|Model $parent
	 * @return boolean
	 */
	public function hasParent ($parent)
	{
	    if ($parent instanceof Model)
	    {
	        if (get_class ($this) != get_class ($parent))
	        {
	            return false;
	        }
	        $parent = $parent->key ();
	    }
	    
	    $current = $this->getParent ();
	    while ($current)
	    {
	        if ($current->key () == $parent)
	        {
	            return true;
	        }
	        $current = $current->getParent ();
	    }
	    
	    return false;
	}
	
	/**
	 * @param integer $rate
	 * 		Множитель. Результат будет домножен на указанную величину.
	 * @return integer
	 */
	public function level ($rate = 1)
	{
	    if ($this->parentId)
	    {
	        return ($this->getParent ()->level () + 1) * $rate;
	    }
	    else
	    {
	        return 0;
	    }
	}
    
}