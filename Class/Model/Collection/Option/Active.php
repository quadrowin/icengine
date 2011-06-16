<?php
/**
 * 
 * @desc Опция для выбора только активных моделей.
 * Если $params ['active'] == false, будут выбраны неактивные.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Collection_Option_Active extends Model_Collection_Option_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see Model_Collection_Option_Abstract::before()
	 */
	public function before (Model_Collection $collection, 
		Query $query, array $params)
	{
		if (isset ($params ['active']) && !$params ['active'])
		{
			$query->where ('`' . $collection->modelName () . '`.`active`', 0);
		}
		else
		{
			$query->where ('`' . $collection->modelName () . '`.`active`', 1);
		}
	}
	
}