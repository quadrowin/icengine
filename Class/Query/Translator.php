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
	 * @return mixed Результат трансляции.
	 */
	public function translate (Query $query)
	{
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