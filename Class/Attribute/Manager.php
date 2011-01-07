<?php

class Attribute_Manager
{
	
    const TABLE = 'Attribute';
    
	/**
	 * 
	 * @var Data_Source_Abstract
	 */
	protected $_source;
	
	/**
	 * 
	 * @param Data_Source_Abstract $source
	 */
	public function __construct (Data_Source_Abstract $source)
	{
		$this->_source = $source;
	}
	
	/**
	 * Удаляет все атрибуты модели
	 * @param Model $model
	 */
	public function deleteFor (Model $model)
	{
	    $this->_source->execute (
	        Query::instance ()
	        ->delete ()
	        ->from (self::TABLE)
	        ->where ('table', $model->table ())
	        ->where ('rowId', $model->key ())
	    );
	}
	
	/**
	 * Получение значения атрибута
	 * 
	 * @param Model $model
	 * 		Модель
	 * @param string $key
	 * 		Название атрибута
	 * @return mixed
	 * 		Значение атрибута
	 */
	public function get (Model $model, $key)
	{
		return $this->_source->execute (
			Query::instance ()
			->select ('value')
			->from (self::TABLE)
			->where ('`table`=?', $model->table ())
			->where ('`rowId`=?', $model->key ())
			->where ('`key`=?', $key)
		)->getResult ()->asValue ();
	}
	
	/**
	 * Задание значения атрибуту
	 * 
	 * @param Model $model
	 * 		Модель
	 * @param string $key
	 * 		Название атрибута
	 * @param mixed $value
	 * 		Значение атрибута
	 */
	public function set (Model $model, $key, $value)
	{
	    $table = $model->table ();
	    $row_id = $model->key ();
	    
	     $this->_source->execute (
			Query::instance ()
			->delete ()
			->from (self::TABLE)
			->where ('`table`=?', $table)
			->where ('`rowId`=?', $row_id)
			->where ('`key`=?', $key)
		);
	    
	    if (!is_array ($key))
	    {
	   		$key = array (
	   			$key => $value
	   		);
	    }

		foreach ($key as $k => $value)
		{
			$this->_source->execute (
				Query::instance ()->
				insert (self::TABLE)->
				values (array(
					'table'	=> $table,
					'rowId'	=> $row_id,
					'key'	=> $k,
				    'value'	=> $value
				))
			); 
	    }
	    
	}
	
}