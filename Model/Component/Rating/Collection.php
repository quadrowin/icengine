<?php
/**
 * 
 * @desc Компонент рейтинг для сущностей
 * @author Юрий
 * @package IcEngine
 * 
 */
class Component_Rating_Collection extends Component_Collection
{
	
	/**
	 * @desc Изменить рейтинг 
	 * @param string $table Модель
	 * @param integer $row_id Запись
	 * @param integer $change Изменение рейтинга
	 * @return Component_Rating Модель рейтинга
	 */
	public function vote ($table, $row_id, $change)
	{
		$rating = $this->first ();
		
		return $rating->increment ($change);
	}
	
}