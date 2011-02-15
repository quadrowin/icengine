<?php

class Model_Factory_Delegate extends Model
{
    
    /**
     * Фабрика
     * @var Model_Factory
     */
    protected $_modelFactory;
    
    public function modelName ()
    {
        return get_class ($this->_modelFactory);
    }
    
    /**
     * Задает фабрику.
     * @param Model_Factory $factory
     * 		Экземпляр фабрики
     */
    public function setModelFactory (Model_Factory $factory)
    {
        $this->_modelFactory = $factory;
    }
    
    public function table ()
    {
        return $this->_modelFactory->table ();
    }
    
}