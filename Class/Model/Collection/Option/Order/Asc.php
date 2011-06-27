<?php
/**
 * 
 * @desc Опция для добавления правила упорядочивания в порядке возрастания.
 * Возможно передать поле для сортировки, если поле не передано, сортировка
 * будет идти по ключевому полю.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Collection_Option_Order_Asc extends Model_Collection_Option_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see Model_Collection_Option_Abstract::before()
	 */
	public function before (Model_Collection $collection, 
		Query $query, array $params)
	{
		$field = isset ($params ['field']) ?
			$params ['field'] :
			(
				'`' . $collection->modelName () . '`.`' . 
				$collection->keyField () . '`'
			);
		
		$query->order (array ($field => Query::ASC));
	}
	
}