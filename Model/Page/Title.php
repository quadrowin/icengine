<?php

if (!class_exists ('Model_Child'))
{
	Loader::load ('Model_Child');
}

class Page_Title extends Model_Child
{
	
	/**
	 * Переменные для подстановки в заголовок
	 * @var array
	 */
	protected static $_variables = array ();
	
	protected function _compile ()
	{
		$keys = array_keys (self::$_variables);
		$vals = array_values (self::$_variables);
		
		foreach ($keys as &$key)
		{
			$key = '{$' . $key . '}';
		}
		
		return str_replace (
			$keys,
			$vals,
			$this->title
		);
	}
	
	/**
	 * Получение заголовка по ссылке на страницу
	 * @param string $uri
	 * @return Page_Title
	 */
	public static function byUri ($uri)
	{
		$row = DDS::execute (
			Query::instance ()
			->select ('*')
			->from ('Page_Title')
			->where ('? RLIKE `pattern`', $uri)
			->limit (1)
		)->getResult ()->asRow ();
		
		if (!$row)
		{
			return null;
		}
		
		return IcEngine::$modelManager->get ('Page_Title', $row ['id'], $row);
	}
	
	/**
	 * Получене результирующего заголовка.
	 * 
	 * @return string
	 */
	public function compile ()
	{
		$parent = $this->getParent ();
		return ($parent ? $parent->compile () : '') . $this->_compile ();
	}
	
	/**
	 * Получение или установка значения.
	 * 
	 * @param string $key
	 * 		Ключ.
	 * @param mixed $value [optional]
	 * 		Значение.
	 * @return mixed
	 * 		Если передан только ключ, возвращает значение.
	 * 		Иначе null.
	 */
	public static function variable ($key)
	{
		if (func_num_args () > 1)
		{
			self::$_variables [$key] = func_get_arg (1);
		}
		else
		{
			return self::$_variables [$key];
		}
	}
	
}