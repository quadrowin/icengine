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
		Loader::load ('Component_Rating_Log');
		$log = new Component_Rating_Log (array (
			'table'		=> $this->table,
			'rowId'		=> $this->rowId,
			'change'	=> $change,
			'ip'		=> Request::ip (),
			'User__id'	=> User::id (),
			'time'		=> Helper_Date::toUnix ()
		));
		
		$this->update (array (
			'value'			=> $this->value + $change,
			'votes'			=> $this->votes + 1,
			'changeTime'	=> Helper_Date::toUnix ()
		));
		
		return $this;
	}
	
}