<?php
/**
 * 
 * @desc Базовая схема рейтинга.
 * @author Юрий
 * @package IcEngine
 *
 */
class Component_Rating_Scheme_Abstract extends Model_Factory_Delegate
{
	
	/**
	 * @desc Изменение рейтинга
	 * @param string $table Модель
	 * @param integer $row_id Запись
	 * @param integer $value Изменение рейтинга.
	 * Может быть величиной изменения или типом, в зависимости от схемы.
	 * @return Component_Rating
	 */
	public function vote ($table, $row_id, $value)
	{
		$rating = Model_Manager::byQuery (
			'Component_Rating',
			Query::instance ()
			->where ('table', $table)
			->where ('rowId', $row_id)
		);
		
		return $rating->increment ($value);
	}
	
}