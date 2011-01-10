<?php

class Temp_Content extends Model
{
	
	public static $scheme = array(
		Query::FROM	    => __CLASS__,
		Query::INDEX	=> array (
			array ('utcode'),
			array ('day')
		)
	);
	
	/**
	 * Созданные за этот запрос
	 * @var array
	 */
	protected static $_created = array ();
    
	/**
	 * 
	 * @param string $utcode
	 * @return Temp_Content
	 */
	public static function byUtcode ($utcode)
	{
	    return IcEngine::$modelManager->modelBy (
	        __CLASS__,
	        Query::instance ()
	        ->where ('utcode', (string) $utcode)
		);
	}
	
	/**
	 * 
	 * @param string $controller
	 * @param string $table
	 * @param integer $row_id
	 * @return Temp_Content
	 */
	public static function create ($controller, $table = '', $row_id = 0)
	{
		$utcode = self::genUtcode ();
		
		Loader::load ('Helper_Date');
		$tc = new Temp_Content (array (
			'time'			=> date ('Y-m-d H:i:s'),
			'utcode'		=> $utcode,
			'ip'			=> Request::ip (),
			'controller'	=> $controller,
			'table'			=> $table,
			'rowId'			=> (int) $row_id,
		    'day'			=> Helper_Date::eraDayNum (),
			'User__id'	    => User::id ()
		));
		
		return $tc->save ();
	}
	
	/**
	 * Возвращает временный контент для модели на этом запросе
	 * @param Model $model
	 * @param Controller_Abstract $controller
	 * @return Temp_Content
	 */
	public static function getFor (Model $model, 
	    Controller_Abstract $controller = null)
	{
	    $mname = $model->modelName ();
	    $mkey = $model->key ();
	    
	    if (!isset (self::$_created [$mname]))
	    {
	        self::$_created [$mname] = array ();
	    }
	    
	    if (!isset (self::$_created [$mname][$mkey]))
	    {
	        self::$_created [$mname][$mkey] = self::create (
	            $controller ? $controller->name () : 'unknown',
	            $model->table (),
	            $mkey
	        );
	    }
	    
	    return self::$_created [$mname][$mkey];
	}
	
	/**
	 * Генерация уникального кода
	 * @return string
	 */
	public static function genUtcode ()
	{
		// ucac7fe407f8e5e1c683005867edd74439452c4.39068717
		$u = uniqid ('', true);
		// Вырезаем точку
		return 'uc' . md5 (time ()) . substr ($u, 9, 5) . substr ($u, 15);
	}
	
}

Model_Scheme::add ('Temp_Content', Temp_Content::$scheme);