<?php
/**
 * 
 * @desc Модель логов баланса
 * @author Гурус
 * @package IcEngine
 *
 */
class User_Balance_Log extends Model
{
	
	/**
	 * @desc Добавить запись в лог об изменении баланса
	 * @param integer $user_id
	 * @param integer $change
	 * @return User_Balance_Log
	 */
	public static function addLog ($user_id, $change)
	{
		$log = new User_Balance_Log (array (
			'time'			=> Helper_Date::toUnix (),
			'User__id'		=> $user_id,
			'change'		=> $change,
		));
		return $log->save ();
	}
	
}