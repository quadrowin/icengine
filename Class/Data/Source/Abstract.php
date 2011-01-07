<?php

class Data_Source_Abstract
{
	
	/**
	 * Текущий запрос
	 * @var Query
	 */
	private $_query;
	
	/**
	 * 
	 * @var Data_Mapper_Abstract
	 */
	protected $_mapper;
	
	/**
	 * Результат выполнения запроса
	 * @var Query_Result
	 */
	private $_result;
	
	const CALLBACK_PREFIX = 'scopeCallback_';
	
	const SCOPE = 'scope';
	const SCOPE_ALL		= 'all';
	const SCOPE_FIRST	= 'first';
	
	const AFTER	= 'after';
	const BEFORE = 'before';
	
	const ACTION = 'action';
	
		
	protected static $_objCount = 0;
	
	protected $_objIndex = null;
	
	/**
	 * 
	 * @var array
	 */
	protected $_actionScheme = array (
		Query::SELECT => array (
			self::ACTION	=> '_select',
			self::BEFORE	=> array (
				self::ACTION	=> Query::SELECT,
				self::SCOPE		=> self::SCOPE_FIRST
			),
			self::AFTER => array (
				self::ACTION	=> Query::INSERT,
				self::SCOPE		=> self::SCOPE_ALL
			)
		),
		Query::DELETE	=> array (
			self::ACTION	=> '_delete',
			self::BEFORE	=> array (),
			self::AFTER		=> array (
				self::ACTION	=> Query::DELETE,
				self::SCOPE		=> self::SCOPE_ALL
			)
		),
		Query::INSERT	=> array (
			self::ACTION	=> '_insert',
			self::BEFORE	=> array (),
			self::AFTER		=> array (
				self::ACTION	=> Query::INSERT,
				self::SCOPE		=> self::SCOPE_ALL
			)
		),
		Query::UPDATE	=> array (
			self::ACTION	=> '_update',
			self::BEFORE	=> array (),
			self::AFTER		=> array (
				self::ACTION	=> Query::UPDATE,
				self::SCOPE		=> self::SCOPE_ALL
			)
		)
	);
		
	public function __call ($method, $args)
	{	
//		echo '__call:';
//		print_r($method) . print_r($args);
		//die();		
		//echo '<pre>';
		debug_print_backtrace();
		if (!$this->_actionScheme[$method])
		{
			return null;
		}
		
		$results = array();
		
		$before = $this->callNotify ($method, $args, self::BEFORE, $results);
		
		if (is_numeric ($before))
		{
			if ($before > 0)
			{
				for ($i = 0; $i < $before; $i++)
				{
					call_user_func_array(array(
						$results [$i]->getSource (),
						$this->_actionScheme [$method] [self::AFTER] [self::ACTION]), 
						$args
					);
				}
			}
			
			return $results [$before];
		}
		
		$action = $this->_actionScheme [$method] [self::ACTION];
		if (!isset($args[1]))
		{
			$args[] = null;
		}
	 
		$result = call_user_func_array (array($this, $action), $args);
		
		$this->callNotify($method, $args, self::AFTER, $results);
				
		return $result;
	}
	
	public function _delete ()
	{
	}
	
	public function _insert ()
	{
	}
	
	/**
	 * 
	 * @param Query $query
	 * @param Query_Options $options
	 */
	public function _select ($query, $options = null)
	{
		return $this->get($query, $options);
	}
	
	public function _update ()
	{
	}
	
	/**
	 * 
	 * @param string $method
	 * @param array $args
	 * @param string $on
	 * @return boolean
	 */
	public function callNotify ($method, array $args, $on, array &$results)
	{
		if ($this->_actionScheme[$method][$on])
		{
			$scope = $this->_actionScheme[$method][$on][self::SCOPE];
			
			return Observer::call (
				$this, 
				$this->_actionScheme[$method][$on][self::ACTION],
				$args,
				$results,
				array($this, self::CALLBACK_PREFIX . $scope)
			);
		}
		
		return false;
	}
	
	/**
	 * 
	 * @param Query $query
	 * @param Query_Options $options
	 * @return Data_Source_Abstract $this
	 */
	public function execute ($query = null, $options = null)
	{
		$this->setQuery ($query);
		$this->setResult ($this->_mapper->execute ($this, $this->_query, $options));
		return $this;
	}
	
	/**
	 * 
	 * @param Query $query
	 * @param Query_Options $options
	 * @return Query_Result|null
	 */
	public function get ($query = null, $options = null)
	{
		$this->setQuery ($query);
		$this->execute ($this->_query, $options);
		if ($this->success ())
		{
			return $this->_result;
		}
		return null;
	}
	
	/**
	 * @return Data_Mapper_Abstract
	 */
	public function getDataMapper ()
	{
		return $this->_mapper;
	}
	
	/**
	 * @return integer
	 */
	public function getIndex ()
	{
		if (is_null ($this->_objIndex))
		{
			$this->_objIndex = ++self::$_objCount;
		}
		return $this->_objIndex;
	}
	
	/**
	 * Возвращает запрос
	 * @params null|string $translator
	 * 		Ожидаемый вид запроса.
	 * 		Если необходим объект запроса, ничего не указывется (по умолчанию).
	 * 		Если указать транслятор, то результом будет результат трансляции.
	 * @return Query|mixed
	 */
	public function getQuery ($translator = null)
	{
		if ($translator)
		{
			return $this->_query->translate (
				$translator,
				$this->_mapper->getModelScheme ()
			);
		}
		return $this->_query;
	}
	
	/**
	 * @return Query_Result
	 */
	public function getResult ()
	{
		return $this->_result;
	}
	
	public function initFilters ()
	{
		Loader::load ('Filter_Collection');
		$this->_filters = new Filter_Collection ();
		return $this;
	}
	
	/**
	 * @param array $args
	 * @param Query_Result $result
	 */
	public function scopeCallback_all (array $args, $result)
	{
		return false;
	}
	
	/**
	 * @param array
	 * @param Query_Result $result
	 * @return boolean
	 */
	public function scopeCallback_first (array $args, $result)
	{
		if (is_null ($result))
		{
			return false;
		}
		if ($result->isNull ())
		{
			return true;
		}
		
		return false;
	}
	
	public function select (Query $query, Query_Options $options = null)
	{	
		$args = func_get_args ();
		return $this->__call (Query::SELECT, $args);
	}
	
	// Select::SELECT => new HotelsDataSourceCollection (new HotelsDataSource_Redis ())
	
	public function setCacheScheme (array $scheme)
	{
		foreach ($scheme as $action => $collection)
		{
			foreach ($collection->items() as $item)
			{
				foreach ($this->_actionScheme[$action] as $name => $data)
				{
					if (is_array($data))
					{
						Observer::appendObject(
							$this,
							$action,
							array($item, $this->_actionScheme[$action][self::ACTION])
						);
					}
				}
			} 
		}
	}
	
	/**
	 * 
	 * @param Data_Source_Collection $sources
	 * @return Data_Source_Abstract $this
	 */
	public function setIndexSources (Data_Source_Collection $sources)
	{
		$this->_indexSources = $sources;
		return $this;
	}
	
	/**
	 * 
	 * @param Query_Result $result
	 * @return Data_Source_Abstract
	 */
	public function setResult ($result)
	{
		if ($result instanceof Query_Result)
		{
			$this->_result = $result;
		}
		return $this;
	}
	
	/**
	 * 
	 * @param Query $query
	 * @return Data_Source_Abstract
	 */
	public function setQuery (Query $query)
	{
		$this->_query = $query;
		return $this;
	}
	
	/**
	 * 
	 * @param Data_Mapper_Abstract $mapper
	 */
	public function setDataMapper (Data_Mapper_Abstract $mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}
	
	/**
	 * @return boolean
	 */
	public function success ()
	{
		if ($this->_result)
		{
			return (bool) ($this->_result->touchedRows () > 0);
		}
		return false;
	}
}