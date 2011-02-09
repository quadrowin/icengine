<?php

abstract class Component_Collection extends Model_Collection
{ 
	
	/**
	 * 
	 * @var Model
	 */
	protected $_model;
	
	public function forRowId ()
	{
	    return $this->_model->key ();
	}
	
	public function forTable ()
	{
	    return $this->_model->table ();
	}
	
	/**
	 * Загрузка списка комментариев для записи
	 * 
	 * @param Model $model
	 * 		Модель, для которой подгружаются объекты
	 * @return Component_Collection
	 * 		Экземпляр коллекции
	 */
	public function getFor (Model $model)
	{
		$this->_model = $model;
		
		$this
		    ->where ('table', $this->_model->table ())
		    ->where ('rowId', $this->_model->key ());
			
		return $this;
	}
	
	/**
	 * @return Model
	 */
	public function model ()
	{
	    return $this->_model;
	}
	
	/**
	 * Привязывает элементы коллекции к заданной сущности.
	 * Существующая ранее связь будет утеряна.
	 * 
	 * @param Model $model
	 * 		Модель, к которой будут привязаны элементы коллекции
	 * @return Component_Collection
	 * 		Эта коллекция
	 */
	public function rejoin (Model $model)
	{
	    $this->_model->component ($this->type (), null);
        $this->_model = $model;
	    
	    $items = &$this->items ();
	    
	    foreach ($items as $item)
	    {
	        /**
	         * @var $item Model
	         */
	        $item->update (array (
	            'table'	=> $this->_model->table (),
	            'rowId'	=> $this->_model->key ()
	        ));
	    }
	    
	    $this->_model->component ($this->type (), $this);
	    
	    return $this;
	}
	
	/**
	 * Возвращает тип коллекции компонентов.
	 * @return string
	 * 		Имя класса без приставки "Component_" и без суффикса "_Collection"
	 */
	public function type ()
	{
	    return substr (get_class ($this), 10, -11);
	}
	
}