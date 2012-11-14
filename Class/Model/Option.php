<?php

/**
 * Абстрактный класс опций модели.
 *
 * @author goorus, morph
 */
abstract class Model_Option
{
	/**
	 * Коллекция, на которую наложен опшн
     *
	 * @var Model_Collection
	 */
	public $collection;

	/**
	 * Название опции
     *
	 * @var string
	 */
	public $name;

	/**
	 * Опции
     *
	 * @var array
	 */
	public $params;

	/**
	 * Имя части запроса
	 *
	 * @var string
	 */
	protected $queryName;

	/**
	 * Запрос, выполняемый коллекцией
     *
	 * Переменная $query отличается от запроса, возвращаемого методом
	 * <i>$colleciton->query()</i>. По умолчанию эта переменная - клон
	 * изначального запроса коллекции, на который наложены опции.
	 * @var Query
	 */
	public $query;

	/**
	 * Создает и возвращает опцию
	 */
	public function __construct(Model_Collection $collection, $params)
	{
		$class = get_class($this);
		$delim = '_Model_Option_';
		$pos = strrpos($class, $delim);
		$this->name = substr($class, $pos + strlen ($delim));
		$this->collection = $collection;
		$this->params = $params;
	}

	/**
	 * Вызывается после выполения запроса.
	 */
	public function after()
	{

	}

	/**
	 * Вызывается перед выполнением запроса.
	 */
	public function before()
	{
		if ($this->queryName) {
			$className = 'Query_Part_' . $this->queryName;
			$modelName = $this->collection->modelName();
			$queryPart = new $className($modelName, $this->params);
			$queryPart->inject($this->query);
		}
	}

	/**
	 * Создание опции
     *
	 * @param string $name
	 * @param Model_Collection $collection
	 * @param array $params
	 */
	public static function create($name, Model_Collection $collection, $params)
	{
		$class = self::getClassName($name, $collection);
		return new $class($collection, $params);
	}

	/**
	 * Возвращает название класса опции
     *
	 * @param string $option Название опции
	 * @param Model_Collection $collection Коллекция.
	 * @return string
	 */
	public static function getClassName($option, $collection)
	{
		$p = strpos($option, '::');
		if ($p === false) {
			// Опция этой модели
			return $collection->table() . '_Option_' . $option;
		} elseif ($p === 0) {
			// Базовые опции всех моделей, например '::Limit'
			return 'Model_Option_' . substr($option, $p + 2);
		}
		// Опция другой модели
		return substr($option, 0, $p) . '_Option_' . substr($option, $p + 2);
	}
}