<?php

/**
 * 
 * @desc Рейтинг для любой сущности
 * @author Юрий
 * @package IcEngine
 *
 */
class Component_Rating extends Model_Component
{
	
	/**
	 * @desc Изменение рейтинга
	 * @param integer $change
	 * @return Component_Rating
	 */
	public function increment ($change)
	{
		$this->update (array (
			'value'			=> $this->value + $change,
			'changeTime'	=> Helper_Date::toUnix ()
		));
		
		return $this;
	}
	
}