<?php
/**
 * 
 * @desc Транслятор запросов.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Query_Translator
{
	
	/**
	 * @desc Схема моделей текущего запроса.
	 * @var Model_Scheme
	 */
	protected $_modelScheme;
	
	/**
	 * @desc Подключенные трансляторы.
	 * @var array
	 */
	protected static $_translators = array ();
	
	/**
	 * @desc Возвращает объект транслятора по имени.
	 * @param string $name Название транслятора.
	 * @return Query_Translator
	 */
	public static function factory ($name)
	{
		if (!isset (self::$_translators [$name]))
		{
			$class_name = 'Query_Translator_' . $name;
			Loader::load ($class_name);
			self::$_translators [$name] = new $class_name ();
		}
		
		return self::$_translators [$name];
	}
	
	/**
	 * @desc Транслирует запрос.
	 * @param Query $query Запрос.
	 * @param Model_Scheme $model_scheme Схема моделей.
	 * @return mixed Результат трансляции.
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