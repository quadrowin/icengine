<?php

class Query_Translator
{
	
	/**
	 * Схема моделей текущего запроса.
	 * @var Model_Scheme
	 */
	protected $_modelScheme;
	
	/**
	 * Подключенные трансляторы.
	 * @var array
	 */
	protected static $_translators = array ();
	
	/**
	 * 
	 * @param string $name
	 * @return Query_Translator
	 */
	public static function factory ($name)
	{
		if (!isset (self::$_translators [$name]))
		{
			$class_name = 'Query_Translator_' . $name;
			
			if (!class_exists ($class_name))
			{
				require_once dirname (__FILE__) . "/Translator/$name.php";
			}
		
			self::$_translators [$name] = new $class_name ();
		}
		
		return self::$_translators [$name];
	}
	
	/**
	 * 
	 * @param Query $query
	 * @param Model_Scheme $model_scheme
	 * @return mixed
	 */
	public function translate (Query $query, Model_Scheme $model_scheme)
	{
		$this->_modelScheme = $model_scheme;
		
		$type = $query->type ();
		$type = 
			strtoupper (substr ($type, 0, 1)) . 
			strtolower (substr ($type, 1));
		
		return call_user_func (
			array ($this, '_render' . $type),
			$query
		);
	}
	
}