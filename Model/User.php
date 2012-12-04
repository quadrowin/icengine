<?php
/**
 * 
 * @desc Модель пользователя.
 * Для корректной работы необходима модель User_Session.
 * @author Юрий
 * @package IcEngine
 *
 */
class User extends User_Abstract
{
	/*
	 * Имеет ли юзер доступ к админке
	 */
	public function hasAdminAccess()
	{
		if (!$this->_current || !$this->_current->id)
		{
			return false;
		}
		
		$config = self::config();
		
		if (empty($config['roles_to_admin_access']))
		{
			return false;
		}
		
		foreach ($config['roles_to_admin_access'] as $role)
		{
			if ($this->hasRole($role))
			{
				return true;
			}
		}
		return false;
	}
}