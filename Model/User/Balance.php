<?php
/**
 * 
 * @desc Модель баланса пользователя
 * @author Гурус
 * @package IcEngine
 *
 */
class User_Balance extends Model
{
	
	/**
	 * 
	 * @param integer|User $user
	 * @return User_Balance
	 */
	public static function getFor ($user)
	{
		$balance = IcEngine::$modelManager->modelByKey (
			'User_Balance', 
			is_object ($user) ? $user->key () : $user
		);
		
		if (!$balance)
		{
			if (is_numeric ($user))
			{
				$user = IcEngine::$modelManager->modelByKey ('User', $user);
			}
			
			if (!$user)
			{
				return null;
			}
			
			$balance = new User_Balance (array (
				'id'			=> $user->key (),
				'User__id'		=> $user->key (),
				'value'			=> 0
			));
			$balance->save (true);
		}
		
		return $balance;
	}
	
	/**
	 * @desc Увеличить баланс пользователя
	 * @param integer|User $user Пользователь или id
	 * @param integer $value Значение.
	 * @param string $comment Комментарий.
	 * @return User_Balance_Log
	 */
	public static function incrementFor ($user, $value, $comment = '')
	{
		Loader::load ('User_Balance_Log');
		$balance = self::getFor ($user);
		
		$log = User_Balance_Log::addLog ($user->key (), $value, $comment);
		$balance->update (array (
			'value'	=> $balance->value + $value
		));
		
		return $log;
	}
	
}