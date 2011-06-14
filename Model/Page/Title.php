<?php

if (!class_exists ('Model_Child'))
{
	Loader::load ('Model_Child');
}
/**
 *
 * @desc Модель для формирования заголовка страницы.
 * @package Ice_Vipgeo
 * 
 */
class Page_Title extends Model_Child
{
	
	/**
	 * @desc Переменные для подстановки в заголовок
	 * @var array
	 */
	protected static $_variables = array ();
	
	/**
	 * @desc Компиляция заголовка.
	 * @return string
	 */
	protected function _compile ()
	{
		if ($this->action)
		{
			$a = explode ('/', $this->action);
			$task = Controller_Manager::call (
				$a [0],
				isset ($a [1]) ? $a [1] : 'index',
				Request::params ()
			);
			
			$this->variable ($task->getTransaction ()->buffer ());
		}
		
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
	 * @desc Получение заголовка по ссылке на страницу
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
		
		return 
			$row ?
			Model_Manager::get ('Page_Title', $row ['id'], $row) :
			null;
	}
	
	/**
	 * @desc Получене результирующего заголовка.
	 * @return string
	 */
	public function compile ()
	{
		$parent = $this->getParent ();
		return 
			($parent ? $parent->compile () : '') .
			$this->_compile ();
	}
	
	/**
	 * @desc Получение или установка значения.
	 * @param string|array $key Ключ или массв пар ключ-значение.
	 * @param mixed $value [optional] Значение.
	 * @return mixed Если передан только ключ, возвращает значение, иначе null.
	 */
	public static function variable ($key)
	{
		if (func_num_args () > 1)
		{
			self::$_variables [$key] = func_get_arg (1);
		}
		elseif (is_array ($key))
		{
			self::$_variables = array_merge (
				self::$_variables,
				$key
			);
		}
		else
		{
			return self::$_variables [$key];
		}
	}
	
}