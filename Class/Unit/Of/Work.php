<?php

/**
 * Unit of work
 *
 * @author neon
 */
class Unit_Of_Work
{
	/**
	 * Очередь запросов
	 * @var array
	 */
	private static $queries = array();

	/**
	 * Необработанные запросы
	 * @var array
	 */
	private static $raw = array();
	/**
	 * Количество запросов в очереди
	 * @var int
	 */
	private static $rawCount = 0;
	/**
	 * Количество запросов на автостарт
	 * @var int
	 */
	private static $autoFlush = 0;

	/**
	 * Незагруженные модели, для селекта
	 * @var array
	 * @deprecated
	 */
	private static $rawModel = array();

	/**
	 * Загрузчик по умолчанию, для SELECT
	 * @var type
	 * @deprecated
	 */
	private static $loader = null;

	/**
	 * Максимальное число запросов в буфере
	 *
	 * @param type $value
	 */
	public static function setAutoFlush($value)
	{
		self::$autoFlush = (int) $value;
	}

	/**
	 * Обработка $raw данных и сборка запросов
	 */
	private static function build()
	{
		foreach (self::$raw as $type=>$array) {
			foreach ($array as $key=>$data) {
				$uowQuery = Unit_Of_Work_Manager::byName($type);
				$query = $uowQuery->build($key, $data);
				self::pushQuery($query, $key);
			}
		}
	}

	/**
	 * Загрузить одну модель и сопутствующие ей, по raw
	 *
	 * @param string $key
	 */
	private static function buildOne($key)
	{
		$uowQuery = Unit_Of_Work_Manager::byName(QUERY::SELECT);
		$query = $uowQuery->build($key, self::getRaw(QUERY::SELECT, $key));
		self::pushQuery($query, $key);
	}

	/**
	 * Освободить очередь
	 */
	public static function flush()
	{
		echo 'flush' . "\n";
		self::build();
		foreach (self::$queries as $key=>$query) {
			self::_execute($key, $query);
		}
		self::reset();
	}

	/**
	 * Выполнить группу запросов по ключу
	 *
	 * @param string $key
	 * @return void
	 */
	private static function _execute($key)
	{
		$query = self::$queries[$key];
		//echo $key . ' ' . $query['query']->translate() . '<br />';
		$result = Model_Scheme::dataSource($query['modelName'])
			->execute($query['query']);
		//print_r($result);die;
		self::$rawCount--;
		if (isset($query['loader'])) {
			$loader = Unit_Of_Work_Loader_Manager::get($query['loader']);
			$loader->load($key, $result);
		}
		unset(self::$raw[$key]);
		unset(self::$queries[$key]);
	}

	/**
	 * Получить необработанные данные
	 * поставить потом в private
	 *
	 * @return array
	 */
	public static function getRaw($type = null, $key = null)
	{
		if ($type) {
			if (isset($key)) {
				return self::$raw[$type][$key];
			}
			if (!isset(self::$raw[$type])) {
				self::$raw[$type] = array();
			}
			return self::$raw[$type];
		}
		return self::$raw;
	}

	/**
	 * Функция загрузки моделей, работает только для SELECT
	 *
	 * @param Model $object
	 */
	public static function load($object)
	{
		$uniqName = get_class($object) . '@' .
			implode(':', array_keys($object->getFields()));
		//echo $uniqName . '<br />';
		self::buildOne($uniqName);
		self::_execute($uniqName);
		//self::reset();
	}

	/**
	 * Добавить запрос в очередь
	 *
	 * @param Query_Abstract $query
	 * @param Model $object модель, для возврата данных SELECT
	 * @param string|null $loaderName
	 */
	public static function push(Query_Abstract $query, $object = null, $loaderName = null)
	{
		$uowQuery = Unit_Of_Work_Manager::get($query);
		$uowQuery->push($query, $object, $loaderName);
		if (self::$autoFlush && self::$autoFlush == self::$rawCount) {
			self::flush();
		}
		self::$rawCount++;
	}

	/**
	 * Добавить запрос в очередь
	 *
	 * @param string $query
	 */
	public static function pushQuery($query, $key = null)
	{
		self::$queries[$key] = $query;
	}

	/**
	 * Добавить необработанные данные
	 *
	 * @param string $type тип запроса
	 * @param string $table таблица запроса
	 * @param array $data данные запроса
	 */
	public static function pushRaw($type, $uniqName, $data, $loaderName = null)
	{
		if (!isset(self::$raw[$type])) {
			self::$raw[$type] = array();
		}
		if (!isset(self::$raw[$type][$uniqName])) {
			self::$raw[$type][$uniqName] = array();
		}
		if ($loaderName) {
			if (!isset(self::$raw[$type][$uniqName])) {
				self::$raw[$type][$uniqName][$loaderName] = array();
			}
			self::$raw[$type][$uniqName][$loaderName][] = $data;
		} else {
			self::$raw[$type][$uniqName][] = $data;
		}
	}

	/**
	 * Сброс
	 */
	private static function reset()
	{
		self::$queries = array();
		self::$raw = array();
		self::$rawCount = 0;
	}
}