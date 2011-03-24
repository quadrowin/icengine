<?php
/**
 * 
 * @desc Абстрактный класс опции коллекции.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
Loader::load ('Model_Collection_Option');
abstract class Model_Collection_Option_Abstract extends Model_Collection_Option
{
	
	final public function __construct ()
	{
		
	}
	
	/**
	 * Вызывается после выполения запроса.
	 * @param Model_Collection $collection
	 * @param Query $query
	 * @param array $params
	 */
	public function after (Model_Collection $collection, 
		Query $query, array $params)
	{
		
	}
		
	/**
	 * Вызывается перед выполнением запроса.
	 * Переменная <i>$query</i> отличается от запроса, возвращаемого методом
	 * <i>$colleciton->query()</i>. По умолчанию эта переменная - клон
	 * изначального запроса коллекции, на который наложены опции.
	 * @param Model_Collection $collection
	 * @param Query $query
	 * @param array $params
	 */
	public function before (Model_Collection $collection, 
		Query $query, array $params)
	{
		
	}
	
}