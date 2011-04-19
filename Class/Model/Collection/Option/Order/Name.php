<?php
/**
 * 
 * @desc Опция для добавления правила упорядочивания в обратном порядке.
 * Возможно передать поле для сортировки, если поле не передано, сортировка
 * будет идти по ключевому полю.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Collection_Option_Order_Name extends Model_Collection_Option_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see Model_Collection_Option_Abstract::before()
	 */
	public function before (Model_Collection $collection, 
		Query $query, array $params)
	{
		$field = '`' . $collection->modelName () . '`.`name`';
		
		$desc = 
			isset ($params ['order']) &&
			strtoupper ($params ['order']) == 'DESC'; 
		
		$query->order (array (
			$field => $desc ? Query::DESC : Query::ASC
		));
	}
	
}