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
 * @property string $keywords Ключевые слова.
 * @property string $description Описание.
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
	protected function _compile ($field = 'title')
	{
		if ($this->sfield ($field . 'Action'))
		{
			$a = explode ('/', $this->field ($field . 'Action'));
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
			$this->$field
		);
	}
	
	/**
	 * @desc Данные для страницы по хосту и адресу.
	 * @param string $host
	 * @param string $page 
	 * @return Page_Title
	 */
	public static function byAddress ($host, $page)
	{
		return Model_Manager::byQuery (
			'Page_Title',
			Query::instance ()
				->where ('(? RLIKE `host` OR `host`="")', $host)
				->where ('? RLIKE `pattern`', $page)
				->order ('`host`=""')
				->limit (1)
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
	public function compile ($field = 'title')
	{
		$parent = $this->getParent ();
		return 
			($parent ? $parent->compile ($field) : '') .
			$this->_compile ($field);
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