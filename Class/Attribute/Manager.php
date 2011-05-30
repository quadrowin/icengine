<?php
/**
 * 
 * @desc Менеджер атрибутов.
 * Получает и устанавливает значения атрибутов модели.
 * @author Yury Shvedov
 * @package IcEngine
 * 
 */
class Attribute_Manager
{
	
	/**
	 * @desc Таблица аттрибутов
	 * @var string
	 */
    const TABLE = 'Attribute';
    
	/**
	 * 
	 * @var Data_Source_Abstract
	 */
	protected static $_source;
	
	/**
	 * 
	 * @param Data_Source_Abstract $source
	 */
	public static function init (Data_Source_Abstract $source)
	{
		self::$_source = $source;
	}
	
	/**
	 * @desc Удаляет все атрибуты модели.
	 * @param Model $model
	 */
	public static function deleteFor (Model $model)
	{
	    self::$_source->execute (
	        Query::instance ()
	        ->delete ()
	        ->from (self::TABLE)
	        ->where ('table', $model->table ())
	        ->where ('rowId', $model->key ())
	    );
	}
	
	/**
	 * @desc Получение значения атрибута.
	 * @param Model $model Модель.
	 * @param string $key Название атрибута.
	 * @return mixed Значение атрибута.
	 */
	public static function get (Model $model, $key)
	{
		$value = self::$_source->execute (
			Query::instance ()
			->select ('value')
			->from (self::TABLE)
			->where ('`table`=?', $model->table ())
			->where ('`rowId`=?', $model->key ())
			->where ('`key`=?', $key)
		)->getResult ()->asValue ();
		
		return json_decode ($value, true);
	}
	
	/**
	 * @desc Задание значения атрибуту.
	 * @param Model $model Модель.
	 * @param string|array $key Название атрибута.
	 * @param mixed $value Значение атрибута.
	 */
	public static function set (Model $model, $key, $value)
	{
	    $table = $model->table ();
	    $row_id = $model->key ();
	    
	    $query = Query::instance ()
			->delete ()
			->from (self::TABLE)
			->where ('`table`=?', $table)
			->where ('`rowId`=?', $row_id);
			
	    if (!is_array ($key))
	    {
	        $query->where ('key', $key);
	   		$key = array (
	   			$key => $value
	   		);
	    }
	    else
	    {
            $query->where ('`key` IN (?)', array_keys ($key));
	    }
	    
	    self::$_source->execute ($query);

		foreach ($key as $k => $value)
		{
			self::$_source->execute (
				Query::instance ()->
				insert (self::TABLE)->
				values (array(
					'table'	=> $table,
					'rowId'	=> $row_id,
					'key'	=> $k,
				    'value'	=> json_encode ($value)
				))
			); 
	    }
	    
	}
	
}